<?php

namespace JansenFelipe\OMR\Contracts;

abstract class Target
{
    /**
     * Store results if the target was marked
     *
     * @var boolean
     */
    protected $marked = false;

    /**
     * Identifier
     *
     * @var string
     */
    protected $id;

    /**
     * Black pixels percentage compared to whites to consider marked
     *
     * @var float
     */
    protected $tolerance = 24;

    /**
     * Black pixels percentage
     *
     * @var float
     */
    protected $blackPixelsPercent = 0;

    /**
     * Checks if the target was marked
     *
     * @return bool
     */
    public function isMarked()
    {
        return $this->marked;
    }

    /**
     * Tells whether the target was marked
     *
     * @param bool $marked Whether or not the target is marked
     * @return void
     */
    public function setMarked($marked)
    {
        $this->marked = $marked;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id The ID
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return float
     */
    public function getTolerance()
    {
        return $this->tolerance;
    }

    /**
     * @param float $tolerance The tolerance
     * @return void
     */
    public function setTolerance($tolerance)
    {
        $this->tolerance = $tolerance;
    }

    /**
     * @return float
     */
    public function getBlackPixelsPercent()
    {
        return $this->blackPixelsPercent;
    }

    /**
     * @param float $blackPixelsPercent The percentage of black pixels
     * @return void
     */
    public function setBlackPixelsPercent($blackPixelsPercent)
    {
        $this->blackPixelsPercent = $blackPixelsPercent;
    }
}
