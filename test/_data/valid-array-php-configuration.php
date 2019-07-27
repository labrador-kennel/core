<?php declare(strict_types=1);

return [
    'labrador' => [
        'environment' => 'staging',
        'applicationClass' => \Cspray\Labrador\CallbackApplication::class,
        'logging' => [
            'name' => 'php-array-log',
            'outputPath' => 'php://stdout'
        ],
        'injectorProviderPath' => "file://yadda/yadda/array",
        'plugins' => [
            \Cspray\Labrador\Test\Stub\PluginStub::class,
            \Cspray\Labrador\Test\Stub\FooPluginDependentStub::class
        ]
    ]
];
