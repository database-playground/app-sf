<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Schema;
use App\Repository\SchemaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ComplementaryController extends AbstractController
{
    #[Route('/complementary', name: 'app_complementary')]
    public function index(SchemaRepository $schemaRepository): Response
    {
        return $this->render('complementary/index.html.twig', [
            'schemas' => $schemaRepository->findAll(),
        ]);
    }

    #[Route('/complementary/schema/{schema}', name: 'app_complementary_schema_retrieve')]
    public function retrieveSchema(Schema $schema): Response
    {
        return new Response($schema->getSchema(), Response::HTTP_OK, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$schema->getId()}.sql\"",
        ]);
    }
}
