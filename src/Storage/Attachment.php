<?php

namespace Mostafaznv\Larupload\Storage;

use Mostafaznv\Larupload\Concerns\Storage\Attachment\AttachmentActions;
use Mostafaznv\Larupload\Concerns\Storage\Attachment\AttachmentEvents;
use Mostafaznv\Larupload\Concerns\Storage\Attachment\BaseAttachment;
use Mostafaznv\Larupload\Concerns\Storage\Attachment\CoverAttachment;
use Mostafaznv\Larupload\Concerns\Storage\Attachment\QueueAttachment;
use Mostafaznv\Larupload\Concerns\Storage\Attachment\RetrieveAttachment;
use Mostafaznv\Larupload\Concerns\Storage\Attachment\StyleAttachment;
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
