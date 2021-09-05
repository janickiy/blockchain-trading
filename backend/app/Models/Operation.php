<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    public const OPERATION_ACTION_BUY = 1;
    public const OPERATION_ACTION_SELL = 0;

    public const OPERATION_CONDITION_ABT_PRICE = 0;  # ~ price
    public const OPERATION_CONDITION_EQ_PRICE = 1;  # = price
    public const OPERATION_CONDITION_GE_PRICE = 2;  # >= price
    public const OPERATION_CONDITION_LE_PRICE = 3;  # <= price
    public const OPERATION_CONDITION_GE_FRP = 4;  # >= frp
    public const OPERATION_CONDITION_LE_FRP = 5;  # <= frp

    public const OPERATION_STATUS_WAIT = 0;
    public const OPERATION_STATUS_IN_PROCESS = 1;
    public const OPERATION_STATUS_FAILED = 2;
    public const OPERATION_STATUS_SUCCESS = 3;

    public const OPERATION_ATTEMPTS = 3;

    protected $fillable = [
        'from_account_id',
        'action',
        'target_public_key',
        'target_username',
        'amount',
        'condition',
        'condition_payload',
        'status',
        'result',
        'is_cloner',
        'attempts',
    ];

    protected $casts = [
        'is_cloner' => 'boolean',
        'condition_payload' => 'array',
        'result' => 'array',
    ];

    public function getKeyType()
    {
        return 'string';
    }

    public function scopeInProccess($query)
    {
        return $query->where('status', self::OPERATION_STATUS_IN_PROCESS);
    }

    public function scopeWait($query)
    {
        return $query->where('status', self::OPERATION_STATUS_WAIT);
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'from_account_id', 'id');
    }
}
