<?php

namespace Mostafaznv\Larupload;

use Mostafaznv\Larupload\Concerns\Standalone\BaseStandaloneLarupload;
use Mostafaznv\Larupload\Concerns\Standalone\BootStandaloneLarupload;
use Mostafaznv\Larupload\Concerns\Standalone\StandaloneLaruploadCallables;
use Mostafaznv\Larupload\Concerns\Standalone\StandaloneLaruploadCover;
use Mostafaznv\Larupload\Concerns\Standalone\StandaloneLaruploadNotCallables;
use Mostafaznv\Larupload\Storage\Attachment;

class Larupload extends Attachment
{
    use BootStandaloneLarupload;
    use BaseStandaloneLarupload;
    use StandaloneLaruploadCover;
    use StandaloneLaruploadCallables;
    use StandaloneLaruploadNotCallables;
}
