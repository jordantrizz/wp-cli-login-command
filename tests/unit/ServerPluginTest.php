<?php

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use WP_CLI_Login\ServerPlugin;

class ServerPluginTest extends TestCase
{
    private function pluginWithVersion($version)
    {
        return new class('dummy', $version) extends ServerPlugin {
            private $version;

            public function __construct($file, $version)
            {
                parent::__construct($file);
                $this->version = $version;
            }

            public function version()
            {
                return $this->version;
            }
        };
    }

    /** @test */
    public function empty_constraint_requires_update()
    {
        $plugin = $this->pluginWithVersion('1.5.0');

        Assert::assertFalse($plugin->versionSatisfies(''));
        Assert::assertFalse($plugin->versionSatisfies(null));
    }

    /** @test */
    public function empty_version_requires_update()
    {
        $plugin = $this->pluginWithVersion('');

        Assert::assertFalse($plugin->versionSatisfies('^1.5'));
    }

    /** @test */
    public function invalid_constraint_requires_update()
    {
        $plugin = $this->pluginWithVersion('1.5.0');

        Assert::assertFalse($plugin->versionSatisfies('not-a-version'));
    }

    /** @test */
    public function valid_constraint_comparison_still_works()
    {
        $plugin = $this->pluginWithVersion('1.5.2');

        Assert::assertTrue($plugin->versionSatisfies('^1.5'));
        Assert::assertFalse($plugin->versionSatisfies('^2.0'));
    }
}
