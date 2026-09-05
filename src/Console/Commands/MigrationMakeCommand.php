<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MigrationMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-migration
                            {module : The name of the module}
                            {name : The name of the migration (e.g. create_orders_table)}';

    protected $description = 'Create a new database migration file inside a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $migrationName = Str::snake(trim($rawName));

        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$migrationName}.php";
        $filePath = $module->getPath("database/migrations/{$fileName}");

        $tableName = 'table_name';
        $isCreate = false;

        if (preg_match('/^create_(.+)_table$/', $migrationName, $matches)) {
            $tableName = $matches[1];
            $isCreate = true;
        }

        if ($isCreate) {
            $content = <<<PHP
                <?php

                declare(strict_types=1);

                use Illuminate\\Database\\Migrations\\Migration;
                use Illuminate\\Database\\Schema\\Blueprint;
                use Illuminate\\Support\\Facades\\Schema;

                return new class extends Migration
                {
                    public function up(): void
                    {
                        Schema::create('{$tableName}', function (Blueprint \$table) {
                            \$table->id();
                            \$table->timestamps();
                        });
                    }

                    public function down(): void
                    {
                        Schema::dropIfExists('{$tableName}');
                    }
                };

                PHP;
        } else {
            $content = <<<PHP
                <?php

                declare(strict_types=1);

                use Illuminate\\Database\\Migrations\\Migration;
                use Illuminate\\Database\\Schema\\Blueprint;
                use Illuminate\\Support\\Facades\\Schema;

                return new class extends Migration
                {
                    public function up(): void
                    {
                        Schema::table('{$tableName}', function (Blueprint \$table) {
                            //
                        });
                    }

                    public function down(): void
                    {
                        Schema::table('{$tableName}', function (Blueprint \$table) {
                            //
                        });
                    }
                };

                PHP;
        }

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Migration [{$fileName}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
