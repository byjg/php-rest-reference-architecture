<?php

use ByJG\JinjaPhp\Loader\FileSystemLoader;
use ByJG\Mail\Envelope;
use ByJG\Mail\MailerFactory;
use ByJG\Mail\Wrapper\FakeSenderWrapper;
use ByJG\Mail\Wrapper\MailgunApiWrapper;
use ByJG\Mail\Wrapper\MailWrapperInterface;
use RestReferenceArchitecture\Psr11;

return [

    // Mail Configuration
    MailWrapperInterface::class => function () {
        $apiKey = Psr11::get('EMAIL_CONNECTION');
        MailerFactory::registerMailer(MailgunApiWrapper::class);
        MailerFactory::registerMailer(FakeSenderWrapper::class);

        return MailerFactory::create($apiKey);
    },

    // Mail Envelope Factory
    'MAIL_ENVELOPE' => function ($to, $subject, $template, $mapVariables = []) {
        $body = "";

        $loader = new FileSystemLoader(__DIR__ . "/../../templates/emails", ".html");
        $template = $loader->getTemplate($template);
        $body = $template->render($mapVariables);

        $prefix = "";
        if (Psr11::environment()->getCurrentEnvironment() != "prod") {
            $prefix = "[" . Psr11::environment()->getCurrentEnvironment() . "] ";
        }
        return new Envelope(Psr11::get('EMAIL_TRANSACTIONAL_FROM'), $to, $prefix . $subject, $body, true);
    },

];
