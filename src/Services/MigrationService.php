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

class MigrationService
{
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

    /**
     * The schame string instance.
     *
     * @var Filesystem
     */
    protected $schema;

    public function __construct(Array $meta, Filesystem $files, $schema){
        $this->files = $files;
        $this->meta = $meta;
        $this->schema = $schema;
    }

    /**
     * Generate the desired migration.
     */
    public function makeMigration()
    {
        $name = $this->meta['name'];
        if ($this->files->exists($path = $this->getPath($name))) {
            return $this->error($this->type . ' already exists!');
        }
        $this->makeDirectory($path);
        if($this->files->put($path, $this->compileMigrationStub())){
            return true;
        }

        return false;
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
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceClassName(&$stub)
    {
        $className = ucwords(camel_case($this->meta['name']));
        $stub = str_replace('{{class}}', $className, $stub);
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
        if ($schema = $this->schema) {
            $schema = (new SchemaParser)->parse($schema);
        }
        $schema = (new SyntaxBuilder)->create($schema, $this->meta);
        $stub = str_replace(['{{schema_up}}', '{{schema_down}}'], $schema, $stub);
        return $this;
    }
}
