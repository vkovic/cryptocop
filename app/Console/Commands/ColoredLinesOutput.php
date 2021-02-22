<?php

namespace App\Console\Commands;

trait ColoredLinesOutput
{
    protected function lineRed($message)
    {
        $this->line('<fg=red>' . $message . '</>');
    }

    protected function lineMagenta($message)
    {
        $this->line('<fg=magenta>' . $message . '</>');
    }

    protected function lineYellow($message)
    {
        $this->line('<fg=yellow>' . $message . '</>');
    }

    protected function lineGreen($message)
    {
        $this->line('<fg=green>' . $message . '</>');
    }

    protected function lineWhite($message)
    {
        $this->line('<fg=white>' . $message . '</>');
    }
}
