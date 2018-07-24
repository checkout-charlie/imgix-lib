<?php

namespace Sparwelt\ImgixLib\Components;

use Sparwelt\ImgixLib\Exception\ConfigurationException;
use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\Interfaces\AttributeGeneratorInterface;
use Sparwelt\ImgixLib\Interfaces\UrlGeneratorInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:08
 */
class AttributeGenerator implements AttributeGeneratorInterface
{
    /** @var UrlGeneratorInterface  */
    private $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param string       $sourceImageUrl
     * @param array|string $filters
     *
     * @return string
     */
    public function generateAttributeValue($sourceImageUrl, $filters = [])
    {
        // ['1x' => ['w' => 123, 'h' => 200], '2x' = > [..]]
        if ($this::isMatrix($filters)) {
            $srcset = [];
            foreach ($filters as $format => $formatFilters) {
                $srcset[] = sprintf(
                    '%s %s',
                    $this->urlGenerator->generateUrl($sourceImageUrl, $formatFilters),
                    $format
                );
            }

            return implode(', ', $srcset);
        }

    // ['w' => 123, 'h' => 200]
        if (is_array($filters)) {
            return $this->urlGenerator->generateUrl($sourceImageUrl, $filters);
        }

        // '(min-width: 36em) 33.3vw, 100vw' or 'auto' (e.g. 'sizes')
        if (is_scalar($filters)) {
            return $filters;
        }

        throw new ConfigurationException('Filters should be either array or scalar');
    }

    /**
     * @param mixed $var
     *
     * @return bool
     */
    public static function isMatrix($var)
    {
        if (!is_array($var)) {
            return false;
        }

        foreach ($var as $v) {
            if (is_array($v)) {
                return true;
            }
        }

        return false;
    }
}
