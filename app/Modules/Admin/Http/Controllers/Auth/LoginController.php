<?php

namespace App\Modules\Admin\Http\Controllers\Auth;

use App\Providers\RouteServiceProvider;
use Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class LoginController extends \App\Http\Controllers\Auth\LoginController
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
//        dd(route('asset.index'));
        $this->redirectTo = route('admin.user.index');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('admin::auth.login');
    }

    /**
     * @return mixed
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     */
    protected function validateLogin(Request $request)
    {
        if (config('admin.recaptcha.modules.login.status')) {
            $request->validate([
                $this->username() => 'required|string',
                'password' => 'required|string',
                'g-recaptcha-response'=>'required|recaptcha'
            ],[
                'recaptcha'=>'Please ensure that you are a human!'
            ]);
        } else {
            $request->validate([
                $this->username() => 'required|string',
                'password' => 'required|string',
            ]);
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin');
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $user = Auth::guard('admin')->user();

        if ($user->hasRole('administrator')) {
            return redirect()->route('admin.user.index');
        }elseif ($user->getRolesNameAttribute() == 'Sale Manager'||
            $user->getRolesNameAttribute() == 'Sales Manager') {
            return redirect()->route('lead.index');
        } else {
            return redirect()->route('admin.investor.index');
        }
    }

}
