<?php

namespace App\Console\Commands;

use Faker\Core\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Psy\Util\Str;
use function PHPUnit\Framework\fileExists;

class CrudGeneratorCommant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:generator {name : Class (singular), e.g User}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create CRUD operations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $this->controller($name);
        $this->model($name);
        $this->request($name);
        $strlower = strtolower($name);
        $data = <<<data
        use App\Http\Controllers\\{$name}Controller;

          Route::prefix('{$strlower}')->group(function () {
            Route::get('/', [{$name}Controller::class, 'index'])->name('admin.{$name}.index');
            Route::get('/create', [{$name}Controller::class, 'create'])->name('admin.{$name}.create');
            Route::post('/store', [{$name}Controller::class, 'store'])->name('admin.{$name}.store');
            Route::get('/edit/{{$name}}', [{$name}Controller::class, 'edit'])->name('admin.{$name}.edit');
            Route::put('/update/{{$name}}', [{$name}Controller::class, 'update'])->name('admin.{$name}.update');
            Route::delete('/destroy/{{$name}}', [{$name}Controller::class, 'destroy'])->name('admin.{$name}.destroy');
            Route::get('/status/{{$name}}', [{$name}Controller::class, 'status'])->name('admin.{$name}.status');
        });
data;

            \Illuminate\Support\Facades\File::append(base_path('routes/api.php'),$data);
        Artisan::call('make:migration create_'.strtolower(\Illuminate\Support\Str::plural($name)).'_table --create='.strtolower(\Illuminate\Support\Str::plural($name)));
        $this->info('crud was suu');
    }

    public function getStub($type){
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    public function model($name){
        $Template = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Model')
        );
        $name = ucwords($name);
        file_put_contents(app_path("/Models/{$name}.php"),$Template);
    }

    public function request($name){
        $Template = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Request')
        );
        if (!file_exists($path = app_path('/http/Requests'))){
            mkdir($path,0777,true);
        }
        file_put_contents(app_path("/Http/Requests/{$name}Request.php"),$Template);
    }

    public function controller($name){
        $Template = str_replace(
            [
              '{{modelName}}',
              '{{modelNamePluralLowerCase}}',
              '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(\Illuminate\Support\Str::plural($name)),
                strtolower($name),
            ],
            $this->getStub('Controller')
        );
        file_put_contents(app_path("/Http/Controllers/{$name}Controller.php"),$Template);

    }
};
