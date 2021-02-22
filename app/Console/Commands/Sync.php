<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        while (true) {
            $this->alert('> SYNC STARTED: ' . now());

            try {
                $this->call('sync:trader');
                $this->call('sync:position');
            } catch (\Throwable $exception) {
                $this->error($exception->getMessage());

                sleep(120);

                continue;
            }

            $this->alert('< SYNC END: ' . now());

            sleep(120);
        }

        return 0;
    }
}
