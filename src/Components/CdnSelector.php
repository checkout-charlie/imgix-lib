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
            $originalPath = parse_url($originalUr, PHP_URL_PATH);
            $originalHost = parse_url($originalUr, PHP_URL_HOST);

            if (null === $originalPath) {
                throw new ResolutionException('Image has no path');
            }

            if (!$this->isDomainMatch($originalHost, $configuration->getSourceDomains())) {
                continue;
            }

            if (!$this->isPatternMatch($configuration, $originalPath)) {
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
        if (null === $imageHost) {
            return empty($sourceDomains) || in_array(null, $sourceDomains);
        }

        if (in_array($imageHost, $sourceDomains)) {
            return true;
        }

        // check sub domain
        $imageDomainParts = explode('.', $imageHost, 2);
        if (isset($imageDomainParts[1]) && in_array($imageDomainParts[1], $sourceDomains)) {
            return true;
        }

        return false;
    }

    /**
     * @param CdnConfiguration $configuration
     * @param string           $imagePath
     *
     * @return bool
     */
    protected function isPatternMatch(CdnConfiguration $configuration, $imagePath)
    {
        if (empty($configuration->getPathPatterns())) {
            return true;
        }

        foreach ($configuration->getPathPatterns() as $pattern) {
            if (preg_match(sprintf('~%s~', $pattern), $imagePath)) {
                return true;
            }
        }

        return false;
    }
}
