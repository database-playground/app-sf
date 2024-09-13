<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Form\PasswordChangeModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class PasswordChangeFormType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'label' => $this->translator->trans('form.password_old'),
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => $this->translator->trans('form.password_new'),
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => $this->translator->trans('form.password_confirm'),
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('form.submit'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PasswordChangeModel::class,
        ]);
    }
}
