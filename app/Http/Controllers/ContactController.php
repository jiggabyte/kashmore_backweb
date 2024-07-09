<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;

class ContactController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('contact');
    }
    
    public function sendMail(Request $request){
        $input = $request->all();
        $senderMail = $input['mail'];
        $senderName = $input['name'];
        $senderMsg = $input['msg'];
        $senderFone = $input['fone'];
        
        
        try {
                    $data = array(
                        'sender_name' => $senderName,
                        'sender_mail' => $senderMail,
                        'sender_msg' => $senderMsg,
                        'sender_fone' => $senderFone,
                        
                    );
                    Mail::send('mail', $data, function ($message) {
                        $message->to(env("MAIL_FROM_ADDRESS"), env("APP_NAME"))->subject
                            ('Contact Mail from Betgames App.');
                        $message->from(env("MAIL_FROM_ADDRESS"), env("APP_NAME"));
                    });
                    
                    if(count(Mail::failures()) > 0){
                        // Your error message or whatever you want.
                         return response()->json(['successor' => 'failure'], 200);
                    } else {
                        return response()->json(['successor' => 'success'], 200);
                    }
                    
                    
                } catch (\Exception $e) {
 
                return response()->json(['successor' => $e->getMessage()], 500);
                }
    }
}
