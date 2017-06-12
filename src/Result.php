<?php

namespace JansenFelipe\OMR;

use JansenFelipe\OMR\Contracts\Target;

class Result
{
    /**
     * Path Image
     *
     * @var string
     */
    private $imagePath;

    /**
     * MIME Image
     *
     * @var string
     */
    private $imageMime;

    /**
     * Width Image
     *
     * @var int
     */
    private $width;

    /**
     * Height Image
     *
     * @var int
     */
    private $height;

    /**
     * Targets
     *
     * @var Target[]
     */
    private $targets;

    /**
     * Set dimensions
     *
     * @param int $width The width
     * @param int $height The height
     * @return void
     */
    public function setDimensions($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Add target
     *
     * @param Target $target The target
     * @return void
     */
    public function addTarget(Target $target)
    {
        $this->targets[] = $target;
    }

    /**
     * Get target
     *
     * @return Target[]
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Set mime image
     *
     * @param string $imageMime The image mimetype
     * @return void
     */
    public function setImageMime($imageMime)
    {
        $this->imageMime = $imageMime;
    }

    /**
     * Set Path image
     *
     * @param string $imagePath The image path
     * @return void
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }
}
