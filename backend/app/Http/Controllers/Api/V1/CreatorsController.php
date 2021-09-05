<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Exception;
use Illuminate\Http\Request;

class CreatorsController extends Controller
{
    public function index(string $id)
    {
        $account = Account::with(['coins' => function ($query) {
            $query->withPivot('spent', 'sold', 'balance');
        }])
            ->where('public_key', $id)
            ->first();

        return $account->coins;
    }
}
