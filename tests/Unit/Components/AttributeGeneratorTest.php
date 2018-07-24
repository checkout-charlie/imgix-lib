<?php

use Sparwelt\ImgixLib\Components\AttributeGenerator;
use Sparwelt\ImgixLib\Exception\ConfigurationException;
use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\Interfaces\UrlGeneratorInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 */
class AttributeGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \Sparwelt\ImgixLib\Components\AttributeGenerator::__construct()
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
            ->willReturn('test.png?foo=bar');

        $attributeGenerator = new AttributeGenerator($urlGenerator);

        $this->assertSame(
            'test.png?foo=bar 1x, test.png?foo=bar 380w',
            $attributeGenerator->generateAttributeValue('test.png', $filter)
        );
    }

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

    public function testGenerateAttributeInvalidFilter()
    {
        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)
            ->getMock();

        $urlGenerator
            ->expects($this->exactly(0))
            ->method('generateUrl');

        $attributeGenerator = new AttributeGenerator($urlGenerator);

        $this->expectException(ConfigurationException::class);
        $attributeGenerator->generateAttributeValue('test.png', new stdClass());
    }

    public function testGenerateMatrixAttributeUrlResolutionException()
    {
        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)
            ->getMock();

        $urlGenerator
            ->expects($this->exactly(1))
            ->method('generateUrl')
            ->willThrowException(new ResolutionException());

        $attributeGenerator = new AttributeGenerator($urlGenerator);

        $this->expectException(ResolutionException::class);
        $this->assertEquals('', $attributeGenerator->generateAttributeValue('malformedurl.png', ['w' => 12]));
    }

    public function testIsMatrix()
    {
        $this->assertTrue(AttributeGenerator::isMatrix(['a' => ['b']]));
        $this->assertTrue(AttributeGenerator::isMatrix(['a' => ['b' => 1, 'c' => 2]]));
        $this->assertFalse(AttributeGenerator::isMatrix(['a' => 1, 'b' => 2]));
        $this->assertFalse(AttributeGenerator::isMatrix(['a', 'b']));
        $this->assertFalse(AttributeGenerator::isMatrix('a'));
        $this->assertFalse(AttributeGenerator::isMatrix(new stdClass()));
    }
}
