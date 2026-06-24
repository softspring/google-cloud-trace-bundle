<?php

namespace Softspring\GoogleCloudTraceBundle\Tests\Unit\Middleware;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Softspring\GoogleCloudTraceBundle\Middleware\ConnectionTracerMiddleware;

class ConnectionTracerMiddlewareTest extends TestCase
{
    public function testQueryAndExecDelegateToWrappedConnection(): void
    {
        if (!class_exists(AbstractConnectionMiddleware::class)) {
            self::markTestSkipped('Doctrine DBAL connection middleware is not available.');
        }

        $reflectionClass = new ReflectionClass(ConnectionTracerMiddleware::class);
        if (!$reflectionClass->isSubclassOf(AbstractConnectionMiddleware::class)) {
            self::markTestSkipped('Doctrine DBAL connection middleware is not available.');
        }

        $result = $this->createStub(Result::class);
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('query')->with('SELECT 1')->willReturn($result);
        $connection->expects(self::once())->method('exec')->with('DELETE FROM trace')->willReturn(3);

        $middleware = $reflectionClass->newInstance($connection);
        $middleware->{'setIncludeSql'}(true);

        self::assertSame($result, $middleware->{'query'}('SELECT 1'));
        self::assertSame(3, $middleware->{'exec'}('DELETE FROM trace'));
    }
}
