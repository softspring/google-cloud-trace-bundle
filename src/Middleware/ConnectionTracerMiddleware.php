<?php

namespace Softspring\GoogleCloudTraceBundle\Middleware;

use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Softspring\GoogleCloudTraceBundle\Trace\Tracer;

if (class_exists(AbstractConnectionMiddleware::class)) {
    class ConnectionTracerMiddleware extends AbstractConnectionMiddleware
    {
        private bool $includeSql = false;

        public function setIncludeSql(bool $includeSql): void
        {
            $this->includeSql = $includeSql;
        }

        public function query(string $sql): Result
        {
            Tracer::start($span = Tracer::createSpan('doctrine.query', $this->sqlAttributes($sql)));

            try {
                return parent::query($sql);
            } finally {
                Tracer::stop($span);
            }
        }

        public function exec(string $sql): int
        {
            Tracer::start($span = Tracer::createSpan('doctrine.exec', $this->sqlAttributes($sql)));

            try {
                return parent::exec($sql);
            } finally {
                Tracer::stop($span);
            }
        }

        private function sqlAttributes(string $sql): array
        {
            return $this->includeSql ? ['sql' => $sql] : [];
        }
    }
} else {
    class ConnectionTracerMiddleware
    {
    }
}
