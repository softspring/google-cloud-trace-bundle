<?php

namespace Softspring\GoogleCloudTraceBundle\HttpCache;

use Softspring\GoogleCloudTraceBundle\Trace\Tracer;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class HttpCacheTracer extends HttpCache
{
    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
    {
        Tracer::start($requestSpan = Tracer::createKernelSpan($request->getPathInfo(), $request));
        Tracer::start($handleSpan = Tracer::createKernelSpan('kernel_cache.handle', $request));
        $response = parent::handle($request, $type, $catch);
        Tracer::stop($handleSpan);
        Tracer::stop($requestSpan);

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        Tracer::start($span = Tracer::createKernelSpan('kernel_cache.terminate', $request, $response));
        parent::terminate($request, $response);
        Tracer::stop($span);
    }
}
