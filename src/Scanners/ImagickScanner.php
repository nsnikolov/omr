<?php

namespace JansenFelipe\OMR\Scanners;

use Imagick;
use ImagickDraw;
use JansenFelipe\OMR\Area;
use JansenFelipe\OMR\Contracts\Scanner;
use JansenFelipe\OMR\Point;

class ImagickScanner extends Scanner
{
    /**
     * Colors used when processing
     *
     * @var array
     */
    private $_colors = [
        'red' => '#CC0000',
        'green' => '#00CC00',
        'blue' => '#0000CC',
        'white' => '#FFFFFF',
        'black' => '#000000',
        'yellow' => '#FFFF00',
        'purple' => '#CC00CC'
    ];

    /**
     * Original image
     *
     * @var Imagick
     */
    private $original;

    /**
     * Imagick
     *
     * @var Imagick
     */
    private $imagick;

    /**
     * Imagick Draw
     *
     * @var ImagickDraw
     */
    private $draw;

    /**
     * Construct method
     */
    public function __construct()
    {
        $this->draw = new ImagickDraw();
        $this->draw->setFontSize(6);
    }

    /**
     * Create or return instance Imagick
     *
     * @return Imagick
     */
    private function getImagick()
    {
        if (is_null($this->imagick)) {
            $this->original = new Imagick($this->imagePath);

            $this->imagick = new Imagick($this->imagePath);
            $this->imagick->setResolution(300, 300);
            $this->imagick->thresholdImage(0.5);
            $this->imagick->medianFilterImage(2);
            $this->imagick->setImageCompression(imagick::COMPRESSION_JPEG);
            $this->imagick->setImageCompressionQuality(100);
        }

        return $this->imagick;
    }

    /**
     * Most point to the top/right
     *
     * @param Point $near A point
     * @return Point
     */
    protected function topRight(Point $near)
    {
        $imagick = $this->getImagick();

        $first = new Point($near->getX() - 200, $near->getY() - 100);
        $last = new Point($near->getX() + 100, $near->getY() + 200);

        $point = new Point($first->getX(), $last->getY());

        //Add draw debug
        $this->draw->setStrokeOpacity(1);
        $this->draw->setFillOpacity(0);
        $this->draw->setStrokeWidth(2);
        $this->draw->setStrokeColor($this->_colors['green']);
        $this->draw->rectangle($first->getX(), $first->getY(), $last->getX(), $last->getY());

        for ($y = $first->getY(); $y != $last->getY(); $y++) {
            for ($x = $first->getX(); $x != $last->getX(); $x++) {
                $color = $imagick->getImagePixelColor($x, $y)->getColor();

                if ($color['r'] <= 5 && $color['g'] <= 5 && $color['b'] <= 5) {
                    if ($x >= $point->getX()) {
                        $point->setX($x);
                    }

                    if ($y <= $point->getY()) {
                        $point->setY($y);
                    }
                }
            }
        }

        //Debug draw
        $this->draw->setFillColor($this->_colors['green']);
        $this->draw->point($point->getX(), $point->getY());
        $this->debug();

        return $point;
    }

    /**
     * Most point to the bottom/left
     *
     * @param Point $near The point
     *
     * @return Point
     */
    protected function bottomLeft(Point $near)
    {
        $imagick = $this->getImagick();
        $side = 200;

        $first = new Point($near->getX() - 100, $near->getY() - 200);
        $last = new Point($near->getX() + 200, $near->getY() + 100);

        $point = new Point($last->getX(), $first->getY());

        //Add draw debug
        $this->draw->setStrokeOpacity(1);
        $this->draw->setFillOpacity(0);
        $this->draw->setStrokeWidth(2);
        $this->draw->setStrokeColor($this->_colors['green']);
        $this->draw->rectangle($first->getX(), $first->getY(), $last->getX(), $last->getY());

        for ($y = $first->getY(); $y != $last->getY(); $y++) {
            for ($x = $first->getX(); $x != $last->getX(); $x++) {
                $color = $imagick->getImagePixelColor($x, $y)->getColor();

                if ($color['r'] <= 5 && $color['g'] <= 5 && $color['b'] <= 5) {
                    if ($x <= $point->getX()) {
                        $point->setX($x);
                    }
                    if ($y >= $point->getY()) {
                        $point->setY($y);
                    }
                }
            }
        }

        //Debug draw
        $this->draw->setFillColor($this->_colors['green']);
        $this->draw->point($point->getX(), $point->getY());

        return $point;
    }

    /**
     * Increases or decreases image size
     *
     * @param float $percent The Percentage
     * @return void
     */
    protected function adjustSize($percent)
    {
        $imagick = $this->getImagick();

        $widthAdjusted = $imagick->getImageWidth() + (($imagick->getImageWidth() * $percent) / 100);
        $heightAdjust = $imagick->getImageHeight() + (($imagick->getImageHeight() * $percent) / 100);

        $this->imagick->resizeImage($widthAdjusted, $heightAdjust, Imagick::FILTER_POINT, 0, false);

        $this->original->resizeImage($widthAdjusted, $heightAdjust, Imagick::FILTER_POINT, 0, false);
    }

    /**
     * Rotate image
     *
     * @param float $degrees The degrees to adjust
     * @return void
     */
    protected function adjustRotate($degrees)
    {
        if ($degrees < 0) {
            $degrees = 360 + $degrees;
        }

        $imagick = $this->getImagick();

        $originalWidth = $imagick->getImageWidth();
        $originalHeight = $imagick->getImageHeight();

        $this->imagick->rotateImage($this->_colors['white'], $degrees);
        $this->imagick->setImagePage($imagick->getimageWidth(), $imagick->getimageheight(), 0, 0);
        $this->imagick->cropImage($originalWidth, $originalHeight, ($imagick->getimageWidth() - $originalWidth) / 2, ($imagick->getimageHeight() - $originalHeight) / 2);

        $this->original->rotateImage($this->_colors['white'], $degrees);
        $this->original->setImagePage($imagick->getimageWidth(), $imagick->getimageheight(), 0, 0);
        $this->original->cropImage($originalWidth, $originalHeight, ($imagick->getimageWidth() - $originalWidth) / 2, ($imagick->getimageHeight() - $originalHeight) / 2);
    }

    /**
     * Generate file debug.jpg with targets, topRight and buttonLeft
     *
     * @return void
     */
    public function debug()
    {
        $imagick = $this->getImagick();
        $imagick->drawImage($this->draw);
        $imagick->writeImage($this->debugPath);
    }

    /**
     * Returns pixel analysis in a rectangular area
     *
     * @param Point $a A point
     * @param Point $b A point
     * @param float $tolerance The tolerance
     * @return Area
     */
    protected function rectangleArea(Point $a, Point $b, $tolerance)
    {
        $imagick = $this->getImagick();

        $width = $b->getX() - $a->getX();
        $height = $b->getY() - $a->getY();

        $pixels = $imagick->exportImagePixels($a->getX(), $a->getY(), $width, $height, "I", Imagick::PIXEL_CHAR);
        $counts = array_count_values($pixels);

        $blacks = 0;
        $whites = 0;

        foreach ($counts as $k => $qtd) {
            if ($k === 0) {
                $whites += $qtd;
            } else {
                $blacks += $qtd;
            }
        }

        $area = new Area(count($pixels), $whites, $blacks);

        //Add draw debug
        $this->draw->setStrokeOpacity(1);
        $this->draw->setFillOpacity(0);
        $this->draw->setStrokeWidth(2);
        $this->draw->setStrokeColor($area->percentBlack() >= $tolerance ? $this->_colors['blue'] : $this->_colors['red']);
        $this->draw->rectangle($a->getX(), $a->getY(), $b->getX(), $b->getY());

        return $area;
    }

    /**
     * Returns pixel analysis in a circular area
     *
     * @param Point $a A point
     * @param float $radius The radius
     * @param float $tolerance The tolerance
     * @return Area
     */
    protected function circleArea(Point $a, $radius, $tolerance)
    {
        $imagick = $this->getImagick();
        $x = $a->getX();
        $y = $a->getY();
        $leg = sqrt(($radius * $radius) / 2);

        $pixels = $imagick->exportImagePixels($x, $y, $leg, $leg, "I", Imagick::PIXEL_CHAR);
        $counts = array_count_values($pixels);

        $blacks = 0;
        $whites = 0;

        foreach ($counts as $k => $qtd) {
            if ($k === 255) {
                $whites += $qtd;
            } else {
                $blacks += $qtd;
            }
        }

        $area = new Area(count($pixels), $whites, $blacks);

        //Add draw debug
        $this->draw->setStrokeOpacity(1);
        $this->draw->setFillOpacity(0);
        $this->draw->setStrokeWidth(2);
        $this->draw->setStrokeColor($area->percentBlack() >= $tolerance ? $this->_colors['blue'] : $this->_colors['red']);
        $this->draw->rectangle(($x - $leg), ($y + $leg), ($x + $leg), ($y - $leg));

        $this->draw->annotation(($x + $leg * 2), $y, number_format($area->percentBlack(), 2) . '%');

        return $area;
    }

    /**
     * Returns image blob in a rectangular area
     *
     * @param Point $a A point
     * @param Point $b A point
     * @return string
     */
    protected function textArea(Point $a, Point $b)
    {
        $width = $b->getX() - $a->getX();
        $height = $b->getY() - $a->getY();

        $region = $this->original->getImageRegion($width, $height, $a->getX(), $a->getY());

        //Add draw debug
        $this->draw->setStrokeOpacity(1);
        $this->draw->setFillOpacity(0);
        $this->draw->setStrokeWidth(2);
        $this->draw->setStrokeColor($this->_colors['yellow']);
        $this->draw->rectangle($a->getX(), $a->getY(), $b->getX(), $b->getY());

        return $region->getImageBlob();
    }

    /**
     * Finish processes
     *
     * @return void
     */
    protected function finish()
    {
        $this->getImagick()->clear();
        $this->original->clear();
    }
}
