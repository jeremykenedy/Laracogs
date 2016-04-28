<?php

namespace Yab\Laracogs\Generators;

use Exception;
use Illuminate\Filesystem\Filesystem;

/**
 * Generate the CRUD
 */
class CrudGenerator
{
    protected $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
    }

    /**
     * Create the controller
     * @param  array $config
     * @return bool
     */
    public function createController($config)
    {
        $request = file_get_contents($config['template_source'].'/Controller.txt');

        foreach ($config as $key => $value) {
            $request = str_replace($key, $value, $request);
        }

        $request = file_put_contents($config['_path_controller_'].'/'.$config['_camel_case_'].'Controller.php', $request);

        return $request;
    }

    /**
     * Create the repository
     * @param  array $config
     * @return bool
     */
    public function createRepository($config)
    {
        if (! is_dir($config['_path_repository_'])) mkdir($config['_path_repository_']);
        if (! is_dir($config['_path_model_'])) mkdir($config['_path_model_']);

        $repo = file_get_contents($config['template_source'].'/Repository/Repository.txt');
        $model = file_get_contents($config['template_source'].'/Repository/Model.txt');

        if (! is_null($config['schema'])) {
            $model = str_replace('// _camel_case_ table data', $this->prepareTableDefinition($config['schema']), $model);
        }

        foreach ($config as $key => $value) {
            $repo = str_replace($key, $value, $repo);
            $model = str_replace($key, $value, $model);
        }

        $repository = file_put_contents($config['_path_repository_'].'/'.$config['_camel_case_'].'Repository.php', $repo);
        $model = file_put_contents($config['_path_model_'].'/'.$config['_camel_case_'].'.php', $model);

        return ($repository && $model);
    }

    /**
     * Create the request
     * @param  array $config
     * @return bool
     */
    public function createRequest($config)
    {
        if (! is_dir($config['_path_request_'])) mkdir($config['_path_request_']);

        $request = file_get_contents($config['template_source'].'/Request.txt');

        foreach ($config as $key => $value) {
            $request = str_replace($key, $value, $request);
        }

        $request = file_put_contents($config['_path_request_'].'/'.$config['_camel_case_'].'Request.php', $request);

        return $request;
    }

    /**
     * Create the service
     * @param  array $config
     * @return bool
     */
    public function createService($config)
    {
        if (! is_dir($config['_path_service_'])) mkdir($config['_path_service_']);

        $request = file_get_contents($config['template_source'].'/Service.txt');

        foreach ($config as $key => $value) {
            $request = str_replace($key, $value, $request);
        }

        $request = file_put_contents($config['_path_service_'].'/'.$config['_camel_case_'].'Service.php', $request);

        return $request;
    }

    /**
     * Create the routes
     * @param  array $config
     * @return bool
     */
    public function createRoutes($config, $appendRoutes = true)
    {
        if ($appendRoutes) {
            $routesMaster = app_path('Http/routes.php');
        } else {
            $routesMaster = $config['_path_routes_'];
        }

        file_put_contents($routesMaster, $config['routes_prefix'], FILE_APPEND);

        $routes = file_get_contents($config['template_source'].'/Routes.txt');

        foreach ($config as $key => $value) {
            $routes = str_replace($key, $value, $routes);
        }

        file_put_contents($routesMaster, $routes, FILE_APPEND);

        return file_put_contents($routesMaster, $config['routes_suffix'], FILE_APPEND);
    }

    /**
     * Append to the factory
     * @param  array $config
     * @return bool
     */
    public function createFactory($config)
    {
        $factory = file_get_contents($config['template_source'].'/Factory.txt');

        if (! is_null($config['schema'])) {
            $factory = str_replace('// _camel_case_ table data', $this->prepareTableExample($config['schema']), $factory);
        }

        $factoryMaster = base_path('database/factories/ModelFactory.php');

        foreach ($config as $key => $value) {
            $factory = str_replace($key, $value, $factory);
        }

        return file_put_contents($factoryMaster, $factory, FILE_APPEND);
    }

    /**
     * Create the facade
     * @param  array $config
     * @return bool
     */
    public function createFacade($config)
    {
        if (! is_dir($config['_path_facade_'])) mkdir($config['_path_facade_']);

        $facade = file_get_contents($config['template_source'].'/Facade.txt');

        foreach ($config as $key => $value) {
            $facade = str_replace($key, $value, $facade);
        }

        $facade = file_put_contents($config['_path_facade_'].'/'.$config['_camel_case_'].'.php', $facade);

        return $facade;
    }

    /**
     * Create the tests
     * @param  array $config
     * @return bool
     */
    public function createTests($config)
    {
        $integrationTest = file_get_contents($config['template_source'].'/Tests/IntegrationTest.txt');
        $repositoryTest = file_get_contents($config['template_source'].'/Tests/RepositoryTest.txt');
        $serviceTest = file_get_contents($config['template_source'].'/Tests/ServiceTest.txt');

        if (! is_null($config['schema'])) {
            $integrationTest = str_replace('// _camel_case_ table data', $this->prepareTableExample($config['schema']), $integrationTest);
            $repositoryTest = str_replace('// _camel_case_ table data', $this->prepareTableExample($config['schema']), $repositoryTest);
            $serviceTest = str_replace('// _camel_case_ table data', $this->prepareTableExample($config['schema']), $serviceTest);
        }

        foreach ($config as $key => $value) {
            $integrationTest = str_replace($key, $value, $integrationTest);
            $repositoryTest = str_replace($key, $value, $repositoryTest);
            $serviceTest = str_replace($key, $value, $serviceTest);
        }

        $integrationTest = file_put_contents($config['_path_tests_'].'/'.$config['_camel_case_'].'IntegrationTest.php', $integrationTest);
        $repositoryTest = file_put_contents($config['_path_tests_'].'/'.$config['_camel_case_'].'RepositoryTest.php', $repositoryTest);
        $serviceTest = file_put_contents($config['_path_tests_'].'/'.$config['_camel_case_'].'ServiceTest.php', $serviceTest);

        return ($integrationTest && $repositoryTest && $serviceTest);
    }

    /**
     * Create the views
     * @param  array $config
     * @return bool
     */
    public function createViews($config)
    {
        if (! is_dir($config['_path_views_'].'/'.$config['_lower_casePlural_'])) mkdir($config['_path_views_'].'/'.$config['_lower_casePlural_']);

        $viewTemplates = 'Views';

        if ($config['bootstrap']) {
            $viewTemplates = 'BootstrapViews';
        }

        if ($config['semantic']) {
            $viewTemplates = 'SemanticViews';
        }

        foreach (glob($config['template_source'].'/'.$viewTemplates.'/*') as $file) {
            $createdView = file_get_contents($file);
            $basename = str_replace('txt', 'php', basename($file));
            foreach ($config as $key => $value) {
                $createdView = str_replace($key, $value, $createdView);
            }
            $createdView = file_put_contents($config['_path_views_'].'/'.$config['_lower_casePlural_'].'/'.$basename, $createdView);
        }

        return ($createdView);
    }

    /**
     * Create the Api
     * @param  array $config
     * @return bool
     */
    public function createApi($config, $appendRoutes = true)
    {
        if ($appendRoutes) {
            $routesMaster = app_path('Http/api-routes.php');
        } else {
            $routesMaster = $config['_path_api_routes_'];
        }

        file_put_contents($routesMaster, $config['routes_prefix'], FILE_APPEND);

        $routes = file_get_contents($config['template_source'].'/ApiRoutes.txt');

        foreach ($config as $key => $value) {
            $routes = str_replace($key, $value, $routes);
        }

        file_put_contents($routesMaster, $routes, FILE_APPEND);

        $routeBuild = file_put_contents($routesMaster, $config['routes_suffix'], FILE_APPEND);

        $request = file_get_contents($config['template_source'].'/ApiController.txt');

        foreach ($config as $key => $value) {
            $request = str_replace($key, $value, $request);
        }

        $request = file_put_contents($config['_path_api_controller_'].'/'.$config['_camel_case_'].'Controller.php', $request);

        return $request;
    }

    /**
     * Prepare a string of the table
     * @param  string $table
     * @return string
     */
    private function prepareTableDefinition($table)
    {
        $tableDefintion = '';

        foreach (explode(',', $table) as $column) {
            $columnDefinition = explode(':', $column);
            $tableDefintion .= "\t\t'$columnDefinition[0]',\n";
        }

        return $tableDefintion;
    }

    /**
     * Prepare a table array example
     * @param  string $table
     * @return string
     */
    private function prepareTableExample($table)
    {
        $tableExample = '';

        foreach (explode(',', $table) as $key => $column) {
            $columnDefinition = explode(':', $column);
            $example = $this->createExampleByType($columnDefinition[1]);
              if ($key === 0) {
                    $tableExample .= "'$columnDefinition[0]' => '$example',\n";
                } else {
                    $tableExample .= "\t\t'$columnDefinition[0]' => '$example',\n";
                }
        }

        return $tableExample;
    }

    /**
     * Create an example by type for table definitions
     * @param  string  $type
     * @return mixed
     */
    private function createExampleByType($type)
    {
        $faker = \Faker\Factory::create();

        switch ($type) {
            case 'increments':      return 1;
            case 'string':          return $faker->word;
            case 'boolean':         return 1;
            case 'text':            return $faker->sentence;
            case 'datetime':        return $faker->dateTime()->format('Y-m-d h:i:s');
            case 'date':            return $faker->date()->format('Y-m-d');
            case 'time':            return $faker->time()->format('h:i:s');
            case 'float':           return 1.1;
            case 'decimal':         return 1.1;
            case 'integer':         return 1;
            case 'bigint':          return 1;
            case 'smallint':        return 1;
            case 'tinyint':         return 1;

            default:                return 1;
        }
    }

}
