<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\LoginNeedsVerfication;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function submit(Request $request)
    {
        // Validierung
        $request->validate([
            'phone' => 'required|numeric|min:10',
    ]);
        // Nutzer erstellen oder abrufen
        $user = User::firstOrCreate([
            'phone' => $request->phone,
        ]);

        if (!$user) {
            return response()->json(['message' => 'Could not process a user with that pohne numer.'],401);
        }

        
        $user->notify(new LoginNeedsVerfication());


        return response()->json(['message' =>'Text message notification sent.']);
    }

    public function verify( Request $request)
    {
        // validate the incoming request
        $request->validate([
            'phone' => 'required|numeric|min:10',
            'login_code' => 'required|numeric|between:111111,999999'
        ]);
        
        // find the user
        $user = User::where('phone', $request->phone) 
            ->where('login_code', $request->login_code)
            ->first();

        // is the code provider the same and save?
        //if so, return back an auth token
        if ($user){
            $user->update([
                'login_code' => null
            ]);
            
            return $user->createToken($request->login_code)->plainTextToken;
        }

        // if not, return back a massage
        return response()->json(['message' => 'Invalid verification code'],401);
    }
}
