<?php

namespace App\Controller\Admin;

use App\Entity\Schema;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SchemaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Schema::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', "ID"),
            TextField::new('picture', "Picture"),
            TextEditorField::new('description', "Description"),
            CodeEditorField::new('schema', "Schema SQL")->setLanguage("sql"),
        ];
    }
}
