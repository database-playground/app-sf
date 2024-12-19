<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\EmailTemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class EmailTemplateController extends AbstractController
{
    private readonly string $templateDir;

    public function __construct(
        private readonly EmailTemplateService $emailTemplateService,
        private readonly string $projectDir,
    ) {
        $this->templateDir = $this->projectDir.'/templates/email/mjml';
    }

    #[Route('/admin/email-template', name: 'admin_emailtemplate_index')]
    public function index(): Response
    {
        $templateFiles = glob($this->templateDir.'/*.mjml.twig');
        if (false === $templateFiles) {
            throw new \RuntimeException('Failed to list email templates.');
        }

        $templateFiles = array_map(
            static fn (string $file) => basename($file, '.mjml.twig'),
            $templateFiles
        );

        return $this->render('admin/email_template/index.html.twig', [
            'templates' => $templateFiles,
        ]);
    }

    #[Route('/admin/email-template/login-reminder', name: 'admin_emailtemplate_loginreminder')]
    public function loginReminder(): Response
    {
        $emailDto = $this->emailTemplateService->createLoginReminderDto([]);

        try {
            $content = $this->renderView('admin/email_template/preview.html.twig', [
                'emailDto' => $emailDto,
            ]);
            $error = null;
        } catch (\Throwable $e) {
            $content = null;
            $error = $e->getMessage();
        }

        return $this->render('admin/email_template/details.html.twig', [
            'name' => '登入提醒',
            'content' => $content,
            'error' => $error,
        ]);
    }
}
