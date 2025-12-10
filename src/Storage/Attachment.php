<?php

namespace Mostafaznv\Larupload\Storage;

use Mostafaznv\Larupload\Concerns\Storage\Attachment\AttachmentActions;
use Mostafaznv\Larupload\Concerns\Storage\Attachment\CoverAttachment;
use Mostafaznv\Larupload\Concerns\Storage\Attachment\RetrieveAttachment;
use Mostafaznv\Larupload\UploadEntities;


class Attachment extends UploadEntities
{
    use AttachmentActions;
    use CoverAttachment;
    use RetrieveAttachment;
}
