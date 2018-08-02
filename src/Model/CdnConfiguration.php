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
     * @var string[]
     */
    private $pathPatterns;
    /**
     * @var string|null
     */
    private $signKey;
    /**
     * @var string
     */
    private $shardStrategy;

    /** @var bool */
    private $useSsl;

    /** @var string[] */
    private $defaultQueryParams;

    /** @var bool */
    private $generateFilterParams;

    /**
     * @param string[]    $cdnDomains
     * @param string[]    $sourceDomains
     * @param string[]    $pathPatterns
     * @param string|null $signKey
     * @param string      $shardStrategy
     * @param bool        $useSsl
     * @param string[]    $defaultQueryParams
     * @param bool        $generateFilterParams
     */
    public function __construct(array $cdnDomains, array $sourceDomains = [], array $pathPatterns = [], $signKey = null, $shardStrategy = 'crc', $useSsl = true, array $defaultQueryParams = [], $generateFilterParams = true)
    {
        $this->cdnDomains = $cdnDomains;
        $this->sourceDomains = $sourceDomains;
        $this->pathPatterns = $pathPatterns;
        $this->signKey = $signKey;
        $this->shardStrategy = $shardStrategy;
        $this->useSsl = $useSsl;
        $this->defaultQueryParams = $defaultQueryParams;
        $this->generateFilterParams = $generateFilterParams;
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
     * @return string[]
     */
    public function getPathPatterns()
    {
        return $this->pathPatterns;
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

    /**
     * @return bool
     */
    public function isUseSsl()
    {
        return $this->useSsl;
    }

    /**
     * @return string[]
     */
    public function getDefaultQueryParams()
    {
        return $this->defaultQueryParams;
    }

    /**
     * @return bool
     */
    public function isGenerateFilterParams()
    {
        return $this->generateFilterParams;
    }
}
