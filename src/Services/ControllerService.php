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

class ControllerService
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
    public function makeController()
    {
        $controllerPath = $this->getPath($this->getClassName());

        if (!$this->files->exists($controllerPath)) {
            if($this->files->put($controllerPath, $this->compileControllerStub())){
                return true;
            }
        }

        return false;
    }

    /**
     * Compile the controller stub.
     *
     * @return string
     */
    protected function compileControllerStub()
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/controller.stub');
        $this->replaceClassName($stub)
            ->replaceVariableName($stub);
        return $stub;
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
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
        return app_path() . '/Http/Controllers/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceClassName(&$stub)
    {
        $className = ucwords(camel_case($this->getClassName()));
        $stub = str_replace('{{class}}', $className, $stub);
        return $this;
    }

        /**
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceVariableName(&$stub)
    {
        $pluralVariableName = str_plural(strtolower(camel_case($this->getClassName())));
        $stub = str_replace('{{plural}}', $pluralVariableName, $stub);

        $variableName = str_singular(strtolower(camel_case($this->getClassName())));
        $stub = str_replace('{{singular}}', $variableName, $stub);

        return $this;
    }

    /**
     * Get the class name for the Eloquent model generator.
     *
     * @return string
     */
    protected function getClassName()
    {
        return ucwords(str_singular(camel_case($this->meta['table'])));
    }
}
