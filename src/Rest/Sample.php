<?php
/**
 * User: jg
 * Date: 30/09/17
 * Time: 18:06
 */

namespace RestTemplate\Rest;

class Sample extends ServiceAbstractBase
{
    public function getPing()
    {
        $this->getResponse()->write([
            'result' => 'pong'
        ]);
    }
}
