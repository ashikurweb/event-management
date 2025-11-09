<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CustomRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:run {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run fresh migrations and seed the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting database refresh...');
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
        $this->info('✨ Database refresh completed!');
        
        return Command::SUCCESS;
    }
}
