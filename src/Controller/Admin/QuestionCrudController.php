<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', "ID")->hideOnForm(),
            TextField::new('title', "Title"),
            TextEditorField::new('description', "Description"),
            AssociationField::new("schema", "Schema"),
            TextField::new('type', "Type")->setHelp("ex. 進階EXISTS指令應用"),
            ChoiceField::new('difficulty', "Difficulty"),
            CodeEditorField::new('answer', "Answer")->setLanguage("sql"),
            TextField::new('solution_video', "Solution video")->setFormType(UrlType::class),
        ];
    }
}
