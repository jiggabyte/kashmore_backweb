<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\CrashError;
use App\Models\Device;
use App\Models\FreeGame;
use App\Models\GameOn;
use App\Http\Controllers\Controller;
use App\Models\Referrer;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Version;
use App\Models\Wallet;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;

class UserController extends Controller
{

    use AuthenticatesUsers;

    public $successStatus = 200;


    public $api_key;

    public function __construct()
    {
        $this->api_key = env('FIREBASE_CLOUD_API_KEY');
    }
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $input = $request->all();
            $one = $input['usernom'];
            $devicer = $input['device_id'];

           // $uuidx = "uuid";

           // $uuidx = filter_var($one,FILTER_VALIDATE_INT)? 'phone': 'uuid';

           // $phoneChecker = User::where('phone', $input['usernom'])->first();

           // if(isset($phoneChecker) ){
           //     $uuidx = "phone";
           // }

            /*
            $user = User::where('uuid',$input['usernom'])->first();
            if($user){
            $user->AauthAcessToken()->delete();
            }
             */

            if (Auth::attempt(['uuid' => $one, 'password' => $input['password']])) {
                //$user = Auth::user();
                $user = User::where('uuid', $input['usernom'])->first();
                if (is_null($user->email_verified_at)) {
                    return response()->json(['error' => 'Not Verified'], 400);
                } else {
                    $success['token'] = $user->createToken('Pato')->accessToken;

                   $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
                   // die(var_dump($factory));
                    $database = $factory->createDatabase();
                    $authy = $factory->createAuth();

                   // die(var_dump($authy));
                    $datas['nota'] = "";
                    $datas['backer'] = "";
                    $datas['updated_at'] = date('Y-m-d H:i:s');

                    $additionalClaims = [
                        'usernom' => true,
                    ];

                    $customToken = $authy->createCustomToken($one, $additionalClaims);

                    $success['firebase'] = $customToken->toString();

                    $uuid = $user->uuid;

                    $success['updated'] = $datas['updated_at'];
                    $success['devicer'] = $devicer;

                    $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                    $gameDatz = $database->getReference("/startz/$uuid")->set($datas);
                    $gameDate = $database->getReference("/logout/" . $uuid . "/backer")->set($one);
                    $gameDati = $database->getReference("/logout/" . $uuid . "/nota")->set($devicer);
                    $gameData = $database->getReference("/logout/" . $uuid . "/updated_at")->set($datas['updated_at']);
                    $gameDatar = $database->getReference("/users/" . $uuid . "/status")->set("online");

                    if ($success and $datas and $gameDat and $gameDatz and $gameDatar) {

                        return response()->json(['success' => $success], $this->successStatus);
                    } else {
                        return response()->json(['error' => 'Unauthorised'], 401);
                    }

                }

            } else if (Auth::attempt(['phone' => $one, 'password' => $input['password']])){
                //$user = Auth::user();
                $user = User::where('phone', $input['usernom'])->first();
                if (is_null($user->email_verified_at)) {
                    return response()->json(['error' => 'Not Verified'], 400);
                } else {
                    $success['token'] = $user->createToken('Pato')->accessToken;

                    $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
                    $database = $factory->createDatabase();
                    $authy = $factory->createAuth();
                    $datas['nota'] = "";
                    $datas['backer'] = "";
                    $datas['updated_at'] = date('Y-m-d H:i:s');

                    $additionalClaims = [
        '               usernom' => true,
                    ];

                    $customToken = $authy->createCustomToken($one, $additionalClaims);

                    $success['firebase'] = (string) $customToken;

                    $uuid = $user->uuid;

                    $success['updated'] = $datas['updated_at'];
                    $success['devicer'] = $devicer;


                    $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                    $gameDatz = $database->getReference("/startz/$uuid")->set($datas);
                    $gameDate = $database->getReference("/logout/" . $uuid . "/backer")->set($one);
                    $gameDati = $database->getReference("/logout/" . $uuid . "/nota")->set($devicer);
                    $gameData = $database->getReference("/logout/" . $uuid . "/updated_at")->set($datas['updated_at']);
                    $gameDatar = $database->getReference("/users/" . $uuid . "/status")->set("online");

                    if ($success and $datas and $gameDat and $gameDatz and $gameDatar) {

                        return response()->json(['success' => $success], $this->successStatus);
                    } else {
                        return response()->json(['error' => 'Unauthorised'], 401);
                    }

                }
            } else {
                return response()->json(['error' => 'Unauthorised'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /*
    protected function credentials(Request $request)
{
    // the value in the 'email' field in the request
    $username = $request->get($this->username());

    // check if the value is a validate email address and assign the field name accordingly
    $field = filter_var($username, FILTER_VALIDATE_INT) ? 'phone'  : $this->username();

    // return the credentials to be used to attempt login
    return [
        $field => $request->get($this->username()),
        'password' => $request->password,
    ];
}  */
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'uuid' => 'required|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
            'phone' => 'required|min:10|unique:users',
        ]);
        if ($validator->fails()) {
            return response()->json(['errorList' => $validator->errors()], 200);
        }
        $input = $request->all();
        $dataCode = mt_rand(1000, 9999);
        if (empty($input['email'])) {
            $input['email'] = $input['uuid'] .env('PHONE_NUMBER_MAILER');;

        }
        $fone = str_replace("+234", "0", $input['phone']);

        $input['sms_code'] = $dataCode;

        try {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'uuid' => $input['uuid'],
                'password' => Hash::make($input['password']),
                'phone' => $fone,
                'sms_code' => $dataCode,
                //'email_verified_at' => now(),
                //  'settings' => '{"locale":"en"}',
            ]);

            $userData = User::where('uuid', $user->uuid)->first();

            $userData->email_verified_at = date('Y-m-d H:i:s');
            $userData->save();

            if ($user->uuid != "") {
                 $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
                $database = $factory->createDatabase();

                $success['backer'] = "";
                $success['nota'] = "";
                $success['user_beta'] = "";
                $success['user_acc'] = "";
                $success['bet_id'] = "";

                $verify['sms_code'] = "" . $dataCode;
                $verify['phone'] = $fone;
                $verify['device'] = $input['uuid'] . "_" . mt_rand(1000, 9999);
                $verify['status'] = "unverified";

                $uuid = $input['uuid'];

                $gameDat = $database->getReference("/startx/$uuid")->set($success);

                $gameDatux = $database->getReference("/verify/$uuid")->set($verify);

                $headers = "From: ".env('MAIL_FROM_ADDRESS') . "\r\n" .
                    "Name: ".env('APP_NAME');

                $msg = "Your One time Pass is $dataCode";

                mail($input['email'], "Sign Up Verification", $msg, $headers);

                if (!$gameDatux) {
                   // $success['nota'] = 'Your verification code was not sent, please visit Not Verified page and resend sms!';
                    $success['nota'] = 'An Error Occurred, please retry or contact us!';
                    return response()->json(['success' => $success], 200);
                } else {

                    $success['token'] = $user->createToken('Pato')->accessToken;
                    return response()->json(['success' => $success], $this->successStatus);
                }

                /**

                $betgames_nota = "Betgames: Your One Time Pass is \r\n";

                $client = new Client();
                $res = $client->get('https://www.bulksmsnigeria.com/api/v1/sms/create?api_token=E8Jf3yhNOZmUU4Ph5j7qbBsSDDC8ahQqoRIg0i0lfyQyzdzjIFsD4DcXOek0&from=Betgames&to='.$fone.'&body='.$betgames_nota.$dataCode.'&dnd=7');
                $clientStatusCode = $res->getStatusCode(); // 200
                $clientResponse = $res->getBody();

                if($clientStatusCode != 200){
                $success['nota'] = 'Your verification code was not sent, please visit Not Verified page and resend sms!';
                return response()->json(['success' => $success], 200);
                } else {
                $success['token'] =  $user->createToken('Betgames')->accessToken;
                return response()->json(['success' => $success], $this->successStatus);
                }

                 **/

            } else {
                return response()->json(['error' => 'Check Your Details, And Retry Again!'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function setOnliner($uuid)
    {

        $user = User::where('uuid', $uuid)->first();

        $user->online_status = 'online';
        $user->updated_at = date('Y-m-d H:i:s');

        if ($user->save()) {

            return true;
        } else {
            return false;
        }

    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details(Request $request)
    {
        try {
            //$user = Auth::user();
            $input = $request->all();
            $one = $input['uuid'];
            $user = User::where('uuid', $one)->first();

            // return response()->json(['detail' => $user], $this->successStatus);

            if (is_null($user)) {
            	$user = User::where('phone', $one)->first();

            	if (is_null($user)) {
            		return response()->json(['error' => 'User details load error!'], 401);
            	} else {
            		return response()->json(['detail' => $user], $this->successStatus);
            	}

                return response()->json(['error' => 'User details load error!'], 401);
            } else {
                return response()->json(['detail' => $user], $this->successStatus);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /**
     * node play on api
     *
     * @return \Illuminate\Http\Response
     */
    public function nodePlayOn(Request $request)
    {

        $input = $request->all();
        $uuid = $input['uuid'];
        $tokin = $input['tokin'];

        $authy = Node::where('usero', $uuid)->orWhere('usera', $uuid)->where('token', $tokin)->where('game_status', 'active')->first();

        if (is_null($authy)) {
            return response()->json(['success' => 'failure'], 402);
        } else {
            return response()->json(['success' => $authy], $this->successStatus);
        }

    }

    public function saveBet(Request $request)
    {
        $input = $request->all();
        try {

            $bet = new Bet();
            $bet->uuid = $input['uuid'];
            $bet->amt = $input['amt'];
            $bet->game = $input['game'];
            $bet->state = 'initiated';
            $bet->created_at = date('Y-m-d H:i:s');

            $uuid = $input['uuid'];

            $this->setOnliner($uuid);

            $userBetData = Bet::where('uuid', $uuid)->where('state', 'initiated')->get();
            if (count($userBetData) === 1 or count($userBetData) > 1) {
                $success['nota'] = 'Failure';
                return response()->json(['success' => $success], 300);
            }

            $addDetails = Wallet::where('owner', $uuid)->where('typer', 'deposit')->sum('amount');
            $addInDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer')->sum('amount');
            $inDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer-deposit')->sum('amount');
            $outDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer-deposit')->sum('amount');
            $stakings = Wallet::where('owner', $uuid)->where('typer', 'staking')->sum('amount');
            $minusOutDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer')->sum('amount');
            $minusLossDetails = Wallet::where('owner', $uuid)->where('typer', 'losing')->sum('amount');
            $minusPendDetails = Wallet::where('owner', $uuid)->where('typer', 'pending')->sum('amount');
            $moneyDetails = ((int) $addDetails + (int) $addInDetails + (int) $inDetails) - ((int) $minusOutDetails + (int) $minusLossDetails + (int) $stakings + (int) $minusPendDetails);

            // $betData = Bet::where('uuid',$uuid)->where('state','initiated')->orWhere('state','private')->where('amt','!=','0')->where('uuid',$uuid)->sum('amt');

            if ((int) $moneyDetails >= (int) $input['amt']) {
                if ($bet->save()) {

                    $receiverZ = new Wallet();
                    $receiverZ->amount = $input['amt'];
                    $receiverZ->typer = 'pending';
                    $receiverZ->owner = $input['uuid'];
                    $receiverZ->notes = $bet->id;
                    $receiverZ->save();

                    $transact = new Transaction();
                    $transact->title = 'placed a bet';
                    $transact->owner = $input['uuid'];
                    $transact->amount = $input['amt'];
                    $transact->save();

                    $success['nota'] = 'Successful';
                    return response()->json(['success' => $success], $this->successStatus);
                } else {

                    $success['nota'] = 'Failure';
                    return response()->json(['success' => $success], 300);
                }
            } else {
                $success['nota'] = 'Failure';
                return response()->json(['success' => $success], 300);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function saveBetX(Request $request)
    {
        $input = $request->all();
        try {

            $bet = new Bet();
            $bet->uuid = $input['uuid'];
            $bet->acceptor = $input['target'];
            $bet->amt = $input['amt'];
            $bet->game = $input['game'];
            $bet->state = 'private';
            $bet->created_at = date('Y-m-d H:i:s');

            $uuid = $input['uuid'];

            $this->setOnliner($uuid);

            $userBetData = Bet::where('uuid', $uuid)->where('state', 'private')->get();
            if (count($userBetData) === 1 or count($userBetData) > 1) {
                $success['nota'] = 'Failure';
                return response()->json(['success' => $success], 300);
            }

            $addDetails = Wallet::where('owner', $uuid)->where('typer', 'deposit')->sum('amount');
            $addInDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer')->sum('amount');
            $inDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer-deposit')->sum('amount');
            $outDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer-deposit')->sum('amount');
            $stakings = Wallet::where('owner', $uuid)->where('typer', 'staking')->sum('amount');
            $minusOutDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer')->sum('amount');
            $minusLossDetails = Wallet::where('owner', $uuid)->where('typer', 'losing')->sum('amount');
            $minusPendDetails = Wallet::where('owner', $uuid)->where('typer', 'pending')->sum('amount');
            $moneyDetails = ((int) $addDetails + (int) $addInDetails + (int) $inDetails) - ((int) $minusOutDetails + (int) $minusLossDetails + (int) $stakings + (int) $minusPendDetails);

            // $betData = Bet::where('uuid',$uuid)->where('state','initiated')->orWhere('state','private')->where('amt','!=','0')->where('uuid',$uuid)->sum('amt');

            if ($moneyDetails >= $input['amt']) {

                if ($bet->save()) {

                    $receiverZ = new Wallet();
                    $receiverZ->amount = $input['amt'];
                    $receiverZ->typer = 'pending';
                    $receiverZ->owner = $input['uuid'];
                    $receiverZ->notes = $bet->id;
                    $receiverZ->save();

                    $transact = new Transaction();
                    $transact->title = 'placed a bet';
                    $transact->owner = $input['uuid'];
                    $transact->amount = $input['amt'];
                    $transact->save();

                    $success['nota'] = 'Successful';
                    return response()->json(['success' => $success], $this->successStatus);
                } else {

                    $success['nota'] = 'Failure';
                    return response()->json(['success' => $success], 300);
                }
            } else {
                $success['nota'] = 'Failure';
                return response()->json(['success' => $success], 300);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function replayBet(Request $request)
    {

         $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
        $database = $factory->createDatabase();

        $input = $request->all();
        $game = GameOn::where('id', $input['game_id'])->first();
        try {

            $uuidax = $input['user_nome'];
            $gameDatax = $database->getReference("/startx/" . $uuidax . "/backer")->getSnapShot()->getValue();
            $gameDatTimax = $database->getReference("/startx/" . $uuidax . "/updated_at")->getSnapShot()->getValue();

            if ($gameDatax == "started" and abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTimax)) < 20) {
                return response()->json(['successor' => "busy"], 200);
            } else {

                // return response()->json(['successor' => "busy"], 200);
            }

            $bet = new Bet();
            $bet->uuid = $input['user_nome'];
            $bet->acceptor = $input['user_challer'];
            $bet->amt = $game->amt;
            $bet->game = $input['bet_game'];
            $bet->state = 'private';
            $bet->created_at = date('Y-m-d H:i:s');

            $uuid = $input['user_nome'];
            $uuidz = $input['user_challer'];

            $this->setOnliner($uuid);

            $userBetCheck = Bet::where('uuid', $uuidz)->where('state', '!=', 'initiated')->where('state', 'private')->where('acceptor', $uuid)->orderBy('id', 'desc')->get();
            if (isset($userBetCheck[0]) and abs(strtotime(date('Y-m-d H:i:s')) - strtotime($userBetCheck[0]->created_at)) < 30) {
                $success['nota'] = 'Failure';
                return response()->json(['success' => $success], 300);
            };
            $userBetData = Bet::where('uuid', $uuid)->where('state', 'initiated')->orWhere('state', 'private')->get();
            if (count($userBetData) == 3) {
                //  $success['nota'] = 'Failure';
                //  return response()->json(['success' => $success], 300);
            };

            $addDetails = Wallet::where('owner', $uuid)->where('typer', 'deposit')->sum('amount');
            $addInDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer')->sum('amount');
            $inDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer-deposit')->sum('amount');
            $outDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer-deposit')->sum('amount');
            $stakings = Wallet::where('owner', $uuid)->where('typer', 'staking')->sum('amount');
            $minusOutDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer')->sum('amount');
            $minusLossDetails = Wallet::where('owner', $uuid)->where('typer', 'losing')->sum('amount');
            $minusPendDetails = Wallet::where('owner', $uuid)->where('typer', 'pending')->sum('amount');
            $moneyDetails = ((int) $addDetails + (int) $addInDetails + (int) $inDetails) - ((int) $minusOutDetails + (int) $minusLossDetails + (int) $stakings + (int) $minusPendDetails);

            $addDetailsz = Wallet::where('owner', $uuidz)->where('typer', 'deposit')->sum('amount');
            $addInDetailsz = Wallet::where('owner', $uuidz)->where('typer', 'in-transfer')->sum('amount');
            $inDetailsz = Wallet::where('owner', $uuidz)->where('typer', 'in-transfer-deposit')->sum('amount');
            $outDetailsz = Wallet::where('owner', $uuidz)->where('typer', 'out-transfer-deposit')->sum('amount');
            $stakingsz = Wallet::where('owner', $uuidz)->where('typer', 'staking')->sum('amount');
            $minusOutDetailsz = Wallet::where('owner', $uuidz)->where('typer', 'out-transfer')->sum('amount');
            $minusLossDetailsz = Wallet::where('owner', $uuidz)->where('typer', 'losing')->sum('amount');
            $minusPendDetailsz = Wallet::where('owner', $uuidz)->where('typer', 'pending')->sum('amount');
            $moneyDetailsz = ((int) $addDetailsz + (int) $addInDetailsz + (int) $inDetailsz) - ((int) $minusOutDetailsz + (int) $minusLossDetailsz + (int) $stakingsz + (int) $minusPendDetailsz);

            // $betData = Bet::where('uuid',$uuid)->where('state','initiated')->orWhere('state','private')->where('amt','!=','0')->where('uuid',$uuid)->sum('amt');

            if ((int) $moneyDetails >= (int) $game->amt and (int) $moneyDetailsz >= (int) $game->amt) {

                if ($bet->save()) {

                    $receiverZ = new Wallet();
                    $receiverZ->amount = (int) $game->amt;
                    $receiverZ->typer = 'pending';
                    $receiverZ->owner = $input['user_nome'];
                    $receiverZ->notes = $bet->id;
                    $receiverZ->save();

                    $transact = new Transaction();
                    $transact->title = 'placed a bet';
                    $transact->owner = $input['user_nome'];
                    $transact->amount = (int) $game->amt;
                    $transact->save();

                    $betItem = Bet::where('id', $bet->id)->first();
                    $beta = Device::where('username', $input['user_challer'])->first();
                    $to = $beta->token;
                    $title = "Bet Game Started";
                    $message = "You have been Challenged by " . $input['user_nome'];

                    //$betItem->state = "initiate";
                    //$betItem->save();
                    $datas = array(
                        'updated_at' => date('Y-m-d H:i:s'),
                        'nota' => 'displayConfirmReplay',
                        'backer' => 'started',
                        'gamer' => $game->game_type,
                        'user_beta' => $input['user_nome'],
                        'user_acc' => $input['user_challer'],
                        'bet_amt' => $bet->amt,
                        'bet_game' => $bet->game,
                        'bet_id' => '' . $bet->id);

                    $this->sendNota($to, $message, $title, $datas);
                    //$this->sendData($to,$datas);

                    $uuid = $input['user_challer'];
                    $gameDato = $database->getReference("/startx/" . $uuid . "/backer")->getSnapShot()->getValue();
                    $gameDatTime = $database->getReference("/startx/" . $uuid . "/updated_at")->getSnapShot()->getValue();

                    if (isset($gameDato)) {
                        switch ($gameDato) {
                            case "started":
                                if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 20) {
                                    $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                    return response()->json(['successor' => "success"], $this->successStatus);
                                } else {
                                    return response()->json(['successor' => "busy"], 200);
                                }
                                break;
                            case "confirmed":
                                if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 20) {
                                    $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                    return response()->json(['successor' => "success"], $this->successStatus);
                                } else {
                                    return response()->json(['successor' => "busy"], 200);
                                }
                                break;
                            case "ongoing":
                                return response()->json(['successor' => "busy"], 200);
                                break;
                            case "move":
                                return response()->json(['successor' => "busy"], 200);
                                break;
                            case "cancelled":
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                                break;
                            case "game_over":
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                                break;
                            case "game_over":
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                                break;
                            case "":
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                                break;
                            default:
                                return response()->json(['successor' => "busy"], 200);
                        }

                    } else {

                        return response()->json(['successor' => "busy"], 200);
                    }

                } else {

                    return response()->json(['successor' => 'failure'], 200);
                }

            } else {
                return response()->json(['successor' => 'failure'], 200);
            }

        } catch (\Exception $e) {
            return response()->json(['successor' => $e->getMessage()], 500);
        }

    }

    public function loadBet(Request $request)
    {
        try {
            $input = $request->all();
            $offset = $input['offset'];
            $limit = $input['limit'];
            $uuid = $input['uuid'];

            $this->setOnliner($uuid);

            $dater = new DateTime(date("Y-m-d H:i:s"));
            $dater->modify("-7 day")->format("Y-m-d H:i:s");

            $count = DB::table('bets')->where('state', 'initiated')
                ->join('users', 'users.uuid', '=', 'bets.uuid')
                ->where('bets.uuid', '!=', $uuid)
                ->whereDate('bets.created_at', '>', $dater)
                ->orderBy('users.online_status', 'desc')
                ->count();

            $selected = DB::table('bets')->where('state', 'initiated')
                ->join('users', 'users.uuid', '=', 'bets.uuid')
                ->where('bets.uuid', '!=', $uuid)
                ->whereDate('bets.created_at', '>', $dater)
                ->select('bets.*', 'users.online_status', 'users.updated_at')
                ->orderBy('users.online_status', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            if ($selected) {
                $success['nota'] = $selected;
                $success['count'] = $count;
                return response()->json(['success' => $success], $this->successStatus);
            } else {
                $success['nota'] = 'Failure';
                return response()->json(['success' => $success], 402);
            }
        } catch (\Exception $e) {
            return response()->json(['successor' => $e->getMessage()], 500);
        }

    }

    public function loadBetX(Request $request)
    {
        $input = $request->all();
        $offset = $input['offset'];
        $limit = $input['limit'];
        $uuid = $input['uuid'];

        $this->setOnliner($uuid);

        $dater = new DateTime(date("Y-m-d H:i:s"));
        $dater->modify("-7 day")->format("Y-m-d H:i:s");

        $count = DB::table('bets')->where('state', 'private')
            ->join('users', 'users.uuid', '=', 'bets.uuid')
            ->where('bets.acceptor', $uuid)
            ->whereDate('bets.created_at', '>', $dater)
            ->orderBy('users.online_status', 'desc')
            ->count();

        $selected = DB::table('bets')->where('state', 'private')
            ->join('users', 'users.uuid', '=', 'bets.uuid')
            ->where('bets.acceptor', $uuid)
            ->whereDate('bets.created_at', '>', $dater)
            ->select('bets.*', 'users.online_status', 'users.updated_at')
            ->orderBy('users.online_status', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        if ($selected) {
            $success['nota'] = $selected;
            $success['count'] = $count;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            $success['nota'] = 'Failure';
            return response()->json(['success' => $success], 402);
        }

    }

    public function loadBetY(Request $request)
    {
        $input = $request->all();
        $offset = $input['offset'];
        $limit = $input['limit'];
        $uuid = $input['uuid'];

        $this->setOnliner($uuid);

        $dater = new DateTime(date("Y-m-d H:i:s"));
        $dater->modify("-7 day")->format("Y-m-d H:i:s");

        $count = Bet::where('state', 'private')
            ->where('uuid', $uuid)
            ->where('state', '!=', 'cancelled')
        //->whereDate('created_at','>',$dater)
            ->orWhere('state', 'initiated')
            ->where('uuid', $uuid)
            ->orderBy('id', 'desc')
            ->count();

        $selected = Bet::where('state', 'private')
            ->where('uuid', $uuid)
            ->where('state', '!=', 'cancelled')
        //->whereDate('created_at','>',$dater)
            ->orWhere('state', 'initiated')
            ->where('uuid', $uuid)
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
        if ($selected) {
            $success['nota'] = $selected;
            $success['count'] = $count;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            $success['nota'] = 'Failure';
            return response()->json(['success' => $success], 402);
        }

    }

    public function searchBet(Request $request)
    {
        $input = $request->all();
        $offset = $input['offset'];
        $limit = $input['limit'];
        $uuid = $input['uuid'];
        $amt = $input['amt'];

        $this->setOnliner($uuid);

        $dater = new DateTime(date("Y-m-d H:i:s"));
        $dater->modify("-7 day")->format("Y-m-d H:i:s");

        $count = DB::table('bets')->where('state', 'initiated')
            ->join('users', 'users.uuid', '=', 'bets.uuid')
            ->where('bets.uuid', '!=', $uuid)
            ->whereDate('bets.created_at', '>', $dater)
            ->where('bets.amt', $amt)
            ->orderBy('users.online_status', 'desc')
            ->count();

        $selected = DB::table('bets')->where('state', 'initiated')
            ->join('users', 'users.uuid', '=', 'bets.uuid')
            ->where('bets.uuid', '!=', $uuid)
            ->where('bets.amt', $amt)
            ->whereDate('bets.created_at', '>', $dater)
            ->select('bets.*', 'users.online_status', 'users.updated_at')
            ->orderBy('users.online_status', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        if ($selected) {
            $success['nota'] = $selected;
            $success['count'] = $count;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            $success['nota'] = 'Failure';
            return response()->json(['success' => $success], 402);
        }

    }

    public function searchBetX(Request $request)
    {
        $input = $request->all();
        $offset = $input['offset'];
        $limit = $input['limit'];
        $uuid = $input['uuid'];
        $amt = $input['amt'];

        $this->setOnliner($uuid);

        $dater = new DateTime(date("Y-m-d H:i:s"));
        $dater->modify("-7 day")->format("Y-m-d H:i:s");

        $count = DB::table('bets')->where('state', 'private')
            ->join('users', 'users.uuid', '=', 'bets.uuid')
            ->where('bets.acceptor', $uuid)
            ->whereDate('bets.created_at', '>', $dater)
            ->where('bets.amt', $amt)
            ->orderBy('users.online_status', 'desc')
            ->count();

        $selected = DB::table('bets')->where('state', 'private')
            ->join('users', 'users.uuid', '=', 'bets.uuid')
            ->where('bets.acceptor', $uuid)
            ->whereDate('bets.created_at', '>', $dater)
            ->where('bets.amt', $amt)
            ->select('bets.*', 'users.online_status', 'users.updated_at')
            ->orderBy('users.online_status', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        if ($selected) {
            $success['nota'] = $selected;
            $success['count'] = $count;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            $success['nota'] = 'Failure';
            return response()->json(['success' => $success], 402);
        }

    }

    public function searchBetY(Request $request)
    {
        $input = $request->all();
        $offset = $input['offset'];
        $limit = $input['limit'];
        $uuid = $input['uuid'];
        $amt = $input['amt'];

        $this->setOnliner($uuid);

        $dater = new DateTime(date("Y-m-d H:i:s"));
        $dater->modify("-7 day")->format("Y-m-d H:i:s");

        $count = Bet::where('state', 'private')
            ->where('uuid', $uuid)
            ->where('amt', $amt)
            ->whereDate('created_at', '>', $dater)
            ->orderBy('id', 'desc')
            ->count();

        $selected = Bet::where('state', 'private')
            ->where('uuid', $uuid)
            ->where('amt', $amt)
            ->whereDate('created_at', '>', $dater)
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
        if ($selected) {
            $success['nota'] = $selected;
            $success['count'] = $count;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            $success['nota'] = 'Failure';
            return response()->json(['success' => $success], 402);
        }

    }

    public function searchUser(Request $request)
    {
        $input = $request->all();
        $uuid = $input['uuid'];
        $usernom = $input['usernom'];
        $success = [];

        $this->setOnliner($uuid);

        $count = User::where('uuid', $usernom)->count();

        $success = User::where('uuid', $usernom)->first();
        if ($count > 0) {
            $success['count'] = $count;
            return response()->json(['detail' => $success], $this->successStatus);
        } else {

            $success['nota'] = 'Failure';
            $success['count'] = '0';
            return response()->json(['detail' => $success], $this->successStatus);
        }

    }

    public function logout(Request $request)
    {

        $input = $request->all();
        $uuid = $input['uuid'];
        $user = User::where('uuid', $input['uuid'])->first();

        $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
        $database = $factory->createDatabase();

        $gameDatar = $database->getReference("/users/" . $uuid . "/status")->set("offline");
        $user->online_status = 'offline';

        if ($user->save() and $gameDatar) {

            // $user->AauthAcessToken()->delete();
            // $device = Device::where('username',$input['uuid'])->first();
            // $device->delete();
            // Auth::logout();
            return response()->json(['successor' => 'success'], $this->successStatus);
        } else {
            return response()->json(['successor' => 'failure'], 300);
        }
    }

    public function resendSms(Request $request)
    {
        $dataCode = mt_rand(1000, 9999);
        $input = $request->all();
        // $fone = str_replace("+234","0",$input['uuid']);
        $uuid = $input['uuid'];
        $user = User::where('uuid', $uuid)->first();
        $fone = $user->phone;

        $headers = "From: ".env('MAIL_FROM_ADDRESS') . "\r\n" .
        "Name: ".env('APP_NAME');

        $msg = "Your One time Pass is $dataCode";

        mail($user->email, "Resent Verification", $msg, $headers);

        $betgames_nota = "Betgames: Your One Time Pass is \r\n";

        $client = new Client();
        $res = $client->get('https://www.bulksmsnigeria.com/api/v1/sms/create?api_token=E8Jf3yhNOZmUU4Ph5j7qbBsSDDC8ahQqoRIg0i0lfyQyzdzjIFsD4DcXOek0&from=Betgames&to=' . $fone . '&body=' . $betgames_nota . $dataCode . '&dnd=7');
        $clientStatusCode = $res->getStatusCode(); // 200
        $clientResponse = $res->getBody();

        $user->sms_code = $dataCode;
        $user->save();

        if ($clientStatusCode != 200) {
            $success['nota'] = 'Your verification code was not sent!';
            return response()->json(['success' => $success], 402);
        } else {
            $success['nota'] = 'Your verification code has been sent!';
            return response()->json(['success' => $success], $this->successStatus);
        }

    }

    public function forgot(Request $request)
    {
        $dataCode = mt_rand(1000, 9999);
        $input = $request->all();
        // $fone = str_replace("+234","0",$input['uuid']);
        $uuid = $input['uuid'];
        $user = User::where('uuid', $uuid)->first();
        $fone = $user->phone;

        $headers = "From: ".env('MAIL_FROM_ADDRESS') . "\r\n" .
        "Name: ".env('APP_NAME');

        $msg = "Your One time Pass is $dataCode";

        mail($user->email, "Forgot Verification", $msg, $headers);

        $betgames_nota = "Betgames: Your One Time Pass is \r\n";

        $client = new Client();
        $res = $client->get('https://www.bulksmsnigeria.com/api/v1/sms/create?api_token=E8Jf3yhNOZmUU4Ph5j7qbBsSDDC8ahQqoRIg0i0lfyQyzdzjIFsD4DcXOek0&from=Betgames&to=' . $fone . '&body=' . $betgames_nota . $dataCode . '&dnd=7');
        $clientStatusCode = $res->getStatusCode(); // 200
        $clientResponse = $res->getBody();

        $user->sms_code = $dataCode;

        if ($user->save() and $clientStatusCode == 200) {
            $success['nota'] = 'Your verification code has been sent!';
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            $success['nota'] = 'Your verification code was not sent!';
            return response()->json(['success' => $success], 402);
        }

    }

    public function verifyUser(Request $request)
    {

        $input = $request->all();
        // $fone = str_replace("+234","0",$input['uuid']);
        $uuid = $input['uuid'];
        $user = User::where('uuid', $uuid)->first();

        try {

            $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            if ($user->sms_code == $input['code']) {

                $user->email_verified_at = date('Y-m-d H:i:s');
                $user->save();

                $gameDatux = $database->getReference("/verify/$uuid")->update(['status' => 'verified']);

                $success['uuid'] = $user->uuid;
                $success['token'] = "1234567890"; // $user->createToken('Pato')->accessToken;
                return response()->json(['success' => $success], $this->successStatus);

            } else {
                $success['nota'] = 'Your account was not verified, ensure your code is correct!';
                return response()->json(['success' => $success], 402);

            }
        } catch (\Exception $e) {
            $success['nota'] = $e->getMessage();
            return response()->json(['success' => $success], 500);

        }

    }

    public function changeUserPassword(Request $request)
    {

        $input = $request->all();
        // $fone = str_replace("+234","0",$input['uuid']);
        $uuid = $input['uuid'];
        $passer = $input['passer'];
        $firmer = $input['firmer'];
        $codist = $input['codist'];
        $user = User::where('uuid', $uuid)->first();

        try {

            if ($user and $passer == $firmer and $user->sms_code == $codist) {

                $user->password = Hash::make($input['passer']);
                $user->save();

                $success['uuid'] = $user->uuid;
                $success['token'] = $user->createToken('Pato')->accessToken;
                return response()->json(['success' => $success], $this->successStatus);

            } else {
                $success['nota'] = 'Your password was not changed!';
                return response()->json(['success' => $success], 200);

            }
        } catch (\Exception $e) {
            $success['nota'] = $e->getMessage();
            return response()->json(['success' => $success], 500);

        }

    }

    public function getReferer(Request $request)
    {

        $input = $request->all();
        $referer = $input['reffer'];
        $referee = $input['reffee'];

        $refData = new Referrer();
        $refData->referrer = $referer;
        $refData->referree = $referee;
        $refData->notes = "active";
        $refData->amt = "100";

        if ($refData->save) {

            return response()->json(['successor' => "success"], $this->successStatus);
        } else {
            return response()->json(['successor' => "failure"], 308);
        }

    }

    public function saveToken(Request $request)
    {
        $input = $request->all();
        $utilitor = $input['uuid'];
        $tokener = $input['token'];
        $devicer = $input['devicer'];

        try {

            $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();
            // $device = Device::where('device_id',$devicer)->first();
            $tokena = Device::where('token', $tokener)->first();
            if ($tokena) {
               // $tokena->delete();
            }
            $user = Device::where('username', $utilitor)->first();
            if ($user) {
                $user->device_id = $devicer;
                $user->username = $utilitor;
                $user->token = $tokener;
                $user->state = 'online';
                $user->save();

                $updated_at = date("Y-m-d H:i:s");
                $success['updated'] = $updated_at;

                //$gameDate = $database->getReference("/logout/" . $utilitor . "/backer")->set($utilitor);
                //$gameDati = $database->getReference("/logout/" . $utilitor . "/nota")->set($devicer);
                //$gameData = $database->getReference("/logout/" . $utilitor . "/updated_at")->set($updated_at);

                $success['token'] = 'success';
                return response()->json(['success' => $success], $this->successStatus);

            } else {
                $insertToken = new Device();
                $insertToken->device_id = $devicer;
                $insertToken->username = $utilitor;
                $insertToken->token = $tokener;
                $insertToken->state = 'online';
                $insertToken->save();

                $updated_at = date("Y-m-d H:i:s");
                $success['updated'] = $updated_at;

                //$gameDate = $database->getReference("/logout/" . $utilitor . "/backer")->set($utilitor);
                //$gameDati = $database->getReference("/logout/" . $utilitor . "/nota")->set($devicer);
                //$gameData = $database->getReference("/logout/" . $utilitor . "/updated_at")->set($updated_at);

                $success['token'] = 'success';
                return response()->json(['success' => $success], $this->successStatus);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }

    }

    public function transactionDetails(Request $request)
    {
        $input = $request->all();
        $uuid = $input['uuid'];
        $offset = $input['offset'];
        $limit = $input['limit'];

        $this->setOnliner($uuid);

        try {
            $transactDet = Transaction::where('owner', $uuid)->orderBy('id', 'desc')->skip($offset)->take($limit)->get();
            $transactCount = Transaction::where('owner', $uuid)->orderBy('id', 'desc')->count();

            if ($transactDet) {
                $transactioner['transaction'] = $transactDet;
                $transactioner['count'] = $transactCount;
                return response()->json(['transactioner' => $transactioner], $this->successStatus);
            } else {
                return response()->json(['error' => 'failure'], 300);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }

    }

    public function creditDetails(Request $request)
    {
        $input = $request->all();
        $uuid = $input['uuid'];

        $this->setOnliner($uuid);

        try {

            $addDetails = Wallet::where('owner', $uuid)->where('typer', 'deposit')->sum('amount');
            $addInDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer')->sum('amount');
            $inDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer-deposit')->sum('amount');
            $outDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer-deposit')->sum('amount');
            $stakings = Wallet::where('owner', $uuid)->where('typer', 'staking')->sum('amount');
            $minusOutDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer')->sum('amount');
            $minusLossDetails = Wallet::where('owner', $uuid)->where('typer', 'losing')->sum('amount');
            $pendings = Wallet::where('owner', $uuid)->where('typer', 'pending')->sum('amount');
            $moneyDetails = ((int) $addDetails + (int) $addInDetails + (int) $inDetails) - ((int) $minusOutDetails + (int) $minusLossDetails + (int) $stakings + (int) $pendings);

            $winnings = Wallet::where('owner', $uuid)->where('typer', 'winning')->sum('amount');

            $winLossOut = Wallet::where('owner', $uuid)->where('typer', 'win-loss-out')->sum('amount');
            $winLossDraw = Wallet::where('owner', $uuid)->where('typer', 'win-loss-draw')->sum('amount');

            $success['credit'] = $moneyDetails;
            $success['balance'] = (int) $winnings - (int) $winLossOut - (int) $winLossDraw - (int) $outDetails;
            if ($success) {
                return response()->json(['success' => $success], $this->successStatus);
            } else {
                return response()->json(['success' => 'failure'], 305);

            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }

    }

    public function payment(Request $request)
    {
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
                'Authorization: Bearer sk_live_5a3bd8d3b28dfb73eda269fee5c15cbfcd4584a2']
        );
        $requesta = curl_exec($ch);
        curl_close($ch);

        if ($requesta) {
            $result = json_decode($requesta, true);
            // print_r($result);
            if ($result) {
                if ($result['data']) {
                    //something came in
                    if ($result['data']['status'] == 'success') {
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

                        $walletData = Wallet::where('owner', $userID)->where('typer', 'deposit')->get();

                        if (count($walletData) == 0) {

                            $wallet = new Wallet();
                            $wallet->owner = $userID;
                            $wallet->amount = $result['data']['amount'];
                            $wallet->typer = 'deposit';
                            $wallet->notes = 'self';

                            if ($wallet->save()) {

                                if ($result['data']['amount']) {

                                    $refData = Referrer::where('referree', $userID)->where('notes', 'active')->get();

                                    if (count($refData) > 0) {

                                        foreach ($refData as $refDatum) {
                                            $wallet = new Wallet();
                                            $wallet->owner = $refDatum->referrer;
                                            $wallet->amount = round((5 / 100 * $result['data']['amount']), 2);
                                            $wallet->typer = 'winning';
                                            $wallet->notes = 'referral';
                                            $wallet->save();
                                        }

                                    }

                                }

                                $transact = new Transaction();
                                $transact->title = 'deposit';
                                $transact->owner = $userID;
                                $transact->amount = $result['data']['amount'];
                                $transact->save();
                                return response()->json(['success' => 'success'], $this->successStatus);
                            } else {
                                return response()->json(['success' => 'failure'], 305);

                            }

                        } else {

                            $wallet = new Wallet();
                            $wallet->owner = $userID;
                            $wallet->amount = $result['data']['amount'];
                            $wallet->typer = 'deposit';
                            $wallet->notes = 'self';

                            if ($wallet->save()) {
                                $transact = new Transaction();
                                $transact->title = 'deposit';
                                $transact->owner = $userID;
                                $transact->amount = $result['data']['amount'];
                                $transact->save();
                                return response()->json(['success' => 'success'], $this->successStatus);
                            } else {
                                return response()->json(['success' => 'failure'], 305);

                            }

                        }

                    } else {
                        // the transaction was not successful, do not deliver value'
                        // print_r($result);  //uncomment this line to inspect the result, to check why it failed.
                        return response()->json(['success' => 'failure'], 300);

                        //echo "Transaction was not successful: Last gateway response was: ".$result['data']['gateway_response'];
                    }
                } else {

                    return response()->json(['success' => $result['message']], 301);
                    // echo $result['message'];
                }

            } else {
                //print_r($result);
                return response()->json(['success' => "Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable."], 302);
                // die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable.");
            }
        } else {
            //var_dump($request);
            return response()->json(['success' => "Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok"], 303);

            // die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
        }
    }

    public function setOnline(Request $request)
    {
        try {
            $input = $request->all();
            $uuid = $input['uuid'];

            $user = User::where('uuid', $input['uuid'])->first();

             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $gameDatar = $database->getReference("/users/" . $uuid . "/status")->set("online");
            $user->online_status = 'online';

            if ($user->save() and $gameDatar) {

                return response()->json(['success' => 'success'], $this->successStatus);
            } else {
                return response()->json(['error' => 'fail'], 300);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function setOffline(Request $request)
    {
        try {
            $input = $request->all();
            $uuid = $input['uuid'];

            $user = User::where('uuid', $input['uuid'])->first();

             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $gameDatar = $database->getReference("/users/" . $uuid . "/status")->set("offline");
            $user->online_status = 'offline';

            if ($user->save() and $gameDatar) {

                return response()->json(['success' => 'success'], $this->successStatus);
            } else {
                return response()->json(['error' => 'fail'], 300);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function stopGame(Request $request)
    {

        $input = $request->all();
        $acc = Device::where('username', $input['user_acc'])->first();
        if (empty($acc)) {
            return response()->json(['successor' => "Ooops, please try again."], 301);
            exit;
        }
        $to = $acc->token;
        $title = "Request Cancelled";
        $message = "Your Accepted Challenge has been cancelled.";

        $betItem = Bet::where('id', $input['bet_id'])->first();

        if ($betItem->state == "private") {
            $betItem->state = "private";
        } else {
            $betItem->state = "initiated";
            $betItem->acceptor = "";
        }

        $betItem->save();

        $datas = array(
            'updated_at' => date('Y-m-d H:i:s'),
            'nota' => 'displayStop',
            'backer' => 'cancelled',
            'user_beta' => $input['user_beta'],
            'user_acc' => $input['user_acc'],
            'bet_id' => $input['bet_id']);

        $dataz = array(
            'updated_at' => date('Y-m-d H:i:s'),
            'nota' => '',
            'backer' => '');

        $this->sendNota($to, $message, $title, $datas);
        //$this->sendData($to,$datas);
         $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
        $database = $factory->createDatabase();

        $uuid = $input['user_acc'];
        $uuidy = $input['user_beta'];
        $gameDat = $database->getReference("/startx/$uuid")->set($datas);
        $gameDatz = $database->getReference("/startz/$uuid")->set($datas);
        $gameDat = $database->getReference("/startx/$uuidy")->set($datas);
        $gameDatz = $database->getReference("/startz/$uuidy")->set($datas);

        return response()->json(['successor' => "success"], $this->successStatus);

    }

    public function removeStop(Request $request)
    {

        $input = $request->all();

        $datas = array(
            'updated_at' => date('Y-m-d H:i:s'),
            'nota' => '',
            'backer' => '',
            'user_beta' => '',
            'user_acc' => '',
            'bet_id' => '');

         $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
        $database = $factory->createDatabase();

        $uuid = $input['user_own'];

        $this->setOnliner($uuid);

        $gameDat = $database->getReference("/startx/$uuid")->set($datas);

        if (isset($gameDat)) {
            return response()->json(['successor' => "success"], $this->successStatus);
        } else {
            return response()->json(['successor' => "failure"], 300);
        }

    }

    public function cancelGame(Request $request)
    {

        $input = $request->all();

        $betItem = Bet::where('id', $input['bet_id'])->first();
        $beta = Device::where('username', $input['user_beta'])->first();
        $to = $beta->token;
        $title = "Bet Game Cancelled";
        $message = "Your Challenge has been cancelled.";

        $betItem->state = "cancelled";

        $this->setOnliner($input['user_beta']);

        if ($betItem->save()) {
            $receiver = Wallet::where('notes', $input['bet_id'])->first();

            $receiver->amount = $betItem->amt;
            $receiver->typer = 'cancelled';
            $receiver->owner = $input['user_beta'];
            $receiver->notes = $betItem->id;
            $receiver->save();

            $transact = new Transaction();
            $transact->title = 'cancelled a bet';
            $transact->owner = $input['user_beta'];
            $transact->amount = $betItem->amt;
            $transact->save();

            return response()->json(['successor' => "success"], $this->successStatus);
        } else {
            return response()->json(['successor' => "failure"], 308);
        }

    }

    public function cancelGamer($bet_id,$user_beta)
    {

        $betItem = Bet::where('id', $bet_id)->first();
        $beta = Device::where('username', $user_beta)->first();
        $to = $beta->token;
        $title = "Bet Game Cancelled";
        $message = "Your Challenge has been cancelled.";

        $betItem->state = "cancelled";

      //  $this->setOnliner($user_beta);

        if ($betItem->save()) {
            $receiver = Wallet::where('notes', $bet_id)->first();

            $receiver->amount = $betItem->amt;
            $receiver->typer = 'cancelled';
            $receiver->owner = $user_beta;
            $receiver->notes = $betItem->id;
            $receiver->save();

            return true;
        } else {
            return false;
        }

    }

    public function startGameX(Request $request)
    {
        try {

            $input = $request->all();

            $betItem = Bet::where('id', $input['bet_id'])->first();
            $beta = Device::where('username', $input['user_beta'])->first();
            $to = $beta->token;
            $title = "Bet Game Started";
            $message = "You have been Challenged by " . $input['user_acc'];

            $uuid = $input['user_acc'];
            $uuidx = $input['user_beta'];

            $addDetails = Wallet::where('owner', $uuid)->where('typer', 'deposit')->sum('amount');
            $addInDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer')->sum('amount');
            $inDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer-deposit')->sum('amount');
            $outDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer-deposit')->sum('amount');
            $winnings = Wallet::where('owner', $uuid)->where('typer', 'winning')->sum('amount');
            $stakings = Wallet::where('owner', $uuid)->where('typer', 'staking')->sum('amount');
            $minusOutDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer')->sum('amount');
            $minusLossDetails = Wallet::where('owner', $uuid)->where('typer', 'losing')->sum('amount');
            $pendings = Wallet::where('owner', $uuid)->where('typer', 'pending')->sum('amount');
            $moneyDetails = ((int) $addDetails + (int) $addInDetails + (int) $inDetails) - ((int) $minusOutDetails + (int) $minusLossDetails + (int) $stakings + (int) $pendings);

            // $betData = Bet::where('uuid',$uuid)->where('state','initiated')->orWhere('state','private')->where('amt','!=','0')->where('uuid',$uuid)->sum('amt');

            $freeData = FreeGame::where('player_one', $uuid)->where('state', date('Y-m-d'))->count();
            $freeDatax = FreeGame::where('player_two', $uuidx)->where('state', date('Y-m-d'))->count();

            if ($freeData >= 5 and $betItem->amt == 0) {
                //  return response()->json(['successor' => "busy"], 300);
            }
            if ($freeDatax >= 5 and $betItem->amt == 0) {
                //  return response()->json(['successor' => "busy"], 302);
            }

            if ((int) $moneyDetails < (int) $betItem->amt) {

                return response()->json(['successor' => "busy"], 303);
            } else {
                //$betItem->state = "initiate";
                //$betItem->save();
                $datas = array(
                    'updated_at' => date('Y-m-d H:i:s'),
                    'nota' => 'displayConfirm',
                    'backer' => 'started',
                    'gamer' => 'true',
                    'user_beta' => $input['user_beta'],
                    'user_acc' => $input['user_acc'],
                    'bet_amt' => $betItem->amt,
                    'bet_game' => $betItem->game,
                    'bet_id' => $input['bet_id']);

                $this->sendNota($to, $message, $title, $datas);
                //$this->sendData($to,$datas);

                 $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
                $database = $factory->createDatabase();

                $uuid = $input['user_beta'];
                $gameDato = $database->getReference("/startx/" . $uuid . "/backer")->getSnapShot()->getValue();
                $gameDatTime = $database->getReference("/startx/" . $uuid . "/updated_at")->getSnapShot()->getValue();

                if (isset($gameDato)) {
                    switch ($gameDato) {
                        case "started":
                            if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 20) {
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                            } else {
                                return response()->json(['successor' => "busy"], 300);
                            }
                            break;
                        case "confirmed":
                            if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 20) {
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                            } else {
                                return response()->json(['successor' => "busy"], 300);
                            }
                            break;
                        case "ongoing":
                            if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 3600) {
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                            } else {
                                return response()->json(['successor' => "busy"], 300);
                            }
                            break;
                        case "move":
                            if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 3600) {
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                            } else {
                                return response()->json(['successor' => "busy"], 300);
                            }
                            break;
                        case "cancelled":
                            $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                            return response()->json(['successor' => "success"], $this->successStatus);
                            break;
                        case "game_over":
                            $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                            return response()->json(['successor' => "success"], $this->successStatus);
                            break;
                        case "game_over":
                            $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                            return response()->json(['successor' => "success"], $this->successStatus);
                            break;
                        case "":
                            $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                            return response()->json(['successor' => "success"], $this->successStatus);
                            break;
                        default:
                            return response()->json(['successor' => "busy"], 300);
                    }

                } else {

                    return response()->json(['successor' => "busy"], 300);
                }

            }

        } catch (\Exception $e) {
            return response()->json(['successor' => $e->getMessage()], 500);
        }

    }

    public function startGame(Request $request)
    {

        try {

            $input = $request->all();

            $betItem = Bet::where('id', $input['bet_id'])->first();
            $beta = Device::where('username', $input['user_beta'])->first();
            $to = $beta->token;
            $title = "Bet Game Started";
            $message = "Your Accepted Challenge has been initiated by " . $input['user_acc'];
            $uuid = $input['user_acc'];
            $uuidx = $input['user_beta'];

            $addDetails = Wallet::where('owner', $uuid)->where('typer', 'deposit')->sum('amount');
            $addInDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer')->sum('amount');
            $inDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer-deposit')->sum('amount');
            $outDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer-deposit')->sum('amount');
            $stakings = Wallet::where('owner', $uuid)->where('typer', 'staking')->sum('amount');
            $minusOutDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer')->sum('amount');
            $minusLossDetails = Wallet::where('owner', $uuid)->where('typer', 'losing')->sum('amount');
            $pendings = Wallet::where('owner', $uuid)->where('typer', 'pending')->sum('amount');
            $moneyDetails = ((int) $addDetails + (int) $addInDetails + (int) $inDetails) - ((int) $minusOutDetails + (int) $minusLossDetails + (int) $stakings + (int) $pendings);

            $betDataX = Bet::where('id', $input['bet_id'])->where('state', 'cancelled')->first();
            if (isset($betDataX)) {
                return response()->json(['successor' => "busy"], 300);
            }

            $freeData = FreeGame::where('player_one', $uuid)->where('state', date('Y-m-d'))->count();
            $freeDatax = FreeGame::where('player_two', $uuidx)->where('state', date('Y-m-d'))->count();

            if ($freeData >= 5 and $betItem->amt == 0) {
                //  return response()->json(['successor' => "busy"], 300);
            }
            if ($freeDatax >= 5 and $betItem->amt == 0) {
                //   return response()->json(['successor' => "busy"], 302);
            }

            if ((int) $moneyDetails < (int) $betItem->amt) {

                return response()->json(['successor' => "busy"], 303);
            } else {

                //$betItem->state = "initiate";
                //$betItem->save();
                $datas = array(
                    'updated_at' => date('Y-m-d H:i:s'),
                    'nota' => 'displayConfirm',
                    'backer' => 'started',
                    'user_beta' => $input['user_beta'],
                    'user_acc' => $input['user_acc'],
                    'bet_amt' => $betItem->amt,
                    'bet_game' => $betItem->game,
                    'bet_id' => $input['bet_id']);

                $this->sendNota($to, $message, $title, $datas);
                //$this->sendData($to,$datas);

                 $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
                $database = $factory->createDatabase();

                $uuid = $input['user_beta'];
                $gameDato = $database->getReference("/startx/" . $uuid . "/backer")->getValue();
                $gameDatTime = $database->getReference("/startx/" . $uuid . "/updated_at")->getValue();

                if (isset($gameDato)) {
                    switch ($gameDato) {
                        case "started":
                            if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 20) {
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                            } else {
                                return response()->json(['successor' => "busy"], 300);
                            }
                            break;
                        case "confirmed":
                            if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 20) {
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                            } else {
                                return response()->json(['successor' => "busy"], 300);
                            }
                            break;
                        case "ongoing":
                            if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 20) {  // 3600
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                            } else {
                                return response()->json(['successor' => "busy"], 300);
                            }
                            break;
                        case "move":
                            if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 20) {  // 3600
                                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                                return response()->json(['successor' => "success"], $this->successStatus);
                            } else {
                                return response()->json(['successor' => "busy"], 300);
                            }
                            break;
                        case "cancelled":
                            $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                            return response()->json(['successor' => "success"], $this->successStatus);
                            break;
                        case "game-over":
                            $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                            return response()->json(['successor' => "success"], $this->successStatus);
                            break;
                        case "game_over":
                            $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                            return response()->json(['successor' => "success"], $this->successStatus);
                            break;
                        case "":
                            $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                            return response()->json(['successor' => "success"], $this->successStatus);
                            break;
                        default:
                            return response()->json(['successor' => "busy"], 300);
                    }

                } else {

                    return response()->json(['successor' => "busy"], 300);
                }

            }

        } catch (\Exception $e) {
            return response()->json(['successor' => $e->getMessage()], 500);
        }

    }

    public function confirmStart(Request $request)
    {
        try {
            $input = $request->all();

            $betItem = Bet::where('id', $input['bet_id'])->first();
            $beta = Device::where('username', $input['user_beta'])->first();
            $acc = Device::where('username', $input['user_acc'])->first();
            $to = $beta->token;
            $title = "Bet Game Confirmed";
            $message = "Your Challenge Start has been confirmed by " . $input['user_acc'];
            $uuid = $input['user_beta'];
            $user_player = $input['user_player'];

            $addDetails = Wallet::where('owner', $uuid)->where('typer', 'deposit')->sum('amount');
            $addInDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer')->sum('amount');
            $inDetails = Wallet::where('owner', $uuid)->where('typer', 'in-transfer-deposit')->sum('amount');
            $outDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer-deposit')->sum('amount');
            $stakings = Wallet::where('owner', $uuid)->where('typer', 'staking')->sum('amount');
            $minusOutDetails = Wallet::where('owner', $uuid)->where('typer', 'out-transfer')->sum('amount');
            $minusLossDetails = Wallet::where('owner', $uuid)->where('typer', 'losing')->sum('amount');
            $pendings = Wallet::where('owner', $uuid)->where('typer', 'pending')->sum('amount');
            $moneyDetails = ((int) $addDetails + (int) $addInDetails + (int) $inDetails) - ((int) $minusOutDetails + (int) $minusLossDetails + (int) $stakings + (int) $pendings);

            // $betData = Bet::where('uuid',$uuid)->where('state','initiated')->orWhere('state','private')->where('amt','!=','0')->where('uuid',$uuid)->sum('amt');

             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
                $database = $factory->createDatabase();
            $gameDatTime = $database->getReference("/startx/" . $user_player . "/updated_at")->getValue();
            if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 30) {

                return response()->json(['successor' => "Ooops, please try again."], 300);
            }

            $betDataX = Bet::where('id', $input['bet_id'])->where('state', 'cancelled')->first();
            if (isset($betDataX)) {
                return response()->json(['successor' => "busy"], 300);
            }

            if ((int) $moneyDetails < (int) $betItem->amt and (int) $pendings < (int) $betItem->amt) {

                return response()->json(['successor' => "busy"], 308);
            } else {

                $datas = array(
                    'updated_at' => date('Y-m-d H:i:s'),
                    'nota' => 'startGame',
                    'backer' => 'confirmed',
                    'gamer' => 'true',
                    'user_beta' => $input['user_beta'],
                    'user_acc' => $input['user_acc'],
                    'bet_id' => $input['bet_id'],
                );

                $dataz = array(
                    'updated_at' => date('Y-m-d H:i:s'),
                    'nota' => '',
                    'backer' => '',
                    'gamer' => 'true',
                    'user_beta' => $input['user_beta'],
                    'user_acc' => $input['user_acc'],
                    'bet_id' => $input['bet_id'],
                );

                $this->sendNota($to, $message, $title, $datas);
                //$this->sendData($to,$datas);

                 $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
                $database = $factory->createDatabase();

                if ($user_player == $input['user_acc']) {
                    $uuid = $input['user_beta'];
                } else {
                    $uuid = $input['user_acc'];
                }

                $gameDat = $database->getReference("/startx/$uuid")->set($datas);
                $gameDatx = $database->getReference("/startz/$uuid")->set($datas);

                $success['user_beta'] = $input['user_beta'];
                $success['user_acc'] = $input['user_acc'];
                $success['bet_id'] = $input['bet_id'];
                $success['backer'] = 'confirmed';

                if ($gameDat and $gameDatx) {
                    return response()->json(['successor' => "success"], $this->successStatus);
                } else {
                    return response()->json(['successor' => "failure"], 300);
                }

            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }

    }

    public function gameStart(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $betItem = Bet::where('id', $input['bet_id'])->first();
            $beta = Device::where('username', $input['user_beta'])->first();
            $acepta = Device::where('username', $input['user_acc'])->first();

            $to = $beta->token;
            $tu = $acepta->token;
            $title = "Bet Game Ongoing";
            $message = "Your Challenge Game is Ongoing";

            $user_player = $input['user_player'];
            $uuid = $input['user_beta'];
            $uuidy = $input['user_acc'];

            $game_onx = GameOn::where("bet_no", $input['bet_id'])->first();

            $gameDatTime = $database->getReference("/startx/" . $user_player . "/updated_at")->getValue();
            if (abs(strtotime(date('Y-m-d H:i:s')) - strtotime($gameDatTime)) > 100) {

                return response()->json(['gamer' => "Ooops, please try again."], 300);
            }

            if ($game_onx) {
                return response()->json(['gamer' => 'already started'], 200);
            } else {

            }

            if ($betItem->state == "ongoing") {
                // return response()->json(['gamer' => "Ooops, please try again."], 301);
            }

            $betItem->acceptor = $input['user_acc'];
            $betItem->state = "ongoing";
            $betItem->save();

            $market = [];
            $play = [];
            $one = [];
            $two = [];

            //  $market[] = "cz1";
            //  $market[] = "cz2";
            $market[] = "cz3";
            $market[] = "cz4";
            $market[] = "cz5";
            $market[] = "cz7";
            $market[] = "cz8";
            $market[] = "cz10";
            $market[] = "cz11";
            $market[] = "cz12";
            $market[] = "cz13";
            //    $market[] = "cz14";

            //   $market[] = "kz1";
            //   $market[] = "kz2";
            $market[] = "kz3";
            $market[] = "kz5";
            $market[] = "kz7";
            $market[] = "kz10";
            $market[] = "kz11";
            $market[] = "kz13";
            //   $market[] = "kz14";

            //   $market[] = "sqz1";
            //   $market[] = "sqz2";
            $market[] = "sqz3";
            $market[] = "sqz5";
            $market[] = "sqz7";
            $market[] = "sqz10";
            $market[] = "sqz11";
            $market[] = "sqz13";
            //   $market[] = "sqz14";

            //  $market[] = "stz1";
            //  $market[] = "stz2";
            $market[] = "stz3";
            $market[] = "stz4";
            $market[] = "stz5";
            $market[] = "stz7";
            $market[] = "stz8";

            //    $market[] = "tz1";
            //    $market[] = "tz2";
            $market[] = "tz3";
            $market[] = "tz4";
            $market[] = "tz5";
            $market[] = "tz7";
            $market[] = "tz8";
            $market[] = "tz10";
            $market[] = "tz11";
            $market[] = "tz12";
            $market[] = "tz13";
            //   $market[] = "tz14";

            //   $market[] = "wz20";
            //   $market[] = "wz21";
            //   $market[] = "wz22";
            //   $market[] = "wz23";
            //   $market[] = "wz24";

            shuffle($market);
            shuffle($market);
            shuffle($market);

            $one[] = array_pop($market);
            $one[] = array_pop($market);
            $one[] = array_pop($market);
            $one[] = array_pop($market);
            $one[] = array_pop($market);

            $two[] = array_pop($market);
            $two[] = array_pop($market);
            $two[] = array_pop($market);
            $two[] = array_pop($market);
            $two[] = array_pop($market);

            $play[] = array_pop($market);

            $playCard = $play[0];
            $playCardArr = explode("z", $playCard);

            $game_ons = new GameOn();

            if (substr($playCard, 0, 1) == "w") {
                $turnCounter = "player1";
                $game_ons->timer_count_one = time();
            } else if ((int) $playCardArr[1] == 14) {
                $two[] = array_pop($market);
                $turnCounter = "player1";
                $game_ons->timer_count_one = time();
            } else if ((int) $playCardArr[1] == 2) {
                $two[] = array_pop($market);
                $two[] = array_pop($market);
                $turnCounter = "player1";
                $game_ons->timer_count_one = time();
            } else if ((int) $playCardArr[1] == 1) {
                $turnCounter = "player1";
                $game_ons->timer_count_one = time();
            } else {
                $turnCounter = "player2";
                $game_ons->timer_count_two = time();
            }

            $game_ons->player1 = $input['user_beta'];
            $game_ons->player2 = $input['user_acc'];
            $game_ons->game_type = $betItem->game;
            $game_ons->market_deck = json_encode($market, JSON_FORCE_OBJECT);
            $game_ons->player1_deck = json_encode($one, JSON_FORCE_OBJECT);
            $game_ons->player2_deck = json_encode($two, JSON_FORCE_OBJECT);
            $game_ons->timer_one = "300";
            $game_ons->timer_two = "300";
            $game_ons->play_deck = json_encode($play, JSON_FORCE_OBJECT);
            $game_ons->game_status = "active";
            $game_ons->turn = $turnCounter;
            $game_ons->amt = $betItem->amt;
            $game_ons->bet_no = $betItem->id;
            $game_ons->pend = "false";

            if ($game_ons->save()) {
                $gameData1 = GameOn::where("bet_no", $betItem->id)->exclude(['player1_deck'])->first();
                $gameData2 = GameOn::where("bet_no", $betItem->id)->exclude(['player2_deck'])->first();
                $gameData1['market_size'] = (string) count($market);
                $gameData2['market_size'] = (string) count($market);
                $gameData1['backer'] = "initiate";
                $gameData2['backer'] = "initiate";
                $gameData1['bet_id'] = $input['bet_id'];
                $gameData2['bet_id'] = $input['bet_id'];
                $gameData1['nota'] = $betItem->game;
                $gameData2['nota'] = $betItem->game;
                $gameData1['one_size'] = "" . count($one);
                $gameData1['two_size'] = "" . count($two);
                $gameData2['one_size'] = "" . count($one);
                $gameData2['two_size'] = "" . count($two);
                $gameData1['updated_at'] = date('Y-m-d H:i:s');
                $gameData2['updated_at'] = date('Y-m-d H:i:s');

                $gamer_id = $game_ons->id;
                $success['updated_at'] = date('Y-m-d H:i:s');
                $success['nota'] = "";
                $success['note'] = "";
                $success['noti'] = "";
                $success['noto'] = "";
                $success['notu'] = "";
                $success['noty'] = "";
                $success['uuid'] = $input['user_acc'];
                $success['backer'] = 'move';
                $success['player_one'] = "false";
                $success['player_two'] = "false";

                $datas = array(
                    'updated_at' => '',
                    'nota' => '',
                    'backer' => '',
                    'user_beta' => '',
                    'user_acc' => '',
                    'bet_id' => '',
                );

                $dataz = array(
                    'backer' => 'ongoing',
                    'updated_at' => '' . date('Y-m-d H:i:s'),
                    'nota' => 'active',
                    'user_beta' => '',
                    'user_acc' => '',
                    'check_timer' => 'false',
                    'bet_id' => '' . $betItem->id,
                );

                $gameDati = $database->getReference("/startx/$uuid")->set($dataz);
                $gameDatxi = $database->getReference("/startx/$uuidy")->set($dataz);

                $database->getReference("/startz/$uuid")->set($gameData2);
                $database->getReference("/startz/$uuidy")->set($gameData1);

                $database->getReference("/gameons/playx$gamer_id")->update(["play0" => $success]);

                if ($gameData1->amt == 0) {
                    $freeGame = new FreeGame();
                    $freeGame->game = $gameData1->game_type;
                    $freeGame->player_one = $gameData1->player1;
                    $freeGame->player_two = $gameData1->player2;
                    $freeGame->state = date('Y-m-d');
                    $freeGame->save();

                    return response()->json(['gamer' => 'success'], $this->successStatus);
                } else {
                    return response()->json(['gamer' => 'second save failure'], 200);
                }

            } else {
                return response()->json(['gamer' => 'first save failure'], 200);
            }
        } catch (\Exception $e) {

            $err = $e->getMessage();
            return response()->json(['gamer' => $err], 500);
        }

    }

    public function startTimer(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();

            $user_player = $input['user_player'];
            $uuid = $input['user_beta'];
            $uuidy = $input['user_acc'];
            $bet_no = $input['bet_id'];

            $game_onx = GameOn::where("bet_no", $bet_no)->first();

            $gameDB = $database->getReference("/startz/$uuid")->getSnapshot();

            $gameDatRes = $database->getReference("/gameons/playx$game_id")->limitToLast(1)->getSnapshot()->getKey();

            $playDB = $database->getReference("/playx$game_onx->id")->getSnapshot();

            if ($user_player == $gameDB->getChild('player1')->getValue()) {
                $playDBOne = $playDB->getChild($gameDatRes . "/player_one")->set("true");
                $playDBTwo = $playDB->getChild($gameDatRes . "/player_two")->getValue();

                if ($playDBOne and $playDBTwo == "true") {
                    if ($user_player == $gameDB->getChild('turn')->getValue()) {

                        $playDeck = (array) json_decode($gameDB->getChild('play_deck')->getValue(), true);
                        $play = $play[0];

                        $playCard = $play[0];
                        $playCardArr = explode("z", $playCard);

                        if (substr($playCard, 0, 1) == "w") {

                            $game_onsx->timer_count_one = time();
                        } else if ((int) $playCardArr[1] == 14) {

                            $game_onsx->timer_count_one = time();
                        } else if ((int) $playCardArr[1] == 2) {

                            $game_onsx->timer_count_one = time();
                        } else if ((int) $playCardArr[1] == 1) {

                            $game_onsx->timer_count_one = time();
                        } else {

                            $game_onsx->timer_count_two = time();
                        }

                        $game_onsx->save();

                    }

                    return response()->json(['gamer' => 'success'], 200);
                } else {

                    return response()->json(['gamer' => 'failure'], 200);
                }

            } else {

                $playDBTwo = $playDB->getChild($gameDatRes . "/player_two")->set("true");
                $playDBOne = $playDB->getChild($gameDatRes . "/player_one")->getValue();

                if ($playDBTwo and $playDBOne == "true") {

                    return response()->json(['gamer' => 'success'], 200);
                } else {

                    return response()->json(['gamer' => 'failure'], 200);
                }

            }

        } catch (\Exception $e) {

            $err = $e->getMessage();
            return response()->json(['gamer' => $err], 500);
        }

    }

    public function playWhot(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $whot_string = $input['whot_string'];
            $game_id = $input['game_id'];
            $timer = $input['timer'];
            $counter = date("His");

            $popData = "";
            $popData1 = "";
            $popData2 = "";

            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();

            $gameStartxDB = $database->getReference("startx/$player_one")->getSnapShot();

            $marketur = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);
            $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);

            $lastCardArr = explode("z", end($playDeck));

            if ($user_player == $gameOneDB->getChild('player1')->getValue()) {

                $player2Deck = (array) json_decode($gameOneDB->getChild('player2_deck')->getValue(), true);

                if (!is_null($gameOneDB->getChild('player1_moves')->getValue())) {
                    $player1_moves = (array) json_decode($gameOneDB->getChild('player1_moves')->getValue(), true);
                } else {
                    $player1_moves = [];
                }
                //$player1_moves = $gameOneDB->getChild('player1_moves')->getValue();
                $player1_moves[] = $whot_string;
                $database->getReference("startz/" . $player_one . "/player1_moves")->set(json_encode($player1_moves, JSON_FORCE_OBJECT));

                $current = $gameOneDB->getChild('timer_count_one')->getValue();
                $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                $new = time();

                $difference = (int) $new - (int) $current;
                if ($difference > 30 and $check_timer == 'false') {
                    if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                    } else {
                        $timerOneDiff = (int) $difference - 30;
                        $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                    }
                } else if ($difference > 30 and $check_timer == 'true') {

                    if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                    } else {
                        $timerOneDiff = (int) $difference - 0;
                        $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                    }
                    $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                } else {

                }
                $database->getReference("startz/" . $player_one . "/timer_count_two")->set((int) $new);

                if ($gameOneDB->getChild('pend')->getValue() == "false") {
                    // $game->timer_count_two = (int)$new;  // Wrong here !!!
                }

                $database->getReference("startz/" . $player_one . "/turn")->set("player2");
                $database->getReference("startz/" . $player_one . "/pend")->set("whot1");
                $database->getReference("startz/" . $player_one . "/command_change")->set($whot_string);

                $success['updated_at'] = date('Y-m-d H:i:s');
                $success['nota'] = $whot_string;
                $success['note'] = (string) count($marketur);
                $success['noti'] = "whotter";
                $success['noto'] = $popData;
                $success['notu'] = $popData1;
                $success['noty'] = $popData2;
                $success['noth'] = json_encode($playDeck, JSON_FORCE_OBJECT);
                $success['notx'] = count($player2Deck);
                $success['uuid'] = $user_player;
                $success['backer'] = 'move';
                $success['timer'] = "" . $gameOneDB->getChild('timer_one')->getValue();
                $success['continua'] = "false";

                $gameDat = $database->getReference("/gameons/playx$game_id")->update(["play" . count($database->getReference("/gameons/playx$game_id")->getChildKeys()) => $success]);

                if ($gameDat) {
                    return response()->json(['success' => $success], $this->successStatus);
                } else {
                    $success['error'] = 'failure';
                    return response()->json(['success' => $success], 200);
                }

            } else {
                $player1Deck = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);

                if (!is_null($gameOneDB->getChild('player2_moves')->getValue())) {
                    $player2_moves = (array) json_decode($gameOneDB->getChild('player2_moves')->getValue(), true);
                } else {
                    $player2_moves = [];
                }
                // $player2_moves = $gameOneDB->getChild('player2_moves')->getValue();
                $player2_moves[] = $whot_string;
                $database->getReference("startz/" . $player_one . "/player2_moves")->set(json_encode($player2_moves, JSON_FORCE_OBJECT));

                $current = $gameOneDB->getChild('timer_count_two')->getValue();
                $currentTimerTwo = $gameOneDB->getChild('timer_two')->getValue();
                $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                $new = time();

                $difference = (int) $new - (int) $current;
                if ($difference > 30 and $check_timer == 'false') {
                    if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                    } else {
                        $timerTwoDiff = (int) $difference - 30;
                        $database->getReference("startz/" . $player_one . "/timer_two")->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                    }

                } else if ($difference > 30 and $check_timer == 'true') {
                    if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                    } else {
                        $timerTwoDiff = (int) $difference - 0;
                        $database->getReference("startz/" . $player_one . "/timer_two")->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                    }

                    $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                } else {

                }
                $database->getReference("startz/" . $player_one . "/timer_count_one")->set((int) $new);

                if ($gameOneDB->getChild('pend')->getValue() == "false") {
                    //  $game->timer_count_one = (int)$new;
                }

                $database->getReference("startz/" . $player_one . "/turn")->set("player1");
                $database->getReference("startz/" . $player_one . "/pend")->set("whot2");

                $success['updated_at'] = date('Y-m-d H:i:s');
                $success['nota'] = $whot_string;
                $success['note'] = (string) count($marketur);
                $success['noti'] = "whotter";
                $success['noto'] = $popData;
                $success['notu'] = $popData1;
                $success['noty'] = $popData2;
                $success['noth'] = json_encode($playDeck, JSON_FORCE_OBJECT);
                $success['notx'] = count($player1Deck);
                $success['uuid'] = $user_player;
                $success['backer'] = 'move';
                $success['timer'] = "" . $gameOneDB->getChild('timer_two')->getValue();
                $success['continua'] = "false";

                $gameDat = $database->getReference("/gameons/playx$game_id")->update(["play" . count($database->getReference("/gameons/playx$game_id")->getChildKeys()) => $success]);

                if ($gameDat) {
                    return response()->json(['success' => $success], $this->successStatus);
                } else {
                    $success['error'] = 'failure';
                    return response()->json(['success' => $success], 200);
                }

            }

        } catch (\Exception $e) {

            $err = $e->getMessage();
            $success['error'] = $err;
            return response()->json(['success' => $success], 500);
        }

    }

    public function playMark(Request $request)
    {

        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $play_deck_one = $input['play_deck_one'];
            $play_deck_two = $input['play_deck_two'];
            $mark_string = $input['market_string'];
            $game_id = $input['game_id'];
            $timer = $input['timer'];
            $counter = date("His");
            $card = '';
            $popData = "";
            $popData1 = "";
            $popData2 = "";
            $gameOverMsg = "";

            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameTwoDB = $database->getReference("startz/$player_two")->getSnapShot();

            $gameStartxDB = $database->getReference("startx/$player_one")->getSnapShot();

            //$marketir = $gameOneDB->getChild('play_deck')->getValue();

            //return response()->json(['success' => $marketir], $this->successStatus);
            $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);

            $lastCardArr = explode("z", end($playDeck));
            $marketir = [];
            if ($gameOneDB->getChild('game_status')->getValue() != 'game_over' or $gameOneDB->getChild('game_status')->getValue() != 'game-over') {

                if ($gameOneDB) {

                    //$marketir = $database->getReference("startz/".$player_one."/market_deck")->getSnapShot()->getValue();
                    // $marketir = (array)json_decode((string)$gameOneDB->getChild('market_deck')->getValue(),true);
                    $marketir = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);
                    $playUnus = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
                    $playDuus = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);

                    $popData = "";
                    $popData1 = "";
                    $popData2 = "";

                    if ($user_player == $gameOneDB->getChild('player1')->getValue()) {

                        $card = array_pop($marketir);
                        if (!is_null($gameOneDB->getChild('player1_moves')->getValue())) {
                            $player1_moves = (array) json_decode($gameOneDB->getChild('player1_moves')->getValue(), true);
                        } else {
                            $player1_moves = [];
                        }

                        $player1_moves[] = $mark_string . '-' . $card;
                        $playUnus[] = $card;

                        $database->getReference('startz/' . $player_one . '/market_deck')->set(json_encode($marketir, JSON_FORCE_OBJECT));
                        $database->getReference('startz/' . $player_one . '/player1_moves')->set(json_encode($player1_moves, JSON_FORCE_OBJECT));
                        $database->getReference('startz/' . $player_one . '/player1_deck')->set(json_encode($playUnus, JSON_FORCE_OBJECT));

                        $current = $gameOneDB->getChild('timer_count_one')->getValue();
                        $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                        $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                        $new = time();

                        $difference = (int) $new - (int) $current;
                        if ($difference > 30 and $check_timer == 'false') {
                            if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                            } else {
                                $timerOneDiff = (int) $difference - 30;
                                $database->getReference("startz/" . $player_one . "/timer_one")->set((int) $currentTimerOne - (int) $timerOneDiff);
                            }

                        } else if ($difference > 30 and $check_timer == 'true') {
                            if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                            } else {
                                $timerOneDiff = (int) $difference - 0;
                                $database->getReference("startz/" . $player_one . "/timer_one")->set((int) $currentTimerOne - (int) $timerOneDiff);
                            }

                            $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                        } else {

                        }
                        $database->getReference("startz/" . $player_one . "/timer_count_two")->set((int) $new);
                        if ($gameOneDB->getChild('pend')->getValue() == "false") {
                            // $game->timer_count_two = (int)$new;
                        }

                        $database->getReference("startz/" . $player_one . "/turn")->set("player2");
                        $database->getReference("startz/" . $player_one . "/pend")->set("mark1");
                        $timerOne = $gameOneDB->getChild('timer_one')->getValue();
                        if ((int) $timerOne <= 0) {
                            $gameOverMsg = "Time Up!";
                            if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);
                            } else {
                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);
                            }

                            $amount = $gameOneDB->getChild('amt')->getValue();

                            $receiverX = new Wallet();
                            $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $receiverX->typer = 'winning';
                            $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiverX->save();

                            $receiver = new Wallet();
                            $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                            $receiver->typer = 'staking';
                            $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiver->save();

                            $transact = new Transaction();
                            $transact->title = 'won a bet';
                            $transact->owner = $gameOneDB->getChild('player2')->getValue();
                            $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $transact->save();

                            $transact = new Transaction();
                            $transact->title = 'lost a bet';
                            $transact->owner = $gameOneDB->getChild('player1')->getValue();
                            $transact->amount = $gameOneDB->getChild('amt')->getValue();
                            $transact->save();

                            $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            $betDta->state = 'finished';
                            $betDta->save();

                            $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                            if ($winnerLoss) {
                                $receiverQ = new Wallet();
                                $receiverQ->amount = $winnerLoss->amount;
                                $receiverQ->typer = 'win-loss-out';
                                $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                $receiverQ->save();

                            }

                            $timerTwox = $gameOneDB->getChild('timer_two')->getValue();

                            $turner = $gameOneDB->getChild('turn')->getValue();
                            $pender = $gameOneDB->getChild('pend')->getValue();

                            $game_ons = GameOn::where("id", $game_id)->first();

                            $game_ons->market_deck = json_encode($marketir, JSON_FORCE_OBJECT);
                            $game_ons->player1_deck = json_encode($playUnus, JSON_FORCE_OBJECT);
                            $game_ons->player2_deck = json_encode($playDuus, JSON_FORCE_OBJECT);
                            $game_ons->timer_one = $timerOne;
                            $game_ons->timer_two = $timerTwox;
                            $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $game_ons->game_status = "game_over";
                            $game_ons->turn = $turner;
                            $game_ons->pend = $pender;

                            $game_ons->save();

                            //  $database->getReference("/gameons/playx$game_id")->remove();

                        }

                        if (count($playUnus) == 0) {
                            $gameOverMsg = "Last Card!";
                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "winner" => $player_one,
                                    "loser" => $player_two,
                                    "game_over_msg" => "Last Card!"]);

                            $database->getReference('startx/' . $player_one)->update(
                                ["backer" => "game_over",
                                    "winner" => $player_one,
                                    "loser" => $player_two,
                                    "updated_at" => date("Y-m-d H:i:s"),
                                    "game_over_msg" => "Last Card!"]);

                            $database->getReference('startx/' . $player_two)->update(
                                ["backer" => "game_over",
                                    "winner" => $player_one,
                                    "loser" => $player_two,
                                    "updated_at" => date("Y-m-d H:i:s"),
                                    "game_over_msg" => "Last Card!"]);

                            $amount = $gameOneDB->getChild('amt')->getValue();

                            $receiverX = new Wallet();
                            $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $receiverX->typer = 'winning';
                            $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                            $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                            $receiverX->save();

                            $receiver = new Wallet();
                            $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                            $receiver->typer = 'losing';
                            $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiver->save();

                            $transact = new Transaction();
                            $transact->title = 'won a bet';
                            $transact->owner = $gameOneDB->getChild('player1')->getValue();
                            $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $transact->save();

                            $transact = new Transaction();
                            $transact->title = 'lost a bet';
                            $transact->owner = $gameOneDB->getChild('player2')->getValue();
                            $transact->amount = $gameOneDB->getChild('amt')->getValue();
                            $transact->save();

                            $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            $betDta->state = 'finished';
                            $betDta->save();

                            $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                            if ($winnerLoss) {
                                $receiverQ = new Wallet();
                                $receiverQ->amount = $winnerLoss->amount;
                                $receiverQ->typer = 'win-loss-out';
                                $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiverQ->save();

                            }

                            $timerTwox = $gameOneDB->getChild('timer_two')->getValue();

                            $turner = $gameOneDB->getChild('turn')->getValue();
                            $pender = $gameOneDB->getChild('pend')->getValue();

                            $game_ons = GameOn::where("id", $game_id)->first();

                            $game_ons->market_deck = json_encode($marketir, JSON_FORCE_OBJECT);
                            $game_ons->player1_deck = json_encode($playUnus, JSON_FORCE_OBJECT);
                            $game_ons->player2_deck = json_encode($playDuus, JSON_FORCE_OBJECT);
                            $game_ons->timer_one = $timerOne;
                            $game_ons->timer_two = $timerTwox;
                            $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $game_ons->game_status = "game_over";
                            $game_ons->turn = $turner;
                            $game_ons->pend = $pender;

                            $game_ons->save();

                            //  $database->getReference("/gameons/playx$game_id")->remove();

                        }

                        if ($gameOneDB->getChild('game_type')->getValue() == "Classic") {
                            if (count($marketir) == 0) {
                                $this->reloadMarket($game_id, $user_player, $player_one, $player_two);
                            }
                        } else {
                            if (count($marketir) == 0) {
                            //    $this->finishAndCount($game_id, $user_player, $player_one, $player_two, $play_deck_one, $play_deck_two);
                            }
                        }

                        $database->getReference('startz/' . $player_one . '/market_size')->set((string) count($marketir));

                        $success['updated_at'] = date('Y-m-d H:i:s');
                        $success['nota'] = $card;
                        $success['note'] = (string) count($marketir);
                        $success['noti'] = $mark_string;
                        $success['noto'] = $popData;
                        $success['notu'] = $popData1;
                        $success['noty'] = $popData2;
                        $success['noth'] = json_encode($playDeck, JSON_FORCE_OBJECT);
                        $success['notx'] = count($playDuus);
                        $success['notz'] = count($playUnus);
                        $success['uuid'] = $user_player;
                        $success['game_over_msg'] = $gameOverMsg;

                        if ($gameOneDB->getChild('game_status') == "game_over") {
                            $success['winner'] = $gameOneDB->getChild('winner')->getValue();
                            $success['loser'] = $gameOneDB->getChild('loser')->getValue();
                        }
                        $success['backer'] = 'move';
                        $success['timer'] = "" . $gameOneDB->getChild('timer_one')->getValue();
                        $success['continua'] = "false";

                        $gameDat = $database->getReference("/gameons/playx$game_id")->update(["play" . count($database->getReference("/gameons/playx$game_id")->getChildKeys()) => $success]);
                        if ($gameDat) {

                            return response()->json(['success' => $success], $this->successStatus);
                        } else {
                            $success['error'] = 'failure';
                            return response()->json(['success' => $success], 200);
                        }

                    } else {

                        $card = array_pop($marketir);
                        if (!is_null($gameOneDB->getChild('player2_moves')->getValue())) {
                            $player2_moves = (array) json_decode($gameOneDB->getChild('player2_moves')->getValue(), true);
                        } else {
                            $player2_moves = [];
                        }
                        // $player2_moves = $gameOneDB->getChild('player2_moves');
                        $player2_moves[] = $mark_string . '-' . $card;
                        $playDuus[] = $card;

                        $database->getReference('startz/' . $player_one . '/market_deck')->set(json_encode($marketir, JSON_FORCE_OBJECT));
                        $database->getReference('startz/' . $player_one . '/player2_moves')->set(json_encode($player2_moves, JSON_FORCE_OBJECT));
                        $database->getReference('startz/' . $player_two . '/player2_deck')->set(json_encode($playDuus, JSON_FORCE_OBJECT));

                        $current = $gameOneDB->getChild('timer_count_two')->getValue();
                        $currentTimerTwo = $gameOneDB->getChild('timer_two')->getValue();
                        $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                        $new = time();

                        $difference = (int) $new - (int) $current;
                        if ($difference > 30 and $check_timer == 'false') {
                            if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                            } else {
                                $timerTwoDiff = (int) $difference - 30;
                                $database->getReference("startz/" . $player_one . "/timer_two")->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                            }

                        } else if ($difference > 30 and $check_timer == 'true') {

                            if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                            } else {
                                $timerTwoDiff = (int) $difference - 0;
                                $database->getReference("startz/" . $player_one . "/timer_two")->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                            }
                            $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                        } else {

                        }
                        $database->getReference("startz/" . $player_one . "/timer_count_one")->set((int) $new);
                        if ($gameOneDB->getChild('pend')->getValue() == "false") {
                            // $game->timer_count_one = (int)$new;
                        }

                        $database->getReference("startz/" . $player_one . "/turn")->set("player1");
                        $database->getReference("startz/" . $player_one . "/pend")->set("mark2");

                        $timerTwo = $gameOneDB->getChild('timer_two')->getValue();
                        if ((int) $timerTwo <= 0) {
                            $gameOverMsg = "Time Up!";
                            if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                            } else {
                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                            }

                            $amount = $gameOneDB->getChild('amt')->getValue();

                            $receiverX = new Wallet();
                            $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $receiverX->typer = 'winning';
                            $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                            $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                            $receiverX->save();

                            $receiver = new Wallet();
                            $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                            $receiver->typer = 'losing';
                            $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiver->save();

                            $transact = new Transaction();
                            $transact->title = 'won a bet';
                            $transact->owner = $gameOneDB->getChild('player1')->getValue();
                            $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $transact->save();

                            $transact = new Transaction();
                            $transact->title = 'lost a bet';
                            $transact->owner = $gameOneDB->getChild('player2')->getValue();
                            $transact->amount = $gameOneDB->getChild('amt')->getValue();
                            $transact->save();

                            $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            $betDta->state = 'finished';
                            $betDta->save();

                            $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                            if ($winnerLoss) {
                                $receiverQ = new Wallet();
                                $receiverQ->amount = $winnerLoss->amount;
                                $receiverQ->typer = 'win-loss-out';
                                $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiverQ->save();

                            }

                            $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                            $turner = $gameOneDB->getChild('turn')->getValue();
                            $pender = $gameOneDB->getChild('pend')->getValue();

                            $game_ons = GameOn::where("id", $game_id)->first();

                            $game_ons->market_deck = json_encode($marketir, JSON_FORCE_OBJECT);
                            $game_ons->player1_deck = json_encode($playUnus, JSON_FORCE_OBJECT);
                            $game_ons->player2_deck = json_encode($playDuus, JSON_FORCE_OBJECT);
                            $game_ons->timer_one = $timerOnex;
                            $game_ons->timer_two = $timerTwo;
                            $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $game_ons->game_status = "game_over";
                            $game_ons->turn = $turner;
                            $game_ons->pend = $pender;

                            $game_ons->save();

                            //  $database->getReference("/gameons/playx$game_id")->remove();
                        }

                        if (count($playDuus) == 0) {
                            $gameOverMsg = "Last Card!";
                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "winner" => $player_two,
                                    "loser" => $player_one,
                                    "game_over_msg" => "Last Card!"]);

                            $database->getReference('startx/' . $player_one)->update(
                                ["backer" => "game_over",
                                    "winner" => $player_two,
                                    "loser" => $player_one,
                                    "updated_at" => date("Y-m-d H:i:s"),
                                    "game_over_msg" => "Last Card!"]);

                            $database->getReference('startx/' . $player_two)->update(
                                ["backer" => "game_over",
                                    "winner" => $player_two,
                                    "loser" => $player_one,
                                    "updated_at" => date("Y-m-d H:i:s"),
                                    "game_over_msg" => "Last Card!"]);

                            $amount = $gameOneDB->getChild('amt')->getValue();

                            $receiverX = new Wallet();
                            $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $receiverX->typer = 'winning';
                            $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiverX->save();

                            $receiver = new Wallet();
                            $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                            $receiver->typer = 'staking';
                            $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiver->save();

                            $transact = new Transaction();
                            $transact->title = 'won a bet';
                            $transact->owner = $gameOneDB->getChild('player2')->getValue();
                            $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $transact->save();

                            $transact = new Transaction();
                            $transact->title = 'lost a bet';
                            $transact->owner = $gameOneDB->getChild('player1')->getValue();
                            $transact->amount = $gameOneDB->getChild('amt')->getValue();
                            $transact->save();

                            $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            $betDta->state = 'finished';
                            $betDta->save();

                            $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                            if ($winnerLoss) {
                                $receiverQ = new Wallet();
                                $receiverQ->amount = $winnerLoss->amount;
                                $receiverQ->typer = 'win-loss-out';
                                $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                $receiverQ->save();

                            }

                            $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                            $turner = $gameOneDB->getChild('turn')->getValue();
                            $pender = $gameOneDB->getChild('pend')->getValue();

                            $game_ons = GameOn::where("id", $game_id)->first();

                            $game_ons->market_deck = json_encode($marketir, JSON_FORCE_OBJECT);
                            $game_ons->player1_deck = json_encode($playUnus, JSON_FORCE_OBJECT);
                            $game_ons->player2_deck = json_encode($playDuus, JSON_FORCE_OBJECT);
                            $game_ons->timer_one = $timerOnex;
                            $game_ons->timer_two = $timerTwo;
                            $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $game_ons->game_status = "game_over";
                            $game_ons->turn = $turner;
                            $game_ons->pend = $pender;

                            $game_ons->save();

                            //  $database->getReference("/gameons/playx$game_id")->remove();

                        }

                        if ($gameOneDB->getChild('game_type')->getValue() == "Classic") {
                            if (count($marketir) == 0) {
                                $this->reloadMarket($game_id, $user_player, $player_one, $player_two);
                            }
                        } else {
                            if (count($marketir) == 0) {
                            //    $this->finishAndCount($game_id, $user_player, $player_one, $player_two, $play_deck_one, $play_deck_two);
                            }
                        }

                        $database->getReference('startz/' . $player_one . '/market_size')->set((string) count($marketir));

                        $success['updated_at'] = date('Y-m-d H:i:s');
                        $success['nota'] = $card;
                        $success['note'] = (string) count($marketir);
                        $success['noti'] = $mark_string;
                        $success['noto'] = $popData;
                        $success['notu'] = $popData1;
                        $success['noty'] = $popData2;
                        $success['noth'] = json_encode($playDeck, JSON_FORCE_OBJECT);
                        $success['notx'] = count($playUnus);
                        $success['notz'] = count($playDuus);
                        $success['uuid'] = $user_player;
                        $success['game_over_msg'] = $gameOverMsg;
                        if ($gameOneDB->getChild('game_status') == "game_over") {
                            $success['winner'] = $gameOneDB->getChild('winner')->getValue();
                            $success['loser'] = $gameOneDB->getChild('loser')->getValue();
                        }
                        $success['backer'] = 'move';
                        $success['timer'] = "" . $gameOneDB->getChild('timer_two')->getValue();
                        $success['continua'] = "false";

                        $gameDat = $database->getReference("/gameons/playx$game_id")->update(["play" . count($database->getReference("/gameons/playx$game_id")->getChildKeys()) => $success]);
                        if ($gameDat) {

                            return response()->json(['success' => $success], $this->successStatus);
                        } else {
                            $success['error'] = 'failure';
                            return response()->json(['success' => $success], 200);
                        }

                    }

                } else {

                    if ($gameOneDB->getChild('game_type')->getValue() == "Classic") {
                        if (count($marketir) == 0) {
                            $this->reloadMarket($game_id, $user_player, $player_one, $player_two);
                        }
                    } else {
                        if (count($marketir) == 0) {
                        //    $this->finishAndCount($game_id, $user_player, $player_one, $player_two, $play_deck_one, $play_deck_two);
                        }
                    }

                    // $this->finishAndCount($game_id, $user_player, $player_one, $player_two, $play_deck_one, $play_deck_two);

                    // $database->getReference('startz/'.$player_one.'/market_size')->set((string)count($marketir));

                    $success['updated_at'] = date('Y-m-d H:i:s');
                    $success['nota'] = $card;
                    $success['note'] = "0";
                    $success['noti'] = $mark_string;
                    $success['noto'] = $popData;
                    $success['notu'] = $popData1;
                    $success['noty'] = $popData2;
                    $success['noth'] = json_encode($playDeck, JSON_FORCE_OBJECT);
                    $success['uuid'] = $user_player;
                    $success['winner'] = $gameOneDB->getChild('winner')->getValue();
                    $success['loser'] = $gameOneDB->getChild('loser')->getValue();
                    $success['backer'] = 'move';
                    $success['game_over_msg'] = $gameOverMsg;

                    $gameDat = $database->getReference("/gameons/playx$game_id")->update(["play" . count($database->getReference("/gameons/playx$game_id")->getChildKeys()) => $success]);
                    if ($gameDat) {

                        return response()->json(['success' => $success], $this->successStatus);
                    } else {
                        $success['error'] = 'failure';
                        return response()->json(['success' => $success], 200);
                    }

                }

            } else {
                $success['error'] = 'failure';
                return response()->json(['success' => $success], 200);
                exit;
            }

        } catch (\Exception $e) {

            $err = $e->getMessage();
            $success['error'] = $err;
            return response()->json(['success' => $success], 500);
        }

    }

    public function playOn(Request $request)
    {
        try {

             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $game_id = $input['game_id'];
            $cardist = trim($input['card_string']);
            $playCard = $cardist;

            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameTwoDB = $database->getReference("startz/$player_two")->getSnapShot();

            $gameStartxDB = $database->getReference("startx/$player_one")->getSnapShot();

            $playist = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
            //$playist = $database->getReference("startz/".$player_one."/play_deck")->getSnapShot()->getValue();

            $playCardArr = explode("z", $cardist);

            $lastCardArr = explode("z", end($playist));

            //$lastCardArr = explode("z",$playist[count($playist) - 1]);

            $popData = "";
            $popData1 = "";
            $popData2 = "";

            $gameOverMsg = "";

            $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
            $marketor = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);
            $playerDeck1 = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
            $playerDeck2 = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);

            if ($gameOneDB->getChild('game_status')->getValue() != 'game_over' or $gameOneDB->getChild('game_status')->getValue() != 'game-over') {

                if ($player_one == $user_player) {

                    if ($gameOneDB->getChild('turn')->getValue() == 'player1') {

                        if ($this->cardRuler($lastCardArr[0], $lastCardArr[1], $playCardArr[0], $playCardArr[1], (array) json_decode($gameOneDB->getChild('player1_moves')->getValue(), true), (array) json_decode($gameOneDB->getChild('player2_moves')->getValue(), true), $gameOneDB->getChild('command_change')->getValue(), $game_id, $user_player)) {

                            if (in_array($cardist, $playerDeck1)) {
                                $counter = date("His");

                                $playDeck[] = $cardist;
                                $indx = array_search($cardist, $playerDeck1);
                                unset($playerDeck1[$indx]);

                                if (substr($playCard, 0, 1) == "w") {
                                    $turnCounter = "player1";
                                    $database->getReference('startz/' . $player_one . '/pend')->set('move1');

                                } else if ((int) $playCardArr[1] == 14) {
                                    $popData = array_pop($marketor);
                                    $playerDeck2[] = $popData;
                                    $database->getReference('startz/' . $player_one . '/market_deck')->set(json_encode($marketor, JSON_FORCE_OBJECT));
                                    $turnCounter = "player1";
                                    $database->getReference('startz/' . $player_one . '/pend')->set('move1x');

                                } else if ((int) $playCardArr[1] == 1) {
                                    $turnCounter = "player1";
                                    $database->getReference('startz/' . $player_one . '/pend')->set('move1x');

                                } else if ((int) $playCardArr[1] == 2) {

                                    $popData1 = array_pop($marketor);
                                    $playerDeck2[] = $popData1;
                                    $database->getReference('startz/' . $player_one . '/market_deck')->set(json_encode($marketor, JSON_FORCE_OBJECT));

                                    $database->getReference('startz/' . $player_one . '/player1_deck')->set(json_encode($playerDeck1, JSON_FORCE_OBJECT));
                                    $database->getReference('startz/' . $player_two . '/player2_deck')->set(json_encode($playerDeck2, JSON_FORCE_OBJECT));
                                    $database->getReference('startz/' . $player_one . '/play_deck')->set(json_encode($playDeck, JSON_FORCE_OBJECT));

                                    $pick2first = true;

                                    if ($gameOneDB->getChild('game_type')->getValue() == "Classic") {
                                        if (count($marketor) == 0) {
                                            $this->reloadMarket($input['game_id'], $user_player, $player_one, $player_two);
                                            $marketor = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);
                                        }
                                    } else {
                                        if (count($marketor) == 0) {
                                           // $this->finishAndCount($input['game_id'], $user_player, $player_one, $player_two);
                                        }
                                    }

                                    $popData2 = array_pop($marketor);
                                    $playerDeck2[] = $popData2;
                                    $database->getReference('startz/' . $player_one . '/market_deck')->set(json_encode($marketor, JSON_FORCE_OBJECT));

                                    $turnCounter = "player1";
                                    $database->getReference('startz/' . $player_one . '/pend')->set('move1x');

                                } else {
                                    $turnCounter = "player2";
                                    $database->getReference('startz/' . $player_one . '/pend')->set('move1');

                                }

                                if ($turnCounter == "player1") {

                                    $current = $gameOneDB->getChild('timer_count_one')->getValue();
                                    $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                                    $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                                    $new = time();

                                    $difference = (int) $new - (int) $current;
                                    if ($difference > 30 and $check_timer == 'false') {
                                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                        } else {
                                            $timerOneDiff = (int) $difference - 30;
                                            $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                                        }

                                    } else if ($difference > 30 and $check_timer == 'true') {
                                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                        } else {
                                            $timerOneDiff = (int) $difference - 0;
                                            $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                                        }

                                        $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                                    } else {

                                    }

                                    $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);

                                }

                                if ($turnCounter == "player2") {

                                    $current = $gameOneDB->getChild('timer_count_one')->getValue();
                                    $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                                    $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                                    $new = time();

                                    $difference = (int) $new - (int) $current;
                                    if ($difference > 30 and $check_timer == 'false') {
                                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                        } else {
                                            $timerOneDiff = (int) $difference - 30;
                                            $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                                        }

                                    } else if ($difference > 30 and $check_timer == 'true') {
                                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                        } else {
                                            $timerOneDiff = (int) $difference - 0;
                                            $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                                        }

                                        $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                                    } else {

                                    }

                                    $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);

                                }

                                if ($gameOneDB->getChild('pend')->getValue() == "false") {
                                    //  $primaData->timer_count_two = (int)$new;
                                }

                                $database->getReference('startz/' . $player_one . '/player1_deck')->set(json_encode($playerDeck1, JSON_FORCE_OBJECT));
                                $database->getReference('startz/' . $player_two . '/player2_deck')->set(json_encode($playerDeck2, JSON_FORCE_OBJECT));
                                $database->getReference('startz/' . $player_one . '/play_deck')->set(json_encode($playDeck, JSON_FORCE_OBJECT));

                                if (!is_null($gameOneDB->getChild('player1_moves')->getValue())) {
                                    $player1_moves = (array) json_decode($gameOneDB->getChild('player1_moves')->getValue(), true);
                                } else {
                                    $player1_moves = [];
                                }

                                //$player1_moves = $gameOneDB->getChild('player1_moves')->getValue();
                                $player1_moves[] = $cardist;
                                $database->getReference('startz/' . $player_one . '/player1_moves')->set(json_encode($player1_moves, JSON_FORCE_OBJECT));

                                $database->getReference('startz/' . $player_one . '/turn')->set($turnCounter);

                                $timerOne = $gameOneDB->getChild('timer_one')->getValue();
                                if ((int) $timerOne <= 0) {
                                    $gameOverMsg = "Time Up!";
                                    if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                        $database->getReference('startz/' . $player_one)->update(
                                            ["game_status" => "game_over",
                                                "winner" => $player_one,
                                                "loser" => $player_two,
                                                "game_over_msg" => "Time Up!"]);
                                        $database->getReference('startx/' . $player_one)->update(
                                            ["backer" => "game_over",
                                                "winner" => $player_one,
                                                "loser" => $player_two,
                                                "updated_at" => date("Y-m-d H:i:s"),
                                                "game_over_msg" => "Time Up!"]);

                                        $database->getReference('startx/' . $player_two)->update(
                                            ["backer" => "game_over",
                                                "winner" => $player_one,
                                                "loser" => $player_two,
                                                "updated_at" => date("Y-m-d H:i:s"),
                                                "game_over_msg" => "Time Up!"]);
                                    } else {
                                        $database->getReference('startz/' . $player_one)->update(
                                            ["game_status" => "game_over",
                                                "winner" => $player_two,
                                                "loser" => $player_one,
                                                "game_over_msg" => "Time Up!"]);
                                        $database->getReference('startx/' . $player_one)->update(
                                            ["backer" => "game_over",
                                                "winner" => $player_two,
                                                "loser" => $player_one,
                                                "updated_at" => date("Y-m-d H:i:s"),
                                                "game_over_msg" => "Time Up!"]);

                                        $database->getReference('startx/' . $player_two)->update(
                                            ["backer" => "game_over",
                                                "winner" => $player_two,
                                                "loser" => $player_one,
                                                "updated_at" => date("Y-m-d H:i:s"),
                                                "game_over_msg" => "Time Up!"]);
                                    }

                                    $amount = $gameOneDB->getChild('amt')->getValue();

                                    $receiverX = new Wallet();
                                    $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                    $receiverX->typer = 'winning';
                                    $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                                    $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                                    $receiverX->save();

                                    $receiver = new Wallet();
                                    $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                    $receiver->typer = 'staking';
                                    $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                    $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                    $receiver->save();

                                    $transact = new Transaction();
                                    $transact->title = 'won a bet';
                                    $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                    $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                    $transact->save();

                                    $transact = new Transaction();
                                    $transact->title = 'lost a bet';
                                    $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                    $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                    $transact->save();

                                    $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                    $betDta->state = 'finished';
                                    $betDta->save();

                                    $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                    if ($winnerLoss) {
                                        $receiverQ = new Wallet();
                                        $receiverQ->amount = $winnerLoss->amount;
                                        $receiverQ->typer = 'win-loss-out';
                                        $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                        $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                        $receiverQ->save();

                                    }

                                    $timerTwox = $gameOneDB->getChild('timer_two')->getValue();

                                    $turner = $gameOneDB->getChild('turn')->getValue();
                                    $pender = $gameOneDB->getChild('pend')->getValue();

                                    $game_ons = GameOn::where("id", $game_id)->first();

                                    $game_ons->market_deck = json_encode($marketor, JSON_FORCE_OBJECT);
                                    $game_ons->player1_deck = json_encode($playerDeck1, JSON_FORCE_OBJECT);
                                    $game_ons->player2_deck = json_encode($playerDeck2, JSON_FORCE_OBJECT);
                                    $game_ons->timer_one = $timerOne;
                                    $game_ons->timer_two = $timerTwox;
                                    $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                    $game_ons->game_status = "game_over";
                                    $game_ons->turn = $turner;
                                    $game_ons->pend = $pender;

                                    $game_ons->save();

                                    //  $database->getReference("/gameons/playx$game_id")->remove();

                                }

                                if (count($playerDeck1) == 0) {
                                    $gameOverMsg = "Last Card!";
                                    $database->getReference('startz/' . $player_one)->update(
                                        ["game_status" => "game_over",
                                            "winner" => $player_one,
                                            "loser" => $player_two,
                                            "game_over_msg" => "Last Card!"]);
                                    $database->getReference('startx/' . $player_one)->update(
                                        ["backer" => "game_over",
                                            "winner" => $player_one,
                                            "loser" => $player_two,
                                            "updated_at" => date("Y-m-d H:i:s"),
                                            "game_over_msg" => "Last Card!"]);

                                    $database->getReference('startx/' . $player_two)->update(
                                        ["backer" => "game_over",
                                            "winner" => $player_one,
                                            "loser" => $player_two,
                                            "updated_at" => date("Y-m-d H:i:s"),
                                            "game_over_msg" => "Last Card!"]);

                                    $amount = $gameOneDB->getChild('amt')->getValue();

                                    $receiverX = new Wallet();
                                    $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                    $receiverX->typer = 'winning';
                                    $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                                    $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                                    $receiverX->save();

                                    $receiver = new Wallet();
                                    $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                    $receiver->typer = 'losing';
                                    $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                    $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                    $receiver->save();

                                    $transact = new Transaction();
                                    $transact->title = 'won a bet';
                                    $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                    $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                    $transact->save();

                                    $transact = new Transaction();
                                    $transact->title = 'lost a bet';
                                    $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                    $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                    $transact->save();

                                    $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                    $betDta->state = 'finished';
                                    $betDta->save();

                                    $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                    if ($winnerLoss) {
                                        $receiverQ = new Wallet();
                                        $receiverQ->amount = $winnerLoss->amount;
                                        $receiverQ->typer = 'win-loss-out';
                                        $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                        $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                        $receiverQ->save();

                                    }

                                    $timerTwox = $gameOneDB->getChild('timer_two')->getValue();

                                    $turner = $gameOneDB->getChild('turn')->getValue();
                                    $pender = $gameOneDB->getChild('pend')->getValue();

                                    $game_ons = GameOn::where("id", $game_id)->first();

                                    $game_ons->market_deck = json_encode($marketor, JSON_FORCE_OBJECT);
                                    $game_ons->player1_deck = json_encode($playerDeck1, JSON_FORCE_OBJECT);
                                    $game_ons->player2_deck = json_encode($playerDeck2, JSON_FORCE_OBJECT);
                                    $game_ons->timer_one = $timerOne;
                                    $game_ons->timer_two = $timerTwox;
                                    $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                    $game_ons->game_status = "game_over";
                                    $game_ons->turn = $turner;
                                    $game_ons->pend = $pender;

                                    $game_ons->save();

                                    // $database->getReference("/gameons/playx$game_id")->remove();

                                }

                                if ($gameOneDB->getChild('game_type')->getValue() == "Classic") {
                                    if (count($marketor) == 0) {
                                        $this->reloadMarket($input['game_id'], $user_player, $player_one, $player_two);

                                    }
                                } else {
                                    if (count($marketor) == 0) {
                                      //  $this->finishAndCount($input['game_id'], $user_player, $player_one, $player_two);

                                    }
                                }

                                $database->getReference('startz/' . $player_one . '/market_size')->set((string) count($marketor));

                                $success['updated_at'] = date('Y-m-d H:i:s');
                                $success['nota'] = trim($cardist);
                                $success['note'] = (string) count($marketor);
                                $success['noti'] = (string) count($playerDeck1);
                                $success['noto'] = $popData;
                                $success['notu'] = $popData1;
                                $success['noty'] = $popData2;
                                $success['noth'] = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $success['notx'] = count($playerDeck2);
                                $success['notz'] = count($playerDeck1);
                                $success['game_type'] = $gameOneDB->getChild('game_type')->getValue();
                                $success['uuid'] = $user_player;
                                $success['continua'] = "false";
                                $success['timer'] = "" . $gameOneDB->getChild('timer_one')->getValue();

                                $success['backer'] = 'move';
                                $success['game_over_msg'] = $gameOverMsg;

                                $gameDat = $database->getReference("/gameons/playx$game_id")->update(["play" . count($database->getReference("/gameons/playx$game_id")->getChildKeys()) => $success]);
                                $database->getReference("/startx/$player_one")->update(["continua" => "false"]);
                                $database->getReference("/startx/$player_two")->update(["continua" => "false"]);
                                if (isset($success['winner']) or isset($success['loser'])) {
                                    $uuid = $player_one;
                                    $uuidx = $player_two;
                                    $gameDaty = $database->getReference("/startx/$uuid")->set($success);
                                    $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                                }
                                if ($gameDat) {
                                    // $gameData['market_size'] = count((array)json_decode($gameData->market_deck,true));

                                    return response()->json(['success' => $success], 200);
                                    exit;
                                } else {
                                    $success['error'] = 'Error/Integrity?';
                                    return response()->json(['success' => $success], 200);
                                    exit;
                                }

                            } else {
                                $error = 'Integrity Error !';
                                $success['error'] = 'Integrity Error !';
                                return response()->json(['success' => $success], 200);
                                exit;
                            }
                        } else {
                            $success['error'] = 'Move Error !';
                            return response()->json(['success' => $success], 200);
                            exit;
                        }

                    }

                } else {

                    if ($this->cardRuler($lastCardArr[0], $lastCardArr[1], $playCardArr[0], $playCardArr[1], (array) json_decode($gameOneDB->getChild('player1_moves')->getValue(), true), (array) json_decode($gameOneDB->getChild('player2_moves')->getValue(), true), $gameOneDB->getChild('command_change')->getValue(), $game_id, $user_player)) {

                        if (in_array($cardist, $playerDeck2)) {
                            $counter = date("His");

                            $playDeck[] = $cardist;
                            $indx = array_search($cardist, $playerDeck2);
                            unset($playerDeck2[$indx]);

                            if (substr($playCard, 0, 1) == "w") {
                                $turnCounter = "player2";
                                $database->getReference('startz/' . $player_one . '/pend')->set('move2');

                            } else if ((int) $playCardArr[1] == 14) {
                                $popData = array_pop($marketor);
                                $playerDeck1[] = $popData;
                                $database->getReference('startz/' . $player_one . '/market_deck')->set(json_encode($marketor, JSON_FORCE_OBJECT));
                                $turnCounter = "player2";
                                $database->getReference('startz/' . $player_one . '/pend')->set('move2x');

                            } else if ((int) $playCardArr[1] == 1) {
                                $turnCounter = "player2";
                                $database->getReference('startz/' . $player_one . '/pend')->set('move2x');

                            } else if ((int) $playCardArr[1] == 2) {

                                $popData1 = array_pop($marketor);
                                $playerDeck1[] = $popData1;
                                $database->getReference('startz/' . $player_one . '/market_deck')->set(json_encode($marketor, JSON_FORCE_OBJECT));

                                $database->getReference('startz/' . $player_one . '/player1_deck')->set(json_encode($playerDeck1, JSON_FORCE_OBJECT));
                                $database->getReference('startz/' . $player_two . '/player2_deck')->set(json_encode($playerDeck2, JSON_FORCE_OBJECT));
                                $database->getReference('startz/' . $player_one . '/play_deck')->set(json_encode($playDeck, JSON_FORCE_OBJECT));

                                $pick2first = true;

                                if ($gameOneDB->getChild('game_type')->getValue() == "Classic") {
                                    if (count($marketor) == 0) {
                                        $this->reloadMarket($input['game_id'], $user_player, $player_one, $player_two);
                                        $marketor = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);
                                    }
                                } else {
                                    if (count($marketor) == 0) {
                                      //  $this->finishAndCount($input['game_id'], $user_player, $player_one, $player_two);
                                    }
                                }

                                $popData2 = array_pop($marketor);
                                $playerDeck1[] = $popData2;
                                $database->getReference('startz/' . $player_one . '/market_deck')->set(json_encode($marketor, JSON_FORCE_OBJECT));

                                $turnCounter = "player2";
                                $database->getReference('startz/' . $player_one . '/pend')->set('move2x');

                            } else {
                                $turnCounter = "player1";
                                $database->getReference('startz/' . $player_one . '/pend')->set('move2');

                            }

                            if ($turnCounter == "player1") {

                                $current = $gameOneDB->getChild('timer_count_two')->getValue();
                                $currentTimerTwo = $gameOneDB->getChild('timer_two')->getValue();
                                $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                                $new = time();

                                $difference = (int) $new - (int) $current;
                                if ($difference > 30 and $check_timer == 'false') {
                                    if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                    } else {
                                        $timerTwoDiff = (int) $difference - 30;
                                        $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                                    }

                                } else if ($difference > 30 and $check_timer == 'true') {
                                    if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                    } else {
                                        $timerTwoDiff = (int) $difference - 0;
                                        $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                                    }

                                    $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                                } else {

                                }

                                $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);

                            }

                            if ($turnCounter == "player2") {

                                $current = $gameOneDB->getChild('timer_count_two')->getValue();
                                $currentTimerTwo = $gameOneDB->getChild('timer_two')->getValue();
                                $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                                $new = time();

                                $difference = (int) $new - (int) $current;
                                if ($difference > 30 and $check_timer == 'false') {
                                    if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                    } else {
                                        $timerTwoDiff = (int) $difference - 30;
                                        $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                                    }

                                } else if ($difference > 30 and $check_timer == 'true') {
                                    if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                    } else {
                                        $timerTwoDiff = (int) $difference - 0;
                                        $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                                    }

                                    $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                                } else {

                                }

                                $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);

                            }

                            if ($gameOneDB->getChild('pend')->getValue() == "false") {
                                //  $primaData->timer_count_two = (int)$new;
                            }

                            $database->getReference('startz/' . $player_one . '/player1_deck')->set(json_encode($playerDeck1, JSON_FORCE_OBJECT));
                            $database->getReference('startz/' . $player_two . '/player2_deck')->set(json_encode($playerDeck2, JSON_FORCE_OBJECT));
                            $database->getReference('startz/' . $player_one . '/play_deck')->set(json_encode($playDeck, JSON_FORCE_OBJECT));

                            if (!is_null($gameOneDB->getChild('player2_moves')->getValue())) {
                                $player2_moves = (array) json_decode($gameOneDB->getChild('player2_moves')->getValue(), true);
                            } else {
                                $player2_moves = [];
                            }

                            //$player2_moves = $gameOneDB->getChild('player2_moves')->getValue();
                            $player2_moves[] = $cardist;
                            $database->getReference('startz/' . $player_one . '/player2_moves')->set(json_encode($player2_moves, JSON_FORCE_OBJECT));

                            $database->getReference('startz/' . $player_one . '/turn')->set($turnCounter);

                            $timerTwo = $gameOneDB->getChild('timer_two')->getValue();
                            if ((int) $timerTwo <= 0) {
                                $gameOverMsg = "Time Up!";
                                if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                    $database->getReference('startz/' . $player_one)->update(
                                        ["game_status" => "game_over",
                                            "winner" => $player_two,
                                            "loser" => $player_one,
                                            "game_over_msg" => "Time Up!"]);
                                    $database->getReference('startx/' . $player_one)->update(
                                        ["backer" => "game_over",
                                            "winner" => $player_two,
                                            "loser" => $player_one,
                                            "updated_at" => date("Y-m-d H:i:s"),
                                            "game_over_msg" => "Time Up!"]);

                                    $database->getReference('startx/' . $player_two)->update(
                                        ["backer" => "game_over",
                                            "winner" => $player_two,
                                            "loser" => $player_one,
                                            "updated_at" => date("Y-m-d H:i:s"),
                                            "game_over_msg" => "Time Up!"]);

                                } else {
                                    $database->getReference('startz/' . $player_one)->update(
                                        ["game_status" => "game_over",
                                            "winner" => $player_one,
                                            "loser" => $player_two,
                                            "game_over_msg" => "Time Up!"]);
                                    $database->getReference('startx/' . $player_one)->update(
                                        ["backer" => "game_over",
                                            "winner" => $player_one,
                                            "loser" => $player_two,
                                            "updated_at" => date("Y-m-d H:i:s"),
                                            "game_over_msg" => "Time Up!"]);

                                    $database->getReference('startx/' . $player_two)->update(
                                        ["backer" => "game_over",
                                            "winner" => $player_one,
                                            "loser" => $player_two,
                                            "updated_at" => date("Y-m-d H:i:s"),
                                            "game_over_msg" => "Time Up!"]);

                                }

                                $amount = $gameOneDB->getChild('amt')->getValue();

                                $receiverX = new Wallet();
                                $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $receiverX->typer = 'winning';
                                $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                                $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                                $receiverX->save();

                                $receiver = new Wallet();
                                $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                $receiver->typer = 'losing';
                                $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiver->save();

                                $transact = new Transaction();
                                $transact->title = 'won a bet';
                                $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $transact->save();

                                $transact = new Transaction();
                                $transact->title = 'lost a bet';
                                $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                $transact->save();

                                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                $betDta->state = 'finished';
                                $betDta->save();

                                $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                if ($winnerLoss) {
                                    $receiverQ = new Wallet();
                                    $receiverQ->amount = $winnerLoss->amount;
                                    $receiverQ->typer = 'win-loss-out';
                                    $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                    $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                    $receiverQ->save();

                                }

                                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                                $turner = $gameOneDB->getChild('turn')->getValue();
                                $pender = $gameOneDB->getChild('pend')->getValue();

                                $game_ons = GameOn::where("id", $game_id)->first();

                                $game_ons->market_deck = json_encode($marketor, JSON_FORCE_OBJECT);
                                $game_ons->player1_deck = json_encode($playerDeck1, JSON_FORCE_OBJECT);
                                $game_ons->player2_deck = json_encode($playerDeck2, JSON_FORCE_OBJECT);
                                $game_ons->timer_one = $timerOnex;
                                $game_ons->timer_two = $timerTwo;
                                $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $game_ons->game_status = "game_over";
                                $game_ons->turn = $turner;
                                $game_ons->pend = $pender;

                                $game_ons->save();

                                // $database->getReference("/gameons/playx$game_id")->remove();

                            }

                            if (count($playerDeck2) == 0) {
                                $gameOverMsg = "Last Card!";
                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "game_over_msg" => "Last Card!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Last Card!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Last Card!"]);

                                $amount = $gameOneDB->getChild('amt')->getValue();

                                $receiverX = new Wallet();
                                $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $receiverX->typer = 'winning';
                                $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiverX->save();

                                $receiver = new Wallet();
                                $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                $receiver->typer = 'staking';
                                $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiver->save();

                                $transact = new Transaction();
                                $transact->title = 'won a bet';
                                $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $transact->save();

                                $transact = new Transaction();
                                $transact->title = 'lost a bet';
                                $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                $transact->save();

                                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                $betDta->state = 'finished';
                                $betDta->save();

                                $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                if ($winnerLoss) {
                                    $receiverQ = new Wallet();
                                    $receiverQ->amount = $winnerLoss->amount;
                                    $receiverQ->typer = 'win-loss-out';
                                    $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                    $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                    $receiverQ->save();

                                }

                                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                                $turner = $gameOneDB->getChild('turn')->getValue();
                                $pender = $gameOneDB->getChild('pend')->getValue();

                                $game_ons = GameOn::where("id", $game_id)->first();

                                $game_ons->market_deck = json_encode($marketor, JSON_FORCE_OBJECT);
                                $game_ons->player1_deck = json_encode($playerDeck1, JSON_FORCE_OBJECT);
                                $game_ons->player2_deck = json_encode($playerDeck2, JSON_FORCE_OBJECT);
                                $game_ons->timer_one = $timerOnex;
                                $game_ons->timer_two = $timerTwo;
                                $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $game_ons->game_status = "game_over";
                                $game_ons->turn = $turner;
                                $game_ons->pend = $pender;

                                $game_ons->save();

                                // $database->getReference("/gameons/playx$game_id")->remove();

                            }

                            if ($gameOneDB->getChild('game_type')->getValue() == "Classic") {
                                if (count($marketor) == 0) {
                                    $this->reloadMarket($input['game_id'], $user_player, $player_one, $player_two);

                                }
                            } else {
                                if (count($marketor) == 0) {
                                   // $this->finishAndCount($input['game_id'], $user_player, $player_one, $player_two);

                                }
                            }

                            $database->getReference('startz/' . $player_one . '/market_size')->set((string) count($marketor));

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['nota'] = trim($cardist);
                            $success['note'] = (string) count($marketor);
                            $success['noti'] = (string) count($playerDeck2);
                            $success['noto'] = $popData;
                            $success['notu'] = $popData1;
                            $success['noty'] = $popData2;
                            $success['noth'] = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $success['notx'] = count($playerDeck1);
                            $success['notz'] = count($playerDeck2);
                            $success['game_type'] = $gameOneDB->getChild('game_type')->getValue();
                            $success['uuid'] = $user_player;
                            $success['continua'] = "false";
                            $success['timer'] = "" . $gameOneDB->getChild('timer_two')->getValue();

                            $success['backer'] = 'move';

                            $success['game_over_msg'] = $gameOverMsg;

                            $gameDat = $database->getReference("/gameons/playx$game_id")->update(["play" . count($database->getReference("/gameons/playx$game_id")->getChildKeys()) => $success]);
                            $database->getReference("/startx/$player_one")->update(["continua" => "false"]);
                            $database->getReference("/startx/$player_two")->update(["continua" => "false"]);
                            if (isset($success['winner']) or isset($success['loser'])) {
                                $uuid = $player_one;
                                $uuidx = $player_two;
                                $gameDaty = $database->getReference("/startx/$uuid")->set($success);
                                $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                            }
                            if ($gameDat) {
                                // $gameData['market_size'] = count((array)json_decode($gameData->market_deck,true));

                                return response()->json(['success' => $success], 200);
                                exit;
                            } else {
                                $success['error'] = 'failure';
                                return response()->json(['success' => $success], 200);
                                exit;
                            }

                        } else {
                            $error = 'Integrity Error !';
                            $success['error'] = 'Integrity Error !';
                            return response()->json(['success' => $success], 200);
                            exit;
                        }
                    } else {
                        $success['error'] = 'Move Error !';
                        return response()->json(['success' => $success], 200);
                        exit;
                    }

                }

            } else {
                $success['error'] = 'failure';
                return response()->json(['success' => $success], 200);
                exit;
            }

        } catch (\Exception $e) {
            $err = $e->getMessage();
            $success['error'] = $err;
            return response()->json(['success' => $success], 500);
        }

    }

    public function checkDelay(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $game_id = $input['game_id'];

            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameDatRes = $database->getReference("/gameons/playx$game_id")->limitToLast(1)->getSnapshot()->getKey();
            $playDB = $database->getReference("/gameons/playx$game_id")->getSnapShot();

            if ($gameOneDB->getChild('player1')->getValue() == $user_player) {

                //  if($gameOneDB->getChild('turn')->getValue() == "player1" and $playDB->getChild($gameDatRes."/continua")->getValue() == "false"){

                if ($gameOneDB->getChild('turn')->getValue() == "player1") {

                    $current = $gameOneDB->getChild('timer_count_one')->getValue();
                    $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                    $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                    $new = time();

                    $difference = (int) $new - (int) $current;
                    if ($difference > 30 and $check_timer == 'false') {
                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                        } else {
                            $timerOneDiff = (int) $difference - 30;
                            $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                        }

                    } else if ($difference > 30 and $check_timer == 'true') {
                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                        } else {
                            $timerOneDiff = (int) $difference - 0;
                            $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                        }

                        $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                    } else {

                    }

                    $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);

                    // $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                    // $database->getReference('startz/'.$player_one.'/timer_one')->set((int)$currentTimerOne - 5);

                    return response()->json(['successor' => "success"], $this->successStatus);

                } else {
                    $current = $gameOneDB->getChild('timer_count_two')->getValue();
                    $currentTimerTwo = $gameOneDB->getChild('timer_two')->getValue();
                    $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                    $new = time();

                    $difference = (int) $new - (int) $current;
                    if ($difference > 30 and $check_timer == 'false') {
                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                        } else {
                            $timerTwoDiff = (int) $difference - 30;
                            $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                        }

                    } else if ($difference > 30 and $check_timer == 'true') {
                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                        } else {
                            $timerTwoDiff = (int) $difference - 0;
                            $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                        }

                        $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                    } else {

                    }

                    $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);

                    // $currentTimerTwo = $gameOneDB->getChild('timer_two')->getValue();
                    // $database->getReference('startz/'.$player_one.'/timer_two')->set((int)$currentTimerTwo - 5);

                    return response()->json(['successor' => "success"], $this->successStatus);

                }

            } else {
                // if($gameOneDB->getChild('turn')->getValue() == "player2" and $playDB->getChild($gameDatRes."continua")->getValue() == "false"){
                if ($gameOneDB->getChild('turn')->getValue() == "player2") {

                    $current = $gameOneDB->getChild('timer_count_two')->getValue();
                    $currentTimerTwo = $gameOneDB->getChild('timer_two')->getValue();
                    $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                    $new = time();

                    $difference = (int) $new - (int) $current;
                    if ($difference > 30 and $check_timer == 'false') {
                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                        } else {
                            $timerTwoDiff = (int) $difference - 30;
                            $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                        }

                    } else if ($difference > 30 and $check_timer == 'true') {
                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                        } else {
                            $timerTwoDiff = (int) $difference - 0;
                            $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                        }

                        $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                    } else {

                    }

                    $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);

                    // $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                    // $database->getReference('startz/'.$player_one.'/timer_one')->set((int)$currentTimerOne - 5);

                    return response()->json(['successor' => "success"], $this->successStatus);

                } else {

                    $current = $gameOneDB->getChild('timer_count_one')->getValue();
                    $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                    $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                    $new = time();

                    $difference = (int) $new - (int) $current;
                    if ($difference > 30 and $check_timer == 'false') {
                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                        } else {
                            $timerOneDiff = (int) $difference - 30;
                            $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                        }

                    } else if ($difference > 30 and $check_timer == 'true') {
                        if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                        } else {
                            $timerOneDiff = (int) $difference - 0;
                            $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                        }

                        $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                    } else {

                    }

                    $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);

                    return response()->json(['successor' => "success"], $this->successStatus);

                }

            }

        } catch (\Exception $e) {

            $err = $e->getMessage();
            return response()->json(['successor' => $err], 500);
        }

    }

    public function checkVersion(Request $request)
    {
        try {

            $input = $request->all();
            $game_version = $input['game_version'];

            $check_version = Version::where('version', $game_version)->where('state', 'active')->first();

            if ($check_version) {

                return response()->json(['successor' => 'success'], 200);
            } else {

                return response()->json(['successor' => 'failure'], 200);

            }

        } catch (\Exception $e) {

            $err = $e->getMessage();
            return response()->json(['successor' => $err], 500);
        }

    }

    public function setContinue(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $game_id = $input['game_id'];
            $lastKey = [];

            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameStartxDB = $database->getReference("startx/$player_one")->getSnapShot();

            $playist = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);

            $lastCardArr = explode("z", end($playist));

            if ($gameOneDB->getChild('player1')->getValue() == $user_player) {

                //  if($gameOneDB->getChild('turn')->getValue() == "player2"){
                $gameDatRes = $database->getReference("/gameons/playx$game_id")->getChildKeys();

                foreach ($gameDatRes as $gameDatRis) {
                    $laster = explode("y", $gameDatRis);
                    array_push($lastKey, $laster[1]);
                }

                $gameDat = $database->getReference("/gameons/playx" . $game_id . "/play" . max($lastKey) . "/continua")->set("true");
                $wasted_time_start = $gameStartxDB->getChild('updated_at')->getValue();
                $continua_time = date("Y-m-d H:i:s");
                $database->getReference("/startx/$player_one")->update(["continua" => "true", "updated_at" => $continua_time]);
                $database->getReference("/startx/$player_two")->update(["continua" => "true", "updated_at" => $continua_time]);
                $wasted_time_end = $continua_time;
                if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {
                    $current = $gameOneDB->getChild('timer_count_one')->getValue();
                    $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                    $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                    $new = time();

                    $difference = (int) $new - (int) $current;
                    if ($difference > 30 and $check_timer == 'false') {

                        $timerOneDiff = (int) $difference - 30;
                        $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);

                    } else if ($difference > 30 and $check_timer == 'true') {

                        $timerOneDiff = (int) $difference - 0;
                        $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                        $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                    } else {

                    }

                    $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);

                }

                if ($gameDat) {
                    return response()->json(['successor' => max($lastKey)], $this->successStatus);
                } else {
                    return response()->json(['successor' => max($lastKey)], 300);
                }

                // } else {
                //    return response()->json(['successor' => 'failure'], 300);
                //  }

            } else {
                // if($gameOneDB->getChild('turn')->getValue() == "player1"){
                $gameDatRes = $database->getReference("/gameons/playx$game_id")->getChildKeys();

                foreach ($gameDatRes as $gameDatRis) {
                    $laster = explode("y", $gameDatRis);
                    array_push($lastKey, $laster[1]);
                }

                $gameDat = $database->getReference("/gameons/playx" . $game_id . "/play" . max($lastKey) . "/continua")->set("true");
                $wasted_time_start = $gameStartxDB->getChild('updated_at')->getValue();
                $continua_time = date("Y-m-d H:i:s");
                $database->getReference("/startx/$player_one")->update(["continua" => "true", "updated_at" => $continua_time]);
                $database->getReference("/startx/$player_two")->update(["continua" => "true", "updated_at" => $continua_time]);
                $wasted_time_end = $continua_time;
                if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {
                    $current = $gameOneDB->getChild('timer_count_two')->getValue();
                    $currentTimerTwo = $gameOneDB->getChild('timer_two')->getValue();
                    $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                    $new = time();

                    $difference = (int) $new - (int) $current;
                    if ($difference > 30 and $check_timer == 'false') {

                        $timerTwoDiff = (int) $difference - 30;
                        $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);

                    } else if ($difference > 30 and $check_timer == 'true') {

                        $timerTwoDiff = (int) $difference - 0;
                        $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                        $database->getReference('startx/' . $player_one)->update(["check_timer" => "false"]);

                    } else {

                    }

                    $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);

                }

                if ($gameDat) {
                    return response()->json(['successor' => max($lastKey)], $this->successStatus);
                } else {
                    return response()->json(['successor' => max($lastKey)], 300);
                }

                //   } else {
                //      return response()->json(['successor' => 'failure'], 300);
                //   }

            }

        } catch (\Exception $e) {

            $err = $e->getMessage();
            return response()->json(['successor' => max($lastKey)], 500);
        }

    }

    public function gameOverX(Request $request)
    {
         $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
        $database = $factory->createDatabase();
        $input = $request->all();
        $game_id = $input['game_id'];
        $user_player = $input['user_player'];
        $player_one = $input['player_one'];
        $player_two = $input['player_two'];

        $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();

        if ($gameOneDB->getChild('game_status')->getValue() == "game_over") {
            $successir['winner'] = $gameOneDB->getChild('winner')->getValue();
            $successir['loser'] = $gameOneDB->getChild('loser')->getValue();

            return response()->json(['successir' => $successir], 200);
        } else {
            return response()->json(['successor' => "failure"], 300);
        }

    }

    public function gameOver(Request $request)
    {
         $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
        $database = $factory->createDatabase();
        $input = $request->all();
        $game_id = $input['game_id'];
        $user_player = $input['user_player'];
        $player_one = $input['player_one'];
        $player_two = $input['player_two'];

        $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();

        if ($gameOneDB->getChild('game_status')->getValue() == "game_over") {
            $successir['winner'] = $gameOneDB->getChild('winner')->getValue();
            $successir['loser'] = $gameOneDB->getChild('loser')->getValue();
            //  $database->getReference("/gameons/playx$game_id")->remove();
            return response()->json(['successir' => $successir], 200);
        } else {
            return response()->json(['successor' => "failure"], 300);
        }

    }

    public function checkStillGame(Request $request)
    {
         $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
        $database = $factory->createDatabase();
        $input = $request->all();
        $game_id = $input['game_id'];
        $user_player = $input['user_player'];
        $player_one = $input['player_one'];
        $player_two = $input['player_two'];

        $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();

        if ($gameOneDB->getChild('game_status')->getValue() == "game_over") {
            $successir['winner'] = $gameOneDB->getChild('winner')->getValue();
            $successir['loser'] = $gameOneDB->getChild('loser')->getValue();
            //  $database->getReference("/gameons/playx$game_id")->remove();
            return response()->json(['successir' => $successir], 200);
        } else {
            return response()->json(['successor' => "failure"], 300);
        }

    }

    public function initUpdate(Request $request)
    {
         $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
        $database = $factory->createDatabase();
        $input = $request->all();
        $game_id = $input['game_id'];
        $player_one = $input['player_one'];
        $player_two = $input['player_two'];

        $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
        $gameTwoDB = $database->getReference("startz/$player_two")->getSnapShot();

        if ($gameOneDB->getChild('game_status')->getValue() != "game_over" or $gameOneDB->getChild('game_status')->getValue() != "game-over") {

            $playerOneDeck = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
            $playerTwoDeck = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);

            $successir['one_size'] = count($playerOneDeck);
            $successir['two_size'] = count($playerTwoDeck);

            $successir['one'] = $playerOneDeck;
            $successir['two'] = $playerTwoDeck;

            return response()->json(['successir' => $successir], 200);
        } else {
            return response()->json(['successor' => "failure"], 300);
        }

    }

    public function timeOver(Request $request)
    {

        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();
            $input = $request->all();
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $game_id = $input['game_id'];
            $gameOverMsg = "";

            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameTwoDB = $database->getReference("startz/$player_two")->getSnapShot();
            $gameDatRes = $database->getReference("gameons/playx$game_id")->getSnapshot()->getKey();
            $playGame = $database->getReference("gameons/playx$game_id")->getSnapShot();

            $gameStartxDB = $database->getReference("startx/$player_one")->getSnapShot();

            $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
            $lastCardArr = explode("z", end($playDeck));
            $marketur = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);

            $playerOnes = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
            $playerTwos = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);

            if ($gameOneDB->getChild('player1')->getValue()) {
                if ($input['user_player'] == $gameOneDB->getChild('player1')->getValue()) {
                    $timerOne = $gameOneDB->getChild('timer_one')->getValue();
                    $timerTwo = $gameOneDB->getChild('timer_two')->getValue();

                    if ((int) $timerOne <= 0) {
                        $gameOverMsg = "Time Up!";
                        if ($gameStartxDB->getChild('backer')->getValue() != "game_over") {

                            if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {

                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);
                            } else {
                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $this->setOnliner($user_player);
                            }

                            if ($playGame) {

                                $amount = $gameOneDB->getChild('amt')->getValue();

                                $receiverX = new Wallet();
                                $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $receiverX->typer = 'winning';
                                $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiverX->save();

                                $receiver = new Wallet();
                                $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                $receiver->typer = 'staking';
                                $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiver->save();

                                $transact = new Transaction();
                                $transact->title = 'won a bet';
                                $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $transact->save();

                                $transact = new Transaction();
                                $transact->title = 'lost a bet';
                                $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                $transact->save();

                                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                $betDta->state = 'finished';
                                $betDta->save();

                                $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                if ($winnerLoss) {
                                    $receiverQ = new Wallet();
                                    $receiverQ->amount = $winnerLoss->amount;
                                    $receiverQ->typer = 'win-loss-out';
                                    $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                    $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                    $receiverQ->save();

                                }

                                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                                $turner = $gameOneDB->getChild('turn')->getValue();
                                $pender = $gameOneDB->getChild('pend')->getValue();

                                $game_ons = GameOn::where("id", $game_id)->first();

                                $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                                $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                                $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                                $game_ons->timer_one = $timerOnex;
                                $game_ons->timer_two = $timerTwo;
                                $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $game_ons->game_status = "game_over";
                                $game_ons->turn = $turner;
                                $game_ons->pend = $pender;

                                $game_ons->save();

                                //   $database->getReference("/gameons/playx$game_id")->remove();

                            }

                        }

                    }
                    return response()->json(['successor' => $gameOneDB->getChild('timer_one')->getValue()], 200);
                } else {
                    $timerTwo = $gameOneDB->getChild('timer_two')->getValue();

                    if ((int) $timerTwo <= 0) {
                        $gameOverMsg = "Time Up!";
                        if ($gameStartxDB->getChild('backer')->getValue() != "game_over") {

                            if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {
                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                            } else {
                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $this->setOnliner($user_player);

                            }

                            if ($playGame) {

                                $amount = $gameOneDB->getChild('amt')->getValue();

                                $receiverX = new Wallet();
                                $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $receiverX->typer = 'winning';
                                $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                                $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                                $receiverX->save();

                                $receiver = new Wallet();
                                $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                $receiver->typer = 'losing';
                                $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiver->save();

                                $transact = new Transaction();
                                $transact->title = 'won a bet';
                                $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $transact->save();

                                $transact = new Transaction();
                                $transact->title = 'lost a bet';
                                $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                $transact->save();

                                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                $betDta->state = 'finished';
                                $betDta->save();

                                $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                if ($winnerLoss) {
                                    $receiverQ = new Wallet();
                                    $receiverQ->amount = $winnerLoss->amount;
                                    $receiverQ->typer = 'win-loss-out';
                                    $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                    $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                    $receiverQ->save();

                                }

                                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                                $turner = $gameOneDB->getChild('turn')->getValue();
                                $pender = $gameOneDB->getChild('pend')->getValue();

                                $game_ons = GameOn::where("id", $game_id)->first();

                                $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                                $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                                $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                                $game_ons->timer_one = $timerOnex;
                                $game_ons->timer_two = $timerTwo;
                                $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $game_ons->game_status = "game_over";
                                $game_ons->turn = $turner;
                                $game_ons->pend = $pender;

                                $game_ons->save();

                                //     $database->getReference("/gameons/playx$game_id")->remove();

                            }

                        }

                    }
                    return response()->json(['successor' => $gameOneDB->getChild('timer_two')->getValue()], 200);
                }

            } else {
                return response()->json(['successor' => "failure"], 300);
            }

        } catch (\Exception $e) {

            $err = $e->getMessage();
            return response()->json(['successor' => $err], 500);
        }

    }

    public function timeOverX(Request $request)
    {

        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();
            $input = $request->all();
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $game_id = $input['game_id'];

            $gameOverMsg = "";

            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameTwoDB = $database->getReference("startz/$player_two")->getSnapShot();
            $gameDatRes = $database->getReference("gameons/playx$game_id")->getSnapshot()->getKey();
            $playGame = $database->getReference("gameons/playx$game_id")->getSnapShot();

            $gameStartxDB = $database->getReference("startx/$player_one")->getSnapShot();

            $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
            $lastCardArr = explode("z", end($playDeck));
            $marketur = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);

            $playerOnes = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
            $playerTwos = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);

            if ($gameOneDB->getChild('player1')->getValue()) {
                if ($input['user_player'] == $gameOneDB->getChild('player1')->getValue()) {

                    if ($gameOneDB->getChild('turn')->getValue() == "player1") {

                        $current = $gameOneDB->getChild('timer_count_one')->getValue();
                        $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                        $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                        $new = time();

                        $difference = (int) $new - (int) $current;
                        if ($difference > 30 and $check_timer == 'false') {
                            $timerOneDiff = (int) $difference - 30;
                            $checkers = (int) $currentTimerOne - (int) $timerOneDiff;
                            if ($checkers <= 0) {

                                $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);

                            } else {
                                $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                            }

                            $database->getReference('startx/' . $player_one)->update(["check_timer" => "true"]);
                            $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);
                            $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);

                        } else if ($check_timer == 'true') {
                            $timerOneDiff = (int) $difference - 0;
                            $checkers = (int) $currentTimerOne - (int) $timerOneDiff;
                            if ($checkers <= 0) {

                                $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);

                            } else {
                                $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                            }

                            $database->getReference('startx/' . $player_one)->update(["check_timer" => "true"]);
                            $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);
                            $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);

                        }

                    } else {

                        $current = $gameOneDB->getChild('timer_count_two')->getValue();
                        $currentTimerTwo = $gameOneDB->getChild('timer_two')->getValue();
                        $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                        $new = time();

                        $difference = (int) $new - (int) $current;
                        if ($difference > 30 and $check_timer == 'false') {
                            $timerTwoDiff = (int) $difference - 30;

                            $checkers = (int) $currentTimerTwo - (int) $timerTwoDiff;
                            if ($checkers <= 0) {

                                $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);

                            } else {

                                $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);

                            }

                            $database->getReference('startx/' . $player_one)->update(["check_timer" => "true"]);
                            $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);
                            $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);

                        } else if ($check_timer == 'true') {
                            $timerTwoDiff = (int) $difference - 0;

                            $checkers = (int) $currentTimerTwo - (int) $timerTwoDiff;
                            if ($checkers <= 0) {

                                $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);

                            } else {
                                $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                            }

                            $database->getReference('startx/' . $player_one)->update(["check_timer" => "true"]);
                            $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);
                            $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);
                        }

                    }

                    $timerOne = $gameOneDB->getChild('timer_one')->getValue();
                    $timerTwo = $gameOneDB->getChild('timer_two')->getValue();

                    if ((int) $timerOne <= 0) {
                        $gameOverMsg = "Time Up!";
                        if ($gameStartxDB->getChild('backer')->getValue() != "game_over") {

                            if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {
                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);
                            } else {
                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $this->setOnliner($user_player);
                            }

                            if ($playGame) {

                                $amount = $gameOneDB->getChild('amt')->getValue();

                                $receiverX = new Wallet();
                                $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $receiverX->typer = 'winning';
                                $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiverX->save();

                                $receiver = new Wallet();
                                $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                $receiver->typer = 'staking';
                                $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiver->save();

                                $transact = new Transaction();
                                $transact->title = 'won a bet';
                                $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $transact->save();

                                $transact = new Transaction();
                                $transact->title = 'lost a bet';
                                $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                $transact->save();

                                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                $betDta->state = 'finished';
                                $betDta->save();

                                $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                if ($winnerLoss) {
                                    $receiverQ = new Wallet();
                                    $receiverQ->amount = $winnerLoss->amount;
                                    $receiverQ->typer = 'win-loss-out';
                                    $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                    $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                    $receiverQ->save();

                                }

                                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                                $turner = $gameOneDB->getChild('turn')->getValue();
                                $pender = $gameOneDB->getChild('pend')->getValue();

                                $game_ons = GameOn::where("id", $game_id)->first();

                                $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                                $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                                $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                                $game_ons->timer_one = $timerOnex;
                                $game_ons->timer_two = $timerTwo;
                                $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $game_ons->game_status = "game_over";
                                $game_ons->turn = $turner;
                                $game_ons->pend = $pender;

                                $game_ons->save();

                                //   $database->getReference("/gameons/playx$game_id")->remove();

                            }

                        }

                    }

                    if ((int) $timerTwo <= 0) {
                        $gameOverMsg = "Time Up!";
                        if ($gameStartxDB->getChild('backer')->getValue() != "game_over") {

                            if ($lastCardArr[0] == "w" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "1" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "2" and $gameStartxDB->getChild('continua')->getValue() == 'false' or $lastCardArr[1] == "14" and $gameStartxDB->getChild('continua')->getValue() == 'false') {
                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_two,
                                        "loser" => $player_one,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                            } else {
                                $database->getReference('startz/' . $player_one)->update(
                                    ["game_status" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "game_over_msg" => "Time Up!"]);
                                $database->getReference('startx/' . $player_one)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $database->getReference('startx/' . $player_two)->update(
                                    ["backer" => "game_over",
                                        "winner" => $player_one,
                                        "loser" => $player_two,
                                        "updated_at" => date("Y-m-d H:i:s"),
                                        "game_over_msg" => "Time Up!"]);

                                $this->setOnliner($user_player);

                            }

                            if ($playGame) {

                                $amount = $gameOneDB->getChild('amt')->getValue();

                                $receiverX = new Wallet();
                                $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $receiverX->typer = 'winning';
                                $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                                $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                                $receiverX->save();

                                $receiver = new Wallet();
                                $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                $receiver->typer = 'losing';
                                $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiver->save();

                                $transact = new Transaction();
                                $transact->title = 'won a bet';
                                $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $transact->save();

                                $transact = new Transaction();
                                $transact->title = 'lost a bet';
                                $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                $transact->save();

                                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                $betDta->state = 'finished';
                                $betDta->save();

                                $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                if ($winnerLoss) {
                                    $receiverQ = new Wallet();
                                    $receiverQ->amount = $winnerLoss->amount;
                                    $receiverQ->typer = 'win-loss-out';
                                    $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                    $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                    $receiverQ->save();

                                }

                                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                                $turner = $gameOneDB->getChild('turn')->getValue();
                                $pender = $gameOneDB->getChild('pend')->getValue();

                                $game_ons = GameOn::where("id", $game_id)->first();

                                $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                                $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                                $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                                $game_ons->timer_one = $timerOnex;
                                $game_ons->timer_two = $timerTwo;
                                $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $game_ons->game_status = "game_over";
                                $game_ons->turn = $turner;
                                $game_ons->pend = $pender;

                                $game_ons->save();

                                //     $database->getReference("/gameons/playx$game_id")->remove();

                            }

                        }

                    }
                    return response()->json(['successor' => $gameOneDB->getChild('timer_one')->getValue()], 200);
                } else {

                    if ($gameOneDB->getChild('turn')->getValue() == "player2") {

                        $current = $gameOneDB->getChild('timer_count_two')->getValue();
                        $currentTimerTwo = $gameOneDB->getChild('timer_two')->getValue();
                        $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                        $new = time();

                        $difference = (int) $new - (int) $current;
                        if ($difference > 30 and $check_timer == 'false') {
                            $timerTwoDiff = (int) $difference - 30;
                            $checkers = (int) $currentTimerTwo - (int) $timerTwoDiff;
                            if ($checkers <= 0) {

                                $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);

                            } else {
                                $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                            }

                            $database->getReference('startx/' . $player_one)->update(["check_timer" => "true"]);
                            $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);
                            $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);

                        } else if ($check_timer == 'true') {
                            $timerTwoDiff = (int) $difference - 0;
                            $checkers = (int) $currentTimerTwo - (int) $timerTwoDiff;
                            if ($checkers <= 0) {

                                $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);

                            } else {
                                $database->getReference('startz/' . $player_one . '/timer_two')->set((int) $currentTimerTwo - (int) $timerTwoDiff);
                            }

                            $database->getReference('startx/' . $player_one)->update(["check_timer" => "true"]);
                            $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);
                            $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);
                        }

                    } else {

                        $current = $gameOneDB->getChild('timer_count_one')->getValue();
                        $currentTimerOne = $gameOneDB->getChild('timer_one')->getValue();
                        $check_timer = $gameStartxDB->getChild('check_timer')->getValue();

                        $new = time();

                        $difference = (int) $new - (int) $current;
                        if ($difference > 30 and $check_timer == 'false') {
                            $timerOneDiff = (int) $difference - 30;
                            $checkers = (int) $currentTimerOne - (int) $timerOneDiff;
                            if ($checkers <= 0) {

                                $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);

                            } else {
                                $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                            }

                            $database->getReference('startx/' . $player_one)->update(["check_timer" => "true"]);
                            $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);
                            $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);

                        } else if ($check_timer == 'true') {
                            $timerOneDiff = (int) $difference - 0;
                            $checkers = (int) $currentTimerOne - (int) $timerOneDiff;
                            if ($checkers <= 0) {

                                $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);

                            } else {
                                $database->getReference('startz/' . $player_one . '/timer_one')->set((int) $currentTimerOne - (int) $timerOneDiff);
                            }

                            $database->getReference('startx/' . $player_one)->update(["check_timer" => "true"]);
                            $database->getReference('startz/' . $player_one . '/timer_count_one')->set((int) $new);
                            $database->getReference('startz/' . $player_one . '/timer_count_two')->set((int) $new);
                        }

                    }

                    $timerOne = $gameOneDB->getChild('timer_one')->getValue();
                    $timerTwo = $gameOneDB->getChild('timer_two')->getValue();

                    if ((int) $timerOne <= 0) {
                        $gameOverMsg = "Time Up!";
                        if ($gameStartxDB->getChild('backer')->getValue() != "game_over") {

                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "winner" => $player_two,
                                    "loser" => $player_one,
                                    "game_over_msg" => "Time Up!"]);
                            $database->getReference('startx/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "backer" => "game_over",
                                    "winner" => $player_two,
                                    "loser" => $player_one,
                                    "game_over_msg" => "Time Up!"]);

                            $database->getReference('startx/' . $player_two)->update(
                                ["game_status" => "game_over",
                                    "backer" => "game_over",
                                    "winner" => $player_two,
                                    "loser" => $player_one,
                                    "game_over_msg" => "Time Up!"]);

                            $this->setOnliner($user_player);

                            if ($playGame) {

                                $amount = $gameOneDB->getChild('amt')->getValue();

                                $receiverX = new Wallet();
                                $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $receiverX->typer = 'winning';
                                $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiverX->save();

                                $receiver = new Wallet();
                                $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                $receiver->typer = 'staking';
                                $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiver->save();

                                $transact = new Transaction();
                                $transact->title = 'won a bet';
                                $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $transact->save();

                                $transact = new Transaction();
                                $transact->title = 'lost a bet';
                                $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                $transact->save();

                                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                $betDta->state = 'finished';
                                $betDta->save();

                                $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                if ($winnerLoss) {
                                    $receiverQ = new Wallet();
                                    $receiverQ->amount = $winnerLoss->amount;
                                    $receiverQ->typer = 'win-loss-out';
                                    $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                    $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                    $receiverQ->save();

                                }

                                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                                $turner = $gameOneDB->getChild('turn')->getValue();
                                $pender = $gameOneDB->getChild('pend')->getValue();

                                $game_ons = GameOn::where("id", $game_id)->first();

                                $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                                $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                                $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                                $game_ons->timer_one = $timerOnex;
                                $game_ons->timer_two = $timerTwo;
                                $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $game_ons->game_status = "game_over";
                                $game_ons->turn = $turner;
                                $game_ons->pend = $pender;

                                $game_ons->save();

                                //   $database->getReference("/gameons/playx$game_id")->remove();

                            }

                        }

                    }

                    if ((int) $timerTwo <= 0) {
                        $gameOverMsg = "Time Up!";
                        if ($gameStartxDB->getChild('backer')->getValue() != "game_over") {

                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "winner" => $player_one,
                                    "loser" => $player_two,
                                    "game_over_msg" => "Time Up!"]);
                            $database->getReference('startx/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "backer" => "game_over",
                                    "winner" => $player_one,
                                    "loser" => $player_two,
                                    "game_over_msg" => "Time Up!"]);

                            $database->getReference('startx/' . $player_two)->update(
                                ["game_status" => "game_over",
                                    "backer" => "game_over",
                                    "winner" => $player_one,
                                    "loser" => $player_two,
                                    "game_over_msg" => "Time Up!"]);

                            $this->setOnliner($user_player);

                            if ($playGame) {

                                $amount = $gameOneDB->getChild('amt')->getValue();

                                $receiverX = new Wallet();
                                $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $receiverX->typer = 'winning';
                                $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                                $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                                $receiverX->save();

                                $receiver = new Wallet();
                                $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                $receiver->typer = 'losing';
                                $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiver->save();

                                $transact = new Transaction();
                                $transact->title = 'won a bet';
                                $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $transact->save();

                                $transact = new Transaction();
                                $transact->title = 'lost a bet';
                                $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                $transact->save();

                                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                $betDta->state = 'finished';
                                $betDta->save();

                                $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                if ($winnerLoss) {
                                    $receiverQ = new Wallet();
                                    $receiverQ->amount = $winnerLoss->amount;
                                    $receiverQ->typer = 'win-loss-out';
                                    $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                    $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                    $receiverQ->save();

                                }

                                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                                $turner = $gameOneDB->getChild('turn')->getValue();
                                $pender = $gameOneDB->getChild('pend')->getValue();

                                $game_ons = GameOn::where("id", $game_id)->first();

                                $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                                $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                                $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                                $game_ons->timer_one = $timerOnex;
                                $game_ons->timer_two = $timerTwo;
                                $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $game_ons->game_status = "game_over";
                                $game_ons->turn = $turner;
                                $game_ons->pend = $pender;

                                $game_ons->save();

                                //     $database->getReference("/gameons/playx$game_id")->remove();

                            }

                        }

                    }
                    return response()->json(['successor' => $gameOneDB->getChild('timer_two')->getValue()], 200);
                }

            } else {
                return response()->json(['successor' => "failure"], 300);
            }

        } catch (\Exception $e) {

            $err = $e->getMessage();
            return response()->json(['successor' => $err], 500);
        }

    }

    /*
    public function finishOver(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();
            $input = $request->all();
            $game_id = $input['game_id'];
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];

            $gameOverMsg = "";

            $deckOne = 0;
            $deckTwo = 0;
            $playOne = "";
            $playTwo = "";

            $success = [];

            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameTwoDB = $database->getReference("startz/$player_two")->getSnapShot();

           // if($gameOneDB->getChild('player1')->getValue()){

                $playerOnes = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
                $playerTwos = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);
                $decker = [];
                foreach ($playerOnes as $playerOne) {
                    $playOne = explode("z", $playerOne);
                    if ($playOne[0] == "w") {
                        $deckOne += 20;
                    } else {
                        $deckOne += (int) $playOne[1];
                    }

                }
                foreach ($playerTwos as $playerTwo) {
                    $playTwo = explode("z", $playerTwo);
                    if ($playTwo[0] == "w") {
                        $deckTwo += 20;
                    } else {
                        $deckTwo += (int) $playTwo[1];
                    }
                }

                if ($input['user_player'] == $gameOneDB->getChild('player1')->getValue()) {


                    foreach ($playerTwos as $key => $value) {
                        $decker[] = $value;
                    }

                    if ($deckOne < $deckTwo) {

                        if ($gameOneDB->getChild('game_status')->getValue() != "game_over") {
                            $gameOverMsg = "Cards Up!";

                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "winner" => $player_one,
                                    "loser" => $player_two,
                                    "game_over_msg" => "Cards Up!"]);


                            //$database->getReference('startx/' . $player_one)->update(
                            //["game_status" => "game_over",
                            //"backer" => "game_over",
                            //"winner" => $player_one,
                            //"loser" => $player_two,
                            //"game_over_msg" => "Cards Up!"]);


                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['winner'] = $player_one;
                            $success['loser'] = $player_two;
                            $success['notice'] = "";
                            $success['game_status'] = "game_over";
                            $success['game_over_msg'] = "Cards Up!";
                            $success['backer'] = 'game_over';

                            $gameDaty = $database->getReference("startx/$player_one")->set($success);
                            $gameDatx = $database->getReference("startx/$player_two")->set($success);

                            $success['decker'] = $decker;
                            $success['wina'] = $player_one;
                            $success['losa'] = $player_two;

                            $amount = $gameOneDB->getChild('amt')->getValue();

                            $receiverX = new Wallet();
                            $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $receiverX->typer = 'winning';
                            $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                            $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                            $receiverX->save();

                            $receiver = new Wallet();
                            $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                            $receiver->typer = 'losing';
                            $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiver->save();

                            $transact = new Transaction();
                            $transact->title = 'won a bet';
                            $transact->owner = $gameOneDB->getChild('player1')->getValue();
                            $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $transact->save();

                            $transact = new Transaction();
                            $transact->title = 'lost a bet';
                            $transact->owner = $gameOneDB->getChild('player2')->getValue();
                            $transact->amount = $gameOneDB->getChild('amt')->getValue();
                            $transact->save();

                            $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            $betDta->state = 'finished';
                            $betDta->save();

                            $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                            if ($winnerLoss) {
                                $receiverQ = new Wallet();
                                $receiverQ->amount = $winnerLoss->amount;
                                $receiverQ->typer = 'win-loss-out';
                                $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiverQ->save();

                            }

                            $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                            $turner = $gameOneDB->getChild('turn')->getValue();
                            $pender = $gameOneDB->getChild('pend')->getValue();

                            $game_ons = GameOn::where("id", $game_id)->first();

                            $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                            $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                            $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                            $game_ons->timer_one = $timerOnex;
                            $game_ons->timer_two = $timerTwo;
                            $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $game_ons->game_status = "game_over";
                            $game_ons->turn = $turner;
                            $game_ons->pend = $pender;

                            $game_ons->save();

                            //   $database->getReference("/gameons/playx$game_id")->remove();

                        } else {

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['winner'] = "";
                            $success['loser'] = "";
                            $success['notice'] = "";
                            $success['game_status'] = "";
                            $success['game_over_msg'] = "";
                            $success['backer'] = "";

                            $success['decker'] = "";
                            $success['wina'] = "";
                            $success['losa'] = "";

                            return response()->json(['success' => $success], 200);

                        }

                    } else {

                        if ($gameOneDB->getChild('game_status')->getValue() != "game_over") {
                            $gameOverMsg = "Cards Up!";
                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "winner" => $player_two,
                                    "loser" => $player_one,
                                    "game_over_msg" => "Cards Up!"]);

                            //$database->getReference('startx/' . $player_one)->update(
                            //["game_status" => "game_over",
                            //"backer" => "game_over",
                            //"winner" => $player_two,
                            //"loser" => $player_one,
                            //"game_over_msg" => "Cards Up!"]);


                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['winner'] = $player_two;
                            $success['loser'] = $player_one;
                            $success['notice'] = "";
                            $success['game_status'] = "game_over";
                            $success['game_over_msg'] = "Cards Up!";
                            $success['backer'] = 'game_over';

                            $gameDaty = $database->getReference("startx/$player_one")->set($success);
                            $gameDatx = $database->getReference("startx/$player_two")->set($success);

                            $success['decker'] = $decker;
                            $success['wina'] = $player_two;
                            $success['losa'] = $player_one;

                            $amount = $gameOneDB->getChild('amt')->getValue();

                            $receiverX = new Wallet();
                            $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $receiverX->typer = 'winning';
                            $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiverX->save();

                            $receiver = new Wallet();
                            $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                            $receiver->typer = 'staking';
                            $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiver->save();

                            $transact = new Transaction();
                            $transact->title = 'won a bet';
                            $transact->owner = $gameOneDB->getChild('player2')->getValue();
                            $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $transact->save();

                            $transact = new Transaction();
                            $transact->title = 'lost a bet';
                            $transact->owner = $gameOneDB->getChild('player1')->getValue();
                            $transact->amount = $gameOneDB->getChild('amt')->getValue();
                            $transact->save();

                            $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            $betDta->state = 'finished';
                            $betDta->save();

                            $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                            if ($winnerLoss) {
                                $receiverQ = new Wallet();
                                $receiverQ->amount = $winnerLoss->amount;
                                $receiverQ->typer = 'win-loss-out';
                                $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                $receiverQ->save();

                            }

                            $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                            $turner = $gameOneDB->getChild('turn')->getValue();
                            $pender = $gameOneDB->getChild('pend')->getValue();

                            $game_ons = GameOn::where("id", $game_id)->first();

                            $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                            $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                            $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                            $game_ons->timer_one = $timerOnex;
                            $game_ons->timer_two = $timerTwo;
                            $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $game_ons->game_status = "game_over";
                            $game_ons->turn = $turner;
                            $game_ons->pend = $pender;

                            $game_ons->save();

                            //      $database->getReference("/gameons/playx$game_id")->remove();

                        } else {

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['winner'] = "";
                            $success['loser'] = "";
                            $success['notice'] = "";
                            $success['game_status'] = "";
                            $success['game_over_msg'] = "";
                            $success['backer'] = "";

                            $success['decker'] = "";
                            $success['wina'] = "";
                            $success['losa'] = "";

                            return response()->json(['success' => $success], 200);

                        }

                    }

                    return response()->json(['success' => $success], 200);
                } else {


                    foreach ($playerOnes as $key => $value) {
                        $decker[] = $value;
                    }
                    if ($deckOne < $deckTwo) {

                        if ($gameOneDB->getChild('game_status')->getValue() != "game_over") {
                            $gameOverMsg = "Cards Up!";

                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "winner" => $player_one,
                                    "loser" => $player_two,
                                    "game_over_msg" => "Cards Up!"]);


                            //$database->getReference('startx/' . $player_one)->update(
                            //["game_status" => "game_over",
                            //"backer" => "game_over",
                            //"winner" => $player_one,
                            //"loser" => $player_two,
                            //"game_over_msg" => "Cards Up!"]);


                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['winner'] = $player_one;
                            $success['loser'] = $player_two;
                            $success['notice'] = "";
                            $success['game_status'] = "game_over";
                            $success['game_over_msg'] = "Cards Up!";
                            $success['backer'] = 'game_over';

                            $gameDaty = $database->getReference("startx/$player_one")->set($success);
                            $gameDatx = $database->getReference("startx/$player_two")->set($success);

                            $success['decker'] = $decker;
                            $success['wina'] = $player_one;
                            $success['losa'] = $player_two;

                            $amount = $gameOneDB->getChild('amt')->getValue();

                            $receiverX = new Wallet();
                            $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $receiverX->typer = 'winning';
                            $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                            $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                            $receiverX->save();

                            $receiver = new Wallet();
                            $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                            $receiver->typer = 'losing';
                            $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiver->save();

                            $transact = new Transaction();
                            $transact->title = 'won a bet';
                            $transact->owner = $gameOneDB->getChild('player1')->getValue();
                            $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $transact->save();

                            $transact = new Transaction();
                            $transact->title = 'lost a bet';
                            $transact->owner = $gameOneDB->getChild('player2')->getValue();
                            $transact->amount = $gameOneDB->getChild('amt')->getValue();
                            $transact->save();

                            $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            $betDta->state = 'finished';
                            $betDta->save();

                            $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                            if ($winnerLoss) {
                                $receiverQ = new Wallet();
                                $receiverQ->amount = $winnerLoss->amount;
                                $receiverQ->typer = 'win-loss-out';
                                $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiverQ->save();

                            }

                            $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                            $turner = $gameOneDB->getChild('turn')->getValue();
                            $pender = $gameOneDB->getChild('pend')->getValue();

                            $game_ons = GameOn::where("id", $game_id)->first();

                            $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                            $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                            $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                            $game_ons->timer_one = $timerOnex;
                            $game_ons->timer_two = $timerTwo;
                            $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $game_ons->game_status = "game_over";
                            $game_ons->turn = $turner;
                            $game_ons->pend = $pender;

                            $game_ons->save();

                            //   $database->getReference("/gameons/playx$game_id")->remove();

                        } else {

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['winner'] = "";
                            $success['loser'] = "";
                            $success['notice'] = "";
                            $success['game_status'] = "";
                            $success['game_over_msg'] = "";
                            $success['backer'] = "";

                            $success['decker'] = "";
                            $success['wina'] = "";
                            $success['losa'] = "";

                            return response()->json(['success' => $success], 200);

                        }

                    } else {

                        if ($gameOneDB->getChild('game_status')->getValue() != "game_over") {
                            $gameOverMsg = "Cards Up!";
                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "winner" => $player_two,
                                    "loser" => $player_one,
                                    "game_over_msg" => "Cards Up!"]);

                            //$database->getReference('startx/' . $player_one)->update(
                            //["game_status" => "game_over",
                            //"backer" => "game_over",
                            //"winner" => $player_two,
                            //"loser" => $player_one,
                            //"game_over_msg" => "Cards Up!"]);


                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['winner'] = $player_two;
                            $success['loser'] = $player_one;
                            $success['notice'] = "";
                            $success['game_status'] = "game_over";
                            $success['game_over_msg'] = "Cards Up!";
                            $success['backer'] = 'game_over';

                            $gameDaty = $database->getReference("startx/$player_one")->set($success);
                            $gameDatx = $database->getReference("startx/$player_two")->set($success);

                            $success['decker'] = $decker;
                            $success['wina'] = $player_two;
                            $success['losa'] = $player_one;

                            $amount = $gameOneDB->getChild('amt')->getValue();

                            $receiverX = new Wallet();
                            $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $receiverX->typer = 'winning';
                            $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiverX->save();

                            $receiver = new Wallet();
                            $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                            $receiver->typer = 'staking';
                            $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiver->save();

                            $transact = new Transaction();
                            $transact->title = 'won a bet';
                            $transact->owner = $gameOneDB->getChild('player2')->getValue();
                            $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $transact->save();

                            $transact = new Transaction();
                            $transact->title = 'lost a bet';
                            $transact->owner = $gameOneDB->getChild('player1')->getValue();
                            $transact->amount = $gameOneDB->getChild('amt')->getValue();
                            $transact->save();

                            $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            $betDta->state = 'finished';
                            $betDta->save();

                            $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                            if ($winnerLoss) {
                                $receiverQ = new Wallet();
                                $receiverQ->amount = $winnerLoss->amount;
                                $receiverQ->typer = 'win-loss-out';
                                $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                $receiverQ->save();

                            }

                            $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                            $turner = $gameOneDB->getChild('turn')->getValue();
                            $pender = $gameOneDB->getChild('pend')->getValue();

                            $game_ons = GameOn::where("id", $game_id)->first();

                            $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                            $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                            $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                            $game_ons->timer_one = $timerOnex;
                            $game_ons->timer_two = $timerTwo;
                            $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $game_ons->game_status = "game_over";
                            $game_ons->turn = $turner;
                            $game_ons->pend = $pender;

                            $game_ons->save();

                            //      $database->getReference("/gameons/playx$game_id")->remove();

                        } else {

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['winner'] = "";
                            $success['loser'] = "";
                            $success['notice'] = "";
                            $success['game_status'] = "";
                            $success['game_over_msg'] = "";
                            $success['backer'] = "";

                            $success['decker'] = "";
                            $success['wina'] = "";
                            $success['losa'] = "";

                            return response()->json(['success' => $success], 200);

                        }

                    }

                    return response()->json(['success' => $success], 200);
                }

         //   } else {
          //      return response()->json(['successor' => "failure"], 300);
          //  }

        } catch (\Exception $e) {
            $err = $e->getMessage();
            return response()->json(['successor' => $err], 500);
        }

    }
    */

    public function finishOver(Request $request)
    {
     try{
         $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();
        $input = $request->all();
        $game_id = $input['game_id'];
        $user_player = $input['user_player'];
        $player_one = $input['player_one'];
        $player_two = $input['player_two'];
        $play_deck_one = $input['play_deck_one'];
        $play_deck_two = $input['play_deck_two'];

        $gameOneDB  = $database->getReference("startz/$player_one")->getSnapShot();
        $gameTwoDB  = $database->getReference("startz/$player_two")->getSnapShot();

        $game_over_msg = "";
        $finishData = [];

        if($gameOneDB->getChild('player1')->getValue()){
            if($input['user_player'] == $gameOneDB->getChild('player1')->getValue()){
                $finishData = $this->finishAndCount($game_id, $user_player, $player_one, $player_two, $play_deck_one, $play_deck_two);
                $deckers = (array)json_decode($gameTwoDB->getChild('player2_deck')->getValue(),true);
                $decker = [];
                foreach($deckers as $key => $value){
                    $decker[] = $value;
                }

               // $result = $this->finishAndCount($game_id,$user_player,$player_one,$player_two);


                $success['updated_at'] = date('Y-m-d H:i:s');
                $success['winner'] = $gameOneDB->getChild('winner')->getValue();
                $success['loser'] = $gameOneDB->getChild('loser')->getValue();
                $success['backer'] = 'game_over';

                $gameDaty  = $database->getReference("startx/$player_one")->set($success);
                $gameDatx  = $database->getReference("startx/$player_two")->set($success);

                $success['decker'] = $decker;
                $success['wina'] = $gameOneDB->getChild('winner')->getValue();
                $success['losa'] = $gameOneDB->getChild('loser')->getValue();
                $success['game_over_msg'] = $finishData["game_over_msg"] ?? "";

                return response()->json(['success' => $success], 200);
            } else {
                $finishData = $this->finishAndCount($game_id, $user_player, $player_one, $player_two, $play_deck_one, $play_deck_two);
                $deckers = (array)json_decode($gameOneDB->getChild('player1_deck')->getValue(),true);
                $decker = [];
                foreach($deckers as $key => $value){
                    $decker[] = $value;
                }

               // $result = $this->finishAndCount($game_id,$user_player,$player_one,$player_two);

                $success['updated_at'] = date('Y-m-d H:i:s');
                $success['winner'] = $gameOneDB->getChild('winner')->getValue();
                $success['loser'] = $gameOneDB->getChild('loser')->getValue();
                $success['backer'] = 'game_over';

                $gameDaty  = $database->getReference("startx/$player_one")->set($success);
                $gameDatx  = $database->getReference("startx/$player_two")->set($success);

                $success['decker'] = $decker;
                $success['wina'] = $gameOneDB->getChild('winner')->getValue();
                $success['losa'] = $gameOneDB->getChild('loser')->getValue();
                $success['game_over_msg'] = $finishData["game_over_msg"] ?? "";

                return response()->json(['success' => $success], 200);
            }

        } else {
            return response()->json(['successor' => "failure"], 300);
        }

     }
     catch(\Exception $e){
         $err = $e->getMessage();
         return response()->json(['successor' => $err], 500);
     }


    }

    public function marketDecker(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();
            $input = $request->all();
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];

            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();

            if ($gameOneDB) {

                $decker = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);
                if (count($decker) < 3) {
                    // reloadMarket($input['game_id']);
                }

                return response()->json(['successor' => "" . count($decker)], 200);

            } else {
                return response()->json(['successor' => "failure"], 300);
            }

        } catch (\Exception $e) {
            $err = $e->getMessage();
            return response()->json(['successor' => $err], 500);
        }

    }

    public function reloadMarket($game_id, $user_player, $player_one, $player_two)
    {

        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();
            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();

            if ($gameOneDB and count((array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true)) == 0) {
                $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
                $ender = end($playDeck);
                //$keys = array_keys($playDeck);
                //reverse($keys);
                $marketDeckArr = array();
                foreach ($playDeck as $deck) {
                    if ($deck == $ender) {

                    } else {
                        $marketDeckArr[] = $deck;
                    }
                }

                $playDeckX = [];
                array_push($playDeckX, $ender);

                $database->getReference('startz/' . $user_player . '/market_deck')->set(json_encode($marketDeckArr, JSON_FORCE_OBJECT));
                $database->getReference('startz/' . $user_player . '/play_deck')->set(json_encode($playDeckX, JSON_FORCE_OBJECT));

            } else {
                $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
                $ender = end($playDeck);
                $marketLeft = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);
                //$keys = array_keys($playDeck);
                //reverse($keys);
                $marketDeckArr = array();
                foreach ($marketLeft as $markitLift) {
                    $marketDeckArr[] = $markitLift;
                }
                foreach ($playDeck as $deck) {
                    if ($deck == $ender) {

                    } else {
                        $marketDeckArr[] = $deck;
                    }
                }

                $playDeckX = [];
                array_push($playDeckX, $ender);

                $database->getReference('startz/' . $user_player . '/market_deck')->set(json_encode($marketDeckArr, JSON_FORCE_OBJECT));
                $database->getReference('startz/' . $user_player . '/play_deck')->set(json_encode($playDeckX, JSON_FORCE_OBJECT));
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
            return $err;
        }

    }

    public function finishAndCount($game_id, $user_player, $player_one, $player_two, $play_deck_one, $play_deck_two)
    {

        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();
            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameTwoDB = $database->getReference("startz/$player_two")->getSnapShot();

            $deckOne = 0;
            $deckTwo = 0;
            $playOne = "";
            $playTwo = "";

            $gameOverMsg = "";

            $success = [];

            //if(count((array)json_decode($game->market_deck,true)) <= 0){

            $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
            $marketur = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);

            $playerOnes = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
            $playerTwos = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);

            if($user_player == $player_one){
                /*
                if(count($playerOnes) == $play_deck_one){

                } else {
                    $gameOverMsg = "Game Error!";
                    $database->getReference('startz/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "nuller" => $player_two,
                            "noller" => $player_one,
                            "game_over_msg" => "Game Error!"]);

                    $database->getReference('startx/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "backer" => "game_over",
                            "nuller" => $player_two,
                            "noller" => $player_one,
                            "game_over_msg" => "Game Error!",
                            "updated_at" => date('Y-m-d H:i:s')]);


                    $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                    $betDta->state = 'finished';
                    $betDta->save();

                    $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                        $turner = $gameOneDB->getChild('turn')->getValue();
                        $pender = $gameOneDB->getChild('pend')->getValue();

                        $game_ons = GameOn::where("id", $game_id)->first();

                        $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                        $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                        $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                        $game_ons->timer_one = $timerOnex;
                        $game_ons->timer_two = $timerTwo;
                        $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                        $game_ons->game_status = "game_over";
                        $game_ons->turn = $turner;
                        $game_ons->pend = $pender;

                        $game_ons->save();

                        //   $database->getReference("/gameons/playx$game_id")->remove();

                        $success['updated_at'] = date('Y-m-d H:i:s');
                        $success['nuller'] = $player_one;
                        $success['noller'] = $player_two;
                        $success['backer'] = 'game_over';

                        return $success;
                }
                */


            } else {
                /*
                if(count($playerTwos) == $play_deck_two){

                } else {
                    $gameOverMsg = "Game Error!";
                    $database->getReference('startz/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "nuller" => $player_two,
                            "noller" => $player_one,
                            "game_over_msg" => "Game Error!"]);

                    $database->getReference('startx/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "backer" => "game_over",
                            "nuller" => $player_two,
                            "noller" => $player_one,
                            "game_over_msg" => "Game Error!",
                            "updated_at" => date('Y-m-d H:i:s')]);


                    $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                    $betDta->state = 'finished';
                    $betDta->save();

                    $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                        $turner = $gameOneDB->getChild('turn')->getValue();
                        $pender = $gameOneDB->getChild('pend')->getValue();

                        $game_ons = GameOn::where("id", $game_id)->first();

                        $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                        $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                        $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                        $game_ons->timer_one = $timerOnex;
                        $game_ons->timer_two = $timerTwo;
                        $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                        $game_ons->game_status = "game_over";
                        $game_ons->turn = $turner;
                        $game_ons->pend = $pender;

                        $game_ons->save();

                        //   $database->getReference("/gameons/playx$game_id")->remove();

                        $success['updated_at'] = date('Y-m-d H:i:s');
                        $success['nuller'] = $player_one;
                        $success['noller'] = $player_two;
                        $success['backer'] = 'game_over';

                        return $success;
                }
                */

            }

            foreach ($playerOnes as $playerOne) {
                $playOne = explode("z", $playerOne);
                if ($playOne[0] == "w") {
                    $deckOne += 20;
                } else {
                    $deckOne += (int) $playOne[1];
                }

            }
            foreach ($playerTwos as $playerTwo) {
                $playTwo = explode("z", $playerTwo);
                if ($playTwo[0] == "w") {
                    $deckTwo += 20;
                } else {
                    $deckTwo += (int) $playTwo[1];
                }
            }

            if ($deckOne < $deckTwo) {

                if ($gameOneDB->getChild('game_status')->getValue() != "game_over") {
                    $gameOverMsg = "Cards Up!";
                    $database->getReference('startz/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "winner" => $player_one,
                            "loser" => $player_two,
                            "deck_one" => $deckOne,
                            "deck_two" => $deckTwo,
                            "game_over_msg" => "Cards Up!"]);

                    $database->getReference('startx/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "backer" => "game_over",
                            "winner" => $player_one,
                            "loser" => $player_two,
                            "deck_one" => $deckOne,
                            "deck_two" => $deckTwo,
                            "game_over_msg" => "Cards Up!",
                            "updated_at" => date('Y-m-d H:i:s')]);

                    $amount = $gameOneDB->getChild('amt')->getValue();

                    $receiverX = new Wallet();
                    $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                    $receiverX->typer = 'winning';
                    $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                    $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                    $receiverX->save();

                    $receiver = new Wallet();
                    $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                    $receiver->typer = 'losing';
                    $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                    $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                    $receiver->save();

                    $transact = new Transaction();
                    $transact->title = 'won a bet';
                    $transact->owner = $gameOneDB->getChild('player1')->getValue();
                    $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                    $transact->save();

                    $transact = new Transaction();
                    $transact->title = 'lost a bet';
                    $transact->owner = $gameOneDB->getChild('player2')->getValue();
                    $transact->amount = $gameOneDB->getChild('amt')->getValue();
                    $transact->save();

                    $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                    $betDta->state = 'finished';
                    $betDta->save();

                    $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                    if ($winnerLoss) {
                        $receiverQ = new Wallet();
                        $receiverQ->amount = $winnerLoss->amount;
                        $receiverQ->typer = 'win-loss-out';
                        $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                        $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                        $receiverQ->save();

                    }

                    $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                    $turner = $gameOneDB->getChild('turn')->getValue();
                    $pender = $gameOneDB->getChild('pend')->getValue();

                    $game_ons = GameOn::where("id", $game_id)->first();

                    $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                    $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                    $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                    $game_ons->timer_one = $timerOnex;
                    $game_ons->timer_two = $timerTwo;
                    $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                    $game_ons->game_status = "game_over";
                    $game_ons->turn = $turner;
                    $game_ons->pend = $pender;

                    $game_ons->save();

                    //   $database->getReference("/gameons/playx$game_id")->remove();

                    $success['updated_at'] = date('Y-m-d H:i:s');
                    $success['winner'] = $player_one;
                    $success['loser'] = $player_two;
                    $success['backer'] = 'game_over';
                    $success['game_over_msg'] = 'Cards Up!';

                    return $success;

                }

            } else if ($deckOne > $deckTwo){

                if ($gameOneDB->getChild('game_status')->getValue() != "game_over") {
                    $gameOverMsg = "Cards Up!";
                    $database->getReference('startz/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "winner" => $player_two,
                            "loser" => $player_one,
                            "deck_one" => $deckOne,
                            "deck_two" => $deckTwo,
                            "game_over_msg" => "Cards Up!"]);

                    $database->getReference('startx/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "backer" => "game_over",
                            "winner" => $player_two,
                            "loser" => $player_one,
                            "deck_one" => $deckOne,
                            "deck_two" => $deckTwo,
                            "game_over_msg" => "Cards Up!",
                            "updated_at" => date('Y-m-d H:i:s')]);

                    $amount = $gameOneDB->getChild('amt')->getValue();

                    $receiverX = new Wallet();
                    $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                    $receiverX->typer = 'winning';
                    $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                    $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                    $receiverX->save();

                    $receiver = new Wallet();
                    $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                    $receiver->typer = 'staking';
                    $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                    $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                    $receiver->save();

                    $transact = new Transaction();
                    $transact->title = 'won a bet';
                    $transact->owner = $gameOneDB->getChild('player2')->getValue();
                    $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                    $transact->save();

                    $transact = new Transaction();
                    $transact->title = 'lost a bet';
                    $transact->owner = $gameOneDB->getChild('player1')->getValue();
                    $transact->amount = $gameOneDB->getChild('amt')->getValue();
                    $transact->save();

                    $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                    $betDta->state = 'finished';
                    $betDta->save();

                    $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                    if ($winnerLoss) {
                        $receiverQ = new Wallet();
                        $receiverQ->amount = $winnerLoss->amount;
                        $receiverQ->typer = 'win-loss-out';
                        $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                        $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                        $receiverQ->save();

                    }

                    $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                    $turner = $gameOneDB->getChild('turn')->getValue();
                    $pender = $gameOneDB->getChild('pend')->getValue();

                    $game_ons = GameOn::where("id", $game_id)->first();

                    $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                    $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                    $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                    $game_ons->timer_one = $timerOnex;
                    $game_ons->timer_two = $timerTwo;
                    $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                    $game_ons->game_status = "game_over";
                    $game_ons->turn = $turner;
                    $game_ons->pend = $pender;

                    $game_ons->save();

                    //      $database->getReference("/gameons/playx$game_id")->remove();

                    $success['updated_at'] = date('Y-m-d H:i:s');
                    $success['winner'] = $player_two;
                    $success['loser'] = $player_one;
                    $success['backer'] = 'game_over';
                    $success['game_over_msg'] = 'Cards Up!';

                    return $success;

                }

            } else if ($deckOne == $deckTwo) {

                $gameOverMsg = "Game Draw!";
                $database->getReference('startz/' . $player_one)->update(
                    ["game_status" => "game_over",
                        "nuller" => $player_two,
                        "noller" => $player_one,
                        "deck_one" => $deckOne,
                        "deck_two" => $deckTwo,
                        "game_over_msg" => "Game Draw!"]);

                $database->getReference('startx/' . $player_one)->update(
                    ["game_status" => "game_over",
                        "backer" => "game_over",
                        "nuller" => $player_two,
                        "noller" => $player_one,
                        "deck_one" => $deckOne,
                        "deck_two" => $deckTwo,
                        "game_over_msg" => "Game Draw!",
                        "updated_at" => date('Y-m-d H:i:s')]);


                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                $betDta->state = 'finished';
                $betDta->save();

                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                    $turner = $gameOneDB->getChild('turn')->getValue();
                    $pender = $gameOneDB->getChild('pend')->getValue();

                    $game_ons = GameOn::where("id", $game_id)->first();

                    $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                    $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                    $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                    $game_ons->timer_one = $timerOnex;
                    $game_ons->timer_two = $timerTwo;
                    $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                    $game_ons->game_status = "game_over";
                    $game_ons->turn = $turner;
                    $game_ons->pend = $pender;

                    $game_ons->save();

                    //   $database->getReference("/gameons/playx$game_id")->remove();

                    $success['updated_at'] = date('Y-m-d H:i:s');
                    $success['nuller'] = $player_one;
                    $success['noller'] = $player_two;
                    $success['backer'] = 'game_over';
                    $success['game_over_msg'] = 'Game Draw!';

                    return $success;

            } else {
                $gameOverMsg = "Game Error!";
                    $database->getReference('startz/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "nuller" => $player_two,
                            "noller" => $player_one,
                            "deck_one" => $deckOne,
                            "deck_two" => $deckTwo,
                            "game_over_msg" => "Game Error!"]);

                    $database->getReference('startx/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "backer" => "game_over",
                            "nuller" => $player_two,
                            "noller" => $player_one,
                            "deck_one" => $deckOne,
                            "deck_two" => $deckTwo,
                            "game_over_msg" => "Game Error!",
                            "updated_at" => date('Y-m-d H:i:s')]);


                    $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                    $betDta->state = 'finished';
                    $betDta->save();

                    $timerOnex = $gameOneDB->getChild('timer_one')->getValue();

                        $turner = $gameOneDB->getChild('turn')->getValue();
                        $pender = $gameOneDB->getChild('pend')->getValue();

                        $game_ons = GameOn::where("id", $game_id)->first();

                        $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                        $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                        $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                        $game_ons->timer_one = $timerOnex;
                        $game_ons->timer_two = $timerTwo;
                        $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                        $game_ons->game_status = "game_over";
                        $game_ons->turn = $turner;
                        $game_ons->pend = $pender;

                        $game_ons->save();

                        //   $database->getReference("/gameons/playx$game_id")->remove();

                        $success['updated_at'] = date('Y-m-d H:i:s');
                        $success['nuller'] = $player_one;
                        $success['noller'] = $player_two;
                        $success['backer'] = 'game_over';
                        $success['game_over_msg'] = 'Game Error!';

                        return $success;
            }

            // } else {

            //  }

        } catch (\Exception $e) {
            $err = $e->getMessage();
            return $err;
        }

    }

    public function onBack(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $game_id = $input['game_id'];
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameTwoDB = $database->getReference("startz/$player_two")->getSnapShot();

            $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
            $marketur = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);

            $playerOnes = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
            $playerTwos = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);

            $gameOverMsg = "";

            if ($gameOneDB->getChild('player1')->getValue()) {
                if ($input['user_player'] == $gameOneDB->getChild('player1')->getValue()) {

                    if ($gameOneDB->getChild('game_status')->getValue() != "game_over" or $gameOneDB->getChild('game_status')->getValue() != "game-over") {
                        $gameOverMsg = "Game Quit!";
                        $database->getReference('startz/' . $player_one)->update(
                            ["game_status" => "game_over",
                                "winner" => $player_two,
                                "loser" => $player_one,
                                "game_over_msg" => "Game Quit!"]);

                        $this->setOnliner($user_player);

                        $success['updated_at'] = date('Y-m-d H:i:s');
                        $success['winner'] = $gameOneDB->getChild('player2')->getValue();
                        $success['loser'] = $gameOneDB->getChild('player1')->getValue();
                        $success['notice'] = "quited";
                        $success['backer'] = 'game_over';
                        $success['game_over_msg'] = $gameOverMsg;

                        $uuid = $gameOneDB->getChild('player2')->getValue();
                        $uuidx = $gameOneDB->getChild('player1')->getValue();

                        $gameDat = $database->getReference("/startx/$uuid")->set($success);
                        $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                        $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                        $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

                        $amount = $gameOneDB->getChild('amt')->getValue();

                        if ($gameDat and $gameDatx) {

                            $receiverX = new Wallet();
                            $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $receiverX->typer = 'winning';
                            $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiverX->save();

                            $receiver = new Wallet();
                            $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                            $receiver->typer = 'staking';
                            $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiver->save();

                            $transact = new Transaction();
                            $transact->title = 'won a bet';
                            $transact->owner = $gameOneDB->getChild('player2')->getValue();
                            $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $transact->save();

                            $transact = new Transaction();
                            $transact->title = 'lost a bet';
                            $transact->owner = $gameOneDB->getChild('player1')->getValue();
                            $transact->amount = $gameOneDB->getChild('amt')->getValue();
                            $transact->save();

                            $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            $betDta->state = 'finished';
                            $betDta->save();

                            $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                            if ($winnerLoss) {
                                $receiverQ = new Wallet();
                                $receiverQ->amount = $winnerLoss->amount;
                                $receiverQ->typer = 'win-loss-out';
                                $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                $receiverQ->save();

                            }

                            $timerOnex = $gameOneDB->getChild('timer_one')->getValue();
                            $timerTwo = $gameOneDB->getChild('timer_two')->getValue();

                            $turner = $gameOneDB->getChild('turn')->getValue();
                            $pender = $gameOneDB->getChild('pend')->getValue();

                            $game_ons = GameOn::where("id", $game_id)->first();

                            $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                            $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                            $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                            $game_ons->timer_one = $timerOnex;
                            $game_ons->timer_two = $timerTwo;
                            $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $game_ons->game_status = "game_over";
                            $game_ons->turn = $turner;
                            $game_ons->pend = $pender;

                            $game_ons->save();

                            //    $database->getReference("/gameons/playx$game_id")->remove();

                            return response()->json(['successor' => "success"], 200);
                        } else {
                            return response()->json(['successor' => "failure"], 200);
                        }

                    }

                } else {
                    if ($gameOneDB->getChild('game_status')->getValue() != "game_over" or $gameOneDB->getChild('game_status')->getValue() != "game-over") {
                        $gameOverMsg = "Game Quit!";
                        $database->getReference('startz/' . $player_one)->update(
                            ["game_status" => "game_over",
                                "winner" => $player_one,
                                "loser" => $player_two,
                                "game_over_msg" => "Game Quit!"]);

                        $this->setOnliner($user_player);

                        $success['updated_at'] = date('Y-m-d H:i:s');
                        $success['winner'] = $gameOneDB->getChild('player1')->getValue();
                        $success['loser'] = $gameOneDB->getChild('player2')->getValue();
                        $success['notice'] = "quited";
                        $success['backer'] = 'game_over';
                        $success['game_over_msg'] = $gameOverMsg;

                        $uuid = $gameOneDB->getChild('player1')->getValue();
                        $uuidx = $gameOneDB->getChild('player2')->getValue();

                        $gameDat = $database->getReference("/startx/$uuid")->set($success);
                        $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                        $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                        $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

                        $amount = $gameOneDB->getChild('amt')->getValue();

                        if ($gameDat and $gameDatx) {

                            $receiverX = new Wallet();
                            $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $receiverX->typer = 'winning';
                            $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                            $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                            $receiverX->save();

                            $receiver = new Wallet();
                            $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                            $receiver->typer = 'losing';
                            $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                            $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                            $receiver->save();

                            $transact = new Transaction();
                            $transact->title = 'won a bet';
                            $transact->owner = $gameOneDB->getChild('player1')->getValue();
                            $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                            $transact->save();

                            $transact = new Transaction();
                            $transact->title = 'lost a bet';
                            $transact->owner = $gameOneDB->getChild('player2')->getValue();
                            $transact->amount = $gameOneDB->getChild('amt')->getValue();
                            $transact->save();

                            $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            $betDta->state = 'finished';
                            $betDta->save();

                            $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                            if ($winnerLoss) {
                                $receiverQ = new Wallet();
                                $receiverQ->amount = $winnerLoss->amount;
                                $receiverQ->typer = 'win-loss-out';
                                $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiverQ->save();

                            }

                            $timerOnex = $gameOneDB->getChild('timer_one')->getValue();
                            $timerTwo = $gameOneDB->getChild('timer_two')->getValue();

                            $turner = $gameOneDB->getChild('turn')->getValue();
                            $pender = $gameOneDB->getChild('pend')->getValue();

                            $game_ons = GameOn::where("id", $game_id)->first();

                            $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                            $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                            $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                            $game_ons->timer_one = $timerOnex;
                            $game_ons->timer_two = $timerTwo;
                            $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                            $game_ons->game_status = "game_over";
                            $game_ons->turn = $turner;
                            $game_ons->pend = $pender;

                            $game_ons->save();

                            //     $database->getReference("/gameons/playx$game_id")->remove();

                            return response()->json(['successor' => "success"], 200);
                        } else {
                            return response()->json(['successor' => "failure"], 200);
                        }

                    }
                }

            } else {
                return response()->json(['successor' => "fail"], 200);
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
            return response()->json(['successor' => $err], 200);
        }

    }

    public function terminateOnBack(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $game_id = $input['game_id'];
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameTwoDB = $database->getReference("startz/$player_two")->getSnapShot();

            $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
            $marketur = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);

            $playerOnes = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
            $playerTwos = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);

            $gameOverMsg = "";

            if ($gameOneDB->getChild('player1')->getValue()) {
                if ($input['user_player'] == $gameOneDB->getChild('player1')->getValue()) {

                    if ($gameOneDB->getChild('game_status')->getValue() != "game_over") {
                        $gameOverMsg = "Game Abandon!";
                        if (count($playDeck) > 1 or count($marketur) == 41 and explode("z", $playDeck[0])[1] != "2" or count($marketur) == 42 and explode("z", $playDeck[0])[1] != "14") {

                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "winner" => $player_two,
                                    "loser" => $player_one,
                                    "game_over_msg" => "Game Abandon!"]);

                            $this->setOnliner($user_player);

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['winner'] = $gameOneDB->getChild('player2')->getValue();
                            $success['loser'] = $gameOneDB->getChild('player1')->getValue();
                            $success['notice'] = "quited";
                            $success['backer'] = 'game_over';
                            $success['game_over_msg'] = $gameOverMsg;

                            $uuid = $gameOneDB->getChild('player2')->getValue();
                            $uuidx = $gameOneDB->getChild('player1')->getValue();

                            $gameDat = $database->getReference("/startx/$uuid")->set($success);
                            $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                            $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                            $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

                            $amount = $gameOneDB->getChild('amt')->getValue();

                            if ($gameDat and $gameDatx) {

                                $receiverX = new Wallet();
                                $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $receiverX->typer = 'winning';
                                $receiverX->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiverX->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiverX->save();

                                $receiver = new Wallet();
                                $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                $receiver->typer = 'staking';
                                $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiver->save();

                                $transact = new Transaction();
                                $transact->title = 'won a bet';
                                $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $transact->save();

                                $transact = new Transaction();
                                $transact->title = 'lost a bet';
                                $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                $transact->save();

                                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                $betDta->state = 'finished';
                                $betDta->save();

                                $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                if ($winnerLoss) {
                                    $receiverQ = new Wallet();
                                    $receiverQ->amount = $winnerLoss->amount;
                                    $receiverQ->typer = 'win-loss-out';
                                    $receiverQ->owner = $gameOneDB->getChild('player1')->getValue();
                                    $receiverQ->notes = $gameOneDB->getChild('player2')->getValue();
                                    $receiverQ->save();

                                }

                                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();
                                $timerTwo = $gameOneDB->getChild('timer_two')->getValue();

                                $turner = $gameOneDB->getChild('turn')->getValue();
                                $pender = $gameOneDB->getChild('pend')->getValue();

                                $game_ons = GameOn::where("id", $game_id)->first();

                                $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                                $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                                $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                                $game_ons->timer_one = $timerOnex;
                                $game_ons->timer_two = $timerTwo;
                                $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $game_ons->game_status = "game_over";
                                $game_ons->turn = $turner;
                                $game_ons->pend = $pender;

                                $game_ons->save();

                                //    $database->getReference("/gameons/playx$game_id")->remove();

                                return response()->json(['successor' => "success"], 200);
                            } else {
                                return response()->json(['successor' => "failure"], 200);
                            }

                        } else {
                            $gameOverMsg = "Game Error!";
                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "nuller" => $player_two,
                                    "noller" => $player_one,
                                    "game_over_msg" => "Game Error!"]);

                            $this->setOnliner($user_player);

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['nuller'] = $gameOneDB->getChild('player2')->getValue();
                            $success['noller'] = $gameOneDB->getChild('player1')->getValue();
                            $success['backer'] = 'game-over';
                            $success['game_over_msg'] = $gameOverMsg;

                            $uuid = $gameOneDB->getChild('player2')->getValue();
                            $uuidx = $gameOneDB->getChild('player1')->getValue();

                            $gameDat = $database->getReference("/startx/$uuid")->set($success);
                            $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                            $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                            $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

                            //$betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            //$betDta->state = 'initiated';
                            //$betDta->save();
                            $this->cancelGamer($gameOneDB->getChild('bet_id')->getValue(),$gameOneDB->getChild('player1')->getValue());

                            if ($gameDat and $gameDatx) {
                               // $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                               // $betDta->state = 'initiated';
                               // $betDta->save();

                               $this->cancelGamer($gameOneDB->getChild('bet_id')->getValue(),$gameOneDB->getChild('player1')->getValue());


                                return response()->json(['successor' => "nobody"], 200);
                            } else {
                                return response()->json(['successor' => "failure"], 200);
                            }

                        }

                    }

                } else {
                    if ($gameOneDB->getChild('game_status')->getValue() != "game_over") {

                        if (count($playDeck) > 1 or count($marketur) == 41 and explode("z", $playDeck[0])[1] != "2" or count($marketur) == 42 and explode("z", $playDeck[0])[1] != "14") {
                            $gameOverMsg = "Game Abandon!";
                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "winner" => $player_one,
                                    "loser" => $player_two,
                                    "game_over_msg" => "Game Abandon!"]);

                            $this->setOnliner($user_player);

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['winner'] = $gameOneDB->getChild('player1')->getValue();
                            $success['loser'] = $gameOneDB->getChild('player2')->getValue();
                            $success['notice'] = "quited";
                            $success['backer'] = 'game_over';
                            $success['game_over_msg'] = $gameOverMsg;

                            $uuid = $gameOneDB->getChild('player1')->getValue();
                            $uuidx = $gameOneDB->getChild('player2')->getValue();

                            $gameDat = $database->getReference("/startx/$uuid")->set($success);
                            $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                            $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                            $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

                            $amount = $gameOneDB->getChild('amt')->getValue();

                            if ($gameDat and $gameDatx) {

                                $receiverX = new Wallet();
                                $receiverX->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $receiverX->typer = 'winning';
                                $receiverX->owner = $gameOneDB->getChild('player1')->getValue();
                                $receiverX->notes = $gameOneDB->getChild('player2')->getValue();
                                $receiverX->save();

                                $receiver = new Wallet();
                                $receiver->amount = $gameOneDB->getChild('amt')->getValue();
                                $receiver->typer = 'losing';
                                $receiver->owner = $gameOneDB->getChild('player2')->getValue();
                                $receiver->notes = $gameOneDB->getChild('player1')->getValue();
                                $receiver->save();

                                $transact = new Transaction();
                                $transact->title = 'won a bet';
                                $transact->owner = $gameOneDB->getChild('player1')->getValue();
                                $transact->amount = (((int) $amount * 2) - (5 / 100 * ((int) $amount * 2)));
                                $transact->save();

                                $transact = new Transaction();
                                $transact->title = 'lost a bet';
                                $transact->owner = $gameOneDB->getChild('player2')->getValue();
                                $transact->amount = $gameOneDB->getChild('amt')->getValue();
                                $transact->save();

                                $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                                $betDta->state = 'finished';
                                $betDta->save();

                                $winnerLoss = Wallet::where('notes', $gameOneDB->getChild('bet_id')->getValue())->where('typer', 'win-loss-out')->first();

                                if ($winnerLoss) {
                                    $receiverQ = new Wallet();
                                    $receiverQ->amount = $winnerLoss->amount;
                                    $receiverQ->typer = 'win-loss-out';
                                    $receiverQ->owner = $gameOneDB->getChild('player2')->getValue();
                                    $receiverQ->notes = $gameOneDB->getChild('player1')->getValue();
                                    $receiverQ->save();

                                }

                                $timerOnex = $gameOneDB->getChild('timer_one')->getValue();
                                $timerTwo = $gameOneDB->getChild('timer_two')->getValue();

                                $turner = $gameOneDB->getChild('turn')->getValue();
                                $pender = $gameOneDB->getChild('pend')->getValue();

                                $game_ons = GameOn::where("id", $game_id)->first();

                                $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                                $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                                $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                                $game_ons->timer_one = $timerOnex;
                                $game_ons->timer_two = $timerTwo;
                                $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                                $game_ons->game_status = "game_over";
                                $game_ons->turn = $turner;
                                $game_ons->pend = $pender;

                                $game_ons->save();

                                //     $database->getReference("/gameons/playx$game_id")->remove();

                                return response()->json(['successor' => "success"], 200);
                            } else {
                                return response()->json(['successor' => "failure"], 200);
                            }

                        } else {
                            $gameOverMsg = "Game Error!";
                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "nuller" => $player_one,
                                    "noller" => $player_two,
                                    "game_over_msg" => "Game Error!"]);

                            $this->setOnliner($user_player);

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['nuller'] = $gameOneDB->getChild('player1')->getValue();
                            $success['noller'] = $gameOneDB->getChild('player2')->getValue();
                            $success['backer'] = 'game-over';
                            $success['game_over_msg'] = $gameOverMsg;

                            $uuid = $gameOneDB->getChild('player1')->getValue();
                            $uuidx = $gameOneDB->getChild('player2')->getValue();

                            $gameDat = $database->getReference("/startx/$uuid")->set($success);
                            $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                            $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                            $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

                            //$betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                            //$betDta->state = 'initiated';
                            //$betDta->save();

                            $this->cancelGamer($gameOneDB->getChild('bet_id')->getValue(),$gameOneDB->getChild('player1')->getValue());


                            if ($gameDat and $gameDatx) {
                              //  $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                              //  $betDta->state = 'initiated';
                              //  $betDta->save();

                              $this->cancelGamer($gameOneDB->getChild('bet_id')->getValue(),$gameOneDB->getChild('player1')->getValue());


                                return response()->json(['successor' => "nobody"], 200);
                            } else {
                                return response()->json(['successor' => "failure"], 200);
                            }

                        }

                    }
                }

            } else {
                return response()->json(['successor' => "fail"], 200);
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
            return response()->json(['successor' => $err], 500);
        }

    }

    public function terminateOnX(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $game_id = $input['game_id'];
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameTwoDB = $database->getReference("startz/$player_two")->getSnapShot();

            $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
            $marketur = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);

            $playerOnes = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
            $playerTwos = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);

            $gameOverMsg = "";

            if ($gameOneDB->getChild('player1')->getValue()) {
                if ($input['user_player'] == $gameOneDB->getChild('player1')->getValue()) {

                    if ($gameOneDB->getChild('game_status')->getValue() != "game_over") {
                        $gameOverMsg = "Game Abandon!";

                            $gameOverMsg = "Game Error!";
                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "nuller" => $player_two,
                                    "noller" => $player_one,
                                    "game_over_msg" => "Game Error!"]);

                            $this->setOnliner($user_player);

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['nuller'] = $gameOneDB->getChild('player2')->getValue();
                            $success['noller'] = $gameOneDB->getChild('player1')->getValue();
                            $success['backer'] = 'game-over';
                            $success['game_over_msg'] = $gameOverMsg;

                            $uuid = $gameOneDB->getChild('player2')->getValue();
                            $uuidx = $gameOneDB->getChild('player1')->getValue();

                            $gameDat = $database->getReference("/startx/$uuid")->set($success);
                            $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                            $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                            $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

                           // $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                           // $betDta->state = 'initiated';
                           // $betDta->save();

                           $this->cancelGamer($gameOneDB->getChild('bet_id')->getValue(),$gameOneDB->getChild('player1')->getValue());


                            if ($gameDat and $gameDatx) {
                             //   $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                             //   $betDta->state = 'initiated';
                             //   $betDta->save();

                             $this->cancelGamer($gameOneDB->getChild('bet_id')->getValue(),$gameOneDB->getChild('player1')->getValue());


                                return response()->json(['successor' => "nobody"], 200);
                            } else {
                                return response()->json(['successor' => "failure"], 200);
                            }



                    }

                } else {
                    if ($gameOneDB->getChild('game_status')->getValue() != "game_over") {


                            $gameOverMsg = "Game Error!";
                            $database->getReference('startz/' . $player_one)->update(
                                ["game_status" => "game_over",
                                    "nuller" => $player_one,
                                    "noller" => $player_two,
                                    "game_over_msg" => "Game Error!"]);

                            $this->setOnliner($user_player);

                            $success['updated_at'] = date('Y-m-d H:i:s');
                            $success['nuller'] = $gameOneDB->getChild('player1')->getValue();
                            $success['noller'] = $gameOneDB->getChild('player2')->getValue();
                            $success['backer'] = 'game-over';
                            $success['game_over_msg'] = $gameOverMsg;

                            $uuid = $gameOneDB->getChild('player1')->getValue();
                            $uuidx = $gameOneDB->getChild('player2')->getValue();

                            $gameDat = $database->getReference("/startx/$uuid")->set($success);
                            $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                            $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                            $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

                          //  $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                          //  $betDta->state = 'initiated';
                          //  $betDta->save();

                          $this->cancelGamer($gameOneDB->getChild('bet_id')->getValue(),$gameOneDB->getChild('player1')->getValue());


                            if ($gameDat and $gameDatx) {
                              //  $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                               // $betDta->state = 'initiated';
                              //  $betDta->save();

                              $this->cancelGamer($gameOneDB->getChild('bet_id')->getValue(),$gameOneDB->getChild('player1')->getValue());


                                return response()->json(['successor' => "nobody"], 200);
                            } else {
                                return response()->json(['successor' => "failure"], 200);
                            }



                    }
                }

            } else {
                return response()->json(['successor' => "fail"], 200);
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
            return response()->json(['successor' => $err], 500);
        }

    }

    public function terminateOnCrash(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $game_id = $input['game_id'];
            $error_msg = $input['error_msg'];
            $device_id = $input['device_id'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $gameOverMsg = "";

            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();
            $gameOverMsg = "Game Crash!";
            if ($gameOneDB->getChild('player1')->getValue()) {
                $database->getReference('startz/' . $player_one)->update(
                    ["game_status" => "game_over",
                        "nuller" => $player_two,
                        "noller" => $player_one,
                        "error_msg" => $error_msg,
                        "game_over_msg" => "Game Crash!"]);

                $database->getReference('startx/' . $player_one)->update(
                    ["game_status" => "game_over",
                        "nuller" => $player_two,
                        "noller" => $player_one,
                        "error_msg" => $error_msg,
                        "game_over_msg" => "Game Crash!"]);

                $this->setOnliner($user_player);

                $crashError = new CrashError();
                $crashError->error_message = $error_msg;
                $crashError->game_id = $game_id;
                $crashError->notes = $device_id;
                $crashError->save();

                $success['updated_at'] = date('Y-m-d H:i:s');
                $success['nuller'] = $gameOneDB->getChild('player2')->getValue();
                $success['noller'] = $gameOneDB->getChild('player1')->getValue();
                $success['backer'] = 'game-over';
                $success['game_over_msg'] = $gameOverMsg;

                $uuid = $gameOneDB->getChild('player2')->getValue();
                $uuidx = $gameOneDB->getChild('player1')->getValue();

                $gameDat = $database->getReference("/startx/$uuid")->set($success);
                $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

               // $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
               // $betDta->state = 'initiated';
               // $betDta->save();

               $this->cancelGamer($gameOneDB->getChild('bet_id')->getValue(),$gameOneDB->getChild('player1')->getValue());


                if ($gameDat and $gameDatx) {
                   // $betDta = Bet::where('id', $gameOneDB->getChild('bet_id')->getValue())->first();
                   // $betDta->state = 'initiated';
                   // $betDta->save();

                   $this->cancelGamer($gameOneDB->getChild('bet_id')->getValue(),$gameOneDB->getChild('player1')->getValue());


                    return response()->json(['successor' => "nobody"], 200);
                } else {
                    return response()->json(['successor' => "failure"], 300);
                }

            } else {

                return response()->json(['successor' => "fail"], 300);
            }

        } catch (\Exception $e) {
            $err = $e->getMessage();
            return response()->json(['successor' => $err], 500);
        }

    }

    public function startCancel(Request $request)
    {
        try {
             $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
            $database = $factory->createDatabase();

            $input = $request->all();
            $game_id = $input['game_id'];
            $user_player = $input['user_player'];
            $player_one = $input['player_one'];
            $player_two = $input['player_two'];
            $gameOneDB = $database->getReference("startz/$player_one")->getSnapShot();

            $playDeck = (array) json_decode($gameOneDB->getChild('play_deck')->getValue(), true);
            $marketur = (array) json_decode($gameOneDB->getChild('market_deck')->getValue(), true);

            $playerOnes = (array) json_decode($gameOneDB->getChild('player1_deck')->getValue(), true);
            $playerTwos = (array) json_decode($gameTwoDB->getChild('player2_deck')->getValue(), true);

            $gameOverMsg = "";

            if ($gameOneDB->getChild('player1')->getValue()) {
                if ($input['user_player'] == $gameOneDB->getChild('player1')->getValue()) {
                    $gameOverMsg = "Game Error!";
                    $database->getReference('startz/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "winner" => "betgames",
                            "loser" => "betgames",
                            "game_over_msg" => "Game Error!"]);

                    $this->setOnliner($user_player);

                    $success['updated_at'] = date('Y-m-d H:i:s');
                    $success['winner'] = "betgames";
                    $success['loser'] = "betgames";
                    $success['notice'] = "quited";
                    $success['backer'] = 'game_over';
                    $success['game_over_msg'] = $gameOverMsg;

                    $uuid = $gameOneDB->getChild('player2')->getValue();
                    $uuidx = $gameOneDB->getChild('player1')->getValue();

                    $gameDat = $database->getReference("/startx/$uuid")->set($success);
                    $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                    $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                    $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

                    $amount = $gameOneDB->getChild('amt')->getValue();

                    if ($gameDat and $gameDatx) {

                        $timerOnex = $gameOneDB->getChild('timer_one')->getValue();
                        $timerTwo = $gameOneDB->getChild('timer_two')->getValue();

                        $turner = $gameOneDB->getChild('turn')->getValue();
                        $pender = $gameOneDB->getChild('pend')->getValue();

                        $game_ons = GameOn::where("id", $game_id)->first();

                        $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                        $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                        $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                        $game_ons->timer_one = $timerOnex;
                        $game_ons->timer_two = $timerTwo;
                        $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                        $game_ons->game_status = "game_over";
                        $game_ons->turn = $turner;
                        $game_ons->pend = $pender;

                        $game_ons->save();

                        //    $database->getReference("/gameons/playx$game_id")->remove();
                        return response()->json(['successor' => "success"], 200);
                    } else {
                        return response()->json(['successor' => "failureh"], 200);
                    }

                } else {
                    $gameOverMsg = "Game Error!";
                    $database->getReference('startz/' . $player_one)->update(
                        ["game_status" => "game_over",
                            "winner" => "betgames",
                            "loser" => "betgames",
                            "game_over_msg" => "Game Error!"]);

                    $this->setOnliner($user_player);

                    $success['updated_at'] = date('Y-m-d H:i:s');
                    $success['winner'] = "betgames";
                    $success['loser'] = "betgames";
                    $success['backer'] = 'game_over';
                    $success['game_over_msg'] = $gameOverMsg;

                    $uuid = $gameOneDB->getChild('player1')->getValue();
                    $uuidx = $gameOneDB->getChild('player2')->getValue();

                    $gameDat = $database->getReference("/startx/$uuid")->set($success);
                    $gameDatx = $database->getReference("/startx/$uuidx")->set($success);

                    $gameDatz = $database->getReference("/startz/$uuid")->set($success);
                    $gameDatxz = $database->getReference("/startz/$uuidx")->set($success);

                    $amount = $gameOneDB->getChild('amt')->getValue();

                    if ($gameDat and $gameDatx) {

                        $timerOnex = $gameOneDB->getChild('timer_one')->getValue();
                        $timerTwo = $gameOneDB->getChild('timer_two')->getValue();

                        $turner = $gameOneDB->getChild('turn')->getValue();
                        $pender = $gameOneDB->getChild('pend')->getValue();

                        $game_ons = GameOn::where("id", $game_id)->first();

                        $game_ons->market_deck = json_encode($marketur, JSON_FORCE_OBJECT);
                        $game_ons->player1_deck = json_encode($playerOnes, JSON_FORCE_OBJECT);
                        $game_ons->player2_deck = json_encode($playerTwos, JSON_FORCE_OBJECT);
                        $game_ons->timer_one = $timerOnex;
                        $game_ons->timer_two = $timerTwo;
                        $game_ons->play_deck = json_encode($playDeck, JSON_FORCE_OBJECT);
                        $game_ons->game_status = "game_over";
                        $game_ons->turn = $turner;
                        $game_ons->pend = $pender;

                        $game_ons->save();

                        //     $database->getReference("/gameons/playx$game_id")->remove();
                        return response()->json(['successor' => "success"], 200);
                    } else {
                        return response()->json(['successor' => "failure"], 200);
                    }
                }

            } else {
                return response()->json(['successor' => "failurer"], 200);
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
            return response()->json(['successor' => $err], 500);
        }

    }

    public function sendData($to, $data)
    {
        // API access key from Google API's Console
        // replace API

        $tokin = $to;

        $apns = array(
            'headers' => array(
                'apns-expiration' => '3600',
            ),
            'android' => array(
                'ttl' => '3600s',
            ),
            'webpush' => array(
                'headers' => array(
                    'TTL' => '3600',
                ),
            ),
        );

        $fields = array
            (
            'to' => $tokin,
            'data' => $data,
            'apns' => $apns,
            'priority' => 'high',
            'channel_id' => 'BET_GAME_1',

        );
        $headers = array
            (
            'Authorization: key=' . $this->api_key,
            'Content-Type: application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        //$resulta = json_encode($result);
        $resulta = (string) $result;

        return $resulta;

    }

    public function sendNota($to, $message, $title, $data)
    {
        // API access key from Google API's Console
        // replace API
        define('API_ACCESS_KEY', env('FIREBASE_CLOUD_API_KEY'));
        $tokin = $to;
        $msg = array
            (
            'body' => $message,
            'title' => $title,
            'vibrate' => 1,
            'sound' => 'default',
            'click_action' => '.MainActivity',
            // you can also add images, additionalData
        );

        $apns = array(
            'headers' => array(
                'apns-expiration' => '3600',
            ),
            'android' => array(
                'ttl' => '3600s',
            ),
            'webpush' => array(
                'headers' => array(
                    'TTL' => '3600',
                ),
            ),
        );

        $fields = array
            (
            'to' => $tokin,
            'notification' => $msg,
            'data' => $data,
            'apns' => $apns,
            'priority' => 'high',
            'channel_id' => 'BET_GAME_1',

        );
        $headers = array
            (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        //$resulta = json_encode($result);
        $resulta = (string) $result;

        return $resulta;

    }

    public function cardRuler($boarderPart, $boarderNum, $cardorPart, $cardorNum, $movesa, $movese, $changer, $gameId, $user_player)
    {

        $factory = (new Factory())->withServiceAccount(base_path(env("FIREBASE_ADMIN_JSON")))->withDatabaseUri(env('FIREBASE_RTDB'));
        $database = $factory->createDatabase();

        $movera = $movesa;

        $move_onea = str_replace("marketer-", "", end($movera));

        $movere = $movese;
        $move_onee = str_replace("marketer-", "", end($movere));

        if ($boarderPart == $cardorPart) {
            return true;
        } else if ((int) $boarderNum == (int) $cardorNum) {
            return true;
        } else if ($cardorPart == "w" or (int) $cardorNum >= 20) {
            return true;
        } else if ($boarderPart == "w" and $move_onea == $cardorPart or $boarderPart == "w" and $move_onee == $cardorPart or $boarderPart == "w" and $changer == $cardorPart) {

            $database->getReference("startz/" . $user_player . "/command_change")->set("");

            return true;
        } else {
            return false;
        }

    }

}
