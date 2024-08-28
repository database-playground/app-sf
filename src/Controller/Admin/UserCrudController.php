<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher
    ) {}


    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', "ID")->hideOnForm(),
            TextField::new('name', "Display name"),
            TextField::new('email', "Email"),
            ArrayField::new('roles', "Roles"),
            Field::new('password', "Password")
                ->onlyWhenCreating()
                ->setRequired(true)
                ->setFormType(PasswordType::class)
                ->setFormTypeOptions([
                    'mapped' => false,
                    'hash_property_path' => 'password',
                ]),
            Field::new("new-password", "New password")
                ->onlyWhenUpdating()
                ->setFormType(PasswordType::class)
                ->setFormTypeOptions([
                    'mapped' => false,
                    'hash_property_path' => 'password',
                ]),
            AssociationField::new('group_id', "Group"),
            DateTimeField::new('created_at', "Created at")->hideOnForm(),
            DateTimeField::new('updated_at', "Updated at")->hideOnForm(),
        ];
    }
}
