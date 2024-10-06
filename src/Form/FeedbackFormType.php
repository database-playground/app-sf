<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Feedback;
use App\Entity\FeedbackType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\TextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class FeedbackFormType extends AbstractType
{
    public function __construct(
        private readonly FeedbackMetadataModelTransformer $metadataModelTransformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sender', TextType::class, [
                'label' => t('feedback.form.account'),
                'disabled' => true,
            ])
            ->add('type', EnumType::class, [
                'class' => FeedbackType::class,
                'label' => t('feedback.form.type'),
            ])
            ->add('title', TextType::class, [
                'label' => t('feedback.form.subject'),
            ])
            ->add('description', TextEditorType::class, [
                'label' => t('feedback.form.description'),
                'help' => t('feedback.form.description_help'),
                'help_html' => true,
            ])
            ->add('contact', TextType::class, [
                'label' => t('feedback.form.contact'),
                'help' => t('feedback.form.contact_help'),
            ])
            ->add('metadata', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => t('feedback.form.submit'),
            ])
        ;

        $builder->get('metadata')
            ->addModelTransformer($this->metadataModelTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}
