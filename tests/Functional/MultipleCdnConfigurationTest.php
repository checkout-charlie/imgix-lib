<?php

use Sparwelt\ImgixLib\ImgixService;
use Sparwelt\ImgixLib\ImgixServiceFactory;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 *
 * @covers \Sparwelt\ImgixLib\ImgixService
 */
class MultipleCdnConfigurationTest extends \PHPUnit\Framework\TestCase
{
    const CDN_CONFIGURATION = [
        'source_domains_and_pattern' => [
            'cdn_domains' => ['source-domain-and-pattern.imgix.net'],
            'source_domains' => ['mysite.com'],
            'path_patterns' => ['^[/]pattern/'],
        ],
        'source_sub_domain' => [
            'cdn_domains' => ['source-sub-domain.imgix.net'],
            'source_domains' => ['www3.mysite.com', 'www3.mysite.com'],
        ],
        'source_domains' => [
            'cdn_domains' => ['source-domain.imgix.net'],
            'source_domains' => ['mysite.com'],
        ],
        'pattern' => [
            'cdn_domains' => ['pattern.imgix.net'],
            'path_patterns' => ['^[/]pattern/'],
        ],
        'sign_key' => [
            'cdn_domains' => ['sign-key.imgix.net'],
            'path_patterns' => ['^[/]sign-key/'],
            'sign_key' => '12345',
        ],
        'shard_crc' => [
            'cdn_domains' => ['shard-crc1.imgix.net', 'shard-crc2.imgix.net'],
            'path_patterns' => ['^[/]shard-crc/'],
        ],
        'shard_cycle' => [
            'cdn_domains' => ['shard-cycle1.imgix.net', 'shard-cycle2.imgix.net'],
            'path_patterns' => ['^[/]shard-cycle/'],
            'shard_strategy' => 'cycle',
        ],
        'default_params' => [
            'cdn_domains' => ['default-params.imgix.net'],
            'path_patterns' => ['^[/]default-params/'],
            'default_query_params' => ['cb' => 1234],
        ],
        'bypass_dev' => [
            'cdn_domains' => ['my-dev-domain.test'],
            'source_domains' => ['my-dev-domain.test'],
            'generate_filter_params' => false,
            'use_ssl' => false,
        ],
        'default' => [
            'cdn_domains' => ['default.imgix.net'],
        ],
    ];

    const FILTERS_CONFIGURATION = [
    ];

    /** @var ImgixService */
    private $imgix;

    public function setUp()
    {
        parent::setUp();
        $this->imgix = ImgixServiceFactory::createFromConfiguration(
            self::CDN_CONFIGURATION,
            self::FILTERS_CONFIGURATION
        );
    }

    public function testDefault()
    {
        $this->assertEquals(
            'https://default.imgix.net/test.png?h=100&w=200',
            $this->imgix->generateUrl('test.png', ['w' => 200, 'h' => 100])
        );
    }

    public function testDomainAndPattern()
    {
        $this->assertEquals(
            'https://source-domain-and-pattern.imgix.net/pattern/test.png?h=100&w=200',
            $this->imgix->generateUrl('http://www.mysite.com/pattern/test.png', ['w' => 200, 'h' => 100])
        );
    }

    public function testPattern()
    {
        $this->assertEquals(
            'https://pattern.imgix.net/pattern/test.png?h=100&w=200',
            $this->imgix->generateUrl('/pattern/test.png', ['w' => 200, 'h' => 100])
        );
    }

    public function testDomain()
    {
        $this->assertEquals(
            'https://source-domain.imgix.net/test.png?h=100&w=200',
            $this->imgix->generateUrl('http://www.mysite.com/test.png', ['w' => 200, 'h' => 100])
        );

        $this->assertEquals(
            'https://source-domain.imgix.net/test.png?h=100&w=200',
            $this->imgix->generateUrl('http://www2.mysite.com/test.png', ['w' => 200, 'h' => 100])
        );
    }

    public function testSubDomain()
    {
        $this->assertEquals(
            'https://source-sub-domain.imgix.net/test.png?h=100&w=200',
            $this->imgix->generateUrl('http://www3.mysite.com/test.png', ['w' => 200, 'h' => 100])
        );
    }

    public function testSignKey()
    {
        $this->assertEquals(
            'https://sign-key.imgix.net/sign-key/test.png?h=100&w=200&s=37980da0ecb2f0f0b52e05a9b32e78af',
            $this->imgix->generateUrl('/sign-key/test.png', ['w' => 200, 'h' => 100])
        );
    }

    public function testDefaultQueryParams()
    {
        $this->assertEquals(
            'https://default-params.imgix.net/default-params/test.png?cb=1234&h=100&w=200',
            $this->imgix->generateUrl('/default-params/test.png', ['w' => 200, 'h' => 100])
        );
    }

    public function testDisableQueryParams()
    {
        $this->assertEquals(
            'http://my-dev-domain.test/test.png',
            $this->imgix->generateUrl('http://my-dev-domain.test/test.png', ['w' => 200, 'h' => 100])
        );
    }

    public function testShardCRC()
    {
        $this->assertEquals(
            'https://shard-crc1.imgix.net/shard-crc/test-1234.png?h=100&w=200',
            $this->imgix->generateUrl('/shard-crc/test-1234.png', ['w' => 200, 'h' => 100])
        );

        $this->assertEquals(
            'https://shard-crc2.imgix.net/shard-crc/test-4567.png?h=100&w=200',
            $this->imgix->generateUrl('/shard-crc/test-4567.png', ['w' => 200, 'h' => 100])
        );

        $this->assertEquals(
            'https://shard-crc2.imgix.net/shard-crc/test-4567.png?h=100&w=200',
            $this->imgix->generateUrl('/shard-crc/test-4567.png', ['w' => 200, 'h' => 100])
        );

        $this->assertEquals(
            'https://shard-crc1.imgix.net/shard-crc/test-1234.png?h=100&w=200',
            $this->imgix->generateUrl('/shard-crc/test-1234.png', ['w' => 200, 'h' => 100])
        );
    }

    public function testShardCycle()
    {
        $this->assertEquals(
            'https://shard-cycle2.imgix.net/shard-cycle/test-1234.png?h=100&w=200',
            $this->imgix->generateUrl('/shard-cycle/test-1234.png', ['w' => 200, 'h' => 100])
        );

        $this->assertEquals(
            'https://shard-cycle1.imgix.net/shard-cycle/test-4567.png?h=100&w=200',
            $this->imgix->generateUrl('/shard-cycle/test-4567.png', ['w' => 200, 'h' => 100])
        );

        $this->assertEquals(
            'https://shard-cycle2.imgix.net/shard-cycle/test-4567.png?h=100&w=200',
            $this->imgix->generateUrl('/shard-cycle/test-4567.png', ['w' => 200, 'h' => 100])
        );

        $this->assertEquals(
            'https://shard-cycle1.imgix.net/shard-cycle/test-1234.png?h=100&w=200',
            $this->imgix->generateUrl('/shard-cycle/test-1234.png', ['w' => 200, 'h' => 100])
        );
    }
}
