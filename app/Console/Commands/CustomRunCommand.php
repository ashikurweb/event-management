<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CustomRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:run {name? : Optional name parameter} {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run fresh migrations, seed the database, and create storage link';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        
        if ($name) {
            $this->info("🔄 Running custom command with name: {$name}");
        } else {
            $this->info('🔄 Starting database refresh...');
        }
        $this->newLine();

        // Create storage link
        $this->info('🔗 Creating storage link...');
        $this->createStorageLink();
        $this->newLine();

        // Run migrate:fresh
        $this->info('📦 Running fresh migrations...');
        $freshCommand = $this->option('force') ? 'migrate:fresh --force' : 'migrate:fresh';
        
        $exitCode = Artisan::call($freshCommand, [], $this->getOutput());
        
        if ($exitCode !== 0) {
            $this->error('❌ Migration failed!');
            return Command::FAILURE;
        }
        
        $this->info('✅ Migrations completed successfully!');
        $this->newLine();

        // Run db:seed
        $this->info('🌱 Seeding database...');
        $seedCommand = $this->option('force') ? 'db:seed --force' : 'db:seed';
        
        $exitCode = Artisan::call($seedCommand, [], $this->getOutput());
        
        if ($exitCode !== 0) {
            $this->error('❌ Seeding failed!');
            return Command::FAILURE;
        }
        
        $this->info('✅ Database seeded successfully!');
        $this->newLine();
        $this->info('✨ All operations completed successfully!');
        
        return Command::SUCCESS;
    }

    /**
     * Create the storage link.
     */
    protected function createStorageLink(): void
    {
        $links = config('filesystems.links', []);
        
        foreach ($links as $link => $target) {
            try {
                // If link already exists, remove it first
                if (file_exists($link) || is_link($link)) {
                    if (is_link($link)) {
                        $this->info("🔗 Removing existing link [{$link}]...");
                        unlink($link);
                        $this->info("✅ Existing link removed.");
                    } elseif (is_dir($link)) {
                        $this->info("🗑️  Removing existing directory [{$link}]...");
                        File::deleteDirectory($link);
                        $this->info("✅ Existing directory removed.");
                    } elseif (is_file($link)) {
                        $this->info("🗑️  Removing existing file [{$link}]...");
                        File::delete($link);
                        $this->info("✅ Existing file removed.");
                    }
                }

                // Ensure the target directory exists
                if (!File::exists($target)) {
                    File::makeDirectory($target, 0755, true);
                    $this->info("📁 Created target directory [{$target}].");
                }

                // Create the symbolic link
                if (is_dir($target)) {
                    try {
                        // Use native symlink function
                        if (function_exists('symlink')) {
                            symlink($target, $link);
                            $this->info("✅ The [{$link}] link has been connected to [{$target}].");
                        } else {
                            $this->error("❌ symlink function is not available on this system.");
                        }
                    } catch (\Exception $e) {
                        $this->error("❌ Failed to create link: " . $e->getMessage());
                    }
                } else {
                    $this->error("❌ The target [{$target}] does not exist.");
                }
            } catch (\Exception $e) {
                $this->error("❌ Failed to process link: " . $e->getMessage());
            }
        }
    }
}
