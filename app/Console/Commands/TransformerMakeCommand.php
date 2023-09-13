<?php

namespace App\Console\Commands;

use InfyOm\Generator\Commands\BaseCommand;

class TransformerMakeCommand extends BaseCommand
{
    protected $name = 'crud-transformer';

    protected $description = 'Create a new Fractal transformer class';

    public function handle()
    {
        $this->input->setOption('fromTable', true);
        parent::handle();

        /** @var TransformerGenerator $transformerGenerator */
        $modelGenerator = app(TransformerGenerator::class);
        $modelGenerator->generate();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), []);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), []);
    }
}
