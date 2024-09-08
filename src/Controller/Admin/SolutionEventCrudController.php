<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SolutionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

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
        ];
    }
}
