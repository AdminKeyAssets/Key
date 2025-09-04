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
        // Check if the investor exists and is not archived
        $credentials = $request->only($this->username(), 'password');
        
        // Add a custom check for archived status
        $investor = \App\Modules\Admin\Models\User\Investor::where($this->username(), $credentials[$this->username()])->first();
        
        // If investor is archived, prevent login
        if ($investor && $investor->is_archived) {
            $this->incrementLoginAttempts($request);
            return $this->sendFailedLoginResponse($request, 'This account has been archived. Please contact support.');
        }
        
        // Continue with normal login attempt
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
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendFailedLoginResponse(Request $request, $message = null)
    {
        $message = $message ?? trans('auth.failed');
        
        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                $this->username() => $message,
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
