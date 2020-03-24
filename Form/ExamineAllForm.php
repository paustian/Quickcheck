<?php
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\Translator\TranslatorInterface;
/**
 * Description of CategorizeForm
 * Set up the elements for a Exam form. A simple forms
 *
 * @author paustian
 *
 */
class ExamineAllForm extends AbstractType {

    /**
     * @var TranslatorInterface
     */
    private $translator;



    /**
     * BlockType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('list', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => $this->translator->__('List Questions')))
            ->add('cancel', \Symfony\Component\Form\Extension\Core\Type\ButtonType::class, array('label' => $this->translator->__('Cancel')));


        $builder->add('categories', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
            'required' => false,
            'multiple' => false,
            'module' => 'PaustianQuickcheckModule',
            'entity' => 'QuickcheckQuestionEntity',
            'entityCategoryClass' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory',
        ]);

        $builder->add('searchtext', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array('label' => $this->translator->__('Search Text'), 'required' => false));
    }

    public function getPrefixName()
    {
        return 'paustianquickcheckmodule_categorizeform';
    }
}