<?php

use Sparwelt\ImgixLib\ImgixService;
use Sparwelt\ImgixLib\ImgixServiceFactory;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 *
 * @covers \Sparwelt\ImgixLib\ImgixServiceFactory
 */
class ImgixServiceFactoryTest extends \PHPUnit\Framework\TestCase
{
    const CDN_CONFIGURATION = [
        'uploads' => [
            'cdn_domains' => ['foo.imgix.net', 'bar.imgx.net'],
            'source_domains' => ['www.mysite.com', 'www2.mysite.com'],
            'path_patterns' => ['^/media/uploads'],
            'sign_key' => '1234567890',
            'shard_strategy' => 'cycle',
        ],
        'default' => [
            'cdn_domains' => ['test.imgix.net', 'test2.imgx.net'],
        ],
    ];

    const FILTERS_CONFIGURATION = [
        'simple' => ['w' => 100, 'h' => 200],
        'responsive' => [
            'src' => ['w' => 100, 'h' => 200],
            'srcset' => [
                '1x' => [
                    'w' => 300,
                    'h' => 400,
                ],
                '2x' => [
                    'w' => 500,
                    'h' => 600,
                ],
            ],
            'sizes' => '(min-width: 36em) 33.3vw, 100vw',
        ],
        'lazysizes' => [
            'src' => 'data:image/gif;base64,R0lGO...',
            'data-src' => ['w' => 100, 'h' => 200],
            'data-srcset' => [
                '1x' => [
                    'w' => 300,
                    'h' => 400,
                ],
                '2x' => [
                    'w' => 500,
                    'h' => 600,
                ],
            ],
            'data-sizes' => 'auto',
        ],
    ];

    /**
     * @covers \Sparwelt\ImgixLib\ImgixServiceFactory::createFromConfiguration()
     */
    public function testCreateFromConfiguration()
    {
        $this->assertInstanceOf(ImgixService::class, ImgixServiceFactory::createFromConfiguration(self::CDN_CONFIGURATION));
        $this->assertInstanceOf(ImgixService::class, ImgixServiceFactory::createFromConfiguration(self::CDN_CONFIGURATION, self::FILTERS_CONFIGURATION));
    }
}
