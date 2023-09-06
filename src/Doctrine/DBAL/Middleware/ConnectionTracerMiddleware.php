<?php

namespace Softspring\GoogleCloudTraceBundle\Middleware;

use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Softspring\GoogleCloudTraceBundle\Trace\Tracer;

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

    public function beginTransaction(): bool
    {
        Tracer::start($span = Tracer::createSpan('doctrine.beginTransaction'));
        $result = parent::beginTransaction();
        Tracer::stop($span);

        return $result;
    }

    public function commit(): bool
    {
        Tracer::start($span = Tracer::createSpan('doctrine.commit'));
        $result = parent::commit();
        Tracer::stop($span);

        return $result;
    }

    public function rollBack(): bool
    {
        Tracer::start($span = Tracer::createSpan('doctrine.rollBack'));
        $result = parent::rollBack();
        Tracer::stop($span);

        return $result;
    }
}
