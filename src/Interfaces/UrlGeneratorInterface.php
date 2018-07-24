<?php

namespace Sparwelt\ImgixLib\Interfaces;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:27
 */
interface UrlGeneratorInterface
{
    /**
     * @param string $originalUrl
     * @param array  $filters
     *
     * @return string
     */
    public function generateUrl($originalUrl, array $filters = []);
}
