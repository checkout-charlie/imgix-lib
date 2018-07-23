<?php

namespace Sparwelt\ImgixLib\Components;

use Imgix\ShardStrategy;
use Imgix\UrlBuilder;
use Sparwelt\ImgixLib\Exception\ConfigurationException;
use Sparwelt\ImgixLib\Exception\ResolutionException;
use Sparwelt\ImgixLib\Interfaces\CdnSelectorInterface;
use Sparwelt\ImgixLib\Interfaces\UrlGeneratorInterface;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  12.07.18 12:18
 */
class ImgixUrlGenerator implements UrlGeneratorInterface
{
    /** @var CdnSelectorInterface  */
    private $cdnSelector;

    /** @var UrlBuilder[]  */
    private $builders;

    /**
     * @param CdnSelectorInterface $cdnSelector
     */
    public function __construct(CdnSelectorInterface $cdnSelector)
    {
        $this->cdnSelector = $cdnSelector;
        $this->builders = [];
    }

    /**
     * @param string $originalUrl
     * @param array  $filterParams
     *
     * @return string
     *
     * @throws \Sparwelt\ImgixLib\Exception\ResolutionException
     * @throws \Sparwelt\ImgixLib\Exception\ConfigurationException
     *
     * @throws \InvalidArgumentException
     */
    public function generateUrl($originalUrl, array $filterParams = [])
    {
        if (empty($originalUrl)) {
            throw new ResolutionException('Empty url');
        }

        $cdn = $this->cdnSelector->getCdnForImage($originalUrl);
        $cdnId = spl_object_hash($cdn);

        if (!isset($this->builders[$cdnId])) {
            $this->builders[$cdnId] = new UrlBuilder(
                $cdn->getCdnDomains(),
                true,
                $cdn->getSignKey(),
                $this->traslateShardStrategy($cdn->getShardStrategy()),
                false
            );
        }

        return $this->builders[$cdnId]->createURL(parse_url($originalUrl, PHP_URL_PATH), $filterParams);
    }

    /**
     * @param string $shardStrategy
     *
     * @return int
     *
     * @throws \Sparwelt\ImgixLib\Exception\ConfigurationException
     */
    private function traslateShardStrategy($shardStrategy)
    {
        switch ($shardStrategy) {
            case 'crc':
                return ShardStrategy::CRC;
            case 'cycle':
                return ShardStrategy::CYCLE;
            default:
                throw new ConfigurationException(
                    sprintf('Unrecognized shard strategy %s. Possible values: "crc", "cycle', $shardStrategy)
                );
        }
    }
}
