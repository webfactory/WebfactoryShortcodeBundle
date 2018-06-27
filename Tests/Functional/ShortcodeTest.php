<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Abstract template for common shortcode tests.
 */
abstract class ShortcodeTest extends WebTestCase
{
    /**
     * @return string name of the shortcode to test.
     */
    abstract protected function getShortcodeToTest();

    protected function setUp()
    {
        parent::setUp();

        if ($this->getShortcodeToTest() === '' || $this->getShortcodeToTest() === null) {
            throw new \PHPUnit_Framework_IncompleteTestError(
                'Albeit being a ' . __CLASS__ . ', ' . get_called_class() . ' does not define a shortcode to test.'
            );
        }
    }

    /**
     * @param string|null $customParameters
     * @return Crawler
     */
    protected function crawlRenderedExample($customParameters = null)
    {
        $urlWithRenderedExample = $this->getUrlWithRenderedExample($customParameters);

        $client = static::createClient();
        $crawlerOnRenderedExamplePage = $client->request('GET', $urlWithRenderedExample);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $crawlerOnRenderedExample = $crawlerOnRenderedExamplePage->filter('#rendered-example');
        if ($crawlerOnRenderedExample->count() === 0) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                'No rendered example found for shortcode "' . $this->shortcode . '"'
            );
        }

        return $crawlerOnRenderedExample;
    }

    /**
     * @param int $expectedStatusCode
     * @param string|null $customParameters
     * @return Crawler
     */
    protected function assertHttpStatusCodeWhenCrawlingRenderedExample($expectedStatusCode, $customParameters = null)
    {
        $urlWithRenderedExample = $this->getUrlWithRenderedExample($customParameters);

        $client = static::createClient();
        $crawlerOnRenderedExamplePage = $client->request('GET', $urlWithRenderedExample);
        $this->assertEquals($expectedStatusCode, $client->getResponse()->getStatusCode());
    }

    /**
     * @param string|null $customParameters
     * @return string
     */
    protected function getUrlWithRenderedExample($customParameters = null)
    {
        static::bootKernel();

        $urlParameters = ['shortcode' => $this->getShortcodeToTest()];
        if ($customParameters) {
            $urlParameters['customParameters'] = $customParameters;
        }

        return static::$kernel
            ->getContainer()
            ->get('router')
            ->generate('webfactory.shortcode.guide-detail', $urlParameters);
    }
}
