<?php

namespace App\Http\Middleware;

use Closure;
use App\Device;
use App\User;

class LogoutMiddleware
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
        $input = $request->all();
        $userOne = $request->header('user_one');
        if($userOne != null){
            
            $userDevice = Device::where('username',$userOne)->first();
            if ($userDevice != null) {
            
            } else {
                
                return response()->json(['error' => 'logout'], 401);
            }
            
        } 
        
        
        return $next($request);
    }
}
