<?php

namespace Sparwelt\ImgixLib\Interfaces;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:25
 */
interface ImageGeneratorInterface
{
    /**
     * @param string $originalUrl
     * @param array  $attributesFilters
     *
     * @return string
     */
    public function generateImage($originalUrl, array $attributesFilters);
}
