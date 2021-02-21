<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trader extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'sharing' => 'boolean',
        'last_unshared_at' => 'datetime'
    ];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
