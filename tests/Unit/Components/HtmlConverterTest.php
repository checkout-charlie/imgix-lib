<?php

use Sparwelt\ImgixLib\Components\HtmlConverter;
use Sparwelt\ImgixLib\Interfaces\ImageTransformerInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 *
 * @covers \Sparwelt\ImgixLib\Components\HtmlConverter
 */
class HtmlConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \Sparwelt\ImgixLib\Components\HtmlConverter::convertHtml()
     */
    public function testConvertHtml()
    {
        $originalHtml = '
            </ul><ul><img src="/test.png" alt="foo" ></ul>
            </td><IMG DATA-SRC="/test2.png" alt="bar" /><a href="foo"<img src="/test3.png" alt="bar"/>
            <img src="" alt="bar">< img data-src="/test5.png" alt="bar">
            ';

        $expectedHtml = '
            </ul><ul><img src="/test.converted" alt="foo" ></ul>
            </td><IMG DATA-SRC="/test2.converted" alt="bar" /><a href="foo"<img src="/test3.converted" alt="bar"/>
            <img src="" alt="bar">< img data-src="/test5.png" alt="bar">
            ';

        $attributesFilters = ['src' => ['w' => 111, 'h' => 222]];

        $imageTransformer = $this->getMockBuilder(ImageTransformerInterface::class)
            ->getMock();

        $imageTransformer
            ->expects($this->exactly(4))
            ->method('transformImage')
            ->withConsecutive(
                ['<img src="/test.png" alt="foo" >', $attributesFilters],
                ['<IMG DATA-SRC="/test2.png" alt="bar" />', $attributesFilters],
                ['<img src="/test3.png" alt="bar"/>', $attributesFilters],
                ['<img src="" alt="bar">', $attributesFilters]
            )
            ->will($this->returnCallback(function ($imghtml) {
                return str_replace('.png', '.converted', $imghtml);
            }));

        $converter = new HtmlConverter($imageTransformer);

        $this->assertEquals($expectedHtml, $converter->convertHtml($originalHtml, $attributesFilters));
    }
}
