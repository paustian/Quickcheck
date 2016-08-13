<?php

namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Paustian\QuickcheckModule\Controller\AdminController;
/**
 * Description of QuiccheckTFQuestion
 * Set up the elements for a TF form.
 *
 * @author paustian
 * 
 */
class MCQuestion extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quickcheckqtext', TextareaType::class, array('label' => __('Question'), 'required' => true))
            ->add('quickcheckqanswer', TextareaType::class, array('label' => __('Answer'), 'required' => true))
            ->add('quickcheckqexpan', TextareaType::class, array('label' => __('Explanation'), 'required' => true))
            ->add('save', SubmitType::class, array('label' => 'Save Question'));
        $builder->add('cancel', ButtonType::class, array('label' => __('Cancel')));
        
        $builder->add('quickcheckqtype', HiddenType::class, array('data' => AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE));

        $builder->add('categories', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
            'required' => false,
            'multiple' => false,
            'module' => 'PaustianQuickcheckModule',
            'entity' => 'QuickcheckQuestionEntity',
            'entityCategoryClass' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory',
        ]);
    }

    public function getName()
    {
        return 'paustianquickcheckmodule_mcquesiton';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity',
        ));
    }
}