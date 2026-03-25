<?php

namespace Softspring\GoogleCloudTraceBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Softspring\GoogleCloudTraceBundle\Twig\EnvironmentTracer;
use Twig\Loader\ArrayLoader;

class EnvironmentTracerTest extends TestCase
{
    public function testRenderKeepsTwigBehavior(): void
    {
        $twig = new EnvironmentTracer(new ArrayLoader([
            'hello.html.twig' => 'Hello {{ name }}',
        ]));

        self::assertSame('Hello Armonic', $twig->render('hello.html.twig', ['name' => 'Armonic']));
    }
}
