<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use FFMpeg\Format\Audio\Aac;
use FFMpeg\Format\Audio\Flac;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;


class AudioStyle extends Style
{
    public readonly Mp3|Aac|Wav|Flac $format;


    public function __construct(string $name, Mp3|Aac|Wav|Flac $format = new Mp3)
    {
        parent::__construct($name);

        $this->format = $format;

    }

    public static function make(string $name, Mp3|Aac|Wav|Flac $format = new Mp3): self
    {
        return new self($name, $format);
    }

    public function extension(): ?string
    {
        if ($this->format instanceof Aac) {
            return 'aac';
        }
        else if ($this->format instanceof Wav) {
            return 'wav';
        }
        else if ($this->format instanceof Flac) {
            return 'flac';
        }

        return 'mp3';
    }
}
