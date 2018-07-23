<?php

namespace Sparwelt\ImgixLib\Interfaces;

use Sparwelt\ImgixLib\Model\CdnConfiguration;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:24
 */
interface CdnSelectorInterface
{
    /**
     * @param string $originalUrl
     *
     * @return CdnConfiguration
     */
    public function getCdnForImage($originalUrl);
}
