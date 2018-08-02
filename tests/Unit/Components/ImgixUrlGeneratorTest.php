<?php

use Sparwelt\ImgixLib\Components\ImgixUrlGenerator;
use Sparwelt\ImgixLib\Exception\ConfigurationException;
use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\Interfaces\CdnSelectorInterface;
use Sparwelt\ImgixLib\Model\CdnConfiguration;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 */
class ImgixUrlGeneratorTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerate()
    {
        $cdn = new CdnConfiguration(['test.imgix.net']);

        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(3))
            ->method('getCdnForImage')
            ->withConsecutive(
                ['/test/test.png'],
                ['/test/test.png'],
                ['http://original.com/test/test.png']
            )
            ->will($this->returnValue($cdn));

        $generator = new ImgixUrlGenerator($cdnSelector);

        $this->assertEquals(
            'https://test.imgix.net/test/test.png',
            $generator->generateUrl('/test/test.png')
        );

        $this->assertEquals(
            'https://test.imgix.net/test/test.png?h=200&w=300',
            $generator->generateUrl('/test/test.png', ['h' => 200, 'w' => 300])
        );

        $this->assertEquals(
            'https://test.imgix.net/test/test.png?x=100&y=300&z=200',
            $generator->generateUrl('http://original.com/test/test.png', ['x' => 100, 'z' => 200, 'y' => 300])
        );
    }

    public function testSignKey()
    {
        $cdn = new CdnConfiguration(['test.imgix.net'], [], [], 'testSign');

        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(1))
            ->method('getCdnForImage')
            ->with('/test/test.png')
            ->will($this->returnValue($cdn));

        $generator = new ImgixUrlGenerator($cdnSelector);

        $this->assertEquals(
            'https://test.imgix.net/test/test.png?h=200&w=300&s=46b540ef1bde7f797272aa2ca7727b24',
            $generator->generateUrl('/test/test.png', ['h' => 200, 'w' => 300])
        );
    }

    public function testCRCShard()
    {
        $cdn = new CdnConfiguration(['one.imgix.net', 'two.imgix.net']);

        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(4))
            ->method('getCdnForImage')
            ->will($this->returnValue($cdn));

        $generator = new ImgixUrlGenerator($cdnSelector);

        $this->assertEquals(
            'https://two.imgix.net/test/test1.png?h=200&w=300',
            $generator->generateUrl('/test/test1.png', ['h' => 200, 'w' => 300])
        );
        $this->assertEquals(
            'https://one.imgix.net/test/test21.png?h=200&w=300',
            $generator->generateUrl('/test/test21.png', ['h' => 200, 'w' => 300])
        );
        $this->assertEquals(
            'https://one.imgix.net/test/test21.png?h=200&w=300',
            $generator->generateUrl('/test/test21.png', ['h' => 200, 'w' => 300])
        );
        $this->assertEquals(
            'https://two.imgix.net/test/test1.png?h=200&w=300',
            $generator->generateUrl('/test/test1.png', ['h' => 200, 'w' => 300])
        );
    }

    public function testCycleShard()
    {
        $cdn = new CdnConfiguration(['one.imgix.net', 'two.imgix.net'], [], [], null, 'cycle');

        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(4))
            ->method('getCdnForImage')
            ->will($this->returnValue($cdn));

        $generator = new ImgixUrlGenerator($cdnSelector);

        $this->assertEquals(
            'https://two.imgix.net/test/test1.png?h=200&w=300',
            $generator->generateUrl('/test/test1.png', ['h' => 200, 'w' => 300])
        );
        $this->assertEquals(
            'https://one.imgix.net/test/test2.png?h=200&w=300',
            $generator->generateUrl('/test/test2.png', ['h' => 200, 'w' => 300])
        );
        $this->assertEquals(
            'https://two.imgix.net/test/test3.png?h=200&w=300',
            $generator->generateUrl('/test/test3.png', ['h' => 200, 'w' => 300])
        );
        $this->assertEquals(
            'https://one.imgix.net/test/test4.png?h=200&w=300',
            $generator->generateUrl('/test/test4.png', ['h' => 200, 'w' => 300])
        );
    }

    public function testDefaultQueryParams()
    {
        $cdn = new CdnConfiguration(['test.imgix.net'], [], [], null, 'cycle', true, ['cb' => 1234]);

        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(2))
            ->method('getCdnForImage')
            ->will($this->returnValue($cdn));

        $generator = new ImgixUrlGenerator($cdnSelector);

        $this->assertEquals(
            'https://test.imgix.net/test/test1.png?cb=1234&h=200&w=300',
            $generator->generateUrl('/test/test1.png', ['h' => 200, 'w' => 300])
        );

        $this->assertEquals(
            'https://test.imgix.net/test/test2.png?cb=4567&h=200&w=300',
            $generator->generateUrl('/test/test2.png', ['h' => 200, 'w' => 300, 'cb' => 4567])
        );
    }

    public function testDisableGenerateFilterParams()
    {
        $cdn = new CdnConfiguration(['dev-env.test'], [], [], null, 'crc', false, [], false);

        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(1))
            ->method('getCdnForImage')
            ->will($this->returnValue($cdn));

        $generator = new ImgixUrlGenerator($cdnSelector);

        $this->assertEquals(
            'http://dev-env.test/test/test1.png',
            $generator->generateUrl('/test/test1.png', ['h' => 200, 'w' => 300])
        );
    }

    public function testEmptyImageUrl()
    {
        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(0))
            ->method('getCdnForImage');

        $generator = new ImgixUrlGenerator($cdnSelector);
        $this->expectException(ResolutionException::class);
        $generator->generateUrl('', []);
    }

    public function testWrongStrategy()
    {
        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(1))
            ->method('getCdnForImage')
            ->willReturn(new CdnConfiguration(['foo'], [], [], null, 'wrong-shard-strategy'));
        ;

        $generator = new ImgixUrlGenerator($cdnSelector);
        $this->expectException(ConfigurationException::class);
        $generator->generateUrl('test.png', []);
    }

    public function testNoDomain()
    {
        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(1))
            ->method('getCdnForImage')
            ->willReturn(new CdnConfiguration([], [], [], null));
        ;

        $generator = new ImgixUrlGenerator($cdnSelector);
        $this->expectException(ResolutionException::class);
        $generator->generateUrl('test.png', []);
    }

    public function testMalformedPath()
    {
        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(1))
            ->method('getCdnForImage')
            ->willReturn(new CdnConfiguration(['domain.com'], [], [], null));
        ;

        $generator = new ImgixUrlGenerator($cdnSelector);
        $this->expectException(ResolutionException::class);
        $generator->generateUrl('http:///example.com', []);
    }

    public function testNonMonodimensionalQueryParameters()
    {
        $cdnSelector = $this->getMockBuilder(CdnSelectorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnSelector
            ->expects($this->exactly(1))
            ->method('getCdnForImage')
            ->willReturn(new CdnConfiguration(['domain.com'], [], [], null));
        ;

        $generator = new ImgixUrlGenerator($cdnSelector);
        $this->expectException(ConfigurationException::class);
        $generator->generateUrl('http:///example.com', ['src' => ['w' => 15]]);
    }
}
