<?php

namespace Mostafaznv\Larupload;


use Mostafaznv\Larupload\Concerns\Storage\UploadEntity\BaseUploadEntity;
use Mostafaznv\Larupload\Concerns\Storage\UploadEntity\FFMpegUploadEntity;
use Mostafaznv\Larupload\Concerns\Storage\UploadEntity\ImageUploadEntity;
use Mostafaznv\Larupload\Concerns\Storage\UploadEntity\UploadEntityFileSystem;
use Mostafaznv\Larupload\Concerns\Storage\UploadEntity\UploadEntityName;
use Mostafaznv\Larupload\Concerns\Storage\UploadEntity\UploadEntityProperties;
use Mostafaznv\Larupload\Concerns\Storage\UploadEntity\UploadEntityResponse;
use Mostafaznv\Larupload\Concerns\Storage\UploadEntity\UploadEntityStyle;

class UploadEntities
{
    use UploadEntityProperties;
    use BaseUploadEntity;
    use UploadEntityName;
    use UploadEntityStyle;
    use UploadEntityFileSystem;
    use ImageUploadEntity;
    use FFMpegUploadEntity;
    use UploadEntityResponse;
}
