<?php

namespace Sparwelt\ImgixLib\Components;

use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\Exception\TransformationException;
use Sparwelt\ImgixLib\Interfaces\HtmlConverterInterface;
use Sparwelt\ImgixLib\Interfaces\ImageTransformerInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:15
 */
class HtmlConverter implements HtmlConverterInterface
{
    /** @var ImageTransformerInterface */
    private $transformer;

    /**
     * HtmlConverter constructor.
     *
     * @param ImageTransformerInterface $transformer
     */
    public function __construct(ImageTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param string $originalHtml
     * @param array  $attributesFilters
     *
     * @return null|string|string[]
     */
    public function convertHtml($originalHtml, array $attributesFilters = [])
    {
        // regex is used because:
        // 1 - preserves the original html, that would otherwise change when converted to a DOM abstraction
        // 2 - works with invalid surrounding html
        return preg_replace_callback('/(<img[^>]+>)/i', function ($matches) use ($attributesFilters) {
            try {
                return $this->transformer->transformImage($matches[0], $attributesFilters);
            } catch (\Exception $e) {
                if ($e instanceof ResolutionException || $e instanceof TransformationException) {
                    return $matches[0];
                }
                throw $e;
            }
        }, $originalHtml);
    }
}
