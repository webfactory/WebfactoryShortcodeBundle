<?php

namespace Webfactory\ShortcodeBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Webfactory\ShortcodeBundle\DependencyInjection\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function configure_shortcode_with_controller_reference_only(): void
    {
        $processedConfig = $this->processSingleConfig(['shortcodes' => ['test-1' => 'Foo::bar']]);

        self::assertTrue(isset($processedConfig['shortcodes']['test-1']['controller']));
        self::assertSame('Foo::bar', $processedConfig['shortcodes']['test-1']['controller']);
    }

    /**
     * @test
     */
    public function configure_shortcode_with_controller_as_key(): void
    {
        $processedConfig = $this->processSingleConfig(['shortcodes' => ['test-1' => ['controller' => 'Foo::bar']]]);

        self::assertSame('Foo::bar', $processedConfig['shortcodes']['test-1']['controller']);
    }

    /**
     * @test
     */
    public function configure_shortcode_with_method(): void
    {
        $processedConfig = $this->processSingleConfig(['shortcodes' => ['test-1' => ['controller' => 'Foo::bar', 'method' => 'esi']]]);

        self::assertSame('esi', $processedConfig['shortcodes']['test-1']['method']);
    }

    private function processSingleConfig(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [$config]);
    }
}
