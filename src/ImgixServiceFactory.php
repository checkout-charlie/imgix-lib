<?php

namespace Sparwelt\ImgixLib;

use Sparwelt\ImgixLib\Components\AttributeGenerator;
use Sparwelt\ImgixLib\Components\CdnConfigurationParser;
use Sparwelt\ImgixLib\Components\CdnSelector;
use Sparwelt\ImgixLib\Components\ImageGenerator;
use Sparwelt\ImgixLib\Components\ImageRenderer;
use Sparwelt\ImgixLib\Components\HtmlTransformer;
use Sparwelt\ImgixLib\Components\ImageTransformer;
use Sparwelt\ImgixLib\Components\ImgixUrlGenerator;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 12:18
 */
class ImgixServiceFactory
{
    /**
     * @param array $cdnConfiguration
     * @param array $filtersConfigurations
     *
     * @return ImgixService
     */
    public static function createFromConfiguration(array $cdnConfiguration, array $filtersConfigurations = [])
    {
        $cdnSelector = new CdnSelector(...CdnConfigurationParser::parseArray($cdnConfiguration));
        $urlGenerator = new ImgixUrlGenerator($cdnSelector);
        $attributeGenerator = new AttributeGenerator($urlGenerator);

        $imageRenderer = new ImageRenderer();
        $imageTransformer = new ImageTransformer($attributeGenerator, $imageRenderer);
        $imageGenerator = new ImageGenerator($attributeGenerator, $imageRenderer);
        $htmlConverter = new HtmlTransformer($imageTransformer);

        return new ImgixService($urlGenerator, $attributeGenerator, $imageGenerator, $imageTransformer, $htmlConverter, $filtersConfigurations);
    }
}
