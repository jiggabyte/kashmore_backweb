<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use DateTime;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;



class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['max:255', 'unique:users'],
            //'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'numeric', 'min:11', 'unique:users'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $dataCode = mt_rand(1000, 9999);
        if(empty($data['email'])){
            $data['email'] = $data['phone'].env('PHONE_NUMBER_MAILER');

            $client = new Client();
            $res = $client->get('https://www.bulksmsnigeria.com/api/v1/sms/create?api_token=soN7q0CGFLwgsm8QUtORPDFGRDPUrO4hGveTC2rRPP2KTUApdLuDDRsk7rRH&from=Limo&to='.$data['phone'].'&body='.$dataCode.'&dnd=2');
            $clientStatusCode = $res->getStatusCode(); // 200
            $clientResponse = $res->getBody();
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'sms_code' => $dataCode,
        ]);
    }

    protected function smsResend()
    {
        $dataCode = mt_rand(1000, 9999);

            $client = new Client();
            $res = $client->get('https://www.bulksmsnigeria.com/api/v1/sms/create?api_token=soN7q0CGFLwgsm8QUtORPDFGRDPUrO4hGveTC2rRPP2KTUApdLuDDRsk7rRH&from=Limo&to='.$data['phone'].'&body='.$dataCode.'&dnd=2');
            $clientStatusCode = $res->getStatusCode(); // 200
            $clientResponse = $res->getBody();


            $user = Auth::user();

            $user->sms_code = $dataCode;

            $user->save();

            Flash::message('Your verification code has been sent!');
            return Redirect::to('/');
    }

    protected function smsVerify()
    {

            $user = Auth::user();

            if($user->sms_code == Request::input('sms')){


                $user->email_verified_at = date('Y-m-d H:i:s');
                $user->save();

                Flash::message('Your account has been verified!');
                return Redirect::to('/');
            } else {
                Flash::message('Your account was not verified, ensure your code is correct!');
                return Redirect::to('/');
            }



    }
}
