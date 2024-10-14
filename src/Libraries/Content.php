<?php

namespace DGContent\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use DGContent\Config\DGContentCI;
use InvalidArgumentException;

/**
 * Class Content
 *
 * Library class to interact with the DGTTeam Content API.
 *
 * @package DGContent\Libraries
 */
class Content
{
    /**
     * @var DGContentCI Configuration instance.
     */
    protected $config;

    /**
     * @var CURLRequest HTTP client for making API requests.
     */
    protected $http;

    /**
     * @var CacheInterface Cache handler instance.
     */
    protected $cache;

    /**
     * @var bool Whether to process the result or not.
     */
    protected $doProcess = true;

    /**
     * Content Library constructor.
     *
     * Initializes the library with configuration, HTTP client, and caching.
     *
     * @param DGContentCI|null $config Optional configuration instance.
     */
    public function __construct(DGContentCI $config = null)
    {
        // Use provided config or instantiate a new one.
        $this->config = $config ?: new DGContentCI();

        // Check for required API key.
        if (empty($this->config->apiKey)) {
            throw new InvalidArgumentException('DG Content API key is required.');
        }

        // Initialize the websiteKey:
        // If not set via environment, use CodeIgniter's base URL domain.
        if (empty($this->config->websiteKey)) {
            helper('url');
            $baseURL = base_url(); // e.g., 'https://example.com/'
            $parsedURL = parse_url($baseURL, PHP_URL_HOST);
            if (!empty($parsedURL)) {
                $this->config->websiteKey = $parsedURL;
            }
            // If still empty, throw an exception.
            if (empty($this->config->websiteKey)) {
                throw new InvalidArgumentException('Website key is required for filtering content.');
            }
        }

        // Initialize CURLRequest with base URI and headers.
        $this->http = \Config\Services::curlrequest([
            'base_uri' => $this->config->apiBaseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->apiKey,
                'Accept'        => 'application/json',
            ],
            'timeout' => 10, // Set timeout to 10 seconds.
        ]);

        // Initialize the caching mechanism using CodeIgniter's Cache Manager.
        $this->cache = cache();
    }

    /**
     * Sets the duration for caching responses.
     *
     * @param int $seconds Cache duration in seconds.
     * @return $this
     */
    public function setCacheDuration(int $seconds): self
    {
        if ($seconds < 0) {
            throw new InvalidArgumentException('Cache duration must be a positive integer.');
        }
        $this->config->cacheDuration = $seconds;
        return $this;
    }

    /**
     * Sets the ability to process the result or not.
     * 
     * @param bool $doProcess Whether to process the result or not.
     * @return $this
     */
    public function setProcessResult(bool $doProcess): self
    {
        $this->doProcess = $doProcess;
        return $this;
    }

    /**
     * Retrieves a list of posts from the API with optional filters.
     *
     * Implements caching to reduce API calls.
     *
     * @param array $params Query parameters for filtering posts.
     * @return array|null Array of posts or null on failure.
     *
     * @throws InvalidArgumentException If required parameters are missing.
     */
    public function getPosts(array $params = []): ?array
    {
        // Ensure 'resource' parameter is set to 'posts'.
        $params['resource'] = 'posts';

        // Generate a unique cache key based on query parameters.
        $cacheKey = 'dg_content_posts_' . md5(json_encode($params));

        // Attempt to retrieve data from cache.
        if ($cachedData = $this->cache->get($cacheKey)) {
            return $cachedData;
        }

        // Make the GET request to the API.
        $response = $this->makeRequest($params, 'get');

        // Fix the date to be CI I18n\Time object
        if (!empty($response['posts']) && $this->doProcess) {
            $response['posts'] = array_map(function ($post) {
                if (!empty($post['createdAt'])) {
                    $parsedCreatedAt = \CodeIgniter\I18n\Time::parse($post['createdAt']);
                    $post['createdAt'] = $parsedCreatedAt; // Assign Time Object
                }
                if (!empty($post['updatedAt'])) {
                    $parsedUpdatedAt = \CodeIgniter\I18n\Time::parse($post['updatedAt']);
                    $post['updatedAt'] = $parsedUpdatedAt; // Assign Time Object
                }
                // Filter website target
                if (!empty($post['site'])){
                    $post['site'] = array_filter($post['site'], function($site) {
                        return $site['key'] == $this->config->websiteKey;
                    });
                    // Convert to single array
                    $post['site'] = array_shift($post['site']);
                }
                return $post;
            }, $response['posts']);
        }

        // Cache the response data for the configured duration.
        if ($this->config->cacheDuration > 0 && !empty($response['posts'])) {
            $this->cache->save($cacheKey, $response, $this->config->cacheDuration);
        }
        return [
            'posts' => $response['posts'] ?? [],
            'total' => $response['total'] ?? 0,
        ];
    }

    /**
     * Retrieves a single post by ID or slug from the API.
     *
     * Implements caching to reduce API calls.
     *
     * @param array $params Query parameters to identify the post.
     * @return array|null Array of post data or null on failure.
     *
     * @throws InvalidArgumentException If neither 'id' nor 'slug' is provided.
     */
    public function getPost(array $params = []): ?array
    {
        // Validate that either 'id' or 'slug' is provided.
        if (!isset($params['id']) && !isset($params['slug'])) {
            throw new InvalidArgumentException("Either 'id' or 'slug' must be provided to fetch a post.");
        }

        // Ensure 'resource' parameter is set to 'post'.
        $params['resource'] = 'post';

        // Generate a unique cache key based on query parameters.
        $cacheKey = 'dg_content_post_' . md5(json_encode($params));

        // Attempt to retrieve data from cache.
        if ($cachedData = $this->cache->get($cacheKey)) {
            return $cachedData;
        }

        // Make the GET request to the API.
        $response = $this->makeRequest($params, 'get');

        $post = $response['post'] ?? null;

        if($this->doProcess){
            // Fix the date to be CI I18n\Time object
            if (!empty($post['createdAt'])) {
                $post['createdAt'] = \CodeIgniter\I18n\Time::parse($post['createdAt']);
            }
            if (!empty($post['updatedAt'])) {
                $post['updatedAt'] = \CodeIgniter\I18n\Time::parse($post['updatedAt']);
            }
            // Filter website target
            if (!empty($post['site'])){
                $post['site'] = array_filter($post['site'], function($site) {
                    return $site['key'] == $this->config->websiteKey;
                });
                // Convert to single array
                $post['site'] = array_shift($post['site']);
            }
        }

        // Cache the response data
        if ($this->config->cacheDuration > 0 && !empty($post)) {
            $this->cache->save($cacheKey, $post, $this->config->cacheDuration);
        }

        return $post;
    }

    /**
     * Retrieves a list of categories from the API.
     *
     * Implements caching to reduce API calls.
     *
     * @param bool $websiteOnly Whether to filter categories by website key.
     * @return array|null Array of categories or null on failure.
     */
    public function getCategories(bool $websiteOnly = false): ?array
    {
        // Define the cache key for categories.
        $cacheKey = 'dg_content_categories';
        if ($websiteOnly) {
            $cacheKey .= '_' . $this->config->websiteKey;
        }

        // Attempt to retrieve data from cache.
        if ($cachedData = $this->cache->get($cacheKey)) {
            return $cachedData;
        }

        // Define query parameters with 'resource' set to 'categories'.
        $params = ['resource' => 'categories'];
        if ($websiteOnly) {
            $params['website'] = $this->config->websiteKey;
        }

        // Make the GET request to the API.
        $response = $this->makeRequest($params, 'get');

        // Cache the response data
        if ($this->config->cacheDuration > 0 && !empty($response['categories'])) {
            $this->cache->save($cacheKey, $response['categories'], $this->config->cacheDuration);
        }

        return $response['categories'] ?? [];
    }

    /**
     * Updates statistics (views, likes, dislikes) for a specific post.
     *
     * @param array $data Data to update statistics.
     * @return array Updated statistics data.
     * @throws \Exception If an error occurs during the request.
     *
     * @throws InvalidArgumentException If required data fields are missing.
     */
    public function updateStats(string $type, string $id, int $count = 1): ?array
    {
        // Validate required data fields.
        if (empty($type) || empty($id)) {
            throw new InvalidArgumentException('Type and ID are required to update stats.');
        }
        if ($count < 1) {
            throw new InvalidArgumentException('Count must be a positive integer.');
        }

        // Prepare the data for the API request.
        $data = [
            'resource' => 'stats',
            'type' => $type,
            'id' => $id,
            'count' => $count,
        ];

        // Make the POST request to the API.
        $response = $this->makeRequest($data, 'post');

        // If the API call was successful, return the updated stats.
        return $response['stats'];
    }

    /**
     * Validate and fix the parameters for sending to the API.
     *
     * @param array $params Data parameters for the request.
     * @return array Fixed and validated parameters.
     */
    protected function reviewParams(array $params): array
    {
        // Change array to string with comma separated values.
        if (isset($params['tags']) && is_array($params['tags'])) {
            $params['tags'] = implode(',', $params['tags']);
        }
        if (isset($params['category']) && is_array($params['category'])) {
            $params['category'] = implode(',', $params['category']);
        }

        // Value of offset and limit must be positive integer
        if (isset($params['offset']) && !is_int($params['offset'])) {
            throw new InvalidArgumentException('Offset must be a positive integer.');
        }
        if (isset($params['limit']) && !is_int($params['limit'])) {
            throw new InvalidArgumentException('Limit must be a positive integer.');
        }

        // value of count must be positive integer
        if (isset($params['count']) && !is_int($params['count'])) {
            throw new InvalidArgumentException('Count must be a positive integer.');
        }

        // Add the website key to the parameters.
        $params['website'] = $this->config->websiteKey;

        return $params;
    }

    /**
     * Makes a GET/POST request to the API with the specified query parameters.
     *
     * @param array $params Data parameters for the request.
     * @return array Parsed response data.
     * @throws \Exception If an error occurs during the request.
     */
    protected function makeRequest(array $params, string $method = 'get'): array
    {
        try {
            // Validate and fix the parameters.
            $params = $this->reviewParams($params);

            $opts = [];
            if ($method == 'get') {
                $opts = [
                    'query' => $params,
                ];
            } else {
                $opts = [
                    'json' => $params,
                ];
            }
            // Send the request with parameters.
            $response = $this->http->request($method, '', $opts);

            // Handle and parse the API response.
            return $this->handleResponse($response);
        } catch (\CodeIgniter\HTTP\Exceptions\HTTPException $e) {
            throw new \Exception('Error making request to the DG Content API: ' . $e->getMessage());
        }
    }

    /**
     * Handles the API response, parsing JSON and checking for errors.
     *
     * @param ResponseInterface $response The HTTP response from the API.
     * @return array Parsed response data.
     * @throws \Exception If the response is not successful or contains an error.
     */
    protected function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();

        // Check if the response status code indicates success.
        if ($statusCode >= 200 && $statusCode < 300) {
            // Check response is not empty (e.g., for 204 No Content).
            if ($response->getBody() == '') {
                throw new \Exception('Empty response from the DG Content API');
            }
            // Parse the JSON response.
            try {
                $body = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\Exception $e) {
                throw new \Exception('Error parsing JSON response from the DG Content API');
            }

            // Check if the API returned an error message.
            if (isset($body['error'])) {
                throw new \Exception('DG Content API Error: ' . $body['error']);
            }

            // Return the successful data.
            return $body;
        }

        // Handle non-2xx status codes as an error.
        throw new \Exception('DG Content API Error: HTTP ' . $statusCode);
    }

    /**
     * Clears the cache for all cached items.
     * Using CI4's Cache manager
     *
     * @return int Number of items deleted from the cache.
     */
    public function clearCache(): int
    {
        // Clear cache with glob style pattern to match cached items key
        return $this->cache->deleteMatching('dg_content_*');
    }
}
