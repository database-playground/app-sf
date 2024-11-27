<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SqlRunnerDto\SqlRunnerRequest;
use App\Entity\SqlRunnerDto\SqlRunnerResponse;
use App\Entity\SqlRunnerDto\SqlRunnerResult;
use App\Exception\SqlRunner\QueryExecuteException;
use App\Exception\SqlRunner\RunnerException;
use App\Exception\SqlRunner\SchemaExecuteException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class SqlRunnerService
{
    /**
     * @var array<string, mixed> the context for the serializer
     */
    private array $context;

    public function __construct(
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer,
        private string $baseUrl,
    ) {
        $this->context = (new ObjectNormalizerContextBuilder())
            ->withAllowExtraAttributes(false)
            ->withRequireAllProperties()
            ->toArray();
    }

    /**
     * Run the query in the remote SQL runner.
     *
     * @param SqlRunnerRequest $request The request to run the query
     *
     * @returns SqlRunnerResult The result of the query
     *
     * @throws QueryExecuteException  When the query execution fails
     * @throws SchemaExecuteException When the schema execution fails
     * @throws RunnerException        When the runner fails (internal error or client error)
     */
    public function runQuery(SqlRunnerRequest $request): SqlRunnerResult
    {
        $endpoint = $this->baseUrl.'/query';

        try {
            $response = $this->httpClient->request('POST', $endpoint, [
                'json' => (array) $request,
                'headers' => [
                    'User-Agent' => 'dbrunner/v1',
                ],
            ]);
            $content = $response->getContent(false);
        } catch (\Throwable $e) {
            throw new RunnerException('CLIENT_ERROR', $e->getMessage(), previous: $e);
        }

        try {
            $response = $this->serializer->deserialize(
                $content,
                SqlRunnerResponse::class,
                'json',
                $this->context,
            );
        } catch (\Throwable $e) {
            throw new RunnerException('PROTOCOL_ERROR', $e->getMessage(), $e);
        }

        if (!$response->isSuccess()) {
            \assert(null !== $response->getCode(), 'The code should not be null when response is not succeed.');
            \assert(null !== $response->getMessage(), 'The message should not be null when response is not succeed.');

            switch ($response->getCode()) {
                case 'QUERY_ERROR':
                    throw new QueryExecuteException($response->getMessage());
                case 'SCHEMA_ERROR':
                    throw new SchemaExecuteException($response->getMessage());
                default:
                    throw new RunnerException($response->getCode(), $response->getMessage());
            }
        }

        \assert(null !== $response->getData(), 'The data should not be null when response is succeed.');

        return $response->getData();
    }
}
