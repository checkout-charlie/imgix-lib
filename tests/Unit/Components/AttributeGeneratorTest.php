<?php

use Sparwelt\ImgixLib\Components\AttributeGenerator;
use Sparwelt\ImgixLib\Interfaces\UrlGeneratorInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 */
class AttributeGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \Sparwelt\ImgixLib\Components\AttributeGenerator::generateAttributeValue()
     */
    public function testGenerateAttributeArray()
    {
        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)
            ->getMock();

        $filter = ['w' => 100, 'h' => 200];

        $urlGenerator
            ->expects($this->exactly(1))
            ->method('generateUrl')
            ->with('test.png', $filter)
            ->will($this->returnValue('test.png?foo=bar'));

        $attributeGenerator = new AttributeGenerator($urlGenerator);

        $this->assertSame(
            'test.png?foo=bar',
            $attributeGenerator->generateAttributeValue('test.png', $filter)
        );
    }
    /**
     * @covers \Sparwelt\ImgixLib\Components\AttributeGenerator::generateAttributeValue()
     */
    public function testGenerateAttributeMatrix()
    {
        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)
            ->getMock();

        $filter = [
            '1x' => ['w' => 111, 'h' => 222],
            '380w' => ['w' => 333, 'h' => 444],
        ];

        $urlGenerator
            ->expects($this->exactly(2))
            ->method('generateUrl')
            ->with('test.png', $this->logicalOr($filter['1x'], $filter['380w']))
            ->will($this->returnValue('test.png?foo=bar'));

        $attributeGenerator = new AttributeGenerator($urlGenerator);

        $this->assertSame(
            'test.png?foo=bar 1x, test.png?foo=bar 380w',
            $attributeGenerator->generateAttributeValue('test.png', $filter)
        );
    }

    /**
     * @covers \Sparwelt\ImgixLib\Components\AttributeGenerator::generateAttributeValue()
     */
    public function testGenerateAttributeScalar()
    {
        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)
            ->getMock();

        $filter = 'scalar-value';

        $urlGenerator
            ->expects($this->exactly(0))
            ->method('generateUrl');

        $attributeGenerator = new AttributeGenerator($urlGenerator);

        $this->assertSame('scalar-value', $attributeGenerator->generateAttributeValue('test.png', $filter));
    }
}
