<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Transaction;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $modules = Module::paginate(10);

        return view('admin.index', compact('modules'));
    }

    public function transactions(Request $request)
    {
        $transactions = new Transaction();

        // query
        if ($request->has('user_id')) {
            $transactions = $transactions->where('user_id', intval($request->input('user_id')));
        }

        if ($request->has('module_id')) {
            $transactions = $transactions->where('module_id', intval($request->input('module_id')));
        }

        if ($request->has('host_id')) {
            $transactions = $transactions->where('host_id', intval($request->input('host_id')));
        }

        if ($request->has('payment')) {
            $transactions = $transactions->where('payment', $request->input('payment'));
        }


        $transactions = $transactions->latest()->paginate(50);

        return view('admin.transactions', compact('transactions'));
    }
}
