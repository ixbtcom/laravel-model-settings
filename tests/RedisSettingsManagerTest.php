<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Tests\Models\UserWithRedis;
use Glorand\Model\Settings\Tests\Models\UserWithRedis as User;

final class RedisSettingsManagerTest extends TestCase
{
    /** @var UserWithRedis */
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    public function test_settings_persist_across_model_instances(): void
    {
        $settings = ['user' => ['name' => 'Redis user']];

        $this->model->settings()->apply($settings);

        $this->assertSame($settings, $this->model->fresh()->settings()->all());
        $this->model->settings()->clear();
        $this->assertSame([], $this->model->fresh()->settings()->all());
    }
}
