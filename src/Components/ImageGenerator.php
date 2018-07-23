<?php

namespace Sparwelt\ImgixLib\Components;

use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\Interfaces\AttributeGeneratorInterface;
use Sparwelt\ImgixLib\Interfaces\ImageGeneratorInterface;
use Sparwelt\ImgixLib\Interfaces\ImageRendererInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:15
 */
class ImageGenerator implements ImageGeneratorInterface
{
    /** @var AttributeGeneratorInterface */
    private $attributeGenerator;

    /** @var ImageRendererInterface */
    private $renderer;

    /**

     * @param AttributeGeneratorInterface $attributeGenerator
     * @param ImageRendererInterface      $renderer
     */
    public function __construct(AttributeGeneratorInterface $attributeGenerator, ImageRendererInterface $renderer)
    {
        $this->attributeGenerator = $attributeGenerator;
        $this->renderer = $renderer;
    }

    /**
     * @param string $originalUrl
     * @param array  $attributesFilters
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function generateImage($originalUrl, array $attributesFilters)
    {
        if (empty($attributesFilters)) {
            throw new \InvalidArgumentException('Please provide at least one key for attribute generation (e.g. \'src\') ');
        }

        $image = (new \DOMDocument())->createElement('img');

        try {
            foreach ($attributesFilters as $attributeName => $filters) {
                $attributeValue = $this->attributeGenerator->generateAttributeValue($originalUrl, $filters);
                if ('' !== $attributeValue) {
                    $image->setAttribute($attributeName, $attributeValue);
                }
            }
        } catch (ResolutionException $e) {
            $image->setAttribute('src', $originalUrl);
        }

        return $this->renderer->render($image);
    }
}
