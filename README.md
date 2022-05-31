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

### Using Controllers as Shortcode Handlers

This bundle comes with a helper class that allows to use Symfony's [Fragment Sub-Framework](https://symfony.com/blog/new-in-symfony-2-2-the-new-fragment-sub-framework) and the technique of [embedding controllers](https://symfony.com/doc/current/templates.html#embedding-controllers) to have controllers generate the replacement output for shortcodes.

To give an example, assume the following configuration:

```yaml
# config.yml
webfactory_shortcodes:
    shortcodes:
        image: AppBundle\Controller\EmbeddedImageController::show
```

Then, when doing something like this in Twig:

```twig
{{ '[image url="https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"]' |shortcodes }}
```

... the `AppBundle\Controller\EmbeddedImageController::show()` controller method will be called. Additional shortcode attributes, like `url` in the above example, will be passed as parameters to the controller. The response returned by the controller will be used to replace the shortcode in the given content. The controller can generate the response directly, or use Twig to render a template to create it. 

#### Rendering with Edge Side Includes

You can also use [ESI rendering](https://symfony.com/doc/current/http_cache/esi.html) for particular shortcodes. The advantage of ESI is that single shortcode replacements can be stored in edge caches and/or reverse proxies like Varnish and possibly be reused on multiple pages.

⚠️ Take care: Due to the way ESI works, the (master) `Request` visible to controllers is no longer the one where the shortcode was used. Keep that in mind wehn you, for example, want to log the URLs where shortcodes are beiung used.

To use ESI-based embedding for a particular shortcode, use the following configuration:

```yaml
# config.yml
webfactory_shortcodes:
    shortcodes:
        image: 
            controller: AppBundle\Controller\EmbeddedImageController::showAction
            method: esi
```

### Registering Handlers as Services 

In the [thunderer/Shortcode](https://github.com/thunderer/Shortcode) package, _handlers_ transform shortcodes into desired replacements. You can register services from the Symfony Dependency Injection Container to be used as shortcode handlers by tagging them with `webfactory.shortcode` and adding a `shortcode` attribute to the tag indicating the shortcode name.

```yaml
services:
    My\Shortcode\Handler\Service:
        tags:
            - { name: 'webfactory.shortcode', shortcode: 'my-shortcode-name' }
```

### Removing `<p>` Tags around Shortcodes

By default, the `RemoveWrappingParagraphElementsEventHandler` contained in this bundle will be used to remove `<p>...</p>` tags around shortcodes, if the shortcode is the only text content in that paragraph. 

## Activating the Shortcode Guide

The optional Shortcode Guide is a controller providing an overview page of all configured shortcodes. For every shortcode, there is also a detail page including a rendered example. 

To use the Shortcode Guide, include the routing configuration from `@WebfactoryShortcodeBundle/Resources/config/guide-routing.xml`.

⚠️ You probably want to do this only for your Symfony `dev` and/or `test` environment, and possibly restrict access in your security configuration in addition to that.

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
parser needs too much memory for a large snippet. See thunderer's documentation on [parsing](https://github.com/thunderer/Shortcode#parsing)
and [configuration](https://github.com/thunderer/Shortcode#configuration) so you understand the advantages,
disadvantages and limitations:

```yaml
# config.yml

webfactory_shortcode:
    parser: 'regex'      # default: regular
    recursion_depth: 2   # default: null
    max_iterations: 2    # default: null
```  

## Testing your Shortcodes

This section provides a few hints and starting pointers on testing your shortcode handlers and bundle configuration.

### Direct Unit Tests

In general, try to start with unit testing your shortcode handlers directly.

No matter whether your handler is a simple class implementing the `__invoke` magic method or a Symfony Controller with one or several methods: Direct unit tests are the easiest way to have full control over the handler's (or controller's) input, and to get immediate access to its return value. This allows you to test also a broader range of input parameters and verify the outcomes. In this case, you will typically use Mock Objects to substitute some or all other classes and services your handler depends upon.

If your shortcode handler produces HTML output, the [Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html) might be helpful to perform assertions on the HTML structure and content.

### Functional Tests for shortcode-handling Controllers

When using a controller to handle a shortcode, and the controller uses Twig for rendering, you might want to do a full functional (integration) test instead of mocking the Twig engine.

The Symfony documentation describes how [Application Tests](https://symfony.com/doc/current/testing.html#write-your-first-application-test) can be performed. This approach, however, is probably not suited for your shortcode controllers since these typically _are not_ reachable through routes and so you cannot perform direct HTTP requests against them.

Instead, write an [integration test](https://symfony.com/doc/current/testing.html#integration-tests) where you retrieve the controller as a service from the Dependency Injection Container and invoke the appropriate method on it directly. Then, just like described in the section before, perform assertions on the Response returned by the controller.

Here is an example of what a test might look like.

```php
<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MyShortcodeControllerTest extends KernelTestCase
{
    public function test_renderImageAction_returns_img(): void
    {
        // Assume the controller is used to turn `[img id=42]` into some HTML markup

        // create fixture/setup image with ID 42 in the database or similar
        // ...

        // Exercise controller method
        $container = static::getContainer();
        $controller = $container->get(MyShortcodeController::class);
        $response = $controller->renderImageAction(42);

        // Verify outcome
        self::assertStringContainsString('<img src="..." />', (string) $response->getContent());
    }
}
```

### Testing Configuration

After you have written some tests that verify your handlers work as expected for different input parameters or other circumstances (e. g. database content), you also want to make sure a given handler is registered correctly and connected with the right shortcode name. Since we are now concerned with how this bundle, your configuration and your handlers all play together, we're in the realm of integration testing. These tests will be slower, since we need to boot a Symfony Kernel, fetch services from the Dependency Injection Container and test how various parts play together.

This bundle contains the `\Webfactory\ShortcodeBundle\Test\ShortcodeDefinitionTestHelper` class and a public service of the same name. Depending on the degree of test specifity you prefer, you can use this service to verify that...

* A given shortcode name is known, i. e. a handler has been set up for it 
* Retrieve the handler for a given shortcode name, so you can for example perform assertions on the class being used
* When using controllers as shortcode handlers, test if the controller reference for a given shortcode can be resolved (the controller actually exists)
* Retrieve an instance of the controller to perform assertions on it.

For all these tests, you probably need to use `KernelTestCase` as your test base class ([documentation](https://symfony.com/doc/current/testing.html#integration-tests)). Basically, you will need to boot the kernel, then get the `ShortcodeDefinitionTestHelper` from the container and use its methods to check your shortcode configuration.

Maybe you want to have a look at [the tests for `ShortcodeDefinitionTestHelper` itself](tests/Functional/ShortcodeDefinitionTestHelperTest.php) to see a few examples of how this class can be used.

Remember – this type of test should not test all the possible inputs and outputs for your handlers; you've already covered that with more specific, direct tests. In this test layer, we're only concerned with making sure all the single parts are connected correctly.

### Full End-to-End Tests

If, for some reason, you would like to do a full end-to-end test for shortcode processing, from a given string containing shortcode markup to the processed result,  have a look at the `\Webfactory\ShortcodeBundle\Test\EndToEndTestHelper` class.

This helper class can be used in integration test cases and will do the full shortcode processing on a given input, including dispatching sub-requests to controllers used as shortcode handlers.

A test might look like this:

```php
<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Webfactory\ShortcodeBundle\Test\EndToEndTestHelper;

class MyFullScaleTest extends KernelTestCase
{
    /** @test */
    public function replace_text_color(): void
    {
        self::bootKernel();
        
        $result = EndToEndTestHelper::createFromContainer(static::$container)->processShortcode('[text color="red"]This is red text.[/text]');
        
        self::assertSame('<span style="color: red;">This is red text.</span>', $result);
    }
}
```

Assuming that your application configuration registers a handler for the `text` shortcode, which might also be a controller, this test will perform a full-stack test.

## Credits, Copyright and License

This bundle was started at webfactory GmbH, Bonn.

- <https://www.webfactory.de>
- <https://twitter.com/webfactory>

Copyright 2018-2022 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).
