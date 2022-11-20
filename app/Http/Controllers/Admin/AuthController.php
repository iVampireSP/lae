<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 *
 */
class AuthController extends Controller
{
    //

    /**
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        // if not authed

        if (!auth('admin')->check()) {
            return view('admin.login');
        } else {
            return redirect()->route('admin.index');
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        if (auth('admin')->attempt($request->only('email', 'password'), $request->has('remember'))) {
            return redirect()->route('admin.index');
        } else {
            return redirect()->route('admin.login')->with('error', 'Invalid credentials');
        }
    }

    /**
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        auth('admin')->logout();
        return redirect()->route('admin.login');
    }
}
