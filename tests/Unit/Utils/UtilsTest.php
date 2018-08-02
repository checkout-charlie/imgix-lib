<?php

use Sparwelt\ImgixLib\Utils\Utils;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:34
 */
class UtilsTest extends \PHPUnit\Framework\TestCase
{
    public function testIsMatrix()
    {
        $this->assertTrue(Utils::isMatrix(['a' => ['b']]));
        $this->assertTrue(Utils::isMatrix(['a' => ['b' => 1, 'c' => 2]]));
        $this->assertFalse(Utils::isMatrix(['a' => 1, 'b' => 2]));
        $this->assertFalse(Utils::isMatrix(['a', 'b']));
        $this->assertFalse(Utils::isMatrix('a'));
        $this->assertFalse(Utils::isMatrix(new stdClass()));
    }
}
