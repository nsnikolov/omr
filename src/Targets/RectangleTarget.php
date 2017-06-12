<?php

namespace JansenFelipe\OMR\Targets;

use JansenFelipe\OMR\Contracts\Target;
use JansenFelipe\OMR\Point;

class RectangleTarget extends Target
{
    /**
     * Pointer Top/Left
     *
     * @var Point
     */
    private $a;

    /**
     * Pointer Bottom/Right
     *
     * @var Point
     */
    private $b;

    /**
     * Constructor method
     *
     * @param mixed $id The ID
     * @param Point $a A point
     * @param Point $b A point
     */
    public function __construct($id, Point $a, Point $b)
    {
        $this->id = $id;
        $this->a = $a;
        $this->b = $b;
    }

    /**
     * Get Pointer Top/Left
     *
     * @return Point
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * Get Pointer Bottom/Right
     *
     * @return Point
     */
    public function getB()
    {
        return $this->b;
    }
}
