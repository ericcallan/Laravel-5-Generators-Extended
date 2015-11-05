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

class ViewService
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
    public function makeViews()
    {
        $valid = false;
        $indexPath = $this->getPath($this->getClassName(), 'index');
        $this->makeDirectory($indexPath);

        $createPath = $this->getPath($this->getClassName(), 'create');
        $this->makeDirectory($createPath);

        $editPath = $this->getPath($this->getClassName(), 'edit');
        $this->makeDirectory($editPath);

        if (!$this->files->exists($indexPath)) {
            if($this->files->put($indexPath, $this->compileViewStub('index'))){
                $valid = true;
            }
        }

        if (!$this->files->exists($createPath)) {
            if($this->files->put($createPath, $this->compileViewStub('create'))){
                $valid = true;
            }
        }

        if (!$this->files->exists($editPath)) {
            if($this->files->put($editPath, $this->compileViewStub('edit'))){
                $valid = true;
            }
        }

        $masterPath = base_path() . '/resources/views/master.blade.php';
        $stub = $this->files->get(__DIR__ . '/../stubs/views/master.stub');
        if (!$this->files->exists($masterPath)) {
            if($this->files->put($masterPath, $this->compileViewStub('master'))){
                $valid = true;
            }
        }

        return $valid;
    }

    /**
     * Compile the controller stub.
     *
     * @return string
     */
    protected function compileViewStub($type)
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/views/' .$type . '.stub');
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
    protected function getPath($name, $type)
    {
        $name = str_replace($this->getAppNamespace(), '', $name);
        return base_path() . '/resources/views/' . str_replace('\\', '/', $name) . '/' . $type . '.blade.php';
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
