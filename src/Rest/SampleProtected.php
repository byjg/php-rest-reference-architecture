<?php

namespace RestTemplate\Rest;

class SampleProtected extends ServiceAbstractBase
{
    public function getPing()
    {
        $data = $this->decodePreviousToken();

        $this->getResponse()->write([
            'result' => 'pong',
            'metadata' => $data
        ]);
    }
}
