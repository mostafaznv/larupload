<?php

namespace Mostafaznv\Larupload\Storage;

use Mostafaznv\Larupload\Concerns\Storage\AttachmentActions;
use Mostafaznv\Larupload\Concerns\Storage\AttachmentEvents;
use Mostafaznv\Larupload\Concerns\Storage\BaseAttachment;
use Mostafaznv\Larupload\Concerns\Storage\CoverAttachment;
use Mostafaznv\Larupload\Concerns\Storage\QueueAttachment;
use Mostafaznv\Larupload\Concerns\Storage\RetrieveAttachment;
use Mostafaznv\Larupload\Concerns\Storage\StyleAttachment;
use Mostafaznv\Larupload\UploadEntities;

class Attachment extends UploadEntities
{
    use BaseAttachment;
    use AttachmentActions;
    use AttachmentEvents;
    use CoverAttachment;
    use StyleAttachment;
    use RetrieveAttachment;
    use QueueAttachment;
}
