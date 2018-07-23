<?php

namespace Sparwelt\ImgixLib\Interfaces;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:27
 */
interface ImageTransformerInterface
{
    /**
     * @param string $originalImageHtml
     * @param array  $attributesFilters
     *
     * @return \DOMElement
     */
    public function transformImage($originalImageHtml, array $attributesFilters = []);
}
