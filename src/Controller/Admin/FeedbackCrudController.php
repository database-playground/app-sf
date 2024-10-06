<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Feedback;
use App\Entity\FeedbackStatus;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

use function Symfony\Component\Translation\t;

class FeedbackCrudController extends AbstractCrudController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

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
        $actions->addBatchAction(
            Action::new('mark_resolved', icon: 'fa fa-check')
                ->linkToUrl(
                    $this
                        ->adminUrlGenerator
                        ->unsetAll()
                        ->setController(self::class)
                        ->setAction('markStatus')
                        ->set('status', FeedbackStatus::Resolved->value)
                        ->generateUrl()
                )
        );
        $actions->addBatchAction(
            Action::new('mark_closed', icon: 'fa fa-xmark')
                ->linkToUrl(
                    $this
                        ->adminUrlGenerator
                        ->unsetAll()
                        ->setController(self::class)
                        ->setAction('markStatus')
                        ->set('status', FeedbackStatus::Closed->value)
                        ->generateUrl()
                )
        );

        return $actions;
    }

    public function markStatus(
        BatchActionDto $batchActionDto,
        EntityManagerInterface $entityManager,
        #[MapQueryParameter] FeedbackStatus $status,
    ): Response {
        $repository = $entityManager->getRepository(Feedback::class);

        foreach ($batchActionDto->getEntityIds() as $entityId) {
            $feedback = $repository->find($entityId);
            if (null === $feedback) {
                continue;
            }

            $feedback->setStatus($status);
            $entityManager->persist($feedback);
        }

        $entityManager->flush();
        $this->addFlash('success', t('feedback.marked', ['%status%' => $status]));

        return $this->redirect($batchActionDto->getReferrerUrl());
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['createdAt' => 'DESC']);
    }
}
