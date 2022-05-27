<?php

namespace Webfactory\ShortcodeBundle\Tests\Fixtures\Controller;

use Symfony\Component\HttpFoundation\Response;

class ShortcodeTestController
{
    public function test(string $foo = null): Response
    {
        return new Response('test' . ($foo ? ' foo='.$foo : ''));
    }
}
