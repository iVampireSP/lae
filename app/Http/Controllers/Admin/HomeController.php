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

        if ($request->filled('user_id')) {
            $transactions = $transactions->where('user_id', intval($request->input('user_id')));
        }

        if ($request->filled('module_id')) {
            $transactions = $transactions->where('module_id', intval($request->input('module_id')));
        }

        if ($request->filled('host_id')) {
            $transactions = $transactions->where('host_id', intval($request->input('host_id')));
        }

        if ($request->filled('payment')) {
            $transactions = $transactions->where('payment', $request->input('payment'));
        }

        $transactions = $transactions->latest()->paginate(50);

        return view('admin.transactions', compact('transactions'));
    }
}
