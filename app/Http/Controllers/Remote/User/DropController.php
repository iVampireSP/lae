<?php

namespace App\Http\Controllers\Remote\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DropController extends Controller
{
    public function destroy(Request $request, User $user) {
        $request->validate([
            'amount' => 'required|numeric|max:1000',
        ]);

        

        return $this->success();
    }
}
