<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\PublicEvent;

class TestPublicEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-public-event {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test broadcasting a public event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = $this->argument('message') ?? 'Test message from command line';
        
        PublicEvent::dispatch($message);
        
        $this->info("Public event dispatched with message: {$message}");
    }
}
