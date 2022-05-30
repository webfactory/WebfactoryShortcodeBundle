# WebfactoryShortcodeBundle

A Symfony bundle to resolve `[shortcode]` markup in Twig templates, using the [thunderer/Shortcode](https://github.com/thunderer/Shortcode) library.

It allows you to define shortcodes and their replacements in a jiffy. Shortcodes are special text fragments that can be replaced with other content or markup. E.g. a user could use the following in a comment: 

```
[image url="https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"]
[text color="red"]This is red text.[/text]
```

In analogy to living style guides, this bundle provides a shortcode guide that lists all registered shortcodes with an optional description and example.
 
## Installation

As usual, install via [Composer](https://getcomposer.org/) and register the bundle in your application:

    composer require webfactory/shortcode-bundle

```php
<?php
// config/bundles.php

public function registerBundles()
{
    return [
        // ...
        Webfactory\ShortcodeBundle\WebfactoryShortcodeBundle::class => ['all' => true],
        // ...
    ];
    // ...
}
```

## Usage

### Twig Filter

The bundle will set up a `shortcodes` Twig filter. What you pass through this filter will be processed by the `Processor` class (see [docs](https://github.com/thunderer/Shortcode#processing)).

```twig
{% apply shortcodes %}
    [image url="https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"]
    [text color="red"]This is red text.[/text]
{% endapply %}
{{ some_content |shortcodes }}
```

### Registering Handlers

In the [thunderer/Shortcode](https://github.com/thunderer/Shortcode) package, _handlers_ transform shortcodes into desired replacements. You can register services from the Symfony Dependency Injection Container to be used as shortcode handlers by tagging them with `webfactory.shortcode` and adding a `shortcode` attribute to the tag indicating the shortcode name.

```yaml
services:
    My\Shortcode\Handler\Service:
        tags:
            - { name: 'webfactory.shortcode', shortcode: 'my-shortcode-name' }
```

### Using Controllers as Shortcode Handlers

This bundle comes with a helper class that allows to use Symfony's [Fragment Sub-Framework](https://symfony.com/blog/new-in-symfony-2-2-the-new-fragment-sub-framework) and the technique of [embedding controllers](https://symfony.com/doc/current/templates.html#embedding-controllers) to have controllers generate the replacement output for shortcodes.

To give an example, assume the following configuration:

```yaml
# config.yml
webfactory_shortcodes:
    shortcodes:
        image: AppBundle\Controller\EmbeddedImageController::showAction
```

Then, when doing something like this in Twig:

```twig
{# Example content #}
{% set content = '[image url="https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"]' %}
{{ content |shortcodes }}
```

... the `AppBundle\Controller\EmbeddedImageController::showAction()` controller method will be called. Additional shortcode attributes, like `url` in the above example, will be passed as parameters to the controller. The response returned by the controller will be used to replace the shortcode in the given content. The controller can generate the response directly, or use Twig to render a template to create it. 

#### Rendering with Edge Side Includes

You can also use [ESI rendering](https://symfony.com/doc/current/http_cache/esi.html) for particular shortcodes. The advantage of ESI is that single shortcode replacements can be stored in edge caches and/or reverse proxies like Varnish and possibly be reused on multiple pages.

To use ESI-based embedding for a particular shortcode, use the following configuration:

```yaml
# config.yml
webfactory_shortcodes:
    shortcodes:
        image: 
            controller: AppBundle\Controller\EmbeddedImageController::showAction
            method: esi
```

## Activating the Shortcode Guide

The optional Shortcode Guide is a controller providing an overview page of all configured shortcodes. For every shortcode, there is also a detail page including a rendered example. 

To use the Shortcode Guide, include the routing configuration from `@WebfactoryShortcodeBundle/Resources/config/guide-routing.xml`.

⚠️ You probably want to do this only for your Symfony `dev` environment and/or additionally restrict access in your security configuration.

```yaml
# src/routing.yml
_shortcode-guide:
    prefix: /shortcodes
    resource: "@WebfactoryShortcodeBundle/Resources/config/guide-routing.xml"
```

With the route prefix defined as above, visit `/shortcodes/` to see a list of all defined shortcodes. If you want to add descriptions to shortcodes and/or provide the example shortcode that shall be rendered on the detail page, you can add this information when configuring shortcodes:

```yaml
# config.yml
webfactory_shortcodes:
    shortcodes:
        image: 
            controller: AppBundle\Controller\EmbeddedImageController::showAction
            description: "Renders an image tag with the {url} as it's source."
            example: "image url=https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"
```

### Other Configuration Parameters

In most cases, the default values should work fine. But you might want to configure something else, e.g. if the default
parser needs too much memory for a large snippet. See Thunderer's documentation on [parsing](https://github.com/thunderer/Shortcode#parsing)
and [configuration](https://github.com/thunderer/Shortcode#configuration) so you understand the advantages,
disadvantages and limitations:

```yaml
# config.yml

webfactory_shortcode:
    parser: 'regex'      # default: regular
    recursion_depth: 2   # default: null
    max_iterations: 2    # default: null
```  

### Automated Tests for your Shortcodes

With the shortcode guide enabled (remember: you may enable it just in your test environment), you can easily write
functional tests for your shortcodes using the rendered detail pages. This way, you can test even shortcodes with
complex dependencies. But as functional tests are slow, you may want to keep your shortcode tests in a seperate slow
test suite.   

To speed things up, the bundle provides the abstract ```\Webfactory\ShortcodeBundle\Tests\Functional\ShortcodeTest```
class for you to extend. Using it, your test class may look like this (we recommend one test class for each shortcode):

```php
<?php
# src/AppBundle/Tests/Shortcodes/ImageTest.php

namespace AppBundle\Tests\Shortcodes;

use Webfactory\ShortcodeBundle\Tests\Functional\ShortcodeTest;

final class ImageTest extends ShortcodeTest
{
    protected function getShortcodeToTest(): string
    {
        return 'image';
    }

    /** @test */
    public function teaser_gets_rendered(): void
    {
        // without $customParameters, getRenderedExampleHtml() will get a rendering of the example configured in the
        // shortcode tag, in this case "image url=https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"
        $this->assertStringContainsString(
            '<img src="https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png" />',
            $this->getRenderedExampleHtml()
        );
    }

    /** @test */
    public function teaser_with_custom_parameters(): void
    {
        // Pass custom parameters as an array
        $this->assertStringContainsString(
            '<img src="custom-image-url" />',
            $this->getRenderedExampleHtml([
                'url' => 'custom-image-url', 
            ])
        );
    }

    /** @test */
    public function teaser_to_nonexisting_page_gives_error(): void
    {
        // both crawlRenderedExample() and assertHttpStatusCodeWhenCrawlingRenderedExample() accept a $customParameters
        // argument that will replace the parameters provided in the configuration of the shortcode tag.
        // This can be used to cover more test cases, e.g. an unhappy path
        $this->assertHttpStatusCodeWhenCrawlingRenderedExample(500, 'url=');
    }
}
```

## Logging

When something goes wrong with the resolving of a shortcode, maybe you not only want to know which shortcode with
which parameters caused the issue (which you can log in your resolving controller), but also which url was called
that embedded the shortcode.

This is tricky is you embed your shortcode controllers via ESI, as the ESI subrequest is in Symfony terms a master
request, preventing you from getting your answer from RequestStack::getMasterRequest(). Hence, the
`EmbedShortcodeHandler` logs this information in the `shortcode` channel.

```xml
<!-- src/AppBundle/Resources/config/shortcodes.xml -->
<?xml version="1.0" ?>
<container xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://symfony.com/schema/dic/services" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
    <services>
        <service id="webfactory.shortcode.your-shortcode-name" parent="Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.esi" class="Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler">
            <argument index="1">AppBundle\Controller\EmbeddedImageController:showAction</argument>
            <tag name="webfactory.shortcode" ... />
            ...
            
            <argument index="3" type="service" id="monolog.logger.your_channel" />
        </service>
    </services>
</container>
```

## Credits, Copyright and License

This bundle was started at webfactory GmbH, Bonn.

- <https://www.webfactory.de>
- <https://twitter.com/webfactory>

Copyright 2018-2022 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).
