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
        // Retrieve API key from environment variables; default to empty string if not set.
        $this->apiKey = getenv('DGCONTENT_API_KEY') ?: '';
        // Retrieve cache duration from environment variables; default to 1 hour if not set.
        $this->cacheDuration = getenv('DGCONTENT_CACHE_TTL') ?: 3600;
        // Retrieve API base URL from environment variables; default to the DGTTeam Content API.
        $this->apiBaseUrl = getenv('DGCONTENT_API_BASE_URL') ?: $this->apiBaseUrl;
        // Retrieve website key from environment variables; default to empty string if not set.
        $this->websiteKey = getenv('DGCONTENT_WEBSITE_KEY') ?: '';
        // Retrieve timeout from environment variables; default to 10 seconds if not set.
        $this->timeOut = (int) getenv('DGCONTENT_TIMEOUT') ?: 10;
    }
}
