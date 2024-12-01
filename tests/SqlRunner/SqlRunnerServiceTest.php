<?php

declare(strict_types=1);

namespace App\Tests\SqlRunner;

use App\Entity\SqlRunnerDto\SqlRunnerResponse;
use App\Exception\SqlRunnerException;
use Monolog\Test\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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

        $sqlRunnerService = new \App\Service\SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new \App\Entity\SqlRunnerDto\SqlRunnerRequest());
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
            ->willThrowException(new \Symfony\Component\Serializer\Exception\NotNormalizableValueException())
        ;

        $sqlRunnerService = new \App\Service\SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new \App\Entity\SqlRunnerDto\SqlRunnerRequest());
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

        $sqlRunnerService = new \App\Service\SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new \App\Entity\SqlRunnerDto\SqlRunnerRequest());
    }

    public function testRunQueryQueryException(): void
    {
        $this->expectException(\App\Exception\QueryExecuteException::class);
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

        $sqlRunnerService = new \App\Service\SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new \App\Entity\SqlRunnerDto\SqlRunnerRequest());
    }

    public function testRunQuerySchemaException(): void
    {
        $this->expectException(\App\Exception\SchemaExecuteException::class);
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

        $sqlRunnerService = new \App\Service\SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new \App\Entity\SqlRunnerDto\SqlRunnerRequest());
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

        $sqlRunnerService = new \App\Service\SqlRunnerService($httpClient, $serializer, '');
        $sqlRunnerService->runQuery(new \App\Entity\SqlRunnerDto\SqlRunnerRequest());
    }

    public function testRunQuerySuccess(): void
    {
        $httpClient = self::createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn(self::createMock(ResponseInterface::class))
        ;

        $result = (new \App\Entity\SqlRunnerDto\SqlRunnerResult())
            ->setColumns(['column1', 'column2'])
            ->setRows([['row1', 'row2']]);

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

        $sqlRunnerService = new \App\Service\SqlRunnerService($httpClient, $serializer, '');
        $result = $sqlRunnerService->runQuery(new \App\Entity\SqlRunnerDto\SqlRunnerRequest());
        self::assertEquals(['column1', 'column2'], $result->getColumns());
    }
}
