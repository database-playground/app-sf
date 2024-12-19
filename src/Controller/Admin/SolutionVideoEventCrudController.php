<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SolutionVideoEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class SolutionVideoEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SolutionVideoEvent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            AssociationField::new('opener', 'Opener'),
            AssociationField::new('question', 'Question'),
            DateTimeField::new('createdAt', 'Created at'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('opener')
            ->add('question')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE, Action::EDIT, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }
}
