<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class EmailTemplateController extends AbstractController
{
    private readonly string $templateDir;

    public function __construct(
        private readonly string $projectDir,
    ) {
        $this->templateDir = $this->projectDir.'/templates/email/mjml';
    }

    #[Route('/admin/email-template', name: 'app_admin_emailtemplate_index')]
    public function index(): Response
    {
        $templateFiles = glob($this->templateDir.'/*.mjml.twig');
        if (false === $templateFiles) {
            throw new \RuntimeException('Failed to list email templates.');
        }

        $templateFiles = array_map(
            fn (string $file) => basename($file, '.mjml.twig'),
            $templateFiles
        );

        return $this->render('admin/email_template/index.twig', [
            'templates' => $templateFiles,
        ]);
    }

    #[Route('/admin/email-template/{name}', name: 'app_admin_emailtemplate_details')]
    public function details(string $name, Request $request): Response
    {
        $parametersJSON = $request->query->get('parameters', '{}');
        $parameters = json_decode($parametersJSON, true);

        if (!\is_array($parameters)) {
            throw new \InvalidArgumentException('The parameters must be a valid JSON object.');
        }

        try {
            $content = $this->renderView("email/mjml/$name.mjml.twig", $parameters);
            $error = null;
        } catch (\Throwable $e) {
            $content = null;
            $error = $e->getMessage();
        }

        return $this->render('admin/email_template/details.twig', [
            'name' => $name,
            'parameters' => $parameters,
            'content' => $content,
            'error' => $error,
        ]);
    }
}
