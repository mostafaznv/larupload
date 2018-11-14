# Larupload
**Larupload** will help you upload your files easily. in addition to uploading files, Larupload has interesting features for uploading `video`, `audio` and `image`.
 
in larupload we’ve used the laravel [filesystem](https://laravel.com/docs/filesystem). Thanks to laravel filesystem, it’s easy to switch between "any" desired driver (such as local, sftp, s3, etc.) 

> Note: farsi [documentation](README.fa.md)

## Some of the features for Larupload:
- Using different drivers 
- Ability to resize/crop photos and videos
- Ability to create multiple sizes of the videos and images
- Extract the width and height of the image
- Extract width, height and duration of the video
- Extract the duration of the audio
- Extract dominant color from the image and video 
- Automatically create cover image for video files
- Possibility to upload cover for every file
- A specific function for creating database columns when running migration
- Getting the URL of the uploaded file individually or as a set of "defined styles"
- Naming files in several different ways
- Supports Persian and Arabic for file naming
- Validates the input files by file format and file type
- Ability to set the configuration publicly or privately for each model
- Has 2 modes for storage: *heavy* mode and *light* mode

## Requirements:
- Laravel 5.5 or higher
- GD library
- FFMPEG


## Installation

1. ##### Install the package via composer:
    ```shell
    composer require mostafaznv/larupload
    ```

2. ##### Publish config:
    ```shell
    php artisan vendor:publish --provider="Mostafaznv\Larupload\LaruploadServiceProvider"
    ```

3. ##### Done


## Usage
1. ##### Add the corresponding columns to the desired table
    ```php
    <?php
    
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    
    class Uploads extends Migration
    {
        public function up()
        {
            Schema::create('uploads', function (Blueprint $table) {
                $table->increments('id');
                $table->upload('file'); // or $table->upload('file', 'heavy');
                $table->timestamps();
            });
        }
    
        public function down()
        {
            Schema::dropIfExists('uploads');
        }
    }
    ```

2. ##### Add Larupload trait to the model
    ```php
    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    use Mostafaznv\Larupload\Traits\Larupload;
    
    class Upload extends Model
    {
        use Larupload;
    
        public function __construct(array $attributes = [])
        {
            parent::__construct($attributes);
    
            $this->hasUploadFile('file');
        }
    }
    ```

3. ##### Upload file
    ```php
    $upload = new Upload;
    $upload->file = $request->file;
    $upload->save();
    ```

## Note 
> - All files are being uploaded in original style. you can add other styles, but the original file is being uploaded as original format.
> - Create column for heavy mode Additional information such as name, size, format, and so on are stored in the database unless not storing them is enabled in the config file. 

## Following instructions:
- [Create column in the table using migration](#create-column-in-the-table-using-migration)
  - [Create column for heavy mode](#create-column-for-heavy-mode)
  - [Create column for light mode](#create-column-for-light-mode)
- [File upload methods](#file-upload-methods)
  - [Upload by accessor](#upload-by-accessor)
  - [Upload by function](#upload-by-function)
  - [Deleting the existing file](#deleting-the-existing-file-and-the-value-in-the-database)
- [Generate download link](#generate-the-download-link)
- [Get additional information (Meta)](#get-additional-information-meta)
- [Customization](#customization)
  - [Customization by config file](#customization-by-config-file)
  - [Customization by model constructor](#customization-by-model-constructor)
- [Some extra tricks](#some-extra-tricks)
  - [Set Attribute](#set-attribute)
  - [Get Attribute](#get-attribute)

## Create column in the table using migration 
to make creating the columns required by Larupload easier, we have created an ability to easily make the columns you need in the table, with the help of the macro feature.

1. ### Create column for heavy mode
    ```php
    Schema::create('uploads', function (Blueprint $table) {
        $table->increments('id');
        $table->upload('file');
        $table->timestamps();
    });
    ```

2. ### Create column for light mode
    ```php
    Schema::create('uploads', function (Blueprint $table) {
        $table->increments('id');
        $table->upload('file', 'light');
        $table->timestamps();
    });
    ```
The difference between `heavy` and `light` mode is in the `number` of table columns and the way they’re stored.

In heavy mode, a **separate** column is created for **every field** and every data is stored in their own column. this mode is useful when you want to use special queries on table data or use it to sort your data.

But in the light mode, only the **filename** is stored in its own column, and other file information is stored in a **json/string column** named **meta**. This mode is useful to record or display your data.


## File upload methods
There are several methods for uploading a file:
1. ### Upload by accessor
    ```php
    $upload->file = $request->file;
    ```
    
2. ### Upload by function

    With the `setUploadedFile` function, you can upload the file and the file cover (if needed)
    
    **Input arguments of the function:**
    - First: the `name` of the file column in the table *(required field)*
    - Second: the `file` that you want to upload *(required field)*
    - Third: `cover` file *(optional)*
 
    > if you submit the cover file, the priority is to create cover with your uploaded file and it prevents the automatic cover creation by the package

    ```php
    $upload->setUploadedFile('file', $request->file, $request->cover);
    ```

3. ### Deleting the existing file and the value in the database

    If you want to delete the existing file, you can use `LARUPLOAD_NULL` 
    ```php
    $upload->file = LARUPLOAD_NULL;
    ```


## Generate the download link 
You can use the following methods to access the uploaded file link:
1. ### Get link for all styles
    
    Code:
    ```php
    dd($upload->file);
    ```
    Output:
    ```php
    array:3 [▼
      "original"  => "http://larupload.site/uploads/uploads/1/file/original/image.png"
      "cover"     => "http://larupload.site/uploads/uploads/1/file/cover/image.jpg"
      "thumbnail" => "http://larupload.site/uploads/uploads/1/file/thumbnail/image.png"
    ]
    ```

2. ### Get link for a particular style
    ```php
    echo $upload->file['thumbnail'];
    // or
    echo $upload->url('file', 'thumbnail');
    ```
    > If you don’t send the second argument, the link to the original file will be automatically returned.
    
    > You can use `LaruploadUrl` instead of the url function.


## Get additional information (Meta)
The first argument is the filename , and the second argument is the desired Metadata

1. ### Get all additional information of the file 
    
    Code:
    ```php
    dd($upload->meta('file'));
    ```
    
    Output:
    ```php
    array:9 [▼
      "name"           => "64d65a4e98029c37e7fd510c6e0a34d6.png"
      "size"           => 93366
      "type"           => "image"
      "mime_type"      => "image/png"
      "cover"          => "64d65a4e98029c37e7fd510c6e0a34d6.jpg"
      "width"          => 2560
      "format"         => "png"
      "height"         => 1600
      "duration"       => null
      "dominant_color" => "#c5ae0a"
    ]
    ```
    
    > **type**: returns human readable file type with this names: `image`, `video`, `audio`, `pdf`, `compressed`, `file` 

2. ### Get the Meta by its name 
    
    Code:
    ```php
    echo $upload->meta('file', 'dominant_color');
    ```
    
    Output:
    ```php
    #c5ae0a
    ```

## Customization
In larupload, we’ve put a lot of effort into making the package more customized. you can customize the package operation in 2 different ways:

1. Using `configuration` file 
2. Using model `constructor`


### Customization by config file
- #### Storage
    
    With this feature, you can set the driver to upload your file.
 
    Drivers that are supported:
    - local 
    - public
    - sftp 
        > only sftp is supported and we have **no plan** for supporting **ftp**
    - s3 
        > not tested and requires testing; but it doesn't seem to have any problem


- #### Mode
    
    There are two modes for storing the uploaded file information in Larupload. `heavy` mode and `light` mode 
    - Heavy mode stores every information and file details in its own column.
    - Light mode stores additional information as `json_encode` in a column named Meta. 
        > Note that the selection of each of these modes should fit the type of table created.

- #### Naming method 
    
    With this feature, you can specify the naming method for files as follows: 
    - **slug**: the name of the uploaded file is converted into slug. to prevent file from caching in different clients, a random number is always added to the end of the filename.
    - **hash_file**: using the `MD5` algorithm, the hash of uploaded file is used as the filename.
    - **time**: upload time is selected as the uploaded file name. 

- #### Lang 
    This feature is used to name files when using the slug template. if you leave this section blank, we will use the application language (available in the `config/app.php` file).

- #### Image processing library 
    You can specify Larupload to use which image processing library to perform crop and resize operations
    
    options:
    - Imagine\Gd\Imagine
    - Imagine\Imagick\Imagine
    - Imagine\Gmagick\Imagine

- #### Styles 
    This section is used to define different styles on videos and photos. With this feature, you can create as many different copies of the original file as you want, also `crop` or `resize` them. 
    
    example:
    ```php
    'styles' => [
        'thumbnail' => [
            'height' => 500,
            'width'  => 500,
            'mode'   => 'crop',
            'type'   => ['image', 'video'], 
        ],
        'medium' => [
            'height' => 1000,
            'width'  => 1000,
            'mode'   => 'auto',
            'type'   => ['image']
        ]
    ]
    ```
    Description of style items:
    - **height**: height of the photo or video. the height value should be `numeric`
    - **width**: width of the photo or video. the width value should be `numeric` 
    - **mode**: larupload decides how to deal with the uploaded video or photo with this field. acceptable values for this field are: 
        - landscape
        - portrait 
        - crop 
        - exact
        - auto
    - **type**: with this field, you can determine that the defined style is usable for what type of files, `image`, `video` or `both`.

- #### Generate cover
    Larupload allows you to automatically generate cover image from the uploaded image or video. With this field, you can enable or disable this feature.

- #### Cover style
    With this field, you can manage the configuration of the created cover.
    ```php
    'cover_style' => [
        'height' => 500,
        'width'  => 500,
        'mode'   => 'crop'
    ]
    ```

- #### Dominant color
    With this feature, you can extract the dominant color of the image or video.
    > Note that if you disable cover generating, the color extraction of the video will be automatically disabled.

- #### Keep old files
    By enabling this feature, `prevent` old files from `deleting` while `updating` the database record.

- #### Preserve files flag
    Enabling this feature, `prevent` old files from `being deleted` when the database record is `deleted`. 

- #### Upload path
    The address of the place where you want your files to be uploaded. This address uses the root of your file system driver, relatively.

- #### Allowed mime types
    You can validate the input files with this field and by using `MimeType`
    
    Example: `video/mp4`

- #### Allowed Mimes
    With this field and by using the file format, you can validate the input files
    
    Example: `mp4`
    
- #### FFMPEG
    If you keep this section empty, larupload will try to find the FFMPEG path using system environment. But you can manually specify the FFMPEG path this way.
    
    Example:
    ```php
    'ffmpeg' => [
        'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg',
        'ffprobe.binaries' => '/usr/local/bin/ffprobe'
    ],
    ```

### Customization by model constructor 
In addition to using the config file, that is responsible for Larupload general configuration, you can customize each model by Its constructor
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Traits\Larupload;

class Upload extends Model
{
    use Larupload;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->hasUploadFile('file', [
            'naming_method'  => 'slug',
            'keep_old_files' => false,
            'styles'         => [
                'small' => [
                    'width'  => 800,
                    'height' => 800,
                    'mode'   => 'crop',
                    'type'   => ['image', 'video']
                ],

                'thumbnail' => [
                    'width'  => 250,
                    'height' => 250,
                    'mode'   => 'auto',
                    'type'   => ['video']
                ]
            ],
        ]);
    }
}
```

## Some extra tricks

### Set Attribute
Sometimes you need to set some extra attributes to save into database. It's `important` to call larupload initializer function: 

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Traits\Larupload;

class Upload extends Model
{
    use Larupload;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->hasUploadFile('file');
    }
    
    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            if ($value) {
                $attachedFile = $this->attachedFiles[$key];
                $attachedFile->setUploadedFile($value);
    
                $this->attributes['your_own_attribute'] = 'value';
            }
    
            return;
        }
    
        parent::setAttribute($key, $value);
    }
}
``` 

### Get Attribute
Sometimes you want to return files in an API response or you want to use toArray()

#### Return urls for all files:

Code:
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Traits\Larupload;

class Contact extends Model
{
    use Larupload;

    protected $table = 'contacts';
    protected $appends = ['media'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->hasUploadFile('image');
        $this->hasUploadFile('profile_cover');
    }

    public function getMediaAttribute()
    {
        return $this->getFiles();
    }
}

``` 

Output:
```php
"media" => array:1 [▼
    "image" => array:3 [▼
        "original" => "http://larupload.site/uploads/uploads/contacts/18/image/original/38792a2e4497b7b64e0a3f79d581c805.jpeg"
        "cover" => "http://larupload.site/uploads/uploads/contacts/18/image/cover/38792a2e4497b7b64e0a3f79d581c805.jpg"
        "thumbnail" => "http://larupload.site/uploads/uploads/contacts/18/image/thumbnail/38792a2e4497b7b64e0a3f79d581c805.jpeg"
    ],
    "profile_cover" => array:3 [▼
        "original" => "http://larupload.site/uploads/uploads/contacts/18/image/original/38792a2e4497b7b64e0a3f79d581c805.jpeg"
        "cover" => "http://larupload.site/uploads/uploads/contacts/18/image/cover/38792a2e4497b7b64e0a3f79d581c805.jpg"
        "thumbnail" => "http://larupload.site/uploads/uploads/contacts/18/image/thumbnail/38792a2e4497b7b64e0a3f79d581c805.jpeg"
    ]
]
```

#### Return urls for specific file:

Code:
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Traits\Larupload;

class Contact extends Model
{
    use Larupload;

    protected $table = 'contacts';
    protected $appends = ['image'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->hasUploadFile('image');
        $this->hasUploadFile('profile_cover');
    }

    public function getImageAttribute()
    {
        return $this->getFiles('image');
    }
}

``` 

Output:
```php
"image" => array:3 [▼
    "original" => "http://larupload.site/uploads/uploads/contacts/18/image/original/38792a2e4497b7b64e0a3f79d581c805.jpeg"
    "cover" => "http://larupload.site/uploads/uploads/contacts/18/image/cover/38792a2e4497b7b64e0a3f79d581c805.jpg"
    "thumbnail" => "http://larupload.site/uploads/uploads/contacts/18/image/thumbnail/38792a2e4497b7b64e0a3f79d581c805.jpeg"
]
```


## Contributors
- Mostafa Zeinivand [@mostafaznv](https://github.com/mostafaznv)
- Faezeh Ghorbannezhad [@Ghorbannezhad](https://github.com/Ghorbannezhad)
- SamssonApps [@SamssonApps](https://github.com/SamssonApps)


## Changelog
Refer to the [Changelog](CHANGELOG.md) for a full history of the project.

## License
This software is released under [The MIT License (MIT)](LICENSE).

(c) 2018 Mostafaznv, All rights reserved.
