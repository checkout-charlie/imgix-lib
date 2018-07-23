<?php

namespace Sparwelt\ImgixLib\Interfaces;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:25
 */
interface HtmlConverterInterface
{
    /**
     * @param string $originalHtml
     * @param array  $attributesFilters
     *
     * @return string
     */
    public function convertHtml($originalHtml, array $attributesFilters = []);
}
