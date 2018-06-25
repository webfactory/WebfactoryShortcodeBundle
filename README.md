# WebfactoryShortcodeBundle

WebfactoryShortcodeBundle is a Symfony bundle that integrates [thunderer/Shortcode](https://github.com/thunderer/Shortcode).

Shortcodes are special text fragments that can be used in user generated content to embed some other content or markup.
E.g. a user could use the following in a comment: 

```
[image src="https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"]
[text color="red"]This is red text.[/text]
```

This bundle allows you to define shortcodes and their replacements in a jiffy.
 

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

The easiest way is to add one anonymous service for each shortcode in your services definition:

```xml  
<service parent="webfactory.shortcode.embed_esi_for_shortcode_handler">
    <argument index="1">reference-to-your-replacement-controller</argument>
    <tag name="webfactory.shortcode" shortcode="your-shortcode-name"/>
</service>
```

The parent ```webfactory.shortcode.embed_esi_for_shortcode_handler``` will use [ESI rendering](https://symfony.com/doc/current/http_cache/esi.html),
while the parent ```webfactory.shortcode.embed_inline_for_shortcode_handler``` ill use inline rendering.

The ```reference-to-your-replacement-controller``` could be a string like ```app.controller.embedded_image:showAction```
(in this case, it's defined as a service). We recommend using several controllers grouped by feature with only a few
actions to keep things simple and unit testable, instead of one big ShortcodeController for all shortcodes, but of
course, that's up to you.

Finally ```your-shortcode-name``` is the name the users can use in their text inside the squared bracktes. Anything
after the name in the suqared brackets wll be considered as parameters that will be passed onto the controller.   

## Putting it all together

To allow a user input of ```[image src="https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"]``` to be replaced
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
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
    <services>
    
        <!-- ... -->
        
        <service parent="webfactory.shortcode.embed_esi_for_shortcode_handler">
            <argument index="1">app.controller.embedded_image:showAction</argument>
            <tag name="webfactory.shortcode" shortcode="image"/>
        </service>
        
        <service id="app.controller.embedded_image" class="AppBundle\Controller\EmbeddedImageController">
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

    /**
     * @param string $url
     * @return Response
     */
    public function showAction($url)
    {
        return $this->twigEngine->renderResponse(
            '@App/EmbeddedImage/show.html.twig',
            [
                'url' => $url,
            ]
        );
    }
}
```

And finally a twig template like this:

```twig
{# src/Ressources/views/EmbeddedImage/show.html.twig #}
<img src="{{ url }}" />
```


## Credits, Copyright and License

This bundle was started at webfactory GmbH, Bonn.

- <http://www.webfactory.de>
- <http://twitter.com/webfactory>

Copyright 2018 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).
