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
use Laracasts\Generators\Services\ControllerService;
use Laracasts\Generators\Services\ViewService;
use Laracasts\Generators\Services\ModelService;
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
     * The meta data.
     *
     * @var Filesystem
     */
    protected $meta;

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
        $this->meta = (new NameParser)->parse($this->argument('name'));

        $this->buildMigration();
        $this->buildController();
        $this->buildModel();
        $this->buildViews();
        $this->buildRoutes();

        $this->composer->dumpAutoloads();
    }

    /**
     * Build Out Migration.
     *
     * @return mixed
     */
    protected function buildMigration()
    {
        if ($schema = $this->option('schema')) {
            $migrationService = (new MigrationService($this->meta, $this->files, $schema))->makeMigration();
        } else {
            $migrationService = (new MigrationService($this->meta, $this->files, null))->makeMigration();
        }

        if($migrationService) {
            $this->info('Migration created successfully.');
        }
    }

    /**
     * Build Out Controller.
     *
     * @return mixed
     */
    protected function buildController()
    {
        if ($this->option('controller')) {
            $controllerService = (new ControllerService($this->meta, $this->files))->makeController();
            if($controllerService) {
                $this->info('Controller created successfully.');
            } else {
                $this->info('It appears the Controller already exists');
            }
        }
    }

    /**
     * Build Out Controller.
     *
     * @return mixed
     */
    protected function buildViews()
    {
        if ($this->option('views')) {
            $viewService = (new ViewService($this->meta, $this->files))->makeViews();
            if($viewService) {
                $this->info('View created successfully.');
            } else {
                $this->info('It appears the View(s) already exists');
            }
        }
    }

    public function buildRoutes()
    {
        $routes = app_path() . '/Http/routes.php';

        $contents = "Route::get('/" . str_plural(strtolower(camel_case($this->meta['table']))) . "', '" . ucwords(str_singular(camel_case($this->meta['table'])))  . "Controller@index');\n";
        $contents .= "Route::get('/" . str_plural(strtolower(camel_case($this->meta['table']))) . "/create', '" . ucwords(str_singular(camel_case($this->meta['table'])))  . "Controller@create');\n";
        $contents .= "Route::post('/" . str_plural(strtolower(camel_case($this->meta['table']))) . "/create', '" . ucwords(str_singular(camel_case($this->meta['table'])))  . "Controller@store');\n";
        $contents .= "Route::get('/" . str_plural(strtolower(camel_case($this->meta['table']))) . "/edit/{id}', '" . ucwords(str_singular(camel_case($this->meta['table'])))  . "Controller@edit');\n";
        $contents .= "Route::post('/" . str_plural(strtolower(camel_case($this->meta['table']))) . "/edit/{id}', '" . ucwords(str_singular(camel_case($this->meta['table'])))  . "Controller@update');\n";
        $contents .= "Route::get('/" . str_plural(strtolower(camel_case($this->meta['table']))) . "/delete/{id}', '" . ucwords(str_singular(camel_case($this->meta['table'])))  . "Controller@destroy');\n\n";


        $bytesWritten = \File::append($routes, $contents);
        if ($bytesWritten === false)
        {
            $this->info('Hmm the routes file couldnt be written to');
            return true;
        }

        $this->info('Routes successfully created');
        return false;
    }


    /**
     * Generate a fully fleshed out controller, if the user wishes.
     */
    public function buildModel()
    {
        $modelPath = $this->getModelPath($this->getModelName());
        if(!$this->files->exists($modelPath)) {
            $this->call('make:model', [
                'name' => $this->getModelName()
            ]);

            return true;
        }

        $this->info('It appears the Model already exists');
        return false;
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
            ['views', null, InputOption::VALUE_OPTIONAL, 'Want views for this table?', true],
        ];
    }
}
