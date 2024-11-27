<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Feedback;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FeedbackCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Feedback::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnIndex()->setDisabled(),
            ChoiceField::new('type')->hideWhenUpdating(),
            AssociationField::new('sender')->hideWhenUpdating(),
            TextField::new('title')->hideWhenUpdating(),
            TextEditorField::new('description')->hideOnIndex()->hideWhenUpdating(),
            TextField::new('contact')->hideOnIndex()->hideWhenUpdating(),
            ArrayField::new('metadata')->hideOnIndex()->hideWhenUpdating(),
            TextareaField::new('comment', 'feedback.comment')->hideOnIndex(),
            ChoiceField::new('status'),
            DateTimeField::new('createdAt', 'Created at')->setDisabled(),
            DateTimeField::new('updatedAt', 'Updated at')->hideOnIndex()->setDisabled(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('sender')
            ->add('type')
            ->add(FeedbackStatusFilter::new('status'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        return $actions;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['createdAt' => 'DESC']);
    }
}
