<?php declare(strict_types=1);

use Cspray\Labrador\Configuration;
use Cspray\Labrador\Test\Stub\FooPluginStub;
use Cspray\Labrador\Test\Stub\PluginStub;

return new class implements Configuration {

    public function getEnvironmentName() : string {
        return 'dev';
    }

    public function getApplicationClass() : ?string {
        return \Cspray\Labrador\Test\Stub\NoopApplication::class;
    }

    public function getLogName() : string {
        return 'php-instance-log';
    }

    public function getLogPath() : string {
        return 'php://stdout';
    }

    public function getPlugins() : array {
        return [PluginStub::class, FooPluginStub::class];
    }

    public function getInjectorProviderPath() : string {
        return 'file://yadda/yadda/instance';
    }
};
