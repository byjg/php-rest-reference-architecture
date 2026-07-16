<?php

namespace Test\Builder;

use Builder\Scripts;
use Test\Controller\BaseApiTestCase;

/**
 * End-to-end code generator test: introspects the real `dummy` table from the
 * migrated test database and renders through the byjg/gluo templates.
 * Extends BaseApiTestCase to reuse the database reset and environment guard.
 */
class CodegenTest extends BaseApiTestCase
{
    protected function runCodegen(array $arguments): string
    {
        $scripts = new Scripts();
        ob_start();
        try {
            $scripts->runCodeGenerator(array_merge(['--env=test', '--table=dummy'], $arguments));
        } finally {
            $output = ob_get_clean();
        }
        return $output;
    }

    public function testGenerateAllRepositoryPattern(): void
    {
        $output = $this->runCodegen(['all']);

        $this->assertStringContainsString('namespace RestReferenceArchitecture\Model;', $output);
        $this->assertStringContainsString('class Dummy', $output);

        $this->assertStringContainsString('use ByJG\Gluo\Repository\BaseRepository;', $output);
        $this->assertStringContainsString('class DummyRepository extends BaseRepository', $output);

        $this->assertStringContainsString('use ByJG\Gluo\Service\BaseService;', $output);
        $this->assertStringContainsString('class DummyService extends BaseService', $output);

        $this->assertStringContainsString('use ByJG\Gluo\Attribute\RequireAuthenticated;', $output);
        $this->assertStringContainsString('use ByJG\Gluo\Attribute\ValidateRequest;', $output);
        $this->assertStringContainsString('class DummyController', $output);

        $this->assertStringContainsString('use ByJG\Gluo\Util\FakeApiRequester;', $output);
        $this->assertStringContainsString('class DummyTest', $output);
    }

    public function testGenerateActiveRecordPattern(): void
    {
        $output = $this->runCodegen(['model', 'rest', '--activerecord']);

        $this->assertStringContainsString('use ByJG\MicroOrm\Trait\ActiveRecord;', $output);
        $this->assertStringContainsString('use ByJG\Gluo\Attribute\RequireAuthenticated;', $output);
        // ActiveRecord pattern must not generate repository/service imports
        $this->assertStringNotContainsString('use ByJG\Gluo\Service\BaseService;', $output);
    }
}
