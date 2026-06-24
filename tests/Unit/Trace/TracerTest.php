<?php

namespace Softspring\GoogleCloudTraceBundle\Tests\Unit\Trace;

use DateTimeImmutable;
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

    public function testStartAndStopReturnNullWithoutSpan(): void
    {
        self::assertNull(Tracer::start(null));
        self::assertNull(Tracer::stop(null));
    }

    public function testCreateHigherLevelSpansReturnNullWithoutTraceContext(): void
    {
        $request = Request::create('https://example.test/admin?page=2', 'POST');
        $response = new Response('created', 201);

        self::assertNull(Tracer::createEventSpan(new class($request, $response) {
            public function __construct(private readonly Request $request, private readonly Response $response)
            {
            }

            public function getRequest(): Request
            {
                return $this->request;
            }

            public function getResponse(): Response
            {
                return $this->response;
            }
        }, 'kernel.response'));
        self::assertNull(Tracer::createKernelSpan('kernel.handle', $request, $response));
        self::assertNull(Tracer::createServerSpan('https://example.test/admin'));
    }

    public function testGetServerAttributesReadServerValues(): void
    {
        $_SERVER['PHP_VERSION'] = '8.4.0';
        $_SERVER['REQUEST_URI'] = '/admin?page=2';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['QUERY_STRING'] = 'page=2';
        $_SERVER['HTTP_USER_AGENT'] = 'Unit Test';
        $_SERVER['HTTP_COOKIE'] = 'session=1';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
        $_SERVER['HTTP_ACCEPT'] = 'text/html';

        $attributes = $this->invokeTracerMethod('getServerAttributes');

        self::assertSame('8.4.0', $attributes['/server/php_version']);
        self::assertSame('/admin?page=2', $attributes['/http/url']);
        self::assertSame('POST', $attributes['/http/method']);
        self::assertSame('page=2', $attributes['/request/query_string']);
        self::assertSame('Unit Test', $attributes['/client/user_agent']);
        self::assertSame('session=1', $attributes['/client/cookie']);
        self::assertSame('en', $attributes['/client/accept_language']);
        self::assertSame('gzip', $attributes['/client/accept_encoding']);
        self::assertSame('text/html', $attributes['/client/accept']);
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

    public function testGetResponseAttributesIncludesCacheMetadata(): void
    {
        $response = new Response('hello trace');
        $response->setExpires(new DateTimeImmutable('2030-01-02 03:04:05'));
        $response->setDate(new DateTimeImmutable('2030-01-01 03:04:05'));
        $response->setLastModified(new DateTimeImmutable('2029-12-31 03:04:05'));
        $response->setTtl(300);
        $response->setMaxAge(120);
        $response->setSharedMaxAge(60);

        $attributes = $this->invokeTracerMethod('getResponseAttributes', $response);

        self::assertSame('2030-01-02 03:04:05', $attributes['/response/expires']);
        self::assertSame('2030-01-01 03:04:05', $attributes['/response/date']);
        self::assertSame('2029-12-31 03:04:05', $attributes['/response/last_modified']);
        self::assertSame(60, $attributes['/response/ttl']);
        self::assertSame('120', $attributes['/response/max_age']);
        self::assertSame('60', $attributes['/response/s_maxage']);
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
