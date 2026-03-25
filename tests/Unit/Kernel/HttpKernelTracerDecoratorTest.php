<?php

namespace Softspring\GoogleCloudTraceBundle\Tests\Unit\Kernel;

use PHPUnit\Framework\TestCase;
use Softspring\GoogleCloudTraceBundle\Kernel\HttpKernelTracerDecorator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;

class HttpKernelTracerDecoratorTest extends TestCase
{
    public function testHandleAndTerminateDelegateToInnerKernel(): void
    {
        $kernel = $this->getMockBuilder(HttpKernel::class)
            ->setConstructorArgs([
                new EventDispatcher(),
                new ControllerResolver(),
                null,
                new ArgumentResolver(),
            ])
            ->onlyMethods(['handle', 'terminate'])
            ->getMock();

        $kernel->expects(self::once())
            ->method('handle')
            ->willReturn(new Response('ok'));

        $kernel->expects(self::once())
            ->method('terminate');

        $decorator = new HttpKernelTracerDecorator($kernel);
        $request = Request::create('/demo');

        $response = $decorator->handle($request);
        $decorator->terminate($request, $response);

        self::assertSame('ok', $response->getContent());
    }
}
