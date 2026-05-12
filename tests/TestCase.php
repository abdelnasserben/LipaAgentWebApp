<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $compiledViewsPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lipa-agent-views-'.getmypid();

        if (! is_dir($compiledViewsPath)) {
            mkdir($compiledViewsPath, 0777, true);
        }

        putenv('VIEW_COMPILED_PATH='.$compiledViewsPath);
        $_ENV['VIEW_COMPILED_PATH'] = $compiledViewsPath;
        $_SERVER['VIEW_COMPILED_PATH'] = $compiledViewsPath;

        parent::setUp();
    }
}
