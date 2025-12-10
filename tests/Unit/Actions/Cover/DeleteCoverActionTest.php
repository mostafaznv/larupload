<?php

use Mostafaznv\Larupload\Actions\Cover\DeleteCoverAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\DTOs\Style\Output;


beforeEach(function () {
    $this->output = Output::make(
        id: '1234',
        dominantColor: '#000',
        cover: 'cover.jpg',
    );
});


it('will clear dominant-color and cover properties', function () {
    $output = DeleteCoverAction::make(LaruploadFileType::VIDEO, $this->output)->run();

    expect($output->id)
        ->toBe('1234')
        ->and($output->dominantColor)
        ->toBeNull()
        ->and($output->cover)
        ->toBeNull();
});

it('will only clear cover when the type is image', function () {
    $output = DeleteCoverAction::make(LaruploadFileType::IMAGE, $this->output)->run();

    expect($output->id)
        ->toBe('1234')
        ->and($output->dominantColor)
        ->toBe('#000')
        ->and($output->cover)
        ->toBeNull();
});

