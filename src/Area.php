<?php

namespace JansenFelipe\OMR;

class Area
{
    /**
     * Total number of pixels
     *
     * @var int
     */
    private $pixels;

    /**
     * Number of white pixels
     *
     * @var int
     */
    private $whitePixels;

    /**
     * Number of black pixels
     *
     * @var int
     */
    private $blackPixels;

    /**
     * Constructor method
     * @param int $pixels The pixels
     * @param int $whitePixels The white pixels
     * @param int $blackPixels The black pixels
     */
    public function __construct($pixels, $whitePixels, $blackPixels)
    {
        $this->pixels = $pixels;
        $this->whitePixels = $whitePixels;
        $this->blackPixels = $blackPixels;
    }

    /**
     * Percentage of black pixels
     *
     * @return float
     */
    public function percentBlack()
    {
        return (100 * $this->blackPixels) / $this->pixels;
    }

    /**
     * Percentage of white pixels
     *
     * @return float
     */
    public function percentWhite()
    {
        return (100 * $this->whitePixels) / $this->pixels;
    }
}
