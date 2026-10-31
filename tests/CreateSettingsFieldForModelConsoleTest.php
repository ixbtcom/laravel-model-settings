<?php

namespace Glorand\Model\Settings\Tests;

final class CreateSettingsFieldForModelConsoleTest extends TestCase
{
    private $table = 'users_with_field';

    private $fieldName = 'custom_settings_field';

    private $alreadyExistsFieldName = 'settings';

    public function test_empty_table(): void
    {
        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', '')
            ->assertExitCode(1);
    }

    public function test_missing_table(): void
    {
        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', $this->table.'_wrong')
            ->assertExitCode(2);
    }

    public function test_already_exists_field(): void
    {
        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', $this->table)
            ->expectsQuestion('What is the name of the settings field name?', $this->alreadyExistsFieldName)
            ->assertExitCode(3);
    }

    public function test_create_migration_file(): void
    {
        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', $this->table)
            ->expectsQuestion('What is the name of the settings field name?', $this->fieldName)
            ->assertExitCode(0);
    }
}
