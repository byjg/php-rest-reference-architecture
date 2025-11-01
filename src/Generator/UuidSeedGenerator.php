<?php

namespace RestReferenceArchitecture\Generator;

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\MicroOrm\Interface\UniqueIdGeneratorInterface;
use ByJG\MicroOrm\Literal\HexUuidLiteral;
use ByJG\MicroOrm\Literal\Literal;

class UuidSeedGenerator implements UniqueIdGeneratorInterface
{

    /**
     * @inheritDoc
     */
    public function process(DatabaseExecutor $executor, object|array $instance): string|Literal|int
    {
        $value = $executor->getScalar("SELECT hex(uuid_to_bin(uuid()))");
        return new HexUuidLiteral($value);
    }
}