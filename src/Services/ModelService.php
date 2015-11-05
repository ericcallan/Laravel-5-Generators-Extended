<?php

namespace Laracasts\Generators\Services;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Composer;
use Laracasts\Generators\Migrations\NameParser;
use Laracasts\Generators\Migrations\SchemaParser;
use Laracasts\Generators\Migrations\SyntaxBuilder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ModelService
{
    use AppNamespaceDetectorTrait;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $meta;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    public function __construct(Array $meta, Filesystem $files){
        $this->files = $files;
        $this->meta = $meta;
    }

    /**
     * Generate a fully fleshed out controller, if the user wishes.
     */
    public function makeModel()
    {
        $modelPath = $this->getPath($this->getModelName());
        if(!$this->files->exists($modelPath)) {
            $this->call('make:model', [
                'name' => $this->getModelName()
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get the destination class path.
     *
     * @param  string $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace($this->getAppNamespace(), '', $name);
        return app_path() . '/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Get the class name for the Eloquent model generator.
     *
     * @return string
     */
    protected function getModelName()
    {
        return ucwords(str_singular(camel_case($this->meta['table'])));
    }

}
