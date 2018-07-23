<?php

use Sparwelt\ImgixLib\Components\CdnConfigurationParser;
use Sparwelt\ImgixLib\Exception\ConfigurationException;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 *
 * @covers \Sparwelt\ImgixLib\Components\CdnConfigurationParser
 */
class CdnConfigurationParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \Sparwelt\ImgixLib\Components\CdnConfigurationParser::parseArray()
     */
    public function testParseConfiguration()
    {
        $cdns = CdnConfigurationParser::parseArray(
            [
                'uploads' => [
                    'cdn_domains' => ['foo.imgix.net', 'bar.imgx.net'],
                    'source_domains' => ['www.mysite.com', 'www2.mysite.com'],
                    'path_pattern' => '/^\/media/uploads',
                    'sign_key' => '1234567890',
                    'shard_strategy' => 'cycle',
                ],
                'default' => [
                    'cdn_domains' => ['test.imgix.net', 'test2.imgx.net'],
                ],
            ]
        );

        $this->assertCount(2, $cdns);

        $this->assertEquals(['foo.imgix.net', 'bar.imgx.net'], $cdns[0]->getCdnDomains());
        $this->assertEquals(['www.mysite.com', 'www2.mysite.com'], $cdns[0]->getSourceDomains());
        $this->assertEquals('/^\/media/uploads', $cdns[0]->getPathPattern());
        $this->assertEquals('1234567890', $cdns[0]->getSignKey());
        $this->assertEquals('cycle', $cdns[0]->getShardStrategy());

        $this->assertEquals(['test.imgix.net', 'test2.imgx.net'], $cdns[1]->getCdnDomains());
        $this->assertEquals([], $cdns[1]->getSourceDomains());
        $this->assertEquals(null, $cdns[1]->getPathPattern());
        $this->assertEquals(null, $cdns[1]->getSignKey());
        $this->assertEquals('crc', $cdns[1]->getShardStrategy());
    }

    /**
     * @covers \Sparwelt\ImgixLib\Components\CdnConfigurationParser::parseArray()
     */
    public function testParseConfigurationWrongFormat()
    {
        $this->expectException(ConfigurationException::class);

        CdnConfigurationParser::parseArray(
            [
                'cdn_domains' => ['foo.imgix.net', 'bar.imgx.net'],
                'source_domains' => ['www.mysite.com', 'www2.mysite.com'],
                'path_pattern' => '^/media/uploads',
                'sign_key' => '1234567890',
                'shard_strategy' => 'cycle',
            ]
        );
    }
}
