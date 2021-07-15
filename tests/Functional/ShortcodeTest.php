<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use PHPUnit_Framework_ExpectationFailedException;
use PHPUnit_Framework_IncompleteTestError;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Abstract template for common shortcode tests.
 */
abstract class ShortcodeTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    /**
     * @return string name of the shortcode to test.
     */
    abstract protected function getShortcodeToTest(): string;

    protected function setUp(): void
    {
        parent::setUp();

        if ('' === $this->getShortcodeToTest() || null === $this->getShortcodeToTest()) {
            throw new PHPUnit_Framework_IncompleteTestError('Albeit being a '.__CLASS__.', '.static::class.' does not define a shortcode to test.');
        }

        static::bootKernel();
        $this->client = static::createClient();
    }

    /**
     * @param array|string|null $customParameters use of strings is deprecated, use array instead.
     *
     * @return Crawler
     */
    protected function getRenderedExampleHtml(?array $customParameters = null): string
    {
        return $this->crawlRenderedExample($customParameters)->html();
    }

    /**
     * @param array|string|null $customParameters use of strings is deprecated, use array instead.
     */
    protected function crawlRenderedExample(/*array*/ $customParameters = null): Crawler
    {
        $urlWithRenderedExample = $this->getUrlWithRenderedExample($customParameters);

        $crawlerOnRenderedExamplePage = $this->client->request('GET', $urlWithRenderedExample);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $crawlerOnRenderedExample = $crawlerOnRenderedExamplePage->filter('#rendered-example');
        if (0 === $crawlerOnRenderedExample->count()) {
            throw new PHPUnit_Framework_ExpectationFailedException('No rendered example found for shortcode "'.$this->shortcode.'"');
        }

        return $crawlerOnRenderedExample;
    }

    /**
     * @param array|string|null $customParameters use of strings is deprecated, use array instead.
     */
    protected function assertHttpStatusCodeWhenCrawlingRenderedExample(
        int $expectedStatusCode,
        /*array*/ $customParameters = null
    ): Crawler {
        $urlWithRenderedExample = $this->getUrlWithRenderedExample($customParameters);

        $crawlerOnRenderedExamplePage = $this->client->request('GET', $urlWithRenderedExample);
        $this->assertEquals($expectedStatusCode, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param array|string|null $customParameters use of strings is deprecated, use array instead.
     *
     * @return Crawler
     */
    private function getUrlWithRenderedExample(/*array*/ $customParameters = null): string
    {
        $urlParameters = ['shortcode' => $this->getShortcodeToTest()];

        $customParametersAsString = $this->getCustomParametersAsString($customParameters);
        if ($customParametersAsString) {
            $urlParameters['customParameters'] = $customParametersAsString;
        }

        return static::$container->get('router')->generate('webfactory.shortcode.guide-detail', $urlParameters);
    }

    private function getCustomParametersAsString($customParametersAsMixed): ?string
    {
        if (\is_string($customParametersAsMixed)) {
            return $customParametersAsMixed;
        }

        if (\is_array($customParametersAsMixed)) {
            $customParametersAsString = '';
            foreach ($customParametersAsMixed as $name => $value) {
                $customParametersAsString .= $name.'='.$value.' ';
            }

            return $customParametersAsString;
        }

        return null;
    }
}
