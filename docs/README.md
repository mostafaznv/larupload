# Introduction

[<img src="https://img.shields.io/github/actions/workflow/status/mostafaznv/larupload/run-tests.yml?branch=master&#x26;label=Build&#x26;style=flat-square&#x26;logo=github" alt="GitHub Workflow Status" data-size="line">](https://github.com/mostafaznv/larupload/actions) [<img src="https://img.shields.io/codecov/c/github/mostafaznv/larupload/master.svg?style=flat-square&#x26;logo=codecov" alt="Codecov branch" data-size="line">](https://app.codecov.io/gh/mostafaznv/larupload) [<img src="https://img.shields.io/scrutinizer/g/mostafaznv/larupload.svg?style=flat-square" alt="Quality Score" data-size="line">](https://scrutinizer-ci.com/g/mostafaznv/larupload) <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License" data-size="line"> [<img src="https://img.shields.io/packagist/v/mostafaznv/larupload.svg?style=flat-square" alt="Latest Version on Packagist" data-size="line">](https://packagist.org/packages/mostafaznv/larupload) [<img src="https://img.shields.io/packagist/dt/mostafaznv/larupload.svg?style=flat-square" alt="Total Downloads" data-size="line">](https://packagist.org/packages/mostafaznv/larupload)



**Larupload** is a file uploader for Laravel, which is based on ORM and allows users to upload `images`, `videos`, `audios`, and other known file formats.

With Larupload, you can easily upload your files, and it comes with interesting features for uploading videos, audios, and images.

One of the main advantages of using Larupload is that it leverages the Laravel [filesystem](https://laravel.com/docs/filesystem). As a result, it is easy to switch between different drivers such as _Local_, _SFTP_, _S3_, and many others.&#x20;

It offers many useful features, including the ability to resize, crop, and optimize uploaded images, as well as manipulate uploaded videos by resizing and cropping them, and creating HTTP Live Streaming (HLS) content from uploaded videos.

Moreover, Larupload can calculate the dominant colors of videos and images, as well as extract their width, height, and duration for videos and audio files

<div align="left">

<figure><img src="https://mostafaznv.github.io/donate/donate.svg" alt="" width="188"><figcaption></figcaption></figure>

</div>



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
* Official Laravel Nova integration
* Easy to use



