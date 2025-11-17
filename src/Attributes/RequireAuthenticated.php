<?php

namespace RestReferenceArchitecture\Attributes;

use Attribute;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use RestReferenceArchitecture\Util\JwtContext;

#[Attribute(Attribute::TARGET_METHOD)]
class RequireAuthenticated extends \ByJG\RestServer\Attributes\RequireAuthenticated
{
    public function processBefore(HttpResponse $response, HttpRequest $request): void
    {
        parent::processBefore($response, $request);
        JwtContext::setRequest($request);
    }
}