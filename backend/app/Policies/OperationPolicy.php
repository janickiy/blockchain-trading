<?php

namespace App\Policies;

use App\Models\Operation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OperationPolicy
{
    use HandlesAuthorization;

    public function store(User $user)
    {
        return $this->checkAccountId($user, app('request')->get('from_account_id'));
    }

    public function update(User $user, Operation $operation)
    {
        return $this->checkAccountId($user, $operation->from_account_id);
    }
    
    public function destroy(User $user, Operation $operation)
    {
        return $this->checkAccountId($user, $operation->from_account_id);
    }

    private function checkAccountId($user, $accountId)
    {
        return $user
            ->accounts()
            ->where('id', $accountId)
            ->first();
    }
}
