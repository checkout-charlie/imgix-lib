<?php

use Sparwelt\ImgixLib\Components\AttributeGenerator;
use Sparwelt\ImgixLib\Components\ImageGenerator;
use Sparwelt\ImgixLib\Components\ImageRenderer;
use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\ImageTransformer;
use Sparwelt\ImgixLib\Interfaces\AttributeGeneratorInterface;
use Sparwelt\ImgixLib\Interfaces\ImageRendererInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 */
class ImageGeneratorTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerate()
    {
        $attributesFilters = [
            'src' => ['h' => 100, 'w' => 100],
            'srcset' => [
                '1x' => ['h' => 200, 'w' => 200],
                '2x' => ['h' => 300, 'w' => 300],
            ],
            'sizes' => '(min-width: 36em) 33.3vw, 100vw',
        ];

        $attributeGenerator = $this->getMockBuilder(AttributeGeneratorInterface::class)
            ->getMock();

        $attributeGenerator
            ->expects($this->exactly(3))
            ->method('generateAttributeValue')
            ->withConsecutive(
                ['test.png', $attributesFilters['src']],
                ['test.png', $attributesFilters['srcset']],
                ['test.png', $attributesFilters['sizes']]
            )
            ->will($this->returnValue('foo'));

        $imageRenderer = $this->getMockBuilder(ImageRendererInterface::class)
            ->getMock();

        $imageRenderer
            ->expects($this->exactly(1))
            ->method('render')
            ->with(
                $this->callback(
                    function ($domElement) {
                        return ($domElement instanceof \DOMElement)
                            && ('foo' === $domElement->getAttribute('src'))
                            && ('foo' === $domElement->getAttribute('srcset'))
                            && ('foo' === $domElement->getAttribute('sizes'));
                    }
                )
            )
            ->will($this->returnValue('<img src="foo" srcset="foo" sizes="foo">'));

        $imageGenerator = new ImageGenerator($attributeGenerator, $imageRenderer);

        $this->assertEquals('<img src="foo" srcset="foo" sizes="foo">', $imageGenerator->generateImage('test.png', $attributesFilters));
    }

    public function testGenerateImageEmptyFilter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $attributerGenerator = $this->getMockBuilder(AttributeGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder(ImageRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $imageGenerator = new ImageGenerator($attributerGenerator, $renderer);
        $imageGenerator->generateImage('/test.png', []);
    }

    public function testResolutionThrowsException()
    {
        $attributerGenerator = $this->getMockBuilder(AttributeGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attributerGenerator->expects($this->exactly(1))->method('generateAttributeValue')->with(
            'http://external.com/test.png',
            ['w' => 10]
        )->willThrowException(
            new ResolutionException()
        );

        $renderer = $this->getMockBuilder(ImageRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer->expects($this->exactly(1))->method('render')->with(
            $this->callback(
                function ($domElement) {
                    return ($domElement instanceof \DOMElement)
                        && ('http://external.com/test.png' === $domElement->getAttribute('src'));
                }
            )
        )->willReturn('<img src="http://external.com/test.png">');

        $imageGenerator = new ImageGenerator($attributerGenerator, $renderer);
        $this->assertEquals('<img src="http://external.com/test.png">', $imageGenerator->generateImage('http://external.com/test.png', ['src' => ['w' => 10]]));
    }
}
