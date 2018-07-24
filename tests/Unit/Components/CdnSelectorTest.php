<?php

use Sparwelt\ImgixLib\Components\CdnSelector;
use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\Model\CdnConfiguration;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 */
class CdnSelectorTest extends PHPUnit_Framework_TestCase
{
    public function testSelectCdnFor()
    {
        $cdn1 = new CdnConfiguration(['imgix1.net'], ['server1.com', 'server2.com'], ['^/media']);
        $cdn2 = new CdnConfiguration(['imgix2.net'], ['server1.com']);
        $cdn3 = new CdnConfiguration(['imgix3.net'], [], ['^/media']);
        $cdn4 = new CdnConfiguration(['imgix4.net'], []);

        $selector = new CdnSelector($cdn1, $cdn2, $cdn3, $cdn4);

        $this->assertEquals($cdn1, $selector->getCdnForImage('http://server1.com/media/test.png'));
        $this->assertEquals($cdn1, $selector->getCdnForImage('http://server2.com/media/test.png'));
        $this->assertEquals($cdn2, $selector->getCdnForImage('http://server1.com/uploads/media/test.png'));
        $this->assertEquals($cdn3, $selector->getCdnForImage('/media/test.png'));
        $this->assertEquals($cdn4, $selector->getCdnForImage('/uploads/media/test.png'));
    }

    public function testSelectCdnForPatternExceptions()
    {
        $this->expectException(ResolutionException::class);
        $cdn1 = new CdnConfiguration(['imgix1.net'], ['server1.com'], ['^/media']);
        $selector = new CdnSelector($cdn1);
        $selector->getCdnForImage('http://server1.com/uploads/media/test.png');
    }

    public function testSelectCdnForDomainExceptions()
    {
        $this->expectException(ResolutionException::class);
        $cdn1 = new CdnConfiguration(['imgix1.net'], ['server1.com'], ['^/media']);
        $selector = new CdnSelector($cdn1);
        $selector->getCdnForImage('http://server2.com/media/test.png');
    }

    public function testSelectCdnForEncodedImage()
    {
        $this->expectException(ResolutionException::class);
        $cdn1 = new CdnConfiguration(['imgix1.net']);
        $selector = new CdnSelector($cdn1);
        $selector->getCdnForImage('data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
    }

    public function testImagehasNopath()
    {
        $this->expectException(ResolutionException::class);
        $cdn1 = new CdnConfiguration(['imgix1.net'], ['server1.com'], ['^/media']);
        $selector = new CdnSelector($cdn1);
        $selector->getCdnForImage('http://server2.com');
    }
}
