<?php

namespace App\Service;

use App\Exception\QueryExecuteException;
use App\Exception\QueryExecuteServerException;
use Dbrunner\V1\DbRunnerServiceClient;
use Dbrunner\V1\RunQueryRequest;
use Dbrunner\V1\RunQueryResponse;
use Grpc\ChannelCredentials;
use stdClass;

use const Grpc\STATUS_INVALID_ARGUMENT;
use const Grpc\STATUS_OK;

readonly class DbRunnerService
{
    /**
     * @var DbRunnerServiceClient The gRPC client
     */
    private DbRunnerServiceClient $client;

    public function __construct(string $hostname)
    {
        $this->client = new DbRunnerServiceClient($hostname, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);
    }

    public function runQuery(string $schema, string $query): string
    {
        $response = $this->client->RunQuery(
            (new RunQueryRequest())
                ->setSchema($schema)
                ->setQuery($query)
        );

        list($body, $status) = $response->wait();

        assert($status instanceof stdClass);
        switch ($status->code) {
            case STATUS_OK:
                break;
            case STATUS_INVALID_ARGUMENT:
                throw new QueryExecuteException($status->details);
            default:
                throw new QueryExecuteServerException($status->details);
        };

        assert($body instanceof RunQueryResponse);
        if ($body->hasError()) {
            throw new QueryExecuteException($body->getError());
        }

        return $body->getId();
    }
}
