<?php

namespace App\Controller;

use App\Entity\SqlExecuteRequest;
use App\Form\SqlExecuteFormType;
use App\Service\DbRunnerService;
use App\Service\QueryResponse;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

class SqlExecuteController extends AbstractController
{
    public function __construct(
        protected readonly DbRunnerService $dbRunnerService
    ) {
    }

    #[Route('/sql/execute', name: 'app_sql_execute')]
    public function execute(LoggerInterface $logger, Request $request): Response
    {
        $sqlExecuteRequest = new SqlExecuteRequest();
        $form = $this->createForm(SqlExecuteFormType::class, $sqlExecuteRequest, [
            "action" => $this->generateUrl('app_sql_execute'),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $result = $this->dbRunnerService->runQuery($sqlExecuteRequest->getSchema(), $sqlExecuteRequest->getQuery());
            } catch (HttpException $e) {
                $error = $e->getMessage();
            }

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->renderBlock('sql_execute/index.html.twig', 'updated_state', [
                    'result' => isset($result) ? json_encode($result) : null,
                    'error' => isset($error) ? json_encode($error) : null,
                ]);
            }

            return $this->render('sql_execute/index.html.twig', [
                'form' => $form,
                'result' => isset($result) ? json_encode($result) : null,
                'error' => isset($error) ? json_encode($error) : null,
            ]);
        }

        return $this->render('sql_execute/index.html.twig', [
            'form' => $form,
            'result' => null,
        ]);
    }
}
