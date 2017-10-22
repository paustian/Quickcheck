<?php

namespace Paustian\QuickcheckModule\HookHandler;


use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\ServiceIdTrait;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * Copyright 2017 Timothy Paustian
 *
 * @license MIT
 *
 */


class UiHooksProviderHandler  implements HookProviderInterface
{
    use ServiceIdTrait;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * ProviderHandler constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(TranslatorInterface $translator,
                                RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->requestStack = $requestStack;

    }

    public function getOwner()
    {
        return 'PaustianQuickcheckModule';
    }

    public function getTitle()
    {
        return $this->translator->__('Quickcheck Display Provider');
    }

    public function getCategory()
    {
        return UiHooksCategory::NAME;
    }

    public function getProviderTypes()
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => 'uiView',
            UiHooksCategory::TYPE_PROCESS_DELETE => 'processDelete',
            UiHooksCategory::TYPE_PROCESS_EDIT => 'processEdit'
        ];
    }


    public function uiView(DisplayHook $hook)
    {
        $hook->setResponse(new DisplayHookResponse('provider.paustianquickcheckmodule.ui_hooks.quickcheck', $this->translator__('This is the quickcheck Response')));
    }

    public function processDelete(ProcessHook $hook)
    {

        $this->requestStack->getMasterRequest()->getSession()->getFlashBag()->add('success', 'Ui hook delete properly processed!');
    }

    public function processEdit(ProcessHook $hook)
    {
        $this->requestStack->getMasterRequest()->getSession()->getFlashBag()->add('success', 'Ui hook edit properly processed!');
    }
}
/*class UiHooksProviderHandler extends AbstractHookListener {


    public function display_view(DisplayHook $hook) {

        // Security check
        $is_admin = SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_ADMIN);
        $route_url = $hook->getUrl();
        if(isset($route_url)){
            $return_url = $route_url->getRoute();
        } else {
            $return_url = "";
            //You need to 
            //throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException($this->__("A return url must be supplied in the subscriber."));
        }
            $id = $hook->getId();
        $repo = $this->entityManager->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');
        $examObj = $repo->get_exam($id);
        $exams = null;
        $admininterface = "";
        if ($is_admin) {
            $qb2 = $this->entityManager->createQueryBuilder();

            // add select and from params
            $qb2->select('u')
                    ->from('PaustianQuickcheckModule:QuickcheckExamEntity', 'u', 'u.quickcheckname');
            $query2 = $qb2->getQuery();
            $exams = $query2->getResult();
            $admininterface = $this->renderEngine->render('PaustianQuickcheckModule:Hook:quickcheck.addquiz.html.twig', [
                'exams' => $exams,
                'art_id' => $id,
                'return_url' => $return_url]);
        }
        if (false === $examObj) {
            //Now just use the renderEngine to renger a twig template and send it as a string back as a response.
            if ($is_admin) {
                $content = $admininterface;
            } else {
                return null;
            }
        } else {
            //render the exam.
            $sq_ids = array();
            $letters = array();
            $questions = array();
            $examQuestions = $examObj->getQuickcheckquestions();
            $examName = $examObj->getQuickcheckname();

            $repo->render_quiz($examQuestions, $questions, $sq_ids, $letters);

            if (!$is_admin) {
                $admininterface = "";
            }
            $content = $this->renderEngine->render('PaustianQuickcheckModule:User:quickcheck_user_renderexam.html.twig', ['letters' => $letters,
                'q_ids' => $sq_ids,
                'questions' => $questions,
                'return_url' => $return_url,
                'exam_name' => $examName,
                'admininterface' => $admininterface]);
        }
        $response = new DisplayHookResponse(QuickcheckModuleVersion::QCPROVIDER_UIAREANAME, $content);
        $hook->setResponse($response);
    }

    public function process_delete(ProcessHook $hook) {
        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_DELETE)) {
            return;
        }
        $id = $hook->getId();
        if ($id) {
            $repo = $this->entityManager->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');
            $exam = $repo->get_exam($id);
            if (false !== $exam) {
                //set the refid to -1 so that we know no document is attached to this exam.
                $exam->setQuickcheckrefid(-1); 
                $this->entityManager->merge($exam);
                $this->entityManager->flush();
            }
        }
    }

}*/
