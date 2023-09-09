<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;

class TransformerGenerator extends BaseGenerator
{
    /**
     * Fields not included in the generator by default.
     */
    protected array $excluded_fields = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    private string $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->path = config('laravel_generator.path.transformer', app_path('transformer/'));
        $this->fileName = $this->config->modelNames->name.'Transformer.php';
    }

    public function generate()
    {
        $templateData = view('laravel-generator::transformer.transformer', $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment(infy_nl().'Transformer created: ');
        $this->config->commandInfo($this->fileName);
    }

    public function variables(): array
    {
        return [
            'transforms'        => implode(','.infy_nl_tab(1, 2), $this->generateTransforms()),
        ];
    }

    protected function generateTransforms(): array
    {
        $transforms = [];
        if (isset($this->config->fields) && !empty($this->config->fields)) {
            $modelName =  Str::lower($this->config->modelNames->name);
            foreach ($this->config->fields as $field) {
                $transforms[] = "'".$field->name."' => \$". $modelName."->".$field->name;
            }
        }

        return $transforms;
    }
}
