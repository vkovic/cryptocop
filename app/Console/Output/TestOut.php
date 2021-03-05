<?php

namespace App\Console\Output;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

class TestOut
{
    use InteractsWithIO;

    /**
     * The output interface implementation.
     *
     * @var \Illuminate\Console\OutputStyle
     */
    protected $output;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->output = app()->make(ConsoleOutput::class);

        //$this->output = app()->make(OutputStyle::class);

    }

    public function render()
    {
        //dd($this->output->info('a'));
        $this->info('info');
        $this->warn('warn');
        //$this->alert('Alert');

        $this->table(['a', 'b'], [['1', 2]]);

        //$this->output->newLine();
    }


}
