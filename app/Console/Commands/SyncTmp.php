<?php

namespace App\Console\Commands;

use App\Models\Trader;
use Illuminate\Console\Command;
use noximo\PHPColoredAsciiLinechart\Colorizers\AsciiColorizer;
use noximo\PHPColoredAsciiLinechart\Linechart;
use noximo\PHPColoredAsciiLinechart\Settings;

class SyncTmp extends Command
{
    use ColoredLinesOutput;

    protected $syncCycle = 0;

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
        $i = 0;
        while (true) {

            if ($i > 10) {
                break;
            }

            Trader::factory()->create();



            $i++;

            sleep(2);
        }

        die;
        while (true) {
            $this->lineWhite('SYNC START #' . $this->syncCycle + 1 . ': ' . now());

            try {
                // Sync traders
                $this->call('sync:trader');
                usleep(10000); // 0.01 sec

                // Sync positions
                $this->call('sync:position');
                $this->syncCycle++;
            } catch (\Throwable $exception) {
                $this->error($exception->getMessage());

                sleep(120);

                continue;
            }

            $this->lineWhite('SYNC SYNC END #' . $this->syncCycle + 1 . ': ' . now());
            $this->lineWhite('...');

            if ($this->syncCycle === 1 || $this->syncCycle % 3 === 0) {
                $this->displayStats();
            }

            sleep(30);
        }

        return 0;
    }

    protected function displayStats()
    {
        $title = 'STATS | ' . now()->format('m-d H:i:s');
        $this->alert($title);

        $this->displayTopSymbolStats();

        $this->newLine();
        $this->lineYellow('**********************************');
    }

    protected function displayTopSymbolStats()
    {
        $positions = \DB::table('position_stats')->where('total', '>', 4)->get();

        $tableData = [];
        $tableHeaders = ['SYMBOL', 'POSITIONS', 'SIZE', 'MONEY IN (lev)', 'AVG LEV', 'SCORE'];
        foreach ($positions as $position) {
            $coin = str_replace('USDT', '', $position->symbol);

            $tableData[] = [
                $this->textGreen($coin . ' longs'),
                $this->textGreen(number_format($position->longs, 0, ',', '.')),
                $this->textGreen($position->size_longs),
                $this->textGreen(number_format($position->cost_longs, 0, ',', '.')),
                $this->textGreen($position->avg_lev_longs),
                $this->textGreen($position->trader_score_longs),
            ];

            $tableData[] = [
                $this->textRed($coin . ' shorts'),
                $this->textRed(number_format($position->shorts, 0, ',', '.')),
                $this->textRed($position->size_shorts),
                $this->textRed(number_format($position->cost_shorts, 0, ',', '.')),
                $this->textRed($position->avg_lev_shorts),
                $this->textRed($position->trader_score_shorts),
            ];

            if ($position !== $positions[count($positions) - 1]) {
                $tableData[] = ['', '', '', ''];
            }
        }

        $this->lineYellow('TOP SYMBOLS STATS');
        $this->table($tableHeaders, $tableData);
    }
}
