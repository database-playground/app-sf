<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\EmailEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EmailEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EmailEvent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnIndex()->setDisabled(),
            AssociationField::new('toUser'),
            TextField::new('toAddress')->hideOnIndex(),
            TextField::new('subject'),
            TextEditorField::new('content'),
            DateTimeField::new('createdAt', 'Created at')->setDisabled(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('toUser')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $previewAction = Action::new('preview', 'Preview', 'fa fa-eye')
            ->linkToUrl(fn (EmailEvent $entity) => $this->generateUrl(
                'app_email_preview',
                ['id' => $entity->getId()]
            ));

        return $actions
            ->disable(Action::DELETE, Action::EDIT, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $previewAction)
            ->add(Crud::PAGE_DETAIL, $previewAction);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['createdAt' => 'DESC']);
    }
}
