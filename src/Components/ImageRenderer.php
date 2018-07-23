<?php

namespace Sparwelt\ImgixLib\Components;

use Sparwelt\ImgixLib\Interfaces\ImageRendererInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:16
 */
class ImageRenderer implements ImageRendererInterface
{
    /**
     * @param \DOMElement $element
     *
     * @return string
     */
    public function render(\DOMElement $element)
    {
        return html_entity_decode($element->ownerDocument->saveHTML($element));
    }
}
