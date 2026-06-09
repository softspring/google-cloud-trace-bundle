<?php

namespace Softspring\GoogleCloudTraceBundle;

use Google\Cloud\Trace\Span;
use Softspring\GoogleCloudTraceBundle\Trace\Tracer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait TraceKernelTrait
{
    protected ?Span $serverSpan = null;

    public function boot(): void
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            Tracer::start($this->serverSpan = Tracer::createServerSpan($_SERVER['REQUEST_URI']));
        }
        Tracer::start($span = Tracer::createKernelSpan('kernel.boot'));

        try {
            parent::boot();
        } finally {
            Tracer::stop($span);
        }
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            parent::terminate($request, $response);
        } finally {
            Tracer::stop($this->serverSpan);
            Tracer::send();
        }
    }
}
