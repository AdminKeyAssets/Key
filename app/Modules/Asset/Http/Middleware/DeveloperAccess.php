<?php

namespace App\Modules\Asset\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class DeveloperAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if the user is authenticated with the developer guard
        if (Auth::guard('developer')->check()) {
            // Get the developer's name to match with assets
            $developerName = Auth::guard('developer')->user()->name;
            
            // Get the asset ID from the request
            $assetId = $request->route('id') ?? $request->route('asset_id') ?? null;
            
            // If no asset ID in route, continue without checking
            if (!$assetId) {
                return $next($request);
            }
            
            // Get the asset model from the route binding
            $asset = app(\App\Modules\Asset\Models\Asset::class)->find($assetId);
            
            // If the asset exists and its name matches the developer's name, allow access
            if ($asset && $asset->project_name === $developerName) {
                return $next($request);
            }
            
            // Access denied
            return redirect()->route('asset.myassets')->with('error', 'You do not have permission to access this asset.');
        }
        
        return $next($request);
    }
}
