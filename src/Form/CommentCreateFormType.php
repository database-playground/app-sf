<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Comment>
 */
class CommentCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextType::class, [
                'label' => '留言',
                'attr' => [
                    'placeholder' => '撰寫你的留言……',
                ],
                'label_attr' => [
                    'class' => 'visually-hidden',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => '<i class="bi bi-send"></i>',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary',
                    'aria-label' => '送出留言',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
