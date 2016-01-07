<?php

/**
 * quickcheck Module
 *
 * The quickcheck module is a module for entering microbial strain data into
 * a mysql database. The completed database can then be used to identify unknown
 * microbes. I also used this module as an example Zikula module to demonstrates
 * some of the frameworks functionality
 *
 * Purpose of file:  Table information for quickcheck module --
 *                   This file contains all information on database
 *                   tables for the module
 *
 * @package      None
 * @subpackage   Quickcheck
 * @version      2.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2009-2010 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

namespace Paustian\QuickcheckModule\Controller;

use Zikula\Core\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use ModUtil;
use SecurityUtil;
use CategoryUtil;
use DataUtil;
use Paustian\QuickcheckModule\Controller\AdminController;

class UserController extends AbstractController {

    /**
     * @Route("")
     * 
     * view
     * This routine allows for the user to perform the only function, creating a
     * quiz.
     * Using all (or a subset of) the questions available, create a multiple choice
     * quiz.
     *
     * Params: none
     * Returns: the quiz. This can be graded by the gradequiz funciton below
     */
    public function indexAction() {
        //securtiy check first
        if (!SecurityUtil::checkPermission('quickcheck::', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        list($properties, $propertiesdata) = $this->_getCategories();

        $categoryData = $propertiesdata[0]['subcategories'];

        return new Response($this->render('PaustianQuickcheckModule:User:quickcheck_user_index.html.twig', ['categories' => $categoryData]));
    }

    /**
     * Get the categories registered for the Pages
     *
     * @return array
     */
    private function _getCategories() {
        $categoryRegistry = \CategoryRegistryUtil::getRegisteredModuleCategories('PaustianQuickcheckModule', 'QuickcheckQuestionEntity');
        $properties = array_keys($categoryRegistry);
        $propertiesdata = array();
        foreach ($properties as $property) {
            $rootcat = CategoryUtil::getCategoryByID($categoryRegistry[$property]);
            if (!empty($rootcat)) {
                $rootcat['path'] .= '/';
                // add this to make the relative paths of the subcategories with ease - mateo
                $subcategories = CategoryUtil::getCategoriesByParentID($rootcat['id']);
                foreach ($subcategories as $k => $category) {
                    $subcategories[$k]['count'] = $this->countItems(array('category' => $category['id'], 'property' => $property));
                }
                $propertiesdata[] = array('name' => $property, 'rootcat' => $rootcat, 'subcategories' => $subcategories);
            }
        }

        return array($properties, $propertiesdata);
    }

    /**
     * utility function to count the number of items held by this module
     *
     * @param array $args Arguments.
     *
     * @return integer number of items held by this module
     */
    private function countItems($args) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.entitymanager');

        if (isset($args['category']) && !empty($args['category'])) {
            if (is_array($args['category'])) {
                $args['category'] = $args['category']['Main'][0];
            }
            $qb = $em->createQueryBuilder();
            $qb->select('count(p)')
                    ->from('Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity', 'p')
                    ->join('p.categories', 'c')
                    ->where('c.category = :categories')
                    ->setParameter('categories', $args['category']);

            return $qb->getQuery()->getSingleScalarResult();
        }
        $qb = $em->createQueryBuilder();
        $qb->select('count(p)')->from('Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity', 'p');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * sort_cat_array
     * Sort an array based on the sort value of the array
     * This is used to sort a category array before display
     * Date: July 11 2010
     * @author Timothy Paustian
     * @param array $a one value in the array
     * @param array $b secont value in array to compare
     * @return (0 if same, -1 if b less than a, 1 if b more than a)
     */
    static function sort_cat_array($a, $b) {
        if ($a['sort'] == $b['sort']) {
            return 0;
        }
        return ($a['sort'] < $b['sort']) ? -1 : 1;
    }

    /**
     * sort_by_id
     * This sorts the array of questions based upon what is in the
     * category id (sorts by chapter)
     * Date: July 11 2010
     * @author Timothy Paustian
     * @param array $a one value in the array
     * @param array $b secont value in array to compare
     * @return (0 if same, -1 if b less than a, 1 if b more than a)
     */
    static function sort_by_catid($a, $b) {
//stopped here. I need to sort the question by their cat id, makes it easy for picking them out
        $a_id = $a['__CATEGORIES__']['Main']['sort_value'];
        $b_id = $b['__CATEGORIES__']['Main']['sort_value'];
        if ($a_id == $b_id) {
            return 0;
        }
        return ($a_id < $b_id) ? -1 : 1;
    }

    /**
     * @Route("/createExam")
     * @Method("POST")
     * 
     * @param $request
     * @return Response
     */
    public function createExamAction(Request $request) {
        //you have to have edit access to do this
        if (!SecurityUtil::checkPermission('quickcheck::', "::", ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }


        $ret_url = $this->get('router')->generate('paustianquickcheckmodule_user_index', array(), RouterInterface::ABSOLUTE_URL);

        $num_quests = $request->request->get('num_questions', null);
        //now create the quiz

        $em = $this->getDoctrine()->getManager();
        // create a QueryBuilder instance
        $qb = $em->createQueryBuilder();
        // add select and from params
        $qb->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckQuestionEntity', 'u');
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();
        $questions = $query->getResult();
        //bin the questions into separate categories
        $bin_questions = $this->_binQuestionCategories($questions);
        $quiz_questions = array(); //the array that will hold the questions
        $random_questions = array(); //the random questions from a category
        foreach ($num_quests as $catid => $number_of_questions) {
            if (!is_numeric($number_of_questions) || ($number_of_questions < 0)) {
                $request->getSession()->getFlashBag()->add('error', $this->__('You need to pick the number of questions.'));
                return new RedirctResponse($ret_url);
            }
            if ($number_of_questions > 0) {
                //grab the random keys from the array of questions
                $random_questions = array_rand($bin_questions[$catid], $number_of_questions);
                if ($number_of_questions == 1) {
                    $the_question = $this->_unpackQuestion($bin_questions[$catid][$random_questions]);
                    $quiz_questions[] = $the_question;
                } else {
                    //now fill our array with these questions
                    foreach ($random_questions as $qIndex) {
                        $the_question = $this->_unpackQuestion($bin_questions[$catid][$qIndex]);
                        $quiz_questions[] = $the_question;
                    }
                }
            }
        }
        //shuffle the array to randomize the order in which they get asked.
        shuffle($quiz_questions);
        return $this->_render_quiz($quiz_questions, __('Practice Questions'), $ret_url);
    }

    /**
     * _binQuestionCategories - Given an array of questions bin them into categories based upon their category id.
     * 
     * @param type $questions
     * @return array
     */
    private function _binQuestionCategories($questions) {
        $binned_questions = array();
        foreach ($questions as $question) {
            $item = $question->getCategories();
            $aCollection = array_shift($item);
            $category = $aCollection->current();
            $reg_id = $category->getId();
            $binned_questions[$reg_id][] = $question;
        }
        return $binned_questions;
    }

    private function _unpackQuestion($in_question, $shuffle = true) {
        $type = $in_question->getQuickcheckqType();
        //We need to unpack this a bit to prepare it for display
        //We parse out the correct answer and put those in the param variable of the class
        if (($type == Admincontroller::_QUICKCHECK_MULTIPLECHOICE_TYPE) ||
                ($type == Admincontroller::_QUICKCHECK_MATCHING_TYPE) ||
                ($type == Admincontroller::_QUICKCHECK_MULTIANSWER_TYPE)) {
            $qAnswer = $in_question->getQuickcheckqAnswer();
            preg_match_all("|(.*)\|(.*)|", $qAnswer, $matches);
            $in_question->setQuickcheckqAnswer($matches[1]);
            if (($type == Admincontroller::_QUICKCHECK_MATCHING_TYPE) && $shuffle) {
                $param = array();
                $orig_array = $matches[2];
                $shuff_array = array_keys($matches[2]);
                shuffle($shuff_array);
                foreach ($shuff_array as $item) {
                    //The array item
                    $param[0][] = $orig_array[$item];
                    //It's original position.
                    $param[1][] = $item;
                }
                $in_question->setQuickcheckqParam($param);
            } else {
                $in_question->setQuickcheckqParam($matches[2]);
            }
        }

        return $in_question;
    }

    private function _fetch_cat_questions($in_questions, $in_cat_id) {
        $ret_questions = array();
        $started = false;
        foreach ($in_questions as $question) {
            if ($question['__CATEGORIES__']['Main']['id'] == $in_cat_id) {
                $started = true;
                $ret_questions[] = $question;
            } else if ($started) {
//we can break out of the loop now because we have collected all
//the questions (they are sorted)
                break;
            }
        }
        return $ret_questions;
    }

    /**
     * @Route("/display")
     * 
     * This displays an quiz from the database, or it displays a quiz set up by 
     * the student for self study.
     * 
     * Date: October 3 2010
     * @author Timothy Paustian
     * 
     * @param Request the exam info that holds the questions* 
     * @return Response
     *
     */
    public function displayAction(Request $request) {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('quickcheck::', '::', ACCESS_OVERVIEW)) {
            throw AccessDeniedException();
        }
        $exam = $request->request->get('exam', null);
        $return_url = $request->request->get('ret_url');
        $questions = array();
        //grab the questions
        foreach ($exam['questions'] as $quest) {
            $questions[] = modUtil::apiFunc('paustianquickcheckmodule', 'user', 'getquestion', array('id' => $quest));
        }
        return new Response($this->_display_quiz($questions, $exam['name'], $return_url));
    }

    /*
     * _render_quiz
     *
     * A private function that displays the quiz
     * Date: October 3 2010
     * @author Timothy Paustian
     * @param array $questions the questions to render
     * @param string $return_url the return url to go back to once the quiz is graded.
     * @param a text item for feedback to the quiz taker.
     * @return the text of the quiz.
     */

    private function _render_quiz($questions, $exam_name, $return_url = '', $notice = null) {
//we need to walk questions array and find all the matching questions and randomize the answers
        $total = count($questions);
        $q_ids = array();
        for ($i = 0; $i < $total; $i++) {
            $item = $questions[$i];
            $q_ids[] = $item['id'];
            if ($item['q_type'] == 3) {
//matching question, add a new parameter
                $ran_array = $item['q_answer'];
                shuffle($ran_array);
                $item['ran_array'] = $ran_array;
                $questions[$i] = $item;
            }
        }


        $sq_ids = DataUtil::formatForDisplay(serialize($q_ids));
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');

        return new Response($this->render('PaustianQuickcheckModule:User:quickcheck_user_renderexam.html.twig', ['letters' => $letters,
                    'q_ids' => $sq_ids,
                    'questions' => $questions,
                    'notice' => $notice,
                    'ret_url' => $return_url,
                    'exam_name' => $exam_name]));
    }

    /**
     * @Route("/gradeexam")
     * @Method("POST")
     * 
     * gradequizAction
     *
     * Here we get the information back from the quiz. We take this, extract the question ids first
     * and then find the right answer to each question. Each question answer comes back as an array, corresponding to
     * the question id. We can then compare this to the correct answer for each type.
     * @param $request
     */
    public function gradeexamAction(Request $request) {

        if (!SecurityUtil::checkPermission('quickcheck::', '::', ACCESS_OVERVIEW)) {
            throw AccessDeniedException();
        }
        $return_url = $request->request->get('ret_url', null);
        $sq_ids = $request->request->get('q_ids', null);
        $q_ids = unserialize($sq_ids);
        $score = 0;
        $display_questions = array();
        $student_answers = array();
        $em = $this->getDoctrine()->getManager();
        $correct = false;
        $ur_answer = "";
        foreach ($q_ids as $q_id) {
            $student_answer = $request->request->get($q_id, null);
            $question = $em->find('Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity', $q_id);

            if (!isset($student_answer)) {
                $student_answer = "";
            }
            switch ($question['quickcheckqtype']) {
                case AdminController::_QUICKCHECK_TEXT_TYPE:
                    $score += 1;
                    $correct = true;
                    $ur_answer = $student_answer;
                    //we don't grade text types
                    break;
                case AdminController::_QUICKCHECK_TF_TYPE:
                    if ($student_answer == $question->getQuickcheckqAnswer()) {
                        $score += 1;
                        $correct = true;
                    }
                    $ur_answer = $student_answer;
                    break;
                case AdminController::_QUICKCHECK_MATCHING_TYPE:
                    //I set this up so that if all the matches are correct
                    //the order returned will be in order.
                    $student_order = $request->request->get('order_' . $q_id);
                    parse_str($student_order, &$matches);
                    //walk the arrays comapring each value. I cannot use a php array
                    //function because position is important
                    $match_right = 0;
                    $size = count($matches);
                    for ($i = 0; $i < $size; $i++) {
                        if ($matches[$i] == $i) {
                            $match_right++;
                        }
                    }
                    $this_score = $match_right / $size;
                    if ($this_score >= 1) {
                        $question['correct'] = true;
                    }
                    $score += $this_score;
                    break;
                case AdminController::_QUICKCHECK_MULTIANSWER_TYPE:
                    //the student answer containg the position of the values that
                    //they entered. Award points for correct answers.
                    $total = 0;
                    if (is_array($student_answer)) {
                        foreach ($student_answer as $checked_item) {
                            $mc_answers = explode('_', $checked_item);
                            //you get points added if this is a correct mark
                            $total += $mc_answers[0];
                            $ur_answer[] = $mc_answers[1];
                        }
                        $score += $total / 100;
                    }
                    break;
                case AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE:
                    $mc_answers = explode('_', $student_answer);
                    $score += $mc_answers[0] / 100;
                    $ur_answer = $mc_answers[1];
                    break;
            }
//save the questions in an array for display.
            $display_questions[$q_id] = $question;
            $student_answers[$q_id]['uranswer'] = $ur_answer;
        }

//score is calculated, now I need to display it with the questions.
        /* $render = $this->view;
          $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
          $render->assign('letters', $letters);
          $render->assign('questions', $display_questions);
          $num_quest = count($display_questions);
          $score_percent = 100 * ($score / $num_quest);
          $render->assign('score', $score);
          $render->assign('score_percent', $score_percent);
          $render->assign('num_quest', $num_quest);
          return $render->fetch('User\quickcheck_admin_gradequiz.tpl'); */
    }

}

?>
