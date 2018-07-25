<?php

use Sparwelt\ImgixLib\Components\CdnConfigurationParser;
use Sparwelt\ImgixLib\Exception\ConfigurationException;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 */
class CdnConfigurationParserTest extends \PHPUnit\Framework\TestCase
{
    public function testParseConfiguration()
    {
        $cdns = CdnConfigurationParser::parseArray(
            [
                'uploads' => [
                    'use_ssl' => true,
                    'cdn_domains' => ['foo.imgix.net', 'bar.imgx.net'],
                    'source_domains' => ['www.mysite.com', 'www2.mysite.com'],
                    'path_patterns' => ['^/media/uploads/'],
                    'sign_key' => '1234567890',
                    'shard_strategy' => 'cycle',
                ],
                'default' => [
                    'use_ssl' => true,
                    'cdn_domains' => ['test.imgix.net', 'test2.imgx.net'],
                ],
            ]
        );

        $this->assertCount(2, $cdns);

        $this->assertEquals(['foo.imgix.net', 'bar.imgx.net'], $cdns[0]->getCdnDomains());
        $this->assertEquals(['www.mysite.com', 'www2.mysite.com'], $cdns[0]->getSourceDomains());
        $this->assertEquals(['^/media/uploads/'], $cdns[0]->getPathPatterns());
        $this->assertEquals('1234567890', $cdns[0]->getSignKey());
        $this->assertEquals('cycle', $cdns[0]->getShardStrategy());

        $this->assertEquals(['test.imgix.net', 'test2.imgx.net'], $cdns[1]->getCdnDomains());
        $this->assertEquals([], $cdns[1]->getSourceDomains());
        $this->assertEquals([], $cdns[1]->getPathPatterns());
        $this->assertEquals(null, $cdns[1]->getSignKey());
        $this->assertEquals('crc', $cdns[1]->getShardStrategy());
    }

    public function testParseConfigurationWrongFormat()
    {
        $this->expectException(ConfigurationException::class);

        CdnConfigurationParser::parseArray(
            [
                'cdn_domains' => ['foo.imgix.net', 'bar.imgx.net'],
                'source_domains' => ['www.mysite.com', 'www2.mysite.com'],
                'path_patterns' => ['^/media/uploads/'],
                'sign_key' => '1234567890',
                'shard_strategy' => 'cycle',
            ]
        );
    }

    public function testParseConfigurationWrongType1()
    {
        $this->expectException(ConfigurationException::class);

        CdnConfigurationParser::parseArray([['cdn_domains' => 'string']]);
    }

    public function testParseConfigurationWrongType2()
    {
        $this->expectException(ConfigurationException::class);

        CdnConfigurationParser::parseArray([['cdn_domains' => ['foo'], 'path_patterns' => 'string']]);
    }

    public function testParseConfigurationWrongType3()
    {
        $this->expectException(ConfigurationException::class);

        CdnConfigurationParser::parseArray([['cdn_domains' => ['foo'], 'sign_key' => ['array']]]);
    }

    public function testParseConfigurationWrongType4()
    {
        $this->expectException(ConfigurationException::class);

        CdnConfigurationParser::parseArray([['cdn_domains' => ['foo'], 'shard_strategy' => ['array']]]);
    }

    public function testInvalidRegex()
    {
        $this->expectException(ConfigurationException::class);

        CdnConfigurationParser::parseArray([['cdn_domains' => ['foo'], 'path_patterns' => ['[']]]);
    }
}
