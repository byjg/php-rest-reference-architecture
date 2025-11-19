<?php

namespace RestReferenceArchitecture\Generator;

use ByJG\AnyDataset\Db\DatabaseExecutor;
use ByJG\MicroOrm\Interface\MapperFunctionInterface;
use ByJG\MicroOrm\Literal\HexUuidLiteral;
use Override;

class UuidSeedGenerator implements MapperFunctionInterface
{

    /**
     * @inheritDoc
     */
    #[Override]
    public function processedValue(mixed $value, mixed $instance, ?DatabaseExecutor $executor = null): mixed
    {
        $value = $executor->getScalar("SELECT hex(uuid_to_bin(uuid()))");
        return new HexUuidLiteral($value);
    }
}