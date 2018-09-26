<?php

namespace Sparwelt\ImgixLib\Components;

use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\Exception\TransformationException;
use Sparwelt\ImgixLib\Interfaces\AttributeGeneratorInterface;
use Sparwelt\ImgixLib\Interfaces\ImageRendererInterface;
use Sparwelt\ImgixLib\Interfaces\ImageTransformerInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:17
 */
class ImageTransformer implements ImageTransformerInterface
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
     * @param string $originalImageHtml
     * @param array  $attributesFilters
     *
     * @return \DOMElement|string
     *
     * @throws \Sparwelt\ImgixLib\Exception\TransformationException
     */
    public function transformImage($originalImageHtml, array $attributesFilters = [])
    {
        $dom = new \DOMDocument();
        $dom->loadHTML($originalImageHtml);
        if (0 === $dom->getElementsByTagName('img')->length) {
            throw new TransformationException(sprintf('Unable to parse img element: %s', $originalImageHtml));
        }

        $image = $dom->getElementsByTagName('img')->item(0);

        // apply the specified filters
        $originalImageUrl = $image->hasAttribute('src') ? trim($image->getAttribute('src')) : null;
        $processedAttributes = [];
        if (!empty($originalImageUrl) && false === strpos($originalImageUrl, 'data:image/')) {
            try {
                foreach ($attributesFilters as $attributeName => $filters) {
                    $attributeValue = $this->attributeGenerator->generateAttributeValue($originalImageUrl, $filters);
                    if ('' !== $attributeValue) {
                        $image->setAttribute($attributeName, $attributeValue);
                        $processedAttributes[] = $attributeName;
                    }
                }
            } catch (ResolutionException $e) {
            } catch () {

            }
        }

        // apply the cdn domain on the remaining attributes
        foreach ($image->attributes as $attribute) {
            if (!in_array($attribute->name, $processedAttributes)) {
                try {
                    $this->applyCdnDomain($attribute);
                } catch (\Exception $e) {
                    // pass
                }
            }
        }

        return $this->renderer->render($image);
    }

    /**
     * @param string $word
     *
     * @return bool
     */
    protected function isImageUrl($word)
    {
        return in_array(
            strtolower(pathinfo(parse_url($word, PHP_URL_PATH), PATHINFO_EXTENSION)),
            ['png', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'ico']
        );
    }

    /**
     * @param \DOMAttr $attribute
     */
    private function applyCdnDomain(\DOMAttr $attribute)
    {
        $words = preg_split("/[\s]+/", $attribute->value);
        $processedWords = [];

        foreach ($words as $word) {
            if ($this->isImageUrl($word)) {
                try {
                    $processedWords[] = $this->attributeGenerator->generateAttributeValue($word, []);
                } catch (ResolutionException $e) {
                    $processedWords[] = $word;
                }
            } else {
                $processedWords[] = $word;
            }
        }

        $attribute->value = implode(' ', $processedWords);
    }
}
