<?php

namespace RestReferenceArchitecture\Service;

use ByJG\Gluo\Service\BaseService;
use RestReferenceArchitecture\Repository\DummyRepository;

class DummyService extends BaseService
{
    public function __construct(DummyRepository $baseRepository)
    {
        parent::__construct($baseRepository);
    }
}
