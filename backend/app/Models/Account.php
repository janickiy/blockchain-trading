<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'mnemonic',
        'password',
        'public_key',
        'username',
        'balance',
        'user_id',
    ];

    public function getKeyType()
    {
        return 'string';
    }

    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    public function coins()
    {
        return $this->belongsToMany(Coin::class, 'coin_account', 'account_id', 'public_key');
    }
}
