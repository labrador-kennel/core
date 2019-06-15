<labrador xmlns="https://labrador-kennel.io/core/schemas/configuration.schema.xsd">
    <environment>dev</environment>
    <logging>
        <name><?= $appNamespace ?></name>
        <outputPath>php://stdout</outputPath>
    </logging>
    <injectorProviderPath><?= $injectorProviderPath ?></injectorProviderPath>
    <plugins>
    </plugins>
</labrador>
