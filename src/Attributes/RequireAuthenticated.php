<?php

namespace RestReferenceArchitecture\Attributes;

use Attribute;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use Override;
use RestReferenceArchitecture\Util\JwtContext;

#[Attribute(Attribute::TARGET_METHOD)]
class RequireAuthenticated extends \ByJG\RestServer\Attributes\RequireAuthenticated
{
    #[Override]
    public function processBefore(HttpResponse $response, HttpRequest $request): void
    {
        parent::processBefore($response, $request);
        JwtContext::setRequest($request);
    }
}