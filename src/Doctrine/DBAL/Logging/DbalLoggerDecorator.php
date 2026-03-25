<?php

namespace Softspring\GoogleCloudTraceBundle\Doctrine\DBAL\Logging;

use Doctrine\DBAL\Logging\SQLLogger;
use Google\Cloud\Trace\Span;
use Softspring\GoogleCloudTraceBundle\Trace\Tracer;

if (interface_exists(SQLLogger::class)) {
    class DbalLoggerDecorator implements SQLLogger
    {
        protected object $logger;

        protected ?Span $span = null;

        public function __construct(object $logger)
        {
            $this->logger = $logger;
        }

        public function startQuery($sql, ?array $params = null, ?array $types = null): void
        {
            if (!method_exists($this->logger, 'startQuery')) {
                return;
            }

            // stop if there is a previous span without stop
            $this->span && Tracer::stop($this->span);

            $this->span = Tracer::createSpan('doctrine.query', ['sql' => $sql]);
            Tracer::start($this->span);

            $this->logger->startQuery($sql, $params, $types);
        }

        public function stopQuery(): void
        {
            if (!method_exists($this->logger, 'stopQuery')) {
                return;
            }

            Tracer::stop($this->span);
            $this->span = null;
            $this->logger->stopQuery();
        }
    }
} else {
    class DbalLoggerDecorator
    {
        protected object $logger;

        protected ?Span $span = null;

        public function __construct(object $logger)
        {
            $this->logger = $logger;
        }

        public function startQuery($sql, ?array $params = null, ?array $types = null): void
        {
            if (!method_exists($this->logger, 'startQuery')) {
                return;
            }

            $this->span && Tracer::stop($this->span);

            $this->span = Tracer::createSpan('doctrine.query', ['sql' => $sql]);
            Tracer::start($this->span);

            $this->logger->startQuery($sql, $params, $types);
        }

        public function stopQuery(): void
        {
            if (!method_exists($this->logger, 'stopQuery')) {
                return;
            }

            Tracer::stop($this->span);
            $this->span = null;
            $this->logger->stopQuery();
        }
    }
}
