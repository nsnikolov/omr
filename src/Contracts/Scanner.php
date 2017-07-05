<?php
namespace JansenFelipe\OMR\Contracts;

use JansenFelipe\OMR\Area;
use JansenFelipe\OMR\Point;
use JansenFelipe\OMR\Result;
use JansenFelipe\OMR\Targets\CircleTarget;
use JansenFelipe\OMR\Targets\RectangleTarget;
use JansenFelipe\OMR\Targets\TextTarget;

abstract class Scanner
{
    /**
     * Path image to be scanned
     *
     * @var string
     */
    protected $imagePath;

    /**
     * Debug flag
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * Path image to write debug image file
     *
     * @var string
     */
    protected $debugPath = 'debug.jpg';

    /**
     * Most point to the top/right
     *
     * @param Point $near The point near
     * @return Point
     */
    abstract protected function topRight(Point $near);

    /**
     * Most point to the bottom/left
     *
     * @param Point $near The point near
     * @return Point
     */
    abstract protected function bottomLeft(Point $near);

    /**
     * Returns pixel analysis in a rectangular area
     *
     * @param Point $a A point
     * @param Point $b A point
     * @param float $tolerance The tolerance
     * @return Area
     */
    abstract protected function rectangleArea(Point $a, Point $b, $tolerance);

    /**
     * Returns pixel analysis in a circular area
     *
     * @param Point $a A point
     * @param float $radius The radius
     * @param float $tolerance The tolerance
     * @return Area
     */
    abstract protected function circleArea(Point $a, $radius, $tolerance);

    /**
     * Returns image blob in a rectangular area
     *
     * @param Point $a A point
     * @param Point $b A point
     * @return string
     */
    abstract protected function textArea(Point $a, Point $b);

    /**
     * Increases or decreases image size
     *
     * @param float $percent The percentage
     * @return void
     */
    abstract protected function adjustSize($percent);

    /**
     * Rotate image
     *
     * @param float $degrees The degrees
     * @return void
     */
    abstract protected function adjustRotate($degrees);

    /**
     * Generate file debug.jpg with targets, topRight and buttonLeft
     *
     * @return void
     */
    abstract protected function debug();

    /**
     * Finish processes
     *
     * @return void
     */
    abstract protected function finish();

    /**
     * Set image path
     *
     * @param mixed $imagePath The image path
     * @return void
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * Set image with contents
     *
     * @param string $imagePath The image path
     * @param string $contents The image contents
     * @return void
     */
    public function setImage($imagePath = '', $contents = '')
    {
        $this->setImagePath($imagePath);
        file_put_contents($imagePath, $contents);
    }

    /**
     * Set debug image path
     *
     * @param mixed $debugPath The debug path
     * @return void
     */
    public function setDebugPath($debugPath)
    {
        $this->debugPath = $debugPath;
    }

    /**
     * Scan specific image
     *
     * @param Map $map The map
     * @return Result
     */
    public function scan(Map $map)
    {
        $info = getimagesize($this->imagePath);
        if ($info === false) {
            $command = escapeshellcmd('identify -format "%wx%h" ' . $this->imagePath) . '[0]';
            $geometry = `$command`;
            $info = explode("x", $geometry);
            $info['mime'] = 'image/jpeg';
        }
        /*
         * Setup result
         */
        $result = new Result();
        $result->setDimensions($info[0], $info[1]);
        $result->setImageMime($info['mime']);
        $result->setImagePath($this->imagePath);

        /*
         * Check map
         */
        $topRightMap = $map->topRight();
        $bottomLeftMap = $map->bottomLeft();

        $angleMap = $this->anglePoints($topRightMap, $bottomLeftMap);
        $distanceCornersMap = $this->distancePoints($topRightMap, $bottomLeftMap);

        /*
         * Check image
         */
        $topRightImage = $this->topRight($topRightMap);
        $bottomLeftImage = $this->bottomLeft($bottomLeftMap);

        /*
         * Adjust angle image
         */
        $angleImage = $this->anglePoints($topRightImage, $bottomLeftImage);
        $this->adjustRotate($angleMap - $angleImage);

        /*
         * Check image again
         */
        $topRightImage = $this->topRight($topRightMap);
        $bottomLeftImage = $this->bottomLeft($bottomLeftMap);

        /*
         * Adjust size image
         */
        $distanceCornersImage = $this->distancePoints($topRightImage, $bottomLeftImage);
        $p = 100 - ((100 * $distanceCornersImage) / $distanceCornersMap);
        $this->adjustSize($p);

        /*
         * Check image again
         */
        $topRightImage = $this->topRight($topRightMap);
        $bottomLeftImage = $this->bottomLeft($bottomLeftMap);

        $adjustX = $topRightImage->getX() - $topRightMap->getX();
        $adjustY = $bottomLeftImage->getY() - $bottomLeftMap->getY();

        foreach ($map->targets() as $target) {
            if ($target instanceof TextTarget) {
                $target->setImageBlob($this->textArea($target->getA()->moveX($adjustX)->moveY($adjustY), $target->getB()->moveX($adjustX)->moveY($adjustY)));
            } else {
                if ($target instanceof RectangleTarget) {
                    $area = $this->rectangleArea($target->getA()->moveX($adjustX)->moveY($adjustY), $target->getB()->moveX($adjustX)->moveY($adjustY), $target->getTolerance());
                }

                if ($target instanceof CircleTarget) {
                    $area = $this->circleArea($target->getPoint()->moveX($adjustX)->moveY($adjustY), $target->getRadius(), $target->getTolerance(), $target->getId());
                }

                $target->setBlackPixelsPercent($area->percentBlack());
                $target->setMarked($area->percentBlack() >= $target->getTolerance());
            }

            $result->addTarget($target);
        }

        if ($this->debug) {
            $this->debug();
        }

        $this->finish();

        return $result;
    }

    /**
     * Calculates distance between two points
     *
     * @param Point $a A point
     * @param Point $b A point
     * @return float
     */
    protected function distancePoints(Point $a, Point $b)
    {
        $diffX = $b->getX() - $a->getX();
        $diffY = $b->getY() - $a->getY();

        return sqrt(pow($diffX, 2) + pow($diffY, 2));
    }

    /**
     * Calculates angle between two points
     *
     * @param Point $a A point
     * @param Point $b A point
     * @return float
     */
    protected function anglePoints(Point $a, Point $b)
    {
        $diffX = $b->getX() - $a->getX();
        $diffY = $b->getY() - $a->getY();

        return rad2deg(atan($diffY / $diffX));
    }

    /**
     * Set debug flag
     *
     * @param bool $debug The debug flag
     * @return void
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * Create Result object from imagePath
     *
     * @param string $imagePath The image path
     * @return Result
     */
    protected function createResult($imagePath)
    {
        $info = getimagesize($imagePath);

        $result = new Result();
        $result->setDimensions($info[0], $info[1]);

        return $result;
    }
}
