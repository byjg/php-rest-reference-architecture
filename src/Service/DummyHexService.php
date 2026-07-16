<?php

namespace RestReferenceArchitecture\Service;

use ByJG\Gluo\Service\BaseService;
use RestReferenceArchitecture\Repository\DummyHexRepository;

class DummyHexService extends BaseService
{
    public function __construct(DummyHexRepository $baseRepository)
    {
        parent::__construct($baseRepository);
    }
}
