<?php

use Mostafaznv\Larupload\DTOs\Style\AudioStyle;


it('will throw exception when name is numeric', function() {
    AudioStyle::make('12');

})->throws(Exception::class, 'Style name [12] is numeric. please use string name for your style');

