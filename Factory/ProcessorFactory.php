<?php

namespace Webfactory\ShortcodeBundle\Factory;

use Thunder\Shortcode\EventContainer\EventContainer;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\ParserInterface;
use Thunder\Shortcode\Processor\Processor;

class ProcessorFactory
{
    /**
     * @var ParserInterface
     */
    private $parserInterface;

    /**
     * @var HandlerContainer
     */
    private $handlerContainer;

    /**
     * @var EventContainer
     */
    private $eventContainer;

    /**
     * @var int
     */
    private $recursionDepth;

    /**
     * @var int
     */
    private $maxIterations;

    public function __construct(ParserInterface $parserInterface, HandlerContainer $handlerContainer, EventContainer $eventContainer, ?int $recursionDepth, ?int $maxIterations)
    {
        $this->parserInterface = $parserInterface;
        $this->handlerContainer = $handlerContainer;
        $this->eventContainer = $eventContainer;
        $this->recursionDepth = $recursionDepth;
        $this->maxIterations = $maxIterations;
    }

    public function create(): Processor
    {
        $processor = new Processor($this->parserInterface, $this->handlerContainer);

        return $processor->withEventContainer($this->eventContainer)->withRecursionDepth($this->recursionDepth)->withMaxIterations($this->maxIterations);
    }
}
