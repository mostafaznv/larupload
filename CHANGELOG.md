# Change Log

All notable changes to this project will be documented in this file.

## v0.0.12 - 2021/08/24
- fix "Illegal byte sequence" bug during extracting github archive file

## v0.0.11 - 2021/04/29
new
- drop support for laravel 6,7 and set minimum php version to 7.4
- standalone uploader
- replace attachments method with old construction method to define upload entities
- specific meta/url/getAttachment function for each attachment
- detach/delete cover
- update cover
- hide larupload columns from toArray/toJson
- automatically attach entities into model after toArray/toJson
- add an ability to return cameCase response
- convert style names to kebab-case to storing them into disk
- rename setUploadedFile to attach
- drop support for validating files

improvement

- throw exception on failed ffmpeg process
- calc dominant color for videos in remote drivers
- rename and remove some config keys

bugfix

- calculate dominant color when cover uploaded manually
- handle deleting uploaded files with soft-delete and force-deleting

## v0.0.10 - 2021/02/21
- download attachments using download function 
- fix a bug on escapeshellcmd for windows #2

## v0.0.9 - 2020/09/27
- Support laravel 8.*
- Return object instead of array in getFiles and meta functions
- Cleanup and improvement on migration blueprint
- Improvement on validator
- Merge meta into file object
- Fix a bug on saving svg cover image
- Return null links and meta after delete file
- Unit test
- Throw exception on errors in ffmpeg component
- Add code quality and travis automated test
- Minor bug fix and improvement

## v0.0.8 [hotfix] - 2019/04/29
- change new Process($cmd) from string to array
string cmd deprecated in symfony/process v5.*

## v0.0.8 - 2019/04/27
- Support Laravel 7.*
- Delete unused trait (sluggable)

## v0.0.7 - 2019/10/11
- Support Laravel 6.* 

## v0.0.6 - 2019/06/28
- Set FFMpeg capture frame (in second)
- Minor Bugfix

## v0.0.6 - 2019/05/23
- Queue FFMpeg Process
- Bugfix

## v0.0.5 - 2019/05/06
- Support Laravel 5.7

## v0.0.4 - 2019/02/22
- Fix a bug on defining file duration as an integer value
- Set banner for readme
- Fix a bug on streaming script to support stream in windows 

## v0.0.3 - 2019/02/10
- Fix a bug on calculate scale size
- Make it easier to manipulate svg files for Imagick and Gmagick by change file name to jpg
- Fix a bug on save file when we have multiple instances from a model
- Bugfix on set LARUPLOAD_NULL
- Store human readable file type into database
- Change type of columns in database
- Return null for `meta` when value is LARUPLOAD_NULL
- Streaming - HLS
- Fix a bug on generate cover for transparent png images
- Improvement on crop/resize in ffmpeg scripts

## v0.0.2 - 2018/09/10
- Support Laravel 5.7
- Remove some extra functions (postpone `getAttribute` features to next versions) 

## v0.0.1 - 2018/07/20
- Initial Release
