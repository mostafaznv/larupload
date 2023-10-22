# Larupload

[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/mostafaznv/larupload/run-tests.yml?branch=master\&label=Build\&style=flat-square\&logo=github)](https://github.com/mostafaznv/larupload/actions) [![Codecov branch](https://img.shields.io/codecov/c/github/mostafaznv/larupload/master.svg?style=flat-square\&logo=codecov)](https://app.codecov.io/gh/mostafaznv/larupload) [![Quality Score](https://img.shields.io/scrutinizer/g/mostafaznv/larupload.svg?style=flat-square)](https://scrutinizer-ci.com/g/mostafaznv/larupload) ![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square) [![Latest Version on Packagist](https://img.shields.io/packagist/v/mostafaznv/larupload.svg?style=flat-square)](https://packagist.org/packages/mostafaznv/larupload) [![Total Downloads](https://img.shields.io/packagist/dt/mostafaznv/larupload.svg?style=flat-square)](https://packagist.org/packages/mostafaznv/larupload)


**Larupload** is a file uploader for Laravel, which is based on ORM and allows users to upload `images`, `videos`, `audios`, and other known file formats.

With Larupload, you can easily upload your files, and it comes with interesting features for uploading videos, audios, and images.

One of the main advantages of using Larupload is that it leverages the Laravel [filesystem](https://laravel.com/docs/filesystem). As a result, it is easy to switch between different drivers such as _Local_, _SFTP_, _S3_, and many others.&#x20;

It offers many useful features, including the ability to resize, crop, and optimize uploaded images, as well as manipulate uploaded videos by resizing and cropping them, and creating HTTP Live Streaming (HLS) content from uploaded videos.

Moreover, Larupload can calculate the dominant colors of videos and images, as well as extract their width, height, and duration for videos and audio files

[![Donate](https://mostafaznv.github.io/donate/donate.svg)](getting-started/support-us.md)



### Some features for Larupload:

* Upload with 2 different strategies: ORM-based and Standalone
* Use different drivers
* Ability to resize/crop photos and videos
* Ability to create multiple sizes of videos and images
* Ability to create HTTP Live Streaming (HLS) from video sources
* Ability to hide the real ID of model records by using different ID formats (ULID, UUID and ...)
* Built-in support for optimizing images
* Extract the width and height of the image
* Extract width, height, and duration of the video
* Extract the duration of the audio
* Extract dominant color from the image and video
* Automatically create a cover image for video files
* Possibility to upload a cover for every file
* A specific function (column type) for creating database columns when running migration
* Get the URL of the uploaded file individually or as a set of "defined styles"
* Download response for each style
* Name files in several ways
* Supports Persian and Arabic for file naming
* Has 2 modes for storage: HEAVY (a big table with indexing) mode and LIGHT (it creates just 2 columns without any indexing)
* Queue FFMpeg processes and finish them in the background
* [Official Laravel Nova integration](https://github.com/mostafaznv/nova-file-artisan)
* Easy to use


### Documentation
You can find installation instructions and detailed instructions on how to use this package at the [dedicated documentation site.](https://mostafaznv.gitbook.io/larupload/)
