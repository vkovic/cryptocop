<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Sync extends Command
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
        while (true) {
            $this->lineWhite('SYNC START #' . $this->syncCycle + 1 . ': ' . now());

            try {
                $this->call('sync:trader');
                $this->call('sync:position');
                $this->syncCycle++;
            } catch (\Throwable $exception) {
                $this->error($exception->getMessage());

                sleep(120);

                continue;
            }

            $this->lineWhite('SYNC END: ' . now());
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
        $positions1h = \DB::table('main_view')->whereNull('closed_at')->whereRaw('opened_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)');
        $positions6h = \DB::table('main_view')->whereNull('closed_at')->whereRaw('opened_at >= DATE_SUB(NOW(), INTERVAL 6 HOUR)');
        $positions24h = \DB::table('main_view')->whereNull('closed_at')->whereRaw('opened_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)');
        $allSymbols = \DB::table('main_view')->whereNull('closed_at')->distinct('symbol')->orderBy('symbol')->pluck('symbol')->toArray();

        $title = 'STATS | ' . now()->format('m-d H:i:s');
        $this->alert($title);

        //
        // Longs vs shorts
        //

        $this->lineYellow('LONGS vs SHORTS (1h / 6h / 24h)');
        $longs1h = (clone $positions1h)->where('size', '>', 0)->count();
        $longs6h = (clone $positions6h)->where('size', '>', 0)->count();
        $longs24h = (clone $positions24h)->where('size', '>', 0)->count();
        $this->lineGreen(sprintf('* LONGS: %s / %s / %s', $longs1h, $longs6h, $longs24h));

        $shorts1h = (clone $positions1h)->where('size', '<', 0)->count();
        $shorts6h = (clone $positions6h)->where('size', '<', 0)->count();
        $shorts24h = (clone $positions24h)->where('size', '<', 0)->count();
        $this->lineRed(sprintf('* SHORTS: %s / %s / %s', $shorts1h, $shorts6h, $shorts24h));

        $this->newLine();

        //
        // Top symbols
        //

        $topPositions1h = $this->getTopPositionsForQuery(clone $positions1h, 0);
        $topPositions6h = $this->getTopPositionsForQuery(clone $positions6h, 2);
        $topPositions24h = $this->getTopPositionsForQuery(clone $positions24h, 6);

        $this->lineYellow('TOP SYMBOLS STATS (1h / 6h / 24h)');

        foreach ($allSymbols as $symbol) {
            $coin = str_replace('USDT', '', $symbol);

            $long1h = isset($topPositions1h[$coin]) ? $topPositions1h[$coin]['long'] : 0;
            $long6h = isset($topPositions6h[$coin]) ? $topPositions6h[$coin]['long'] : 0;
            $long24h = isset($topPositions24h[$coin]) ? $topPositions24h[$coin]['long'] : 0;

            $short1h = isset($topPositions1h[$coin]) ? $topPositions1h[$coin]['short'] : 0;
            $short6h = isset($topPositions6h[$coin]) ? $topPositions6h[$coin]['short'] : 0;
            $short24h = isset($topPositions24h[$coin]) ? $topPositions24h[$coin]['short'] : 0;

            if (($long1h + $long6h + $long24h + $short1h + $short6h + $short24h) < 4) {
                continue;
            }

            $this->lineMagenta(sprintf('* %s', $coin));
            $this->lineGreen(sprintf('  - LONGS: %s / %s / %s', $long1h, $long6h, $long24h));
            $this->lineRed(sprintf('  - SHORTS: %s / %s / %s', $short1h, $short6h, $short24h));
        }

        $this->newLine();
        $this->lineYellow('**********************************');
    }

    protected function getTopPositionsForQuery($query)
    {
        $data = [];
        foreach ($query->get() as $position) {
            $coin = str_replace('USDT', '', $position->symbol);

            if (!isset($data[$coin])) {
                $data[$coin]['short'] = 0;
                $data[$coin]['long'] = 0;
            }

            $positionType = $position->size < 0 ? 'short' : 'long';

            $data[$coin][$positionType] = $data[$coin][$positionType] + 1;
        }

        return $data;
    }
}
