<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Group;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use function Symfony\Component\Translation\t;

class GroupCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Group::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $availableLayouts = glob(__DIR__.'/../../../templates/overview/layout/*.html.twig');
        if (false === $availableLayouts) {
            $availableLayouts = [];
        }

        $availableLayouts = array_map(static fn ($path) => pathinfo($path, PATHINFO_BASENAME), $availableLayouts);
        if (0 === \count($availableLayouts)) {
            $availableLayouts = ['default.html.twig'];
        }

        // trim the .html.twig extension
        $availableLayouts = array_map(static fn ($layout) => substr($layout, 0, -10), $availableLayouts);

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextEditorField::new('description', 'Description'),
            ChoiceField::new('layout', 'Layout')
                ->setChoices(array_combine($availableLayouts, $availableLayouts))
                ->setHelp(t('admin.group.layout.help')),
            DateTimeField::new('created_at', 'Created at')->hideOnForm(),
            DateTimeField::new('updated_at', 'Updated at')->hideOnForm(),
        ];
    }
}
