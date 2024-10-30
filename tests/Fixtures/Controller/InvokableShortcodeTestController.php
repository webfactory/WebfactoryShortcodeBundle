<?php

namespace Webfactory\ShortcodeBundle\Tests\Fixtures\Controller;

use Symfony\Component\HttpFoundation\Response;

final class InvokableShortcodeTestController
{
    public function __invoke(): Response
    {
        return new Response('invokable-controller-response');
    }
}
