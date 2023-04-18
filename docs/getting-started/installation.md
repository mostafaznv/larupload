# Installation

### Requirements:

* Laravel 10.\* or higher
* PHP 8.1 or higher
* GD library
* FFMPEG



### Installation

1.  **Install the package via composer:**

    ```shell
    composer require mostafaznv/larupload
    ```
2.  **Publish config, migrations and translations:**

    ```shell
    php artisan vendor:publish --provider="Mostafaznv\Larupload\LaruploadServiceProvider"
    ```
3.  **Create Tables:**

    ```shell
    php artisan migrate
    ```
4. **Done**

