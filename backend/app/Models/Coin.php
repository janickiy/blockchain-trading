<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    use HasFactory;

    public function getKeyType()
    {
        return 'string';
    }

    protected $primaryKey = 'public_key';

    public $incrementing = false;

    protected $fillable = [
        'public_key',
        'username',
        'price',
        'prev_price',
        'change24h',
    ];
}
