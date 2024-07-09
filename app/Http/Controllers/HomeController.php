<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bet;
use App\Models\Device;
use App\Models\GameOn;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\FreeGame;
use App\Models\Referrer;

class HomeController extends Controller
{

    public $successStatus = 200;
    
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
        return view('pay');
    }

    public function privacy()
    {
        return view('privacy');
    }


    public function withdraw()
    {
        return view('take');
    }

    public function withdrawal(Request $request)
    {
        $input = $request->all();
        $uuid = $input['uuid'];

        $withdraw_data = Wallet::where('owner',$uuid)->where('typer','win-loss-draw')->where('notes','withdraw-pending')->orWhere('notes','withdraw-block')->get();

        return view('taker',compact('withdraw_data'));
    }

    public function withdrawRequest(Request $request)
    {
        try {

            $input = $request->all();
            $uuid = $input['uuid'];
            $amount = $input['amount'];
            $acdigits = $input['acdigits'];
            $acnome = $input['acnome'];
            $bknome = $input['bknome'];

              $addDetails = Wallet::where('owner',$uuid)->where('typer','deposit')->sum('amount');
              $addInDetails = Wallet::where('owner',$uuid)->where('typer','in-transfer')->sum('amount');
              $stakings = Wallet::where('owner',$uuid)->where('typer','staking')->sum('amount');
              $inDetails = Wallet::where('owner',$uuid)->where('typer','in-transfer-deposit')->sum('amount');
              $outDetails = Wallet::where('owner',$uuid)->where('typer','out-transfer-deposit')->sum('amount');
              $minusOutDetails = Wallet::where('owner',$uuid)->where('typer','out-transfer')->sum('amount');
              $minusLossDetails = Wallet::where('owner',$uuid)->where('typer','losing')->sum('amount');
              $pendings = Wallet::where('owner',$uuid)->where('typer','pending')->sum('amount');
              $moneyDetails = ((int)$addDetails + (int)$addInDetails + (int)$inDetails) - ((int)$minusOutDetails + (int)$minusLossDetails + (int)$stakings + (int)$pendings);

              $winnings = Wallet::where('owner',$uuid)->where('typer','winning')->sum('amount');
              $winLossOut = Wallet::where('owner',$uuid)->where('typer','win-loss-out')->sum('amount');
              $winLossDraw = Wallet::where('owner',$uuid)->where('typer','win-loss-draw')->sum('amount');

              $winnins = (int)$winnings - (int)$winLossOut - (int)$winLossDraw - (int)$outDetails;






        if((int)$amount <= $winnins){
            $withdraw = new Wallet();
            $withdraw->amount = $amount;
            $withdraw->typer = 'win-loss-draw';
            $withdraw->owner = $uuid;
            $withdraw->notes = 'withdraw-pending';
            $withdraw->extra = "$bknome - $acnome - $acdigits";

            if($withdraw->save()){
                return response()->json(['success' => 'success'], 200);
            } else {
                return response()->json(['success' => 'failure'], 200);
            }
        } else {
            return response()->json(['success' => 'failure'], 200);
        }


        }
        catch(\Exception $e){
            return response()->json(['success' => $e->getMessage()], 500);
        }
    }

    public function withdrawCancel(Request $request)
    {
        $input = $request->all();
        $uuid = $input['uuid'];
        $amount = $input['amount'];
        $notes = $input['notes'];

        $withdraw_data = Wallet::where('owner',$uuid)->where('typer','win-loss-draw')->where('notes','withdraw-pending')->first();

        if($withdraw_data){
            $withdraw_data->typer = 'cancelled';
            if($withdraw_data->save()){
                return response()->json(['success' => 'success'], 200);
            } else {
                return response()->json(['success' => 'failure'], 200);
            }
        } else {
            return response()->json(['success' => 'failure'], 200);
        }

    }


    public function withdrawBlock(Request $request)
    {
        $input = $request->all();
        $id = $input['id'];

        $withdraw_data = Wallet::where('id',$id)->first();

        if($withdraw_data){
            $withdraw_data->notes = 'withdraw-block';
            if($withdraw_data->save()){
                return response()->json(['success' => 'success'], 200);
            } else {
                return response()->json(['success' => 'failure'], 200);
            }
        } else {
            return response()->json(['success' => 'failure'], 200);
        }

    }

    public function withdrawUnBlock(Request $request)
    {
        $input = $request->all();
        $id = $input['id'];

        $withdraw_data = Wallet::where('id',$id)->first();

        if($withdraw_data){
            $withdraw_data->notes = 'withdraw-pending';
            if($withdraw_data->save()){
                return response()->json(['success' => 'success'], 200);
            } else {
                return response()->json(['success' => 'failure'], 200);
            }
        } else {
            return response()->json(['success' => 'failure'], 200);
        }

    }

    public function withdrawBlockX($id)
    {

        $withdraw_data = Wallet::where('id',$id)->first();

        if($withdraw_data){
            $withdraw_data->notes = 'withdraw-block';
            if($withdraw_data->save()){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function withdrawUnBlockX($id)
    {

        $withdraw_data = Wallet::where('id',$id)->first();

        if($withdraw_data){
            $withdraw_data->notes = 'withdraw-pending';
            if($withdraw_data->save()){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function withdrawSend(Request $request)
    {
        $input = $request->all();
        $id = $input['id'];

        $this->withDrawBlockX($id);

        $withdraw_data = Wallet::where('id',$id)->first();

        if($withdraw_data and $withdraw_data->notes == "withdraw-block"){
            $withdraw_data->notes = 'withdraw-success';
            if($withdraw_data->save()){
               // $this->withDrawUnBlockX($id);
                return response()->json(['success' => 'success'], 200);
            } else {
                return response()->json(['success' => 'failure'], 200);
            }
        } else {
            return response()->json(['success' => 'failure'], 200);
        }

    }

    public function transferManual(Request $request)
    {
        try {
        $input = $request->all();
        $uuid = $input['uuid'];
        $datr = $input['datr'];
        $amtr = $input['amtr'];
        $refr = $input['refr'];
        $loctr = $input['loctr'];
        $bankr = $input['bankr'];

        $transfer = new Wallet();
        $transfer->amount = $amtr;
        $transfer->typer = 'deposit-pending';
        $transfer->owner = $uuid;
        $transfer->notes = $refr;
        $transfer->extra = "$bankr - $loctr - $datr";

        if($transfer->save()){
            return response()->json(['transfer' => 'success'], 200);
        } else {
            return response()->json(['transfer' => 'failure'], 200);
        }
        }
        catch(\Exception $e){
            return response()->json(['transfer' => $e->getMessage()], 200);
        }
    }


    public function transferConfirm(Request $request)
    {
        $input = $request->all();
        $id = $input['id'];

        $trans_data = Wallet::where('id',$id)->first();

        if($trans_data){
            $trans_data->typer = 'deposit';
            if($trans_data->save()){
                return response()->json(['transfer' => 'success'], 200);
            } else {
                return response()->json(['transfer' => 'failure'], 200);
            }
        } else {
            return response()->json(['transfer' => 'failure'], 200);
        }
    }


    public function banker()
    {
        return view('bank');
    }

    public function payment(Request $request){
        $input = $request->all();
        $details = $input["reference"];
        $userID = $input["uuid"];
        //$obj = json_decode($details);

        //$reference = $obj -> {"reference"};

        $result = array();
        //The parameter after verify/ is the transaction reference to be verified
        $url = "https://api.paystack.co/transaction/verify/$details";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
        $ch, CURLOPT_HTTPHEADER, [
             'Authorization: Bearer '.env('PAYSTACK_URL')]
        );
       $requesta = curl_exec($ch);
       curl_close($ch);

       if ($requesta) {
           $result = json_decode($requesta, true);
           // print_r($result);
           if($result){
              if($result['data']){
                 //something came in
                 if($result['data']['status'] == 'success'){
                    // the transaction was successful, you can deliver value
                 /*
                 @ also remember that if this was a card transaction, you can store the
                 @ card authorization to enable you charge the customer subsequently.
                 @ The card authorization is in:
                 @ $result['data']['authorization']['authorization_code'];
                 @ PS: Store the authorization with this email address used for this transaction.
                 @ The authorization will only work with this particular email.
                 @ If the user changes his email on your system, it will be unusable
                 */

                 $walletData = Wallet::where('owner',$userID)->where('typer','deposit')->get();

                 if(count($walletData) == 0){

                     $wallet = new Wallet();
                     $wallet->owner = $userID;
                     $wallet->amount = $result['data']['amount'] / 100;
                     $wallet->typer = 'deposit';
                     $wallet->notes = 'self';

                    if($wallet->save()){


                        if($result['data']['amount']){

                            $refData = Referrer::where('referree',$userID)->where('notes','active')->get();

                            if(count($refData) > 0){

                                foreach($refData as $refDatum){
                                    $wallet = new Wallet();
                                    $wallet->owner = $refDatum->referrer;
                                    $wallet->amount = round((5/100 * ($result['data']['amount']/100)),2);
                                    $wallet->typer = 'winning';
                                    $wallet->notes = 'referral';
                                    $wallet->save();
                                }

                            }


                        }




                        $transact = new Transaction();
                        $transact->title = 'deposit';
                        $transact->owner = $userID;
                        $transact->amount = $result['data']['amount'] / 100;
                        $transact->save();
                        return response()->json(['success' => 'success'], $this->successStatus);
                    } else {
                        return response()->json(['success' => 'failure'], 305);

                    }




                 } else {

                    $wallet = new Wallet();
                 $wallet->owner = $userID;
                 $wallet->amount = $result['data']['amount'] / 100;
                 $wallet->typer = 'deposit';
                 $wallet->notes = 'self';

                    if($wallet->save()){
                        $transact = new Transaction();
                        $transact->title = 'deposit';
                        $transact->owner = $userID;
                        $transact->amount = $result['data']['amount'] / 100;
                        $transact->save();
                        return response()->json(['success' => 'success'], $this->successStatus);
                    } else {
                        return response()->json(['success' => 'failure'], 305);

                    }




                 }



                 }else{
                    // the transaction was not successful, do not deliver value'
                    // print_r($result);  //uncomment this line to inspect the result, to check why it failed.
                    return response()->json(['success' => 'failure'],300);

                    //echo "Transaction was not successful: Last gateway response was: ".$result['data']['gateway_response'];
                 }
              }else{

                return response()->json(['success' => $result['message']],301);
                // echo $result['message'];
              }

          }else{
             //print_r($result);
             return response()->json(['success' => "Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable."],302);
            // die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable.");
          }
       }else{
          //var_dump($request);
          return response()->json(['success' => "Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok"],303);

         // die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
       }
    }


    public function transferToken(Request $request)
    {
            $input = $request->all();
            $uuid = $input['uuid'];
            $userh = $input['user'];
            $amount = $input['amount'];

            try{

                    $userhx = User::where('uuid',$userh)->first();
                    if($userhx == null){
                          return response()->json(['transfer' => 'failure'], 200);
                    }
                    if($userh == $uuid){
                        return response()->json(['transfer' => 'failure'], 200);
                    }


              $addDetails = Wallet::where('owner',$uuid)->where('typer','deposit')->sum('amount');
              $addInDetails = Wallet::where('owner',$uuid)->where('typer','in-transfer')->sum('amount');
              $inDetails = Wallet::where('owner',$uuid)->where('typer','in-transfer-deposit')->sum('amount');
              $outDetails = Wallet::where('owner',$uuid)->where('typer','out-transfer-deposit')->sum('amount');
              $stakings = Wallet::where('owner',$uuid)->where('typer','staking')->sum('amount');
              $minusOutDetails = Wallet::where('owner',$uuid)->where('typer','out-transfer')->sum('amount');
              $minusLossDetails = Wallet::where('owner',$uuid)->where('typer','losing')->sum('amount');
              $pendings = Wallet::where('owner',$uuid)->where('typer','pending')->sum('amount');
              $moneyDetails = ((int)$addDetails + (int)$addInDetails + (int)$inDetails) - ((int)$minusOutDetails + (int)$minusLossDetails + (int)$stakings + (int)$pendings);

              $winnings = Wallet::where('owner',$uuid)->where('typer','winning')->sum('amount');
              $winLossOut = Wallet::where('owner',$uuid)->where('typer','win-loss-out')->sum('amount');
              $winLossDraw = Wallet::where('owner',$uuid)->where('typer','win-loss-draw')->sum('amount');

              $winnins = (int)$winnings - (int)$winLossOut - (int)$winLossDraw - (int)$outDetails;

             // $betData = Bet::where('uuid',$uuid)->where('state','initiated')->orWhere('state','private')->where('amt','!=','0')->where('uuid',$uuid)->sum('amt');


                    if((int)$moneyDetails >= (int)$amount){

                        $senderX = new Wallet();
                        $senderX->amount = $amount;
                        $senderX->typer = 'out-transfer';
                        $senderX->owner = $uuid;
                        $senderX->notes = $userh;

                        if($senderX->save()){
                             $receiverX = new Wallet();
                             $receiverX->amount = $amount;
                             $receiverX->typer = 'in-transfer';
                             $receiverX->owner = $userh;
                             $receiverX->notes = $uuid;

                             if($receiverX->save()){

                                 $transact = new Transaction();
                                 $transact->title = 'transfer';
                                 $transact->owner = $uuid;
                                 $transact->amount = $amount;

                                 if($transact->save()){



                                    return response()->json(['transfer' => 'success'], 200);
                                 } else {
                                    return response()->json(['transfer' => 'failure'], 200);

                                }


                             } else {
                                return response()->json(['transfer' => 'failure'], 200);

                             }


                        } else {
                            return response()->json(['transfer' => 'failure'], 200);

                        }


                    } else {
                        return response()->json(['transfer' => 'failure'], 200);
                    }



           }
            catch(\Exception $e){
                 return response()->json(['transfer' => $e->getMessage()], 500);

           }

    }

    public function transferCreditUser(Request $request)
    {
            $input = $request->all();
            $uuid = $input['uuid'];
            $userh = $input['user'];
            $amount = $input['amount'];

            try{

                    $userhx = User::where('uuid',$userh)->first();
                    if($userhx == null){
                          return response()->json(['transfer' => 'failure'], 200);
                    }
                    if($userh == $uuid){
                        return response()->json(['transfer' => 'failure'], 200);
                    }


              $addDetails = Wallet::where('owner',$uuid)->where('typer','deposit')->sum('amount');
              $addInDetails = Wallet::where('owner',$uuid)->where('typer','in-transfer')->sum('amount');
              $inDetails = Wallet::where('owner',$uuid)->where('typer','in-transfer-deposit')->sum('amount');
              $outDetails = Wallet::where('owner',$uuid)->where('typer','out-transfer-deposit')->sum('amount');
              $stakings = Wallet::where('owner',$uuid)->where('typer','staking')->sum('amount');
              $minusOutDetails = Wallet::where('owner',$uuid)->where('typer','out-transfer')->sum('amount');
              $minusLossDetails = Wallet::where('owner',$uuid)->where('typer','losing')->sum('amount');
              $pendings = Wallet::where('owner',$uuid)->where('typer','pending')->sum('amount');
              $moneyDetails = ((int)$addDetails + (int)$addInDetails + (int)$inDetails) - ((int)$minusOutDetails + (int)$minusLossDetails + (int)$stakings + (int)$pendings);

              $winnings = Wallet::where('owner',$uuid)->where('typer','winning')->sum('amount');
              $winLossOut = Wallet::where('owner',$uuid)->where('typer','win-loss-out')->sum('amount');
              $winLossDraw = Wallet::where('owner',$uuid)->where('typer','win-loss-draw')->sum('amount');

              $winnins = (int)$winnings - (int)$winLossOut - (int)$winLossDraw - (int)$outDetails;

             // $betData = Bet::where('uuid',$uuid)->where('state','initiated')->orWhere('state','private')->where('amt','!=','0')->where('uuid',$uuid)->sum('amt');


                    if((int)$moneyDetails >= (int)$amount){

                        $senderX = new Wallet();
                        $senderX->amount = $amount;
                        $senderX->typer = 'out-transfer';
                        $senderX->owner = $uuid;
                        $senderX->notes = $userh;

                        if($senderX->save()){
                             $receiverX = new Wallet();
                             $receiverX->amount = $amount;
                             $receiverX->typer = 'in-transfer';
                             $receiverX->owner = $userh;
                             $receiverX->notes = $uuid;

                             if($receiverX->save()){

                                 $transact = new Transaction();
                                 $transact->title = 'transfer';
                                 $transact->owner = $uuid;
                                 $transact->amount = $amount;

                                 if($transact->save()){



                                    return response()->json(['transfer' => 'success'], 200);
                                 } else {
                                    return response()->json(['transfer' => 'failure'], 200);

                                }


                             } else {
                                return response()->json(['transfer' => 'failure'], 200);

                             }


                        } else {
                            return response()->json(['transfer' => 'failure'], 200);

                        }


                    } else {
                        return response()->json(['transfer' => 'failure'], 200);
                    }



           }
            catch(\Exception $e){
                 return response()->json(['transfer' => $e->getMessage()], 500);

           }

    }


     public function transferBalance(Request $request)
    {
            $input = $request->all();
            $uuid = $input['uuidx'];
            $amount = $input['amountx'];

            try{


              $addDetails = Wallet::where('owner',$uuid)->where('typer','deposit')->sum('amount');
              $addInDetails = Wallet::where('owner',$uuid)->where('typer','in-transfer')->sum('amount');
              $stakings = Wallet::where('owner',$uuid)->where('typer','staking')->sum('amount');
              $inDetails = Wallet::where('owner',$uuid)->where('typer','in-transfer-deposit')->sum('amount');
              $outDetails = Wallet::where('owner',$uuid)->where('typer','out-transfer-deposit')->sum('amount');
              $minusOutDetails = Wallet::where('owner',$uuid)->where('typer','out-transfer')->sum('amount');
              $minusLossDetails = Wallet::where('owner',$uuid)->where('typer','losing')->sum('amount');
              $pendings = Wallet::where('owner',$uuid)->where('typer','pending')->sum('amount');
              $moneyDetails = ((int)$addDetails + (int)$addInDetails + (int)$inDetails) - ((int)$minusOutDetails + (int)$minusLossDetails + (int)$stakings + (int)$pendings);

              $winnings = Wallet::where('owner',$uuid)->where('typer','winning')->sum('amount');
              $winLossOut = Wallet::where('owner',$uuid)->where('typer','win-loss-out')->sum('amount');
              $winLossDraw = Wallet::where('owner',$uuid)->where('typer','win-loss-draw')->sum('amount');

              $winnins = (int)$winnings - (int)$winLossOut - (int)$winLossDraw - (int)$outDetails;


             // $betData = Bet::where('uuid',$uuid)->where('state','initiated')->orWhere('state','private')->where('amt','!=','0')->where('uuid',$uuid)->sum('amt');


                    if((int)$winnins >= (int)$amount){

                        $senderX = new Wallet();
                        $senderX->amount = $amount;
                        $senderX->typer = 'out-transfer-deposit';
                        $senderX->owner = $uuid;
                        $senderX->notes = $uuid;

                        if($senderX->save()){
                            $receiverX = new Wallet();
                            $receiverX->amount = $amount;
                            $receiverX->typer = 'in-transfer-deposit';
                            $receiverX->owner = $uuid;
                            $receiverX->notes = $uuid;

                            if($receiverX->save()){
                                $transact = new Transaction();
                                $transact->title = 'transfer';
                                $transact->owner = $uuid;
                                $transact->amount = $amount;


                                if($transact->save()){
                                    $transact = new Transaction();
                                    $transact->title = 'receive';
                                    $transact->owner = $uuid;
                                    $transact->amount = $amount;
                                    if($transact->save()){

                                        return response()->json(['transfer' => 'success'], 200);
                                    } else {
                                        return response()->json(['transfer' => 'failure'], 200);

                                    }


                                } else {
                                    return response()->json(['transfer' => 'failure'], 200);

                                }


                            } else {
                                 return response()->json(['transfer' => 'failure'], 200);

                            }


                        } else {
                            return response()->json(['transfer' => 'failure'], 200);

                        }


                    } else {
                        return response()->json(['transfer' => 'failure'], 200);
                    }



           }
            catch(\Exception $e){
                 return response()->json(['transfer' => $e->getMessage()], 500);

           }

    }


    public function getBankCode(Request $request)
    {
            $input = $request->all();
            $uuid = $input['uuid'];

            $bank = "";
            $error = "";

            try{

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/bank",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
             'Authorization: Bearer '.env('PAYSTACK_URL'),
             "Cache-Control: no-cache",
    ),
  ));

  $response = curl_exec($curl);
  $err = curl_error($curl);
  curl_close($curl);

  if ($err) {
      $success['sucess'] = 'failure';
      $success['error']  = $err;
    return response()->json(['error' => $success], 300);
  } else {
      $bank = $response;
      $success['success'] = 'success';
      $success['bank']  = $bank;
    return response()->json(['bank' => $success], 200);
  }




           }
            catch(\Exception $e){
                alert($e->getMessage());
                 return response()->json(['error' => $e->getMessage()], 500);

           }

    }


    public function resolveAccount(Request $request)
    {
            $input = $request->all();
            $uuid = $input['uuid'];
            $account = $input['account'];
            $bank_code = $input['bank_code'];

            $bank = "";
            $error = "";

            try{

  $curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/bank/resolve?account_number=".$account."&bank_code=".$bank_code,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
    "Authorization: Bearer ".env('PAYSTACK_URL'),
    "Cache-Control: no-cache",
    ),
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

  if ($err) {
      $success['sucess'] = 'failure';
      $success['error']  = $err;
    return response()->json(['success' => $success], 300);
  } else {

      $success['success'] = 'success';
      $success['resolver']  = $response;
    return response()->json(['bank' => $success], 200);
  }




           }
            catch(\Exception $e){
                 return response()->json(['error' => $e->getMessage()], 500);

           }

    }


    public function createRecipientCode(Request $request)
    {
            $input = $request->all();
            $uuid = $input['uuid'];
            $account = $input['account'];
            $bankCode = $input['bnk_code'];
            $accountName = $input['acct_name'];
            $typer = $input['typer'];
            $curr = $input['curr'];
            $details = $input['details'];

            try{

  $url = "https://api.paystack.co/transferrecipient";
  $fields = [
    "type" => $typer,
    "name" => $accountName,
    "description" => $details,
    "account_number" => $account,
    "bank_code" => $bankCode,
    "currency" => $curr
  ];
  $fields_string = http_build_query($fields);
  //open connection
  $ch = curl_init();

  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_POST, true);
  curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer ".env('PAYSTACK_URL'),
    "Cache-Control: no-cache",
  ));

  //So that curl_exec returns the contents of the cURL; rather than echoing it
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

  //execute post
  $result = curl_exec($ch);


  if ($result) {
    return response()->json(['recipient' => $result], 200);
  } else {
    return response()->json(['error' => 'failure'], 300);
  }



           }
            catch(\Exception $e){
                 return response()->json(['error' => $e->getMessage()], 500);

           }

    }


    public function withdrawMoney(Request $request)
    {
            $input = $request->all();
            $uuid = $input['uuid'];
            $amt = $input['amt'];
            $reci_code = $input['reci_code'];
            $reason = $input['reason'];


            if((int)$amt < 500){
                 return response()->json(['error' => 'failure'], 300);
            }

              $winnings = Wallet::where('owner',$uuid)->where('typer','winning')->sum('amount');
              $winLossOut = Wallet::where('owner',$uuid)->where('typer','win-loss-out')->sum('amount');
              $winLossDraw = Wallet::where('owner',$uuid)->where('typer','win-loss-draw')->sum('amount');

              if((int)$winnings - (int)$winLossOut - (int)$winLossDraw < (int)$amt){
                  return response()->json(['error' => 'failure'], 300);
              }

            try{

  $url = "https://api.paystack.co/transfer";
  $fields = [
    'source' => "balance",
    'amount' => ((int)$amt * 100) - 50,
    'recipient' => $reci_code,
    'reason' => $reason
  ];
  $fields_string = http_build_query($fields);
  //open connection
  $ch = curl_init();

  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_POST, true);
  curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer ".env('PAYSTACK_URL'),
    "Cache-Control: no-cache",
  ));

  //So that curl_exec returns the contents of the cURL; rather than echoing it
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

  //execute post
  $result = curl_exec($ch);

  if ((array)json_decode($result,true)['status'] == true) {
      $transact = new Transaction();
      $transact->title = 'withdraw';
      $transact->owner = $uuid;
      $transact->amount = $amt;
      $transact->save();


                            $receiverS = new Wallet();
                            $receiverS->amount = $amt;
                            $receiverS->typer =  'win-loss-draw';
                            $receiverS->owner = $uuid;
                            $receiverS->notes = 'withdrawal-lost';
                            $receiverS->save();
    return response()->json(['withdraw' => $result], 200);
  } else {
    return response()->json(['error' => 'failure'], 300);
  }



           }
            catch(\Exception $e){
                 return response()->json(['error' => $e->getMessage()], 500);

           }

    }


}
