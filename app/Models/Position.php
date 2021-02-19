<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::creating(function (Position $position) {
            if ($position->pnl === null
                || $position->roe === null
                || $position->size === null
                || $position->entry_price === null
            ) {
                return;
            }

            $position->invested = abs($position->pnl * 100 / $position->roe);
            $position->cost = abs($position->size * $position->entry_price);
            $position->leverage = abs($position->cost / $position->invested);
        });
    }
}
