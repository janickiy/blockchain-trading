<?php

namespace App\Http\Controllers\Api\V1;

use App\Bitclout\Facades\Bitclout;
use App\Events\OperationCreated;
use App\Events\OperationUpdated;
use App\Models\Operation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOperationRequest;

class OperationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accountIds = auth()->user()->accounts()->get()->pluck('id');

        return Operation::with('account')
            ->whereIn('from_account_id', $accountIds)
            ->latest()
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOperationRequest $request)
    {
        $this->authorize('store', Operation::class);

        $profile = Bitclout::profile($request->target_username);

        $operation = new Operation($request->validated());
        $operation->target_public_key = $profile['PublicKeyBase58Check'];
        $operation->save();

        event(new OperationCreated($operation));

        return $operation;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Operation  $operation
     * @return \Illuminate\Http\Response
     */
    public function show(Operation $operation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Operation  $operation
     * @return \Illuminate\Http\Response
     */
    public function edit(Operation $operation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Operation  $operation
     * @return \Illuminate\Http\Response
     */
    public function update(StoreOperationRequest $request, Operation $operation)
    {
        $this->authorize('update', $operation);

        $profile = Bitclout::profile($request->target_username);

        $data = $request->validated();
        $data['target_public_key'] = $profile['PublicKeyBase58Check'];

        $operation->update($data);

        event(new OperationUpdated($operation));

        return $operation;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Operation  $operation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Operation $operation)
    {
        $this->authorize('destroy', $operation);

        $operation->delete();
        return response()->noContent();
    }
}
