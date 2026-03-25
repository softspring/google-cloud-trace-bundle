<?php

namespace Softspring\GoogleCloudTraceBundle\Middleware;

use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Softspring\GoogleCloudTraceBundle\Trace\Tracer;

if (class_exists(AbstractConnectionMiddleware::class)) {
    class ConnectionTracerMiddleware extends AbstractConnectionMiddleware
    {
        public function query(string $sql): Result
        {
            Tracer::start($span = Tracer::createSpan('doctrine.query', ['sql' => $sql]));
            $result = parent::query($sql);
            Tracer::stop($span);

            return $result;
        }

        public function exec(string $sql): int
        {
            Tracer::start($span = Tracer::createSpan('doctrine.exec', ['sql' => $sql]));
            $result = parent::exec($sql);
            Tracer::stop($span);

            return $result;
        }
    }
} else {
    class ConnectionTracerMiddleware
    {
    }
}
