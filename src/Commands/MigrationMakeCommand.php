<?php

namespace Laracasts\Generators\Commands;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Composer;
use Laracasts\Generators\Migrations\NameParser;
use Laracasts\Generators\Migrations\SchemaParser;
use Laracasts\Generators\Migrations\SyntaxBuilder;
use Laracasts\Generators\Services\MigrationService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrationMakeCommand extends Command
{
    use AppNamespaceDetectorTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:migration:schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration class and apply schema at the same time';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     * @param Composer $composer
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $meta = (new NameParser)->parse($this->argument('name'));
        $name = $this->argument('name');
        if ($schema = $this->option('schema')) {
            $migrationService = (new MigrationService($meta, $this->files, $schema))->makeMigration($name);
        } else {
            $migrationService = (new MigrationService($meta, $this->files, null))->makeMigration($name);
        }

        if($migrationService) {
            $this->info('Migration created successfully.');
        }

        // $modelService = (new ModelService($meta))->makeModel();
        // if($modelService) {
        //     $this->info('Model created successfully.');
        // }

        // $controllerService = (new MigrationService($meta))->makeController();
        $this->composer->dumpAutoloads();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the migration'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['schema', 's', InputOption::VALUE_OPTIONAL, 'Optional schema to be attached to the migration', null],
            ['model', null, InputOption::VALUE_OPTIONAL, 'Want a model for this table?', true],
            ['controller', null, InputOption::VALUE_OPTIONAL, 'Want a controller for this table?', true],
        ];
    }
}
