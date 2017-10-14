<?php

namespace RestTemplate\Rest;

use ByJG\Serializer\BinderObject;
use RestTemplate\Model\Dummy;
use RestTemplate\Repository\DummyRepository;

class Sample extends ServiceAbstractBase
{
    public function getPing()
    {
        $this->getResponse()->write([
            'result' => 'pong'
        ]);
    }

    public function getDummy()
    {
        $dummyRepo = new DummyRepository();
        $field = $this->getRequest()->get('field');

        $this->getResponse()->write(
            $dummyRepo->getByField($field)
        );
    }

    public function postDummy()
    {
        $model = new Dummy();
        $payload = json_decode($this->getRequest()->payload());
        BinderObject::bindObject($payload, $model);

        $dummyRepo = new DummyRepository();
        $dummyRepo->save($model);
    }
}
