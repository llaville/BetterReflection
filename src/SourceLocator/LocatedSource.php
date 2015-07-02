<?php

namespace BetterReflection\SourceLocator;

/**
 * Value object containing source code that has been located
 */
class LocatedSource
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $filename;

    public function __construct($source, $filename)
    {
        if (!is_string($source) || empty($source)) {
            throw new \InvalidArgumentException(
                'Source code must be a non-empty string'
            );
        }

        if (!is_string($filename) && null !== $filename) {
            throw new \InvalidArgumentException(
                'Filename must be a string or null'
            );
        }

        $this->source = $source;
        $this->filename = $filename;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getFileName()
    {
        return $this->filename;
    }
}
