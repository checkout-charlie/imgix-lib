<?php

use Sparwelt\ImgixLib\Exception\ConfigurationException;
use Sparwelt\ImgixLib\ImgixService;
use Sparwelt\ImgixLib\ImgixServiceFactory;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:33
 */
class ImgixServiceTest extends \PHPUnit\Framework\TestCase
{
    const CDN = [
        [
            'cdn_domains' => ['test.imgix.net'],
        ],
    ];

    const FILTERS = [
        'normal' => [
            'src' => [
                'h' => 50,
                'w' => 100,
            ],
        ],
        'responsive1' => [
            'src' => [
                'h' => 100,
                'w' => 200,
            ],
            'srcset' => [
                '2x' => [
                    'h' => 200,
                    'w' => 400,
                ],
                '3x' => [
                    'h' => 300,
                    'w' => 600,
                ],
            ],
        ],
        'responsive2' => [
            'src' => [
                'h' => 150,
                'w' => 300,
            ],
            'srcset' => [
                '100w' => [
                    'h' => 300,
                    'w' => 600,
                ],
                '500w' => [
                    'h' => 600,
                    'w' => 900,
                ],
            ],
            'sizes' => '(min-width: 900px) 1000px, (max-width: 900px)',
        ],
        'lazysizes_transparent' => [
            'src' => [
                'h' => 30,
                'w' => 60,
            ],
            'srcset' => 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==',
            'data-srcset' => [
                '100w' => [
                    'h' => 60,
                    'w' => 120,
                ],
                '500w' => [
                    'h' => 90,
                    'w' => 180,
                ],
            ],
            'data-sizes' => 'auto',
            'class' => 'lazyload',
        ],
    ];

    /** @var ImgixService */
    private $imgix;

    public function setUp()
    {
        parent::setUp();
        $this->imgix = ImgixServiceFactory::createFromConfiguration(
            self::CDN,
            self::FILTERS
        );
    }

    /**
     * @covers \Sparwelt\ImgixLib\ImgixService::generateUrl()
     */
    public function testGenerateUrl()
    {
        $this->assertEquals(
            'https://test.imgix.net/test.png',
            $this->imgix->generateUrl('/test.png')
        );


        $this->assertEquals(
            'https://test.imgix.net/test.png?h=50&w=100',
            $this->imgix->generateUrl('/test.png', 'normal.src')
        );

        $this->assertEquals(
            'https://test.imgix.net/test.png?h=50&w=100',
            $this->imgix->generateUrl('/test.png', self::FILTERS['normal']['src'])
        );


        $this->assertEquals(
            'https://test.imgix.net/test.png?h=100&w=200',
            $this->imgix->generateUrl('/test.png', 'responsive1.src')
        );

        $this->assertEquals(
            'https://test.imgix.net/test.png?h=100&w=200',
            $this->imgix->generateUrl('/test.png', self::FILTERS['responsive1']['src'])
        );


        $this->assertEquals(
            'https://test.imgix.net/test.png?h=150&w=300',
            $this->imgix->generateUrl('/test.png', 'responsive2.src')
        );

        $this->assertEquals(
            'https://test.imgix.net/test.png?h=150&w=300',
            $this->imgix->generateUrl('/test.png', self::FILTERS['responsive2']['src'])
        );


        $this->assertEquals(
            'https://test.imgix.net/test.png?h=30&w=60',
            $this->imgix->generateUrl('/test.png', 'lazysizes_transparent.src')
        );

        $this->assertEquals(
            'https://test.imgix.net/test.png?h=30&w=60',
            $this->imgix->generateUrl('/test.png', self::FILTERS['lazysizes_transparent']['src'])
        );
    }

    /**
     * @covers \Sparwelt\ImgixLib\ImgixService::generateImage()
     */
    public function testGenerateImageNoFilters()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->imgix->generateImage('/test.png', []);
    }

    /**
     * @covers \Sparwelt\ImgixLib\ImgixService::generateImage()
     */
    public function testGenerateImage()
    {
        $this->assertEquals(
            '<img src="https://test.imgix.net/test.png?h=50&w=100">',
            $this->imgix->generateImage('/test.png', 'normal')
        );

        $this->assertEquals(
            '<img src="https://test.imgix.net/test.png?h=50&w=100">',
            $this->imgix->generateImage('/test.png', self::FILTERS['normal'])
        );

        $this->assertEquals(
            '<img src="https://test.imgix.net/test.png?h=100&w=200" srcset="https://test.imgix.net/test.png?h=200&w=400 2x, https://test.imgix.net/test.png?h=300&w=600 3x">',
            $this->imgix->generateImage('/test.png', 'responsive1')
        );

        $this->assertEquals(
            '<img src="https://test.imgix.net/test.png?h=100&w=200" srcset="https://test.imgix.net/test.png?h=200&w=400 2x, https://test.imgix.net/test.png?h=300&w=600 3x">',
            $this->imgix->generateImage('/test.png', self::FILTERS['responsive1'])
        );

        $this->assertEquals(
            '<img src="https://test.imgix.net/test.png?h=150&w=300" srcset="https://test.imgix.net/test.png?h=300&w=600 100w, https://test.imgix.net/test.png?h=600&w=900 500w" sizes="(min-width: 900px) 1000px, (max-width: 900px)">',
            $this->imgix->generateImage('/test.png', 'responsive2')
        );

        $this->assertEquals(
            '<img src="https://test.imgix.net/test.png?h=150&w=300" srcset="https://test.imgix.net/test.png?h=300&w=600 100w, https://test.imgix.net/test.png?h=600&w=900 500w" sizes="(min-width: 900px) 1000px, (max-width: 900px)">',
            $this->imgix->generateImage('/test.png', self::FILTERS['responsive2'])
        );

        $this->assertEquals(
            '<img src="https://test.imgix.net/test.png?h=30&w=60" srcset="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-srcset="https://test.imgix.net/test.png?h=60&w=120 100w, https://test.imgix.net/test.png?h=90&w=180 500w" data-sizes="auto" class="lazyload">',
            $this->imgix->generateImage('/test.png', 'lazysizes_transparent')
        );

        $this->assertEquals(
            '<img src="https://test.imgix.net/test.png?h=30&w=60" srcset="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-srcset="https://test.imgix.net/test.png?h=60&w=120 100w, https://test.imgix.net/test.png?h=90&w=180 500w" data-sizes="auto" class="lazyload">',
            $this->imgix->generateImage('/test.png', self::FILTERS['lazysizes_transparent'])
        );
    }

    /**
     * @covers \Sparwelt\ImgixLib\ImgixService::generateAttributeValue()
     */
    public function testGenerateAttributeValue()
    {
        $this->assertEquals(
            'https://test.imgix.net/test.png',
            $this->imgix->generateAttributeValue('/test.png', [])
        );

        $this->assertEquals(
            'https://test.imgix.net/test.png?h=200&w=400 2x, https://test.imgix.net/test.png?h=300&w=600 3x',
            $this->imgix->generateAttributeValue('/test.png', 'responsive1.srcset')
        );

        $this->assertEquals(
            'https://test.imgix.net/test.png?h=200&w=400 2x, https://test.imgix.net/test.png?h=300&w=600 3x',
            $this->imgix->generateAttributeValue('/test.png', self::FILTERS['responsive1']['srcset'])
        );

        $this->assertEquals(
            'https://test.imgix.net/test.png?h=300&w=600 100w, https://test.imgix.net/test.png?h=600&w=900 500w',
            $this->imgix->generateAttributeValue('/test.png', 'responsive2.srcset')
        );

        $this->assertEquals(
            '(min-width: 900px) 1000px, (max-width: 900px)',
            $this->imgix->generateAttributeValue('/test.png', 'responsive2.sizes')
        );

        $this->assertEquals(
            'https://test.imgix.net/test.png?h=300&w=600 100w, https://test.imgix.net/test.png?h=600&w=900 500w',
            $this->imgix->generateAttributeValue('/test.png', self::FILTERS['responsive2']['srcset'])
        );

        $this->assertEquals(
            'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==',
            $this->imgix->generateAttributeValue('/test.png', 'lazysizes_transparent.srcset')
        );

        $this->assertEquals(
            'https://test.imgix.net/test.png?h=100&w=200',
            $this->imgix->generateAttributeValue('/test.png', ['h' => 100, 'w' => 200])
        );

        $this->assertEquals(
            'https://test.imgix.net/test.png?h=100&w=200 1x, https://test.imgix.net/test.png?h=300&w=400 2x',
            $this->imgix->generateAttributeValue('/test.png', ['1x'  => ['h' => 100, 'w' => 200], '2x' => ['h' => 300, 'w' => 400]])
        );
    }

    /**
     * @covers \Sparwelt\ImgixLib\ImgixService::transformHtml()
     */
    public function testTransformHtml()
    {
        $originalHtml = '
            </ul><ul><img src="/test.png" alt="foo" ></ul>
            <a href="foo"</td><img data-src="/test2.png" alt="bar" />
            <img data-href="/mypage.html" src="/test3.png" srcset="/foo.png 1x, /bar.gif 2x" alt="bar" ng-src="/test3.png" />
            <img src="" alt="bar">< img data-src="/test5.png" alt="bar">'
        ;

        $this->assertEquals('
            </ul><ul><img src="https://test.imgix.net/test.png" alt="foo"></ul>
            <a href="foo"</td><img data-src="https://test.imgix.net/test2.png" alt="bar">
            <img data-href="/mypage.html" src="https://test.imgix.net/test3.png" srcset="https://test.imgix.net/foo.png 1x, https://test.imgix.net/bar.gif 2x" alt="bar" ng-src="https://test.imgix.net/test3.png">
            <img src="" alt="bar">< img data-src="/test5.png" alt="bar">',
            $this->imgix->transformHtml($originalHtml)
        );

        $this->assertEquals('
            </ul><ul><img src="https://test.imgix.net/test.png?h=10&w=20" alt="foo"></ul>
            <a href="foo"</td><img data-src="https://test.imgix.net/test2.png" alt="bar">
            <img data-href="/mypage.html" src="https://test.imgix.net/test3.png?h=10&w=20" srcset="https://test.imgix.net/foo.png 1x, https://test.imgix.net/bar.gif 2x" alt="bar" ng-src="https://test.imgix.net/test3.png">
            <img src="" alt="bar">< img data-src="/test5.png" alt="bar">',
            $this->imgix->transformHtml($originalHtml, ['src' => ['h' => 10, 'w' => 20]])
        );

        $this->assertEquals('
            </ul><ul><img src="https://test.imgix.net/test.png?h=100&w=200" alt="foo" srcset="https://test.imgix.net/test.png?h=200&w=400 2x, https://test.imgix.net/test.png?h=300&w=600 3x"></ul>
            <a href="foo"</td><img data-src="https://test.imgix.net/test2.png" alt="bar">
            <img data-href="/mypage.html" src="https://test.imgix.net/test3.png?h=100&w=200" srcset="https://test.imgix.net/test3.png?h=200&w=400 2x, https://test.imgix.net/test3.png?h=300&w=600 3x" alt="bar" ng-src="https://test.imgix.net/test3.png">
            <img src="" alt="bar">< img data-src="/test5.png" alt="bar">',
            $this->imgix->transformHtml($originalHtml, 'responsive1')
        );

        $this->assertEquals('
            </ul><ul><img src="https://test.imgix.net/test.png?h=30&w=60" alt="foo" srcset="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-srcset="https://test.imgix.net/test.png?h=60&w=120 100w, https://test.imgix.net/test.png?h=90&w=180 500w" data-sizes="auto" class="lazyload"></ul>
            <a href="foo"</td><img data-src="https://test.imgix.net/test2.png" alt="bar">
            <img data-href="/mypage.html" src="https://test.imgix.net/test3.png?h=30&w=60" srcset="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt="bar" ng-src="https://test.imgix.net/test3.png" data-srcset="https://test.imgix.net/test3.png?h=60&w=120 100w, https://test.imgix.net/test3.png?h=90&w=180 500w" data-sizes="auto" class="lazyload">
            <img src="" alt="bar">< img data-src="/test5.png" alt="bar">',
            $this->imgix->transformHtml($originalHtml, 'lazysizes_transparent')
        );
    }

    public function testNonExistingConfigurationKey()
    {
        $this->expectException(ConfigurationException::class);
        $this->imgix->generateImage('/test.png', 'non-existing-filter');
    }

    public function testNonExistingConfigurationAttributeKey()
    {
        $this->expectException(ConfigurationException::class);
        $this->imgix->generateUrl('/test.png', 'normal.data-src');
    }

    public function testFailingHtmlTranslation()
    {
        $this->assertEquals('<img src="">', $this->imgix->transformHtml('<img src=" ">'));
    }

    /**
     * @covers \Sparwelt\ImgixLib\ImgixService::prepareFilters
     */
    public function testExtraFilters()
    {
        $this->assertEquals(
            'https://test.imgix.net/test.png?h=100&w=200',
            $this->imgix->generateAttributeValue('/test.png', ['h' => 100], ['w' => 200])
        );
    }

}
