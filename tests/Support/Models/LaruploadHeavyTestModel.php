<?php

namespace Mostafaznv\Larupload\Test\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Test\Support\Models\Traits\TestAttachments;
use Mostafaznv\Larupload\Traits\Larupload;

class LaruploadHeavyTestModel extends Model
{
    use Larupload, TestAttachments;

    public LaruploadMode $mode = LaruploadMode::HEAVY;

    protected $table = 'upload_heavy';
    protected $fillable = [
        'main_file'
    ];
}
