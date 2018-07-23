<?php

namespace Sparwelt\ImgixLib\Interfaces;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:22
 */
interface AttributeGeneratorInterface
{
    /**
     * @param string       $sourceImageUrl
     * @param array|string $filters
     *
     * @return string
     */
    public function generateAttributeValue($sourceImageUrl, $filters = []);
}
