<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Sync extends Command
{
    use ColoredLinesOutput;

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
            $this->lineWhite('SYNC START: ' . now());

            try {
                $this->call('sync:trader');
                $this->call('sync:position');
            } catch (\Throwable $exception) {
                $this->error($exception->getMessage());

                sleep(120);

                continue;
            }

            $this->lineWhite('SYNC END: ' . now());
            $this->lineWhite('...');

            sleep(30);
        }

        return 0;
    }
}
