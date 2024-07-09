<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;



class SmsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('guest');
    }


    protected function smsResend()
    {
        $dataCode = mt_rand(1000, 9999);
        $user = Auth::user();

            $client = new Client();
            $res = $client->get('https://www.bulksmsnigeria.com/api/v1/sms/create?api_token=soN7q0CGFLwgsm8QUtORPDFGRDPUrO4hGveTC2rRPP2KTUApdLuDDRsk7rRH&from=Limo&to='.$user->phone.'&body='.$dataCode.'&dnd=2');
            $clientStatusCode = $res->getStatusCode(); // 200
            $clientResponse = $res->getBody();




            $user->sms_code = $dataCode;

            $user->save();

            flash('Your verification code has been sent!');
            return view('auth.verify');
    }

    protected function smsVerify(Request $request)
    {

            $user = Auth::user();

            if($user->sms_code == $request->input('sms')) {

                $user->email_verified_at = date('Y-m-d H:i:s');
                $user->save();

                flash('Your account has been verified!');
                return view('home');
            } else {
                flash('Your account was not verified, ensure your code is correct!');
                return view('auth.verify');
            }



    }
}
