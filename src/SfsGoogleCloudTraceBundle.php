<?php

namespace Softspring\GoogleCloudTraceBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SfsGoogleCloudTraceBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
