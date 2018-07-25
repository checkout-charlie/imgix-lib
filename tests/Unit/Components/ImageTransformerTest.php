<?php

use Sparwelt\ImgixLib\Components\ImageTransformer;
use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\Exception\TransformationException;
use Sparwelt\ImgixLib\Interfaces\AttributeGeneratorInterface;
use Sparwelt\ImgixLib\Interfaces\ImageRendererInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 */
class ImageTransformerTest extends \PHPUnit\Framework\TestCase
{
    public function testTransformImage()
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
            ->expects($this->exactly(5))
            ->method('generateAttributeValue')
            ->withConsecutive(
                ['src.png', $attributesFilters['src']],
                ['src.png', $attributesFilters['srcset']],
                ['src.png', $attributesFilters['sizes']],
                ['http://mysite.com/extra1.png', []],
                ['/extra2.png', []]
            )
            ->will($this->onConsecutiveCalls('trans1', 'trans2', 'trans3', 'trans4', 'trans5'));

        $imageRenderer = $this->getMockBuilder(ImageRendererInterface::class)
            ->getMock();

        $imageRenderer
            ->expects($this->exactly(1))
            ->method('render')
            ->with(
                $this->callback(
                    function ($domElement) {
                        return ($domElement instanceof \DOMElement)
                            && ('bar' === $domElement->getAttribute('alt'))
                            && ('foo' === $domElement->getAttribute('class'))
                            && ('trans1' === $domElement->getAttribute('src'))
                            && ('trans2' === $domElement->getAttribute('srcset'))
                            && ('trans3' === $domElement->getAttribute('sizes'))
                            && ('trans4 1x, trans5 1x' === $domElement->getAttribute('other'))
                        ;
                    }
                )
            )
            ->will($this->returnValue('<img alt="bar" class="foo" src="trans1" srcset="trans2" sizes="trans4" data-srcset="trans3" other="trans5 1x, trans6 1x">'));

        $imageTransformer = new ImageTransformer($attributeGenerator, $imageRenderer);

        $this->assertEquals(
            '<img alt="bar" class="foo" src="trans1" srcset="trans2" sizes="trans4" data-srcset="trans3" other="trans5 1x, trans6 1x">',
            $imageTransformer->transformImage('<img src="src.png" class="foo" alt="bar" other="http://mysite.com/extra1.png 1x, /extra2.png 1x">', $attributesFilters)
        );
    }

    public function testTransformNoImage()
    {
        $attributesFilters = [
            'src' => ['h' => 100, 'w' => 100],
        ];

        $attributeGenerator = $this->getMockBuilder(AttributeGeneratorInterface::class)
            ->getMock();

        $attributeGenerator
            ->expects($this->exactly(0))
            ->method('generateAttributeValue');

        $imageRenderer = $this->getMockBuilder(ImageRendererInterface::class)
            ->getMock();

        $imageRenderer
            ->expects($this->exactly(0))
            ->method('render');

        $imageTransformer = new ImageTransformer($attributeGenerator, $imageRenderer);

        $this->expectException(TransformationException::class);
        $imageTransformer->transformImage('<a src="test.png" class="foo" alt="bar" other="http://mysite.com/extra1.png 1x, /extra2.png 1x">', $attributesFilters);
    }

    public function testTransformImageWithNoSrc()
    {
        $attributesFilters = [
            'src' => ['h' => 100, 'w' => 100],
        ];

        $attributeGenerator = $this->getMockBuilder(AttributeGeneratorInterface::class)
            ->getMock();

        $attributeGenerator
            ->expects($this->exactly(2))
            ->method('generateAttributeValue')
            ->withConsecutive(
                ['http://mysite.com/extra1.png', []],
                ['/extra2.png', []]
            )
            ->willReturnOnConsecutiveCalls('http://cdn.com/extra1.png', 'http://cdn.com/extra2.png');

        $imageRenderer = $this->getMockBuilder(ImageRendererInterface::class)
            ->getMock();

        $imageRenderer
            ->expects($this->exactly(1))
            ->method('render')
            ->with($this->callback(function ($imageDom) {
                return ($imageDom instanceof \DOMElement)
                    && 'foo' === $imageDom->getAttribute('class')
                    && 'bar' === $imageDom->getAttribute('alt')
                    && 'http://cdn.com/extra1.png 1x, http://cdn.com/extra2.png 2x' === $imageDom->getAttribute('other')
                    && 3 === $imageDom->attributes->length
                    ;
            }))
            ->willReturn('<img class="foo" alt="bar" other="http://cdn.com/extra1.png 1x, http://cdn.com/extra2.png 2x">');

        $imageTransformer = new ImageTransformer($attributeGenerator, $imageRenderer);

        $this->assertEquals(
            '<img class="foo" alt="bar" other="http://cdn.com/extra1.png 1x, http://cdn.com/extra2.png 2x">',
            $imageTransformer->transformImage('<img class="foo" alt="bar" other="http://mysite.com/extra1.png 1x, /extra2.png 2x">', $attributesFilters)
        );
    }

    public function testTransformImagemalformedAttributeSrc()
    {
        $attributesFilters = [
            'src' => ['h' => 100, 'w' => 100],
        ];

        $attributeGenerator = $this->getMockBuilder(AttributeGeneratorInterface::class)
            ->getMock();

        $attributeGenerator
            ->expects($this->exactly(2))
            ->method('generateAttributeValue')
            ->withConsecutive(
                ['http://mysite.com/extra1.png', []],
                ['malformed/extra2.png', []]
            )
            ->willReturnCallback(
                function ($imageUlr) {
                    if ('http://mysite.com/extra1.png' === $imageUlr) {
                        return 'http://cdn.com/extra1.png';
                    }

                     throw new ResolutionException('msg');
                }
            );

        $imageRenderer = $this->getMockBuilder(ImageRendererInterface::class)
            ->getMock();

        $imageRenderer
            ->expects($this->exactly(1))
            ->method('render')
            ->with($this->callback(function ($imageDom) {
                return ($imageDom instanceof \DOMElement)
                    && 'foo' === $imageDom->getAttribute('class')
                    && 'bar' === $imageDom->getAttribute('alt')
                    && 'http://cdn.com/extra1.png 1x, malformed/extra2.png 2x' === $imageDom->getAttribute('other')
                    && 3 === $imageDom->attributes->length
                    ;
            }))
            ->willReturn('<img class="foo" alt="bar" other="http://cdn.com/extra1.png 1x, malformed/extra2.png 2x">');

        $imageTransformer = new ImageTransformer($attributeGenerator, $imageRenderer);

        $this->assertEquals(
            '<img class="foo" alt="bar" other="http://cdn.com/extra1.png 1x, malformed/extra2.png 2x">',
            $imageTransformer->transformImage('<img class="foo" alt="bar" other="http://mysite.com/extra1.png 1x, malformed/extra2.png 2x">', $attributesFilters)
        );
    }
}
