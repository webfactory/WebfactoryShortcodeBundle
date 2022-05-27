<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ShortcodeGuideTest extends WebTestCase
{
    /**
     * @test
     */
    public function shortcode_guide_contains_test_entry(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        $text = $crawler->text();
        self::assertStringContainsString('test-shortcode-guide', $text);
        self::assertStringContainsString('Description for the \'test-shortcode-guide\' shortcode', $text);
        self::assertStringContainsString('test-shortcode-guide test=true', $text);
    }
}
