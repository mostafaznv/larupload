<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use Exception;
use FFMpeg\Format\Audio\Aac;
use FFMpeg\Format\Audio\Flac;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;

class AudioStyle extends Style
{
    public readonly LaruploadMediaStyle $mode;
    public readonly Mp3|Aac|Wav|Flac $format;

    public function __construct(string $name, Mp3|Aac|Wav|Flac $format = new Mp3, int $bitrate = 128)
    {
        parent::__construct($name);

        $format->setAudioKiloBitrate($bitrate);

        $this->format = $format;
    }

    public static function make(string $name, Mp3|Aac|Wav|Flac $format = new Mp3, int $bitrate = 128): self
    {
        return new self($name, $format, $bitrate);
    }
}
