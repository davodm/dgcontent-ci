# DG Content for Codeigniter 4
    
[![Latest Stable Version](https://poser.pugx.org/davodm/dg-content-ci/v)](//packagist.org/packages/davodm/dg-content-ci)
[![Total Downloads](https://poser.pugx.org/davodm/dg-content-ci/downloads)](//packagist.org/packages/davodm/dg-content-ci)
[![Latest Unstable Version](https://poser.pugx.org/davodm/dg-content-ci/v/unstable)](//packagist.org/packages/davodm/dg-content-ci)
[![License](https://poser.pugx.org/davodm/dg-content-ci/license)](//packagist.org/packages/davodm/dg-content-ci)

A robust **CodeIgniter 4** package to interact with the **DGTTeam Content API**, featuring caching mechanisms, secure API key management via environment variables.

## Features

- **Seamless API Integration:** Interact with the DGTTeam Content API effortlessly.
- **Caching Mechanism:** Reduce API calls and improve performance with customizable caching.
- **Secure Configuration:** Manage API keys and sensitive data using environment variables.
- **Flexible Usage:** Easily fetch posts, single posts, categories, and update post statistics.
- **Error Handling:** Gracefully handle API errors with meaningful messages and logging.
- **Extensible:** Easily extend the package to include additional API endpoints or features.


## Prerequisites

Before integrating **DGContent** into your project, ensure you have the following:

- **PHP >= 7.4**
- **Composer** installed globally.
- **CodeIgniter 4** project set up.
- Familiarity with **OOP PHP** and **CodeIgniter 4**.

## Installation

You can install the package via Composer. Run the following command in your CodeIgniter 4 project directory:

```bash
composer require davodm/dgcontent-ci
```

## Configuration
1. Environment Variables
Store sensitive information like the API key in your .env file to keep it secure and out of version control.

Add the following entries to your .env file:

```ini
# API Key for DGTTeam Content API
DGCONTENT_API_KEY=your_api_key_here
# Cache duration in seconds (default: 3600)
DGCONTENT_CACHE_TTL=3600
# Website Domain (without http/https)
DGCONTENT_WEBSITE=xxx.com
# Optional: Base URL for the DGTTeam Content API
DGCONTENT_API_BASE_URL=https://xxx.com/api/
# Optional: Request timeout in seconds (default: 10)
DGCONTENT_TIMEOUT=10
```

2. Configuration File
The package comes with a configuration file located at `src/Config/DGContentCI.php`. You can customize default settings by modifying this file if necessary.

## Usage
You can initialize the library directly in your controller.

```php
$dgContent = new \DGContent\Libraries\Content();
$params = [
    'limit' => 5,
    'offset' => 0,
    'category' => 'Technology',
    'tags' => 'JavaScript,Node.js'
];

$posts = $dgContent
    ->setCacheDuration(7200)
    ->getPosts($params);

if ($posts['posts']) {
    foreach ($posts['posts'] as $post) {
        echo esc($post['title']) . '<br>';
    }
} else {
    echo "No posts found.";
}
```

## Functions
The package provides the following functions to interact with the DGTTeam Content API:

### `getPosts($params)`
Fetch posts based on the specified parameters and return `total` (as total number for pagination) and `posts` array.

**Parameters:**

- `limit`: Number of posts to fetch (default: 10).
- `offset`: Offset for pagination (default: 0).
- `category`: Filter posts by category slug. could be an array or string with comma separated values. (optional)
- `tags`: Filter posts by tags. could be an array or string with comma separated values. (optional)
- `language`: Filter posts by language code e.g. fa, en, es, fr. (optional)

**Returns:** An array with `total` (total number of posts) and `posts` array containing post objects.

### `getPost($params)`
Fetch a single post by ID or slug and return the post object.

**Parameters:**
- `id`: Post ID. (one of `id` or `slug` is required)
- `slug`: Post slug.

**Returns:** A single post object.

### `getPage($slug)`
Fetch a single page by slug and return the page object.

**Returns:** A single page object.

### `getCategories(bool $websiteOnly = false)`
Fetch all available categories. If `$websiteOnly` is set to `true`, it will only return categories that are attached to the website specified in the environment variables.

**Returns:** An array of category objects.

### `updateStats(string $type, string $id, int $count=1)`
Update post statistics (views, likes, dislikes).

**Parameters:**
- `type`: Type of statistic to update (views, likes, dislikes).
- `id`: Post ID.
- `count`: Number of counts to add (default: 1).

**Returns:** `true` if the statistics are updated successfully, `false` otherwise.

### `setCacheDuration(int $duration)`
Set the cache duration for API responses in seconds.

### `setProcessResult(bool $processResult)`
Set whether to process the API response before returning it. Default is `true`.

### `clearCache()`
Clear the cache for the API responses.

#### Post Object
The post object returned by the `getPosts` and `getPost` functions contains the following properties, which may vary based on the API response:
```php
[
    'id' => "xxx",
    'title' => 'Post Title',
    'slug' => 'post-title',
    'featuredImage' => 'https://example.com/image.jpg',
    'images' => [ // Images in the content
        'https://example.com/image1.jpg',
        'https://example.com/image2.jpg'
        ],
    'content' => 'Post content',
    'category' => [
        'title' => 'Category Title',
        'slug' => 'category-slug'
    ],
    'tags' => ['Tag 1', 'Tag 2'],
    'site' => [ // Based on processed result
        'title' => 'Site Title',
        'slug' => 'site-slug',
        'url' => 'xxx.com',
    ],
    'stats'=> [
        'views' => 100,
        'likes' => 10,
        'dislikes' => 2
    ],
    'language' => 'en', // Could be null or not present
    'source' => 'https://example.com', // Could be null or not present
    'author' => 'Author Name', // Could be null or not present
    'createdAt' => \CodeIgniter\I18n\Time Object, // Based on processed result
    'updatedAt' => '2024-10-14T22:26:23Z', // If it's not processed
]
```

## Error Handling
The package gracefully handles API errors and logs them for debugging purposes. If an error occurs during API requests, the package will throw an exception with a meaningful error message and you can catch it in your application to log or display the error.

## License
This project is licensed under the MIT License - see the [LICENSE](./LICENSE) file for details.

## Author
Davod Mozafari - [Twitter](https://twitter.com/davodmozafari)
