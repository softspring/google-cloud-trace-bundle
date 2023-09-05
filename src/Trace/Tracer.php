<?php

namespace Softspring\GoogleCloudTraceBundle\Trace;

use Google\Cloud\Trace\Span;
use Google\Cloud\Trace\Trace;
use Google\Cloud\Trace\TraceClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Tracer
{
    protected static ?TraceClient $traceClient = null;
    protected static ?Trace $trace = null;
    protected static array $traceSpans = [];
    protected static array $parentSpanStack = [];

    public static function send(): void
    {
        if (!self::$traceClient) {
            return;
        }

        /** @var Span $traceSpan */
        foreach (self::$traceSpans as $traceSpan) {
            if (!$traceSpan->endTime()) {
                $traceSpan->setEndTime();
            }
        }

        self::$trace->setSpans(self::$traceSpans);
        self::$traceClient->insert(self::$trace);
    }

    public static function start(?Span $span): ?Span
    {
        if (!$span) {
            return null;
        }

        sizeof(self::$parentSpanStack) && $span->setParentSpanId(end(self::$parentSpanStack));
        $span->setStartTime();
        array_push(self::$parentSpanStack, $span->spanId());

        return $span;
    }

    public static function stop(?Span $span): ?Span
    {
        if (!$span) {
            return null;
        }

        $span->setEndTime();

        array_pop(self::$parentSpanStack);

        return $span;
    }

    protected static function initTrace(): void
    {
        if (self::$trace) {
            return; // also init
        }

        if (!isset($_SERVER['HTTP_TRACEPARENT'])) {
            return; // do not init
        }

        if (!self::$traceClient) {
            self::$traceClient = new TraceClient();
        }

        $traceId = explode('-', $_SERVER['HTTP_TRACEPARENT'])[1];
        array_push(self::$parentSpanStack, explode('-', $_SERVER['HTTP_TRACEPARENT'])[2]); // comment to ignore main span

        self::$trace = self::$traceClient->trace($traceId);
    }

    protected static function createSpan(string $name, array $attributes): ?Span
    {
        self::initTrace();

        if (!self::$traceClient) {
            return null;
        }

        self::$traceSpans[] = $span = self::$trace->span([
            'name' => $name,
        ]);

        foreach ($attributes as $key => $attribute) {
            $span->addAttribute($key, $attribute);
        }

        return $span;
    }

    public static function createEventSpan(object $event, string $eventName = null): ?Span
    {
        $attributes = [];
        method_exists($event, 'getRequest') && $event->getRequest() instanceof Request && $attributes = array_merge($attributes, self::getRequestAttributes($event->getRequest()));
        method_exists($event, 'getResponse') && $event->getResponse() instanceof Response && $attributes = array_merge($attributes, self::getResponseAttributes($event->getResponse()));

        return self::createSpan($eventName ?? get_class($event), $attributes);
    }

    public static function createKernelSpan(string $name, Request $request = null, int $type = null): ?Span
    {
        $attributes = [];
        $request && $attributes = array_merge($attributes, self::getRequestAttributes($request));

        return self::createSpan($name, $attributes);
    }

    public static function createServerSpan(string $url): ?Span
    {
        $attributes = [];
        $attributes = array_merge($attributes, self::getServerAttributes());

        return self::createSpan($url, $attributes);
    }

    protected static function getServerAttributes(): array
    {
        return [
            '/server/php_version' => $_SERVER['PHP_VERSION'] ?? null,
            '/http/url' => $_SERVER['REQUEST_URI'] ?? null,
            '/http/method' => $_SERVER['REQUEST_METHOD'] ?? null,
            '/request/query_string' => $_SERVER['QUERY_STRING'] ?? null,
            '/client/user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            '/client/cookie' => $_SERVER['HTTP_COOKIE'] ?? null,
            '/client/accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null,
            '/client/accept_encoding' => $_SERVER['HTTP_ACCEPT_ENCODING'] ?? null,
            '/client/accept' => $_SERVER['HTTP_ACCEPT'] ?? null,
        ];
    }

    protected static function getRequestAttributes(Request $request): array
    {
        $attributes = [
            '/http/url' => $request->getUri(),
            '/http/method' => $request->getMethod(),
            '/http/request_content_length' => $request->headers->get('Content-Length'),
            '/http/scheme' => $request->getScheme(),
        ];

        if ($request->attributes->has('_route')) {
            $attributes['/symfony/route'] = $request->attributes->get('_route');
        }

        return $attributes;
    }

    protected static function getResponseAttributes(?Response $response): array
    {
        if (!$response) {
            return [];
        }

        $contentLength = $response->headers->get('Content-Length');
        /* @psalm-suppress PossiblyFalseArgument */
        if (null === $contentLength && is_string($response->getContent())) {
            $contentLength = \strlen($response->getContent());
        }

        return [
            '/http/status_code' => $response->getStatusCode(),
            '/http/flavour' => $response->getProtocolVersion(),
            '/http/response_content_length' => $contentLength,
        ];
    }
}
