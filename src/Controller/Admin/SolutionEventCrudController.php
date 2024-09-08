<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SolutionEvent;
use App\Entity\SolutionEventStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class SolutionEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SolutionEvent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            AssociationField::new('submitter', 'Submitter'),
            AssociationField::new('question', 'Question'),
            ChoiceField::new('status', 'Status'),
            CodeEditorField::new('query')->setLanguage('sql'),
            DateTimeField::new('createdAt', 'Created at'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('submitter')
            ->add('question')
            ->add(
                ChoiceFilter::new('status')
                    ->setChoices([
                        'Passed' => SolutionEventStatus::Passed,
                        'Failed' => SolutionEventStatus::Failed,
                    ])
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE, Action::EDIT, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
