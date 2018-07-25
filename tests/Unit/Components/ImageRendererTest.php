<?php

use Sparwelt\ImgixLib\Components\ImageRenderer;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 */
class ImageRendererTest extends \PHPUnit\Framework\TestCase
{
    public function testRenderImage()
    {
        $image = (new \DOMDocument())->createElement('img');
        $image->setAttribute('src', '111');
        $image->setAttribute('foo', '222');
        $image->setAttribute('bar', '333');
        $renderer = new ImageRenderer();
        $this->assertEquals('<img src="111" foo="222" bar="333">', $renderer->render($image));
    }
}
