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
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Check if a developer with these credentials exists
        $developerCredentials = [
            'username' => $request->input($this->username()),
            'password' => $request->input('password')
        ];
        
        if (Auth::guard('developer')->attempt($developerCredentials, $request->filled('remember'))) {
            return $this->sendLoginResponse($request);
        }

        // If not a developer, try investor login as normal
        return $this->attemptInvestorLogin($request);
    }

    /**
     * Attempt to log the investor in.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptInvestorLogin(Request $request)
    {
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
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
        // Logout from both investor and developer guards
        if (Auth::guard('investor')->check()) {
            Auth::guard('investor')->logout();
        }
        
        if (Auth::guard('developer')->check()) {
            Auth::guard('developer')->logout();
        }
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
