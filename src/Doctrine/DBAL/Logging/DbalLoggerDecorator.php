<?php

namespace Softspring\GoogleCloudTraceBundle\Doctrine\DBAL\Logging;

use Doctrine\DBAL\Logging\SQLLogger;
use Google\Cloud\Trace\Span;
use Softspring\GoogleCloudTraceBundle\Trace\Tracer;

class DbalLoggerDecorator implements SQLLogger
{
    protected SQLLogger $logger;

    protected ?Span $span = null;

    public function __construct(SQLLogger $logger)
    {
        $this->logger = $logger;
    }

    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        // stop if there is a previous span without stop
        $this->span && Tracer::stop($this->span);

        $this->span = Tracer::createSpan('doctrine.query', ['sql' => $sql]);
        Tracer::start($this->span);

        $this->logger->startQuery($sql, $params, $types);
    }

    public function stopQuery(): void
    {
        Tracer::stop($this->span);
        $this->span = null;
        $this->logger->stopQuery();
    }
}
