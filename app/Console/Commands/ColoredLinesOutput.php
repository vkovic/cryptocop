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

    protected function textMagenta($text)
    {
        return '<fg=magenta>' . $text . '</>';
    }

    protected function textRed($text)
    {
        return '<fg=red>' . $text . '</>';
    }

    protected function textGreen($text)
    {
        return '<fg=green>' . $text . '</>';
    }

    protected function textYellow($text)
    {
        return '<fg=yellow>' . $text . '</>';
    }

    protected function textWhite($text)
    {
        return '<fg=white>' . $text . '</>';
    }

    protected function textDefault($text)
    {
        return '<fg=default>' . $text . '</>';
    }
}
