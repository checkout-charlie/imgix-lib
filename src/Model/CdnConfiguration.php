<?php

namespace Sparwelt\ImgixLib\Model;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 12:18
 */
class CdnConfiguration
{
    /**
     * @var string[]
     */
    private $cdnDomains;
    /**
     * @var string[]
     */
    private $sourceDomains;
    /**
     * @var string|null
     */
    private $pathPattern;
    /**
     * @var string|null
     */
    private $signKey;
    /**
     * @var string
     */
    private $shardStrategy;

    /**
     * CdnConfiguration constructor.
     *
     * @param string[]    $cdnDomains
     * @param string[]    $sourceDomains
     * @param string|null $matchingPattern
     * @param string|null $signKey
     * @param string      $shardStrategy
     */
    public function __construct(array $cdnDomains, array $sourceDomains = [], $matchingPattern = null, $signKey = null, $shardStrategy = 'crc')
    {
        $this->cdnDomains = $cdnDomains;
        $this->sourceDomains = $sourceDomains;
        $this->pathPattern = $matchingPattern;
        $this->signKey = $signKey;
        $this->shardStrategy = $shardStrategy;
    }

    /**
     * @return string[]
     */
    public function getCdnDomains()
    {
        return $this->cdnDomains;
    }

    /**
     * @return string[]
     */
    public function getSourceDomains()
    {
        return $this->sourceDomains;
    }

    /**
     * @return string|null
     */
    public function getPathPattern()
    {
        return $this->pathPattern;
    }

    /**
     * @return string|null
     */
    public function getSignKey()
    {
        return $this->signKey;
    }

    /**
     * @return string
     */
    public function getShardStrategy()
    {
        return $this->shardStrategy;
    }
}
