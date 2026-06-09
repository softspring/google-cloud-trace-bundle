<?php

namespace Softspring\GoogleCloudTraceBundle\Twig;

use Softspring\GoogleCloudTraceBundle\Trace\Tracer;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EnvironmentTracer extends Environment
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render($name, array $context = []): string
    {
        Tracer::start($span = Tracer::createSpan($name));

        try {
            return parent::render($name, $context);
        } finally {
            Tracer::stop($span);
        }
    }
}
