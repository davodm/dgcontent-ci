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
# DgContentCI Configuration
DGCONTENT_API_KEY=your_api_key_here
DGCONTENT_CACHE_TTL=3600       # Cache duration in seconds (default: 3600)
DGCONTENT_WEBSITE=xxx.com           # Your website URL (without http/https)
```
Parameters:

* `DGCONTENT_API_KEY`: (Required) Your API key for the DGTTeam Content API.
* `DGCONTENT_WEBSITE`: (Required) Your website URL (without http/https). Used for filtering posts by website.
* `DGCONTENT_CACHE_TTL`: (Optional) Duration to cache API responses in seconds. Default is 3600 seconds (1 hour).
* `DGCONTENT_API_BASE_URL` (Optional) Base URL for the DGTTeam Content API if it's different from the default.

2. Configuration File
The package comes with a configuration file located at `src/Config/DgContentCI.php`. You can customize default settings by modifying this file if necessary.

## Usage
You can initialize the library directly in your controller.

```php
$dgContent = new \DGContent\DGContent();
$params = [
    'limit' => 5,
    'offset' => 0,
    'category' => 'Technology',
    'tags' => 'JavaScript,Node.js'
];

$posts = $dgContent->getPosts($params);

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
- `getPosts($params)`: Fetch posts based on the specified parameters and return `total` (as total number for pagination) and `posts` array.
- `getPost($id)`: Fetch a single post by ID.
- `getCategories($websiteOnly = false)`: Fetch all available categories. If `$websiteOnly` is set to `true`, it will only return categories that have posts for the current website.
- `updateStats($type, $id, $count=1)`: Update post statistics (views, likes, dislikes).

### Parameters for `getPosts($params)`
- `limit`: Number of posts to fetch (default: 10).
- `offset`: Offset for pagination (default: 0).
- `category`: Filter posts by category. (optional)
- `tags`: Filter posts by tags. (optional)
- `language`: Filter posts by language code e.g. fa, en, es, fr. (optional)

## Error Handling
The package gracefully handles API errors and logs them for debugging purposes. If an error occurs during API requests, the package will throw an exception with a meaningful error message and you can catch it in your application to log or display the error.

## License
This project is licensed under the MIT License - see the [LICENSE](./LICENSE) file for details.

## Author
Davod Mozafari - [Twitter](https://twitter.com/davodmozafari)
