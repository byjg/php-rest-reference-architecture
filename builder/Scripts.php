<?php

namespace Builder;

use ByJG\Gluo\Builder\BaseScripts;
use Composer\Script\Event;

/**
 * Migration, OpenAPI generation and code generation logic live in
 * BaseScripts (byjg/gluo). This class binds them to composer scripts
 * and anchors the working directory to this project.
 */
class Scripts extends BaseScripts
{
    public function __construct()
    {
        parent::__construct();
        // Anchor to the project root. The base class guesses from the vendor
        // location, which fails when byjg/gluo is symlinked (path repository).
        $this->workdir = realpath(__DIR__ . '/..');
    }

    protected function getAppNamespace(): string
    {
        return 'RestReferenceArchitecture';
    }

    public static function migrate(Event $event): void
    {
        (new Scripts())->runMigrate($event->getArguments());
    }

    public static function genOpenApiDocs(Event $event): void
    {
        (new Scripts())->runGenOpenApiDocs($event->getArguments());
    }

    public static function codeGenerator(Event $event): void
    {
        (new Scripts())->runCodeGenerator($event->getArguments());
    }
}
