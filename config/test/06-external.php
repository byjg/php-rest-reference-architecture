<?php

use ByJG\Mail\MailerFactory;
use ByJG\Mail\Wrapper\FakeSenderWrapper;
use ByJG\Mail\Wrapper\MailWrapperInterface;
use RestReferenceArchitecture\Psr11;

return [

    // Override: Use FakeSenderWrapper for testing
    MailWrapperInterface::class => function () {
        $apiKey = Psr11::get('EMAIL_CONNECTION');
        MailerFactory::registerMailer(FakeSenderWrapper::class);

        return MailerFactory::create($apiKey);
    },

];
