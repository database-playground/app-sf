<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Meilisearch\Bundle\SearchService;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            TextField::new('title', 'Title'),
            TextEditorField::new('description', 'Description'),
            AssociationField::new('schema', 'Schema'),
            TextField::new('type', 'Type')->setHelp('ex. 進階EXISTS指令應用'),
            ChoiceField::new('difficulty', 'Difficulty'),
            CodeEditorField::new('answer', 'Answer')->setLanguage('sql'),
            TextField::new('solution_video', 'Solution video')->setFormType(UrlType::class),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $reindex = Action::new('reindex', 'Reindex', 'fa fa-arrows-rotate')
            ->linkToCrudAction('reindex')
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $reindex);
    }

    public function reindex(
        AdminUrlGenerator $adminUrlGenerator,
        SearchService $searchService,
        QuestionRepository $questionRepository,
    ): Response {
        $questionRepository->reindex($searchService);
        $this->addFlash('success', t('questions.reindex.success'));

        return $this->redirect($adminUrlGenerator->setAction(Crud::PAGE_INDEX)->generateUrl());
    }
}
