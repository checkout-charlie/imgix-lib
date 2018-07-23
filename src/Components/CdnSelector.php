<?php

namespace Sparwelt\ImgixLib\Components;

use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\Interfaces\CdnSelectorInterface;
use Sparwelt\ImgixLib\Model\CdnConfiguration;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:12
 */
class CdnSelector implements CdnSelectorInterface
{
    /** @var CdnConfiguration[] */
    private $cdnConfigurations;

    /** @param CdnConfiguration ...$cdnConfigurations */
    public function __construct(CdnConfiguration ...$cdnConfigurations)
    {
        $this->cdnConfigurations = $cdnConfigurations;
    }

    /**
     * @param string $originalUr
     *
     * @return CdnConfiguration
     *
     * @throws \Sparwelt\ImgixLib\Exception\ResolutionException
     */
    public function getCdnForImage($originalUr)
    {
        if (0 === strpos($originalUr, 'data:')) {
            throw new ResolutionException('Encoded image are not resolvable');
        }

        foreach ($this->cdnConfigurations as $configuration) {
            $imageParts = parse_url($originalUr);

            if (!isset($imageParts['host']) && !empty($configuration->getSourceDomains())) {
                continue;
            }

            if (isset($imageParts['host']) && !$this->isDomainMatch($imageParts['host'], $configuration->getSourceDomains())) {
                continue;
            }

            if (null !== $configuration->getPathPattern()
                && 0 === preg_match($configuration->getPathPattern(), $imageParts['path'])
            ) {
                continue;
            }

            return $configuration;
        }

        throw new ResolutionException(sprintf('Cannot find cdn configuration match for image %s', $originalUr));
    }

    /**
     * @param string $imageHost
     * @param array  $sourceDomains
     *
     * @return bool
     */
    private function isDomainMatch($imageHost, array $sourceDomains)
    {
        return (in_array($imageHost, $sourceDomains) || in_array(explode('.', $imageHost, 2)[1], $sourceDomains));
    }
}
