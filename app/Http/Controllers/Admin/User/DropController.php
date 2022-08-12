<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User\Drop;
use Illuminate\Http\Request;

class DropController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Drop $drop)
    {
        $drop = $drop->simplePaginate(10);
        return $this->success($drop);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'amount' => 'integer|required|min:1|max:1000',
        ]);

        $data = [
            'payment' => 'admin',
            'amount' => $request->amount,
            'status' => 1,
            'user_id' => $request->route('user'),
            'type' => 'add',
        ];

        $drop = Drop::create($data);

        return $this->success($drop);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User\Drop  $drop
     * @return \Illuminate\Http\Response
     */
    public function show(Drop $drop)
    {
        //
        $this->authorize('show', $drop);

        return $drop;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User\Drop  $drop
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Drop $drop)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User\Drop  $drop
     * @return \Illuminate\Http\Response
     */
    public function destroy(Drop $drop)
    {
        //
    }
}
