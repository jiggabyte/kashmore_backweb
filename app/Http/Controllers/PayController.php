<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PayController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {


       //check if request was made with the right data
       if(!$_SERVER['REQUEST_METHOD'] == 'POST' || !isset($_POST['reference'])){
          	die("Transaction reference not found");
       }
       //set reference to a variable @ref
       $reference = $_POST['reference'];
       //The parameter after verify/ is the transaction reference to be verified
       $url = 'https://api.paystack.co/transaction/verify/'.$reference;
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt(
                	$ch, CURLOPT_HTTPHEADER, [
                   "Authorization: Bearer ".env('PAYSTACK_URL')
                    ]
       );
       //send request
       $requester = curl_exec($ch);
       //close connection
       curl_close($ch);
       //declare an array that will contain the result
       $result = array();
       if ($requester) {
	   $result = json_decode($requester, true);
       }
       if (array_key_exists('data', $result) && array_key_exists('status', $result['data']) && ($result['data']['status'] === 'success')) {


            //Perform necessary action

            echo "Payment is Successful!";
       }else{

        echo "Transaction was Unsuccessful!";

       }


    }
}
