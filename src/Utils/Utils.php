<?php

namespace Sparwelt\ImgixLib\Utils;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 12:18
 */
class Utils
{
    /**
     * @param mixed $var
     *
     * @return bool
     */
    public static function isMatrix($var)
    {
        if (!is_array($var)) {
            return false;
        }

        foreach ($var as $v) {
            if (is_array($v)) {
                return true;
            }
        }

        return false;
    }
}
