<?php

namespace App\Modules\Admin\Http\Controllers\Auth;

use App\Providers\RouteServiceProvider;
use Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class InvestorLoginController extends \App\Http\Controllers\Auth\LoginController
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->redirectTo = route('asset.myassets');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('admin::auth.investor_login');
    }

    /**
     * @return mixed
     */
    protected function guard()
    {
        return Auth::guard('investor');
    }

    /**
     * Validate the user login request.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     *
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('investor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
