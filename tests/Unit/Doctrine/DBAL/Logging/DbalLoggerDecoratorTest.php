<?php

namespace Softspring\GoogleCloudTraceBundle\Tests\Unit\Doctrine\DBAL\Logging;

use PHPUnit\Framework\TestCase;
use Softspring\GoogleCloudTraceBundle\Doctrine\DBAL\Logging\DbalLoggerDecorator;

class DbalLoggerDecoratorTest extends TestCase
{
    public function testStartAndStopQueryDelegateToInnerLogger(): void
    {
        $logger = new class {
            public array $queries = [];
            public int $stoppedQueries = 0;

            public function startQuery(string $sql, ?array $params = null, ?array $types = null): void
            {
                $this->queries[] = [$sql, $params, $types];
            }

            public function stopQuery(): void
            {
                ++$this->stoppedQueries;
            }
        };

        $decorator = new DbalLoggerDecorator($logger);
        $decorator->setIncludeSql(true);
        $decorator->startQuery('SELECT 1', ['id' => 1], ['integer']);
        $decorator->stopQuery();

        self::assertSame([['SELECT 1', ['id' => 1], ['integer']]], $logger->queries);
        self::assertSame(1, $logger->stoppedQueries);
    }

    public function testMissingInnerLoggerMethodsAreIgnored(): void
    {
        $decorator = new DbalLoggerDecorator(new class {
        });

        $decorator->startQuery('SELECT 1');
        $decorator->stopQuery();

        $this->addToAssertionCount(1);
    }
}
