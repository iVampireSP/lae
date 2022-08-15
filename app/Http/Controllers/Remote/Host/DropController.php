<?php

namespace App\Http\Controllers\Remote\Host;

use App\Http\Controllers\Controller;
use App\Models\User\Host;
use Illuminate\Http\Request;

class DropController extends Controller
{
    public function update(Request $request, Host $host) {
        $request->validate([
            'managed_price' => 'sometimes|numeric|max:1000|nullable',
        ]);

        $host->managed_price = $request->managed_price;
        $host->save();

        return $this->updated($host);
    }
}
