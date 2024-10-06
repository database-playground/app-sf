<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\FeedbackStatus;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FeedbackStatusFilter implements FilterInterface
{
    use FilterTrait;

    private const string filterBacklog = 'BACKLOG';
    private const string filterNewInProgress = 'NEW_IN_PROGRESS';
    private const string filterResolvedClosed = 'RESOLVED_CLOSED';

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $statusSet = match ($filterDataDto->getValue()) {
            self::filterBacklog => [FeedbackStatus::Backlog],
            self::filterNewInProgress => [FeedbackStatus::New, FeedbackStatus::InProgress],
            self::filterResolvedClosed => [FeedbackStatus::Resolved, FeedbackStatus::Closed],
            default => [],
        };

        $queryBuilder
            ->andWhere("{$filterDataDto->getEntityAlias()}.{$filterDataDto->getProperty()} IN (:status)")
            ->setParameter(
                'status',
                $statusSet,
            )
        ;
    }

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(self::class)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(ChoiceType::class)
            ->setFormTypeOptions([
                'choices' => [
                    'Backlog' => self::filterBacklog,
                    'New & In Progress' => self::filterNewInProgress,
                    'Resolved & Closed' => self::filterResolvedClosed,
                ],
            ])
        ;
    }
}
