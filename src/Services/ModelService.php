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
     * Get the path to where we should store the migration.
     *
     * @param  string $name
     * @return string
     */
    protected function getPath($name)
    {
        return base_path() . '/database/migrations/' . date('Y_m_d_His') . '_' . $name . '.php';
    }

    /**
     * Get the destination class path.
     *
     * @param  string $name
     * @return string
     */
    protected function getModelPath($name)
    {
        $name = str_replace($this->getAppNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
    }

        /**
     * Get the destination class path.
     *
     * @param  string $name
     * @return string
     */
    protected function getControllerPath($name)
    {
        $name = str_replace($this->getAppNamespace(), '', $name);
        return $this->laravel['path'] . '/Http/Controllers/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Compile the migration stub.
     *
     * @return string
     */
    protected function compileMigrationStub()
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/migration.stub');

        $this->replaceClassName($stub)
            ->replaceSchema($stub)
            ->replaceTableName($stub);

        return $stub;
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
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceClassName(&$stub)
    {
        $className = ucwords(camel_case($this->argument('name')));
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
        $variableName = strtolower(camel_case($this->argument('name')));
        $stub = str_replace('{{variable}}', $variableName, $stub);

        return $this;
    }

    /**
     * Replace the table name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceTableName(&$stub)
    {
        $table = $this->meta['table'];

        $stub = str_replace('{{table}}', $table, $stub);

        return $this;
    }

    /**
     * Replace the schema for the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceSchema(&$stub)
    {
        if ($schema = $this->option('schema')) {
            $schema = (new SchemaParser)->parse($schema);
        }

        $schema = (new SyntaxBuilder)->create($schema, $this->meta);

        $stub = str_replace(['{{schema_up}}', '{{schema_down}}'], $schema, $stub);

        return $this;
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

    /**
     * Get the class name for the Controller generator.
     *
     * @return string
     */
    protected function getControllerName()
    {
        return ucwords(str_singular(camel_case($this->meta['table'])));
    }
}
