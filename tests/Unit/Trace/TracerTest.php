<?php

namespace Softspring\GoogleCloudTraceBundle\Tests\Unit\Trace;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Softspring\GoogleCloudTraceBundle\Trace\Tracer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TracerTest extends TestCase
{
    protected function setUp(): void
    {
        unset($_SERVER['HTTP_TRACEPARENT']);
        $this->resetTracerState();
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_TRACEPARENT']);
        $this->resetTracerState();
    }

    public function testCreateSpanReturnsNullWithoutTraceContext(): void
    {
        self::assertNull(Tracer::createSpan('test'));
    }

    public function testGetRequestAttributesIncludesRouteInformation(): void
    {
        $request = Request::create('https://example.test/admin?page=2', 'POST');
        $request->attributes->set('_route', 'admin_dashboard');

        $attributes = $this->invokeTracerMethod('getRequestAttributes', $request);

        self::assertSame('https://example.test/admin?page=2', $attributes['/http/url']);
        self::assertSame('POST', $attributes['/http/method']);
        self::assertSame('https', $attributes['/http/scheme']);
        self::assertSame('admin_dashboard', $attributes['/symfony/route']);
    }

    public function testGetResponseAttributesCalculatesContentLengthWhenHeaderIsMissing(): void
    {
        $response = new Response('hello trace', 201);
        $response->setProtocolVersion('1.1');

        $attributes = $this->invokeTracerMethod('getResponseAttributes', $response);

        self::assertSame(201, $attributes['/http/status_code']);
        self::assertSame('1.1', $attributes['/http/flavour']);
        self::assertSame(11, $attributes['/http/response_content_length']);
    }

    /**
     * @return array<string, mixed>
     */
    private function invokeTracerMethod(string $method, mixed ...$arguments): array
    {
        $reflectionMethod = new ReflectionMethod(Tracer::class, $method);
        $reflectionMethod->setAccessible(true);

        /** @var array<string, mixed> $result */
        $result = $reflectionMethod->invoke(null, ...$arguments);

        return $result;
    }

    private function resetTracerState(): void
    {
        foreach (['traceClient', 'trace', 'traceSpans', 'parentSpanStack'] as $property) {
            $reflectionProperty = new ReflectionProperty(Tracer::class, $property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue(null, in_array($property, ['traceSpans', 'parentSpanStack'], true) ? [] : null);
        }
    }
}
