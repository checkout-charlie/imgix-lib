<?php

namespace Sparwelt\ImgixLib\Interfaces;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:26
 */
interface ImageRendererInterface
{
    /**
     * @param \DOMElement $element
     *
     * @return string
     */
    public function render(\DOMElement $element);
}
