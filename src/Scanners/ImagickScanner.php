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
        'green' => '#009900',
        'blue' => '#0000CC',
        'white' => '#FFFFFF',
        'black' => '#000000',
        'yellow' => '#FFFF00',
        'purple' => '#CC00CC',
        'gray' => '#D3D3D3'
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
    private $drawDebug;

     /**
     * Imagick Draw
     *
     * @var ImagickDraw
     */
    private $drawVerification;

       /**
     * Construct method
     */
    public function __construct()
    {
        $this->drawDebug = new ImagickDraw();
        $this->drawDebug->setFontSize(6);
        $this->drawVerification = new ImagickDraw();
        $this->drawVerification->setFontSize(6);
        $this->drawVerification->setStrokeOpacity(1);
        $this->drawVerification->setFillOpacity(0);
        $this->drawVerification->setStrokeWidth(5);
        $this->drawVerification->setStrokeColor($this->_colors['green']);
    }

    /**
     * Create or return instance Imagick
     *
     * @return Imagick
     */
    private function getImagick()
    {
        if (is_null($this->imagick)) {
            $this->original = new Imagick();
            $this->original->setResolution(300, 300);
            $this->original->readimage($this->imagePath);
            $this->original->setImageFormat('jpeg');

            $this->imagick = new Imagick();
            $this->imagick->setResolution(300, 300);
            $this->imagick->readimage($this->imagePath);
            $this->imagick->setImageCompressionQuality(100);
            $this->imagick->setImageFormat('jpeg');
            $this->imagick->thresholdImage(0.37 * \Imagick::getQuantum()); //sholud be tested for different values of constant 0.37 with different phones pictures
            //$this->imagick->thresholdImage(0.1);
            $this->imagick->medianFilterImage(1);
            $this->imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        }

        return $this->imagick;
    }

    /**
     * Most point to the top/right
     *
     * @param Point $near A point
     * @return Point
     * @todo Get this to work
     */
    protected function topRight(Point $near)
    {
        //return $near;
        $imagick = $this->getImagick();

        //$first = new Point($near->getX() - 200, $near->getY() - 100);
        //$last  = new Point($near->getX() + 100, $near->getY() + 200);

        $first = new Point($near->getX() - 0, $near->getY() - 100);
        $last = new Point($near->getX() + 100, $near->getY() + 0);


        $point = new Point($first->getX(), $last->getY());

        //Add drawDebug debug
        $this->drawDebug->setStrokeOpacity(1);
        $this->drawDebug->setFillOpacity(0);
        $this->drawDebug->setStrokeWidth(2);
        $this->drawDebug->setStrokeColor($this->_colors['blue']);
        $this->drawDebug->rectangle($first->getX(), $first->getY(), $last->getX(), $last->getY());

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

        //Debug drawDebug
        $this->drawDebug->setFillColor($this->_colors['green']);
        $this->drawDebug->point($point->getX(), $point->getY());
        $this->debug();

        return $point;
    }

    /**
     * Most point to the bottom/left
     *
     * @param Point $near The point
     * @return Point
     * @todo Get this to work
     */
    protected function bottomLeft(Point $near)
    {
        //return $near;
        $imagick = $this->getImagick();
        $side = 200;

        //$first = new Point($near->getX() - 100, $near->getY() - 200);
        //$last  = new Point($near->getX() + 200, $near->getY() + 100);

       // $first = new Point($near->getX() - 0, $near->getY() - 100);
        //$last = new Point($near->getX() + 100, $near->getY() + 0);
        $first = new Point($near->getX() - 100, $near->getY() - 0);
        $last = new Point($near->getX() + 0, $near->getY() + 100);

        $point = new Point($last->getX(), $first->getY());

        //Add drawDebug debug
        $this->drawDebug->setStrokeOpacity(1);
        $this->drawDebug->setFillOpacity(0);
        $this->drawDebug->setStrokeWidth(2);
        $this->drawDebug->setStrokeColor($this->_colors['purple']);
        $this->drawDebug->rectangle($first->getX(), $first->getY(), $last->getX(), $last->getY());

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

        //Debug drawDebug
        $this->drawDebug->setFillColor($this->_colors['purple']);
        $this->drawDebug->point($point->getX(), $point->getY());

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
        if(strlen($this->verificationPath) > 0 ){
            $imagickV = $this->getImagick();
            $imagickV->drawImage($this->drawVerification);
            $imagickV->writeImage($this->verificationPath);
        }
        if($this->debug){
            $imagick = $this->getImagick();
            $imagick->drawImage($this->drawDebug);
            $imagick->writeImage($this->debugPath);        
        }

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
                $blacks += $qtd;
            } else {
                $whites += $qtd;
            }
        }

        $area = new Area(count($pixels), $whites, $blacks);

        //Add drawDebug debug
        $this->drawDebug->setStrokeOpacity(1);
        $this->drawDebug->setFillOpacity(0);
        $this->drawDebug->setStrokeWidth(2);
        $this->drawDebug->setStrokeColor($area->percentBlack() >= $tolerance ? $this->_colors['green'] : $this->_colors['red']);
        $this->drawDebug->setStrokeWidth(1);
        $this->drawDebug->annotation(($b->getX() + 10), $a->getY(), number_format($area->percentBlack(), 2) . '%');

        
        $this->drawDebug->rectangle($a->getX(), $a->getY(), $b->getX(), $b->getY());
        if($area->percentBlack() >= $tolerance ){
            $this->drawVerification->rectangle($a->getX(), $a->getY(), $b->getX(), $b->getY());
        }

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
    protected function circleArea(Point $a, $radius, $tolerance, $id = null)
    {
        $imagick = $this->getImagick();
        $x = $a->getX();
        $y = $a->getY();
        $leg = sqrt(($radius * $radius) / 2);

        $pixels = $imagick->exportImagePixels($x-$leg, $y-$leg, 2*$leg, 2*$leg, "I", Imagick::PIXEL_CHAR);
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

        //Add drawDebug debug
        $this->drawDebug->setStrokeOpacity(1);
        $this->drawDebug->setFillOpacity(0);
        $this->drawDebug->setStrokeWidth(2);
        $this->drawDebug->setStrokeColor($area->percentBlack() >= $tolerance ? $this->_colors['green'] : $this->_colors['red']);
        $this->drawDebug->rectangle(($x - $leg), ($y + $leg), ($x + $leg), ($y - $leg));

        $this->drawDebug->setStrokeOpacity(1);
        $this->drawDebug->setFillOpacity(1);
        $this->drawDebug->setStrokeWidth(1);
        $this->drawDebug->annotation(($x + $leg * 2), $y, number_format($area->percentBlack(), 2) . '%');
        
        if (!empty($id)) {
            $this->drawDebug->setStrokeColor($this->_colors['gray']);
            $this->drawDebug->setStrokeOpacity(0.7);
            $this->drawDebug->setFillOpacity(0.7);
            $this->drawDebug->setStrokeWidth(1);
            $this->drawDebug->annotation(($x + $leg * 2), ($y - $leg * 2), $id);
        }

        if($area->percentBlack() >= $tolerance ){
            $this->drawVerification->rectangle(($x - 2*$leg), ($y + 2*$leg), ($x + 2*$leg), ($y - 2*$leg));
        }

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

        //Add drawDebug debug
        $this->drawDebug->setStrokeOpacity(1);
        $this->drawDebug->setFillOpacity(0);
        $this->drawDebug->setStrokeWidth(2);
        $this->drawDebug->setStrokeColor($this->_colors['yellow']);
        $this->drawDebug->rectangle($a->getX(), $a->getY(), $b->getX(), $b->getY());

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
