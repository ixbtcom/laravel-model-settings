<?php

namespace Glorand\Model\Settings\Tests;

use CreateModelSettingsTable;
use Glorand\Model\Settings\ModelSettingsServiceProvider;
use Glorand\Model\Settings\Tests\Models\UsersWithTable;
use Glorand\Model\Settings\Tests\Models\UserWithField;
use Glorand\Model\Settings\Tests\Models\UserWithRedis;
use Glorand\Model\Settings\Tests\Models\UserWithTextField;
use Glorand\Model\Settings\Tests\Models\WrongUser;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunaweb\RedisMock\Providers\RedisMockServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    private array $originalMigrationFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['files']->ensureDirectoryExists(database_path('migrations'));
        $this->originalMigrationFiles = $this->app['files']->glob(database_path('migrations/*.php'));
        $this->setUpDatabase();
        $this->checkRequirements();
    }

    protected function tearDown(): void
    {
        $generatedMigrations = array_diff(
            $this->app['files']->glob(database_path('migrations/*.php')),
            $this->originalMigrationFiles,
        );
        $this->app['files']->delete($generatedMigrations);

        parent::tearDown();
    }

    protected function checkRequirements()
    {
        //
    }

    protected function getPackageProviders($app)
    {
        return [
            ModelSettingsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('auth.providers.users.model', UserWithField::class);
        $app['config']->set('database.redis.client', 'mock');
        $app['config']->set('database.default', 'testing');
        $app->register(RedisMockServiceProvider::class);
    }

    protected function setUpDatabase()
    {
        $this->createSettingsTable();

        $this->createTables('users', 'users_with_table', 'users_with_field', 'users_with_text_field', 'wrong_users');
        $this->seedModels(UserWithField::class, UserWithTextField::class, UsersWithTable::class, WrongUser::class, UserWithRedis::class);
    }

    protected function createSettingsTable()
    {
        include_once __DIR__.'/migrations/create_model_settings_table.php';

        (new CreateModelSettingsTable)->up();
    }

    protected function createTables(...$tableNames)
    {
        collect($tableNames)->each(function (string $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->timestamps();

                if ($tableName === 'users_with_field') {
                    $table->json('settings')->nullable();
                }

                if ($tableName === 'users_with_text_field') {
                    $table->text('settings')->nullable();
                }
            });
        });
    }

    protected function seedModels(...$modelClasses)
    {
        collect($modelClasses)->each(function (string $modelClass) {
            foreach (range(1, 2) as $index) {
                $modelClass::create(['name' => "name {$index}"]);
            }
        });
    }

    /**
     * @return Model|HasSettings
     */
    protected function getModelByType(string $type): Model
    {
        switch ($type) {
            case 'table':
                $model = UsersWithTable::first();
                break;
            case 'text_field':
                $model = UserWithTextField::first();
                break;
            case 'redis':
                $model = UserWithRedis::first();
                break;
            default:
                $model = UserWithField::first();
                break;
        }

        return $model;
    }
}
