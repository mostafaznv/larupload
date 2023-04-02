<?php

namespace Mostafaznv\Larupload\Actions;

use Exception;
use Illuminate\Http\UploadedFile;
use Spatie\ImageOptimizer\Optimizer;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class OptimizeImageAction
{
    private readonly array $config;

    public function __construct(private readonly UploadedFile $file)
    {
        $this->config = config('larupload.optimize-image');
    }

    public static function make(UploadedFile $file): self
    {
        return new self($file);
    }


    public function process(): UploadedFile
    {
        $this->optimizer()->optimize(
            $this->file->getRealPath(),
        );

        return new UploadedFile(
            path: $this->file->getRealPath(),
            originalName: $this->file->getClientOriginalName(),
            mimeType: $this->file->getClientMimeType()
        );
    }

    private function optimizer(): OptimizerChain
    {
        $optimizer = OptimizerChainFactory::create();
        $optimizer->setOptimizers($this->optimizers());
        $optimizer->setTimeout($this->config['timeout']);

        return $optimizer;
    }

    private function optimizers(): array
    {
        return collect($this->config['optimizers'])
            ->mapWithKeys(function(array $options, string $optimizerClass) {
                if (!is_a($optimizerClass, Optimizer::class, true)) {
                    $optimizerInterface = Optimizer::class;

                    throw new Exception("Configured optimizer `{$optimizerClass}` does not implement `{$optimizerInterface}`.");
                }

                $newOptimizerClass = new $optimizerClass();
                $newOptimizerClass->setOptions($options);

                return [$optimizerClass => $newOptimizerClass];
            })
            ->toArray();
    }
}
