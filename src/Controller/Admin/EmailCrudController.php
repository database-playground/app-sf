<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Email;
use App\Entity\EmailKind;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class EmailCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Email::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('subject'),
            TextEditorField::new('content'),
            ChoiceField::new('kind'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(ChoiceFilter::new('kind')->setTranslatableChoices([
            'email-kind.transactional' => EmailKind::Transactional,
            'email-kind.marketing' => EmailKind::Marketing,
        ]));
    }
}
