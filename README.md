# WebfactoryShortcodeBundle

WebfactoryShortcodeBundle is a Symfony bundle that integrates [thunderer/Shortcode](https://github.com/thunderer/Shortcode).

It allows you to define shortcodes and their replacements in a jiffy. Shortcodes are special text fragments that can be
used by users in user generated content to embed some other content or markup. E.g. a user could use the following in a
comment: 

```
[image url="https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"]
[text color="red"]This is red text.[/text]
```

In analogy to living style guides, this bundle also provides an optional shortcode guide. This guide can be used for
automated testing of your shortcodes as well. 
 
## Installation

As usual, install via [composer](https://getcomposer.org/) and register the bundle in your application:

    composer require webfactory/shortcode-bundle

For Symfony < 4:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Webfactory\ShortcodeBundle\WebfactoryShortcodeBundle(),
        // ...
    );
    // ...
}
```

For Symfony >= 4:

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

### Defining your own shortcodes

The easiest way is to add one service for each shortcode in your services definition:

```xml  
<service id="webfactory.shortcode.your-shortcode-name" parent="Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.inline" class="Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler">
    <argument index="1">reference-to-your-replacement-controller</argument>
    <tag name="webfactory.shortcode" shortcode="your-shortcode-name"/>
</service>
```

The parent ```Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.inline``` will use
inline rendering while the parent ```Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.esi``` will use [ESI rendering](https://symfony.com/doc/current/http_cache/esi.html).

ESI may be nice for caching but comes with a problem: ESI embeds controller actions by calling a special internal `_fragment`-URL and needs to somehow serialize all parameters for an action in this URL. This works well for scalar values but neither for objects nor arrays of scalar values. But for context sensitive shortcodes, we pass the request attributes to the embedded controller action. And these request attributes might contain objects, e.g. the result object of a ParamConverter. This can lead to hard to debug errors, especially when recursion comes into play.

Also, logging needs more configuration (explained in the Logging section) with ESI.

The ```reference-to-your-replacement-controller``` could be a string like ```AppBundle\Controller\EmbeddedImageController::showAction```
or if use controllers as services, something like ```AppBundle\Controller\EmbeddedImageController:showAction```. We recommend
using several controllers grouped by feature with only a few actions to keep things simple and unit testable, instead of
one huge ShortcodeController for all shortcodes. But of course, that's up to you.

Finally ```your-shortcode-name``` is the name the users can use in their text inside the squared bracktes. Anything
after the name in the suqared brackets wll be considered as parameters that will be passed onto the controller.   

### Full example

To allow a user input of ```[image url="https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"]``` to be replaced
with HTML markup for this image, use the twig filter "shortcodes" on the user input:

```twig
{# user-generated-comment.html.twig #}
<div class="comment">
    {{ comment |shortcodes }}
</div>
```

Then, write a service definition like this:

```xml  
<?xml version="1.0" ?>
<container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
    <services>
    
        <!-- ... -->
        
        <service id="webfactory.shortcode.image" parent="Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.inline" class="Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler">
            <argument index="1">AppBundle\Controller\EmbeddedImageController:showAction</argument>
            <tag name="webfactory.shortcode" shortcode="image"/>
        </service>
        
        <service id="AppBundle\Controller\EmbeddedImageController">
            <argument type="service" id="templating" />
        </service>
        
        <!-- ... -->
        
    </services>
</container>
```

A controller like this:

```php
<?php
// src/AppBundle/Controller/EmbeddedImageController.php

namespace AppBundle\Controller;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

final class EmbeddedImageController
{
    /** @var TwigEngine */
    private $twigEngine;

    public function __construct(TwigEngine $twigEngine)
    {
        $this->twigEngine = $twigEngine;
    }

    public function showAction(string $url): Response
    {
        if (!$url) {
            throw new \RuntimeException('No url provided');
        }

        return $this->twigEngine->renderResponse('@App/EmbeddedImage/show.html.twig', ['url' => $url]);
    }
}
```

And finally a twig template like this:

```twig
{# src/Ressources/views/EmbeddedImage/show.html.twig #}
<div class="shortcode-container">
    <img src="{{ url }}" />
</div>
```

### Activating the Shortcode Guide

The optional shortcode guide is a controller providing an overview page of the configured shortcodes and a detail page
for each shortcode including a rendered example. Activate it in three simple steps:

At first, include the controller service definition. It is located at ```webfactory/shortcode-bundle/Resources/config/guide.xml```.
You can easily import it from your own configurations, just have a think about the correct environment. E.g.:

```xml
<!-- src/AppBundle/Resources/config/shortcodes.xml -->
<?xml version="1.0" ?>
<container xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://symfony.com/schema/dic/services" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
    <imports>
        <import resource="../../../../vendor/webfactory/shortcode-bundle/Resources/config/guide.xml"/>
    </imports>

    <!-- your shortcode services -->
</container>
```

Secondly, include the routes located at ```@WebfactoryShortcodeBundle/Resources/config/guide-routing.xml```, again
considering the environment. Maybe you want to restrict access in your security configuration.

```yaml
# src/routing.yml
_shortcode-guide:
    prefix: /shortcodes
    resource: "@WebfactoryShortcodeBundle/Resources/config/guide-routing.xml"
```

Finally, enrich your shortcode tags with description and example attributes for the guide:

```xml
<!-- src/AppBundle/Resources/config/shortcodes.xml -->
<?xml version="1.0" ?>
<container xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://symfony.com/schema/dic/services" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <!-- import guide.xml -->

    <services>
        <service id="webfactory.shortcode.image" parent="Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.inline" class="Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler">
            <argument index="1">AppBundle\Controller\EmbeddedImageController:showAction</argument>
            <tag
                name="webfactory.shortcode"
                shortcode="image"
                description="Renders an image tag with the {url} as it's source."
                example="image url=https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"
            />
        </service>
    </services>
</container>
```

With the route prefix defined as above, call ```/shortcodes/``` to get the list of shortcodes and follow the links to the
detail pages.

### Configuration

In most cases, the default values should work fine. But you might want to configure something else, e.g. if the default
parser needs too much memory for a large snippet. See thunderer's documentation on [parsing](https://github.com/thunderer/Shortcode#parsing)
and [configuration](https://github.com/thunderer/Shortcode#configuration) so you understand the advantages,
disadvantages and limitations:

```yaml
// config.yml

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

Copyright 2018-2021 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).
