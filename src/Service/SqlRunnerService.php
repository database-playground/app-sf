<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SqlRunnerDto\SqlRunnerRequest;
use App\Entity\SqlRunnerDto\SqlRunnerResponse;
use App\Entity\SqlRunnerDto\SqlRunnerResult;
use App\Exception\SqlRunner\QueryExecuteException;
use App\Exception\SqlRunner\RunnerException;
use App\Exception\SqlRunner\SchemaExecuteException;
use App\Exception\SqlRunner\StructureMismatchException;
use App\Exception\SqlRunner\UnavailableException;
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
     * @throws UnavailableException       When the SQL runner is unavailable (cannot request)
     * @throws StructureMismatchException When the response structure does not match the expected structure
     * @throws QueryExecuteException      When the query execution fails
     * @throws SchemaExecuteException     When the schema execution fails
     * @throws RunnerException            When the runner fails (internal error?)
     */
    public function runQuery(SqlRunnerRequest $request): SqlRunnerResult
    {
        $endpoint = $this->baseUrl.'/query';

        try {
            $response = $this->httpClient->request('POST', $endpoint, [
                'json' => $request,
                'headers' => [
                    'User-Agent' => 'dbrunner/v1',
                ],
            ]);
            $content = $response->getContent();
        } catch (\Throwable $e) {
            throw new UnavailableException($e);
        }

        try {
            $response = $this->serializer->deserialize(
                $content,
                SqlRunnerResponse::class,
                'json',
                $this->context,
            );
        } catch (\Throwable $e) {
            throw new StructureMismatchException($e);
        }

        if (!$response->isSuccess()) {
            \assert(null !== $response->getCode(), 'The code should not be null when response is not succeed.');
            \assert(null !== $response->getMessage(), 'The message should not be null when response is not succeed.');

            switch ($response->getCode()) {
                case 'QUERY_ERROR':
                    throw new QueryExecuteException($response->getMessage());
                case 'SCHEMA_ERROR':
                    throw new SchemaExecuteException($response->getMessage());
                case 'BAD_PAYLOAD':
                    throw new StructureMismatchException(new RunnerException($response->getCode(), $response->getMessage()));
                default:
                    throw new RunnerException($response->getCode(), $response->getMessage());
            }
        }

        \assert(null !== $response->getData(), 'The data should not be null when response is succeed.');

        return $response->getData();
    }
}
