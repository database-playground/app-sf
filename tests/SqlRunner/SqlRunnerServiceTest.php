<?php

declare(strict_types=1);

namespace App\Tests\SqlRunner;

use App\Entity\SqlRunnerDto\SqlRunnerRequest;
use App\Entity\SqlRunnerDto\SqlRunnerResponse;
use App\Entity\SqlRunnerDto\SqlRunnerResult;
use App\Exception\QueryExecuteException;
use App\Exception\SchemaExecuteException;
use App\Exception\SqlRunnerException;
use App\Service\SqlRunnerService;
use Monolog\Test\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class SqlRunnerServiceTest extends TestCase
{
    public function testRunQueryClientError(): void
    {
        $this->expectException(SqlRunnerException::class);
        $this->expectExceptionMessageMatches('/^CLIENT_ERROR: /');

        $httpClient = self::createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willThrowException(new TransportException())
        ;

        $serializer = self::createMock(SerializerInterface::class);
        $serializer
            ->expects(self::never())
            ->method('deserialize')
        ;

        $sqlRunnerService = new SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new SqlRunnerRequest());
    }

    public function testRunQueryProtocolError(): void
    {
        $this->expectException(SqlRunnerException::class);
        $this->expectExceptionMessageMatches('/^PROTOCOL_ERROR: /');

        $httpClient = self::createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn(self::createMock(ResponseInterface::class))
        ;

        $serializer = self::createMock(SerializerInterface::class);
        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->willThrowException(new NotNormalizableValueException())
        ;

        $sqlRunnerService = new SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new SqlRunnerRequest());
    }

    public function testRunQueryRunnerException(): void
    {
        $this->expectException(SqlRunnerException::class);
        $this->expectExceptionMessage('INTERNAL_ERROR: Internal error');

        $httpClient = self::createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn(self::createMock(ResponseInterface::class))
        ;

        $serializer = self::createMock(SerializerInterface::class);
        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->willReturn(
                (new SqlRunnerResponse())
                    ->setSuccess(false)
                    ->setCode('INTERNAL_ERROR')
                    ->setMessage('Internal error')
            )
        ;

        $sqlRunnerService = new SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new SqlRunnerRequest());
    }

    public function testRunQueryQueryException(): void
    {
        $this->expectException(QueryExecuteException::class);
        $this->expectExceptionMessage('Query error');

        $httpClient = self::createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn(self::createMock(ResponseInterface::class))
        ;

        $serializer = self::createMock(SerializerInterface::class);
        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->willReturn(
                (new SqlRunnerResponse())
                    ->setSuccess(false)
                    ->setCode('QUERY_ERROR')
                    ->setMessage('Query error')
            )
        ;

        $sqlRunnerService = new SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new SqlRunnerRequest());
    }

    public function testRunQuerySchemaException(): void
    {
        $this->expectException(SchemaExecuteException::class);
        $this->expectExceptionMessage('Schema error');

        $httpClient = self::createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn(self::createMock(ResponseInterface::class))
        ;

        $serializer = self::createMock(SerializerInterface::class);
        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->willReturn(
                (new SqlRunnerResponse())
                    ->setSuccess(false)
                    ->setCode('SCHEMA_ERROR')
                    ->setMessage('Schema error')
            )
        ;

        $sqlRunnerService = new SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new SqlRunnerRequest());
    }

    public function testRunQueryBadPayload(): void
    {
        $this->expectException(SqlRunnerException::class);
        $this->expectExceptionMessageMatches('/^BAD_PAYLOAD: /');

        $httpClient = self::createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn(self::createMock(ResponseInterface::class))
        ;

        $serializer = self::createMock(SerializerInterface::class);
        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->willReturn(
                (new SqlRunnerResponse())
                    ->setSuccess(false)
                    ->setCode('BAD_PAYLOAD')
                    ->setMessage('Bad Payload')
            )
        ;

        $sqlRunnerService = new SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new SqlRunnerRequest());
    }

    public function testRunQuerySuccess(): void
    {
        $httpClient = self::createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn(self::createMock(ResponseInterface::class))
        ;

        $result = (new SqlRunnerResult())
            ->setColumns(['column1', 'column2'])
            ->setRows([['row1', 'row2']])
        ;

        $serializer = self::createMock(SerializerInterface::class);
        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->willReturn(
                (new SqlRunnerResponse())
                    ->setSuccess(true)
                    ->setData($result)
            )
        ;

        $sqlRunnerService = new SqlRunnerService($httpClient, $serializer, '');
        $result = $sqlRunnerService->runQuery(new SqlRunnerRequest());
        self::assertSame(['column1', 'column2'], $result->getColumns());
    }
}
