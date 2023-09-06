<?php

namespace Softspring\GoogleCloudTraceBundle\Doctrine\DBAL\Logging;

use Doctrine\DBAL\Logging\SQLLogger;
use Google\Cloud\Trace\Span;
use Softspring\GoogleCloudTraceBundle\Trace\Tracer;

class QueryTracerLogger implements SQLLogger
{
    protected ?Span $span = null;

    public function startQuery($sql, array $params = null, array $types = null): void
    {
        $this->span = Tracer::createSpan('doctrine.query', ['sql' => $sql]);
        Tracer::start($this->span);
    }

    public function stopQuery(): void
    {
        Tracer::stop($this->span);
        $this->span = null;
    }
}
