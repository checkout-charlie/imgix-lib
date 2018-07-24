<?php

namespace Sparwelt\ImgixLib;

use Sparwelt\ImgixLib\Exception\ConfigurationException;
use Sparwelt\ImgixLib\Interfaces\AttributeGeneratorInterface;
use Sparwelt\ImgixLib\Interfaces\HtmlConverterInterface;
use Sparwelt\ImgixLib\Interfaces\ImageGeneratorInterface;
use Sparwelt\ImgixLib\Interfaces\ImageTransformerInterface;
use Sparwelt\ImgixLib\Interfaces\UrlGeneratorInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:06
 */
class ImgixService
{
    /** @var UrlGeneratorInterface  */
    protected $urlGenerator;

    /** @var AttributeGeneratorInterface */
    protected $attributeGenerator;
    /**
     * @var ImageGeneratorInterface
     */
    private $imageGenerator;

    /** @var ImageTransformerInterface  */
    protected $imageTransformer;

    /** @var HtmlConverterInterface  */
    protected $htmlConverter;

    /** @var array */
    protected $filtersConfigurations;

    /**
     * @param UrlGeneratorInterface       $urlGenerator
     * @param AttributeGeneratorInterface $attributeGenerator
     * @param ImageGeneratorInterface     $imageGenerator
     * @param ImageTransformerInterface   $imageTransformer
     * @param HtmlConverterInterface      $htmlConverter
     * @param array                       $filtersConfigurations
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        AttributeGeneratorInterface $attributeGenerator,
        ImageGeneratorInterface $imageGenerator,
        ImageTransformerInterface $imageTransformer,
        HtmlConverterInterface $htmlConverter,
        array $filtersConfigurations = []
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->attributeGenerator = $attributeGenerator;
        $this->imageGenerator = $imageGenerator;
        $this->imageTransformer = $imageTransformer;
        $this->htmlConverter = $htmlConverter;
        $this->filtersConfigurations = $filtersConfigurations;
    }

    /**
     * @param string       $originalUrl
     * @param array|string $filtersOrConfigurationKey
     * @param array        $extraFilters
     *
     * @return string
     * @throws \Sparwelt\ImgixLib\Exception\ResolutionException
     */
    public function generateUrl($originalUrl, $filtersOrConfigurationKey = [], array $extraFilters = [])
    {
        $filters = $this->prepareFilters($filtersOrConfigurationKey, $extraFilters);

        return $this->urlGenerator->generateUrl($originalUrl, $filters);
    }

    /**
     * @param string       $originalUrl
     * @param array|string $filtersOrConfigurationKey
     * @param array        $extraFilters
     *
     * @return string
     * @throws \Sparwelt\ImgixLib\Exception\ResolutionException
     */
    public function generateAttributeValue($originalUrl, $filtersOrConfigurationKey = [], array $extraFilters = [])
    {
        $filters = $this->prepareFilters($filtersOrConfigurationKey, $extraFilters);

        return $this->attributeGenerator->generateAttributeValue($originalUrl, $filters);
    }

    /**
     * @param string $originalUrl
     * @param array  $attributesFiltersOrConfigurationKey
     * @param array  $extraFilters
     *
     * @return string
     */
    public function generateImage($originalUrl, $attributesFiltersOrConfigurationKey = [], array $extraFilters = [])
    {
        $attributesFilters = $this->prepareFilters($attributesFiltersOrConfigurationKey, $extraFilters);

        return $this->imageGenerator->generateImage($originalUrl, $attributesFilters);
    }

    /**
     * @param string $html
     * @param array  $attributesFiltersOrConfigurationKey
     * @param array  $extraFilters
     *
     * @return string
     */
    public function convertHtml($html, $attributesFiltersOrConfigurationKey = [], array $extraFilters = [])
    {
        $attributesFilters = $this->prepareFilters($attributesFiltersOrConfigurationKey, $extraFilters);

        return $this->htmlConverter->convertHtml($html, $attributesFilters);
    }

    /**
     * @param array|string $filtersOrConfigurationKey
     * @param array        $extraFilters
     *
     * @return array
     */
    private function prepareFilters($filtersOrConfigurationKey, array $extraFilters = [])
    {
        if (is_array($filtersOrConfigurationKey)) {
            return array_merge($filtersOrConfigurationKey, $extraFilters);
        }

        if (false !== strpos($filtersOrConfigurationKey, '.')) {
            list($configurationKey, $attribute) = explode('.', $filtersOrConfigurationKey);
        } else {
            $configurationKey = $filtersOrConfigurationKey;
            $attribute = null;
        }

        if (!isset($this->filtersConfigurations[$configurationKey])) {
            throw new ConfigurationException(sprintf('Unable to find filter configuration "%s"', $configurationKey));
        }

        if (null !== $attribute) {
            if (!isset($this->filtersConfigurations[$configurationKey][$attribute])) {
                throw new ConfigurationException(sprintf('Unable to find attribute "%s" in filter configuration "%s"', $attribute, $configurationKey));
            }

            return is_array($this->filtersConfigurations[$configurationKey][$attribute])
                ? array_merge($this->filtersConfigurations[$configurationKey][$attribute], $extraFilters)
                : $this->filtersConfigurations[$configurationKey][$attribute]
                ;
        }

        return is_array($this->filtersConfigurations[$configurationKey])
            ? array_merge($this->filtersConfigurations[$configurationKey], $extraFilters)
            : $this->filtersConfigurations[$configurationKey]
            ;
    }
}
