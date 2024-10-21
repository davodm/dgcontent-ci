<?php

namespace DGContent\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Class DgContent
 *
 * Configuration settings for the DgContent package.
 *
 * @package DgContent\Config
 */
class DGContentCI extends BaseConfig
{
    /**
     * @var string Base URL for the DGTTeam Content API.
     */
    public $apiBaseUrl = 'https://dgtteam-content.vercel.app/api';

    /**
     * @var string API Key for authorization.
     */
    public $apiKey;

    /**
     * @var int Duration for caching responses in seconds.
     */
    public $cacheDuration;

    /**
     * @var string Name of the website key to use for filtering content.
     */
    public $websiteKey;

    /**
     * @var int Timeout for API requests in seconds.
     */
    public $timeOut;

    /**
     * DgContentCI constructor.
     *
     * Initializes configuration settings from environment variables.
     */
    public function __construct()
    {
        // Retrieve API key from environment variables; check both 'DGCONTENT_API_KEY' and 'dgcontent.api.key'.
        $this->apiKey = env('DGCONTENT_API_KEY') ?: env('dgcontent.api.key') ?: '';

        // Retrieve cache duration from environment variables; check both 'DGCONTENT_CACHE_TTL' and 'dgcontent.cache.ttl'.
        $this->cacheDuration = env('DGCONTENT_CACHE_TTL') ?: env('dgcontent.cache.ttl') ?: 3600;

        // Retrieve API base URL from environment variables; check both 'DGCONTENT_API_BASE_URL' and 'dgcontent.api.base.url'.
        $this->apiBaseUrl = env('DGCONTENT_API_BASE_URL') ?: env('dgcontent.api.base.url') ?: $this->apiBaseUrl;

        // Retrieve website key from environment variables; check both 'DGCONTENT_WEBSITE_KEY' and 'dgcontent.website.key'.
        $this->websiteKey = env('DGCONTENT_WEBSITE_KEY') ?: env('dgcontent.website.key') ?: '';

        // Retrieve timeout from environment variables; check both 'DGCONTENT_TIMEOUT' and 'dgcontent.timeout'.
        $this->timeOut = (int) (env('DGCONTENT_TIMEOUT') ?: env('dgcontent.timeout') ?: 10);
    }
}
