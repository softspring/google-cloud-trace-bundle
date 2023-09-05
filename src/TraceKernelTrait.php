<?php

namespace Softspring\GoogleCloudTraceBundle;

use App\Trace\Tracer;
use Google\Cloud\Trace\Span;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait TraceKernelTrait
{
    protected ?Span $serverSpan = null;

    public function boot(): void
    {
        isset($_SERVER['REQUEST_URI']) && Tracer::start($this->serverSpan = Tracer::createServerSpan($_SERVER['REQUEST_URI']));
        Tracer::start($span = Tracer::createKernelSpan('kernel.boot'));
        parent::boot();
        Tracer::stop($span);
    }

    public function terminate(Request $request, Response $response): void
    {
        parent::terminate($request, $response);
        Tracer::stop($this->serverSpan);
        Tracer::send();
    }
}
