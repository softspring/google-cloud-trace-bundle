<?php

namespace Softspring\GoogleCloudTraceBundle\Kernel;

use Softspring\GoogleCloudTraceBundle\Trace\Tracer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class HttpKernelTracerDecorator implements HttpKernelInterface, TerminableInterface
{
    protected HttpKernel $kernel;

    public function __construct(HttpKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
    {
        Tracer::start($requestSpan = Tracer::createKernelSpan($request->getPathInfo(), $request));
        Tracer::start($handleSpan = Tracer::createKernelSpan('kernel.handle', $request));
        $response = $this->kernel->handle($request, $type, $catch);
        Tracer::stop($handleSpan);
        Tracer::stop($requestSpan);

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        Tracer::start($span = Tracer::createKernelSpan('kernel.terminate', $request, $response));
        $this->kernel->terminate($request, $response);
        Tracer::stop($span);
    }
}
