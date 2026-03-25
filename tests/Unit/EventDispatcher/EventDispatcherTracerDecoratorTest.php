<?php

namespace Softspring\GoogleCloudTraceBundle\Tests\Unit\EventDispatcher;

use PHPUnit\Framework\TestCase;
use Softspring\GoogleCloudTraceBundle\EventDispatcher\EventDispatcherTracerDecorator;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherTracerDecoratorTest extends TestCase
{
    public function testDispatchForwardsEventToInnerDispatcher(): void
    {
        $dispatcher = new EventDispatcher();
        $decorator = new EventDispatcherTracerDecorator($dispatcher);
        $event = new stdClass();

        $called = false;
        $dispatcher->addListener('test.event', function (object $receivedEvent) use (&$called, $event): void {
            $called = true;
            self::assertSame($event, $receivedEvent);
        });

        $returnedEvent = $decorator->dispatch($event, 'test.event');

        self::assertTrue($called);
        self::assertSame($event, $returnedEvent);
    }
}
