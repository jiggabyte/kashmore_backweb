<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;

class FirebaseController extends Controller
{
    
    
    
    public function index(){
/**
		$serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/betgames-11542-firebase-adminsdk-eurjq-81ec28a877.json');

	    $firebase  = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://betgames-11542.firebaseio.com')->create();

	    $database = $firebase->getDatabase();
	    
		$gameDat  = $database->getReference('/gameons')->push(['startx' => "Jigga Dogger"]);

		echo"<pre>";

		print_r($gameDat->getvalue());
		
*/		
		
		$factory = (new Factory())->withServiceAccount(__DIR__ . '/'. env('FIREBASE_ADMIN_JSON'))->withDatabaseUri(env('FIREBASE_RTDB'));

        $database = $factory->createDatabase();
        
        $gameDat  = $database->getReference('/gameons')->set(['startx' => "Jigga Doter"]);
        
        

		print_r($gameDat->getvalue());

	}
	
	
	public function connect(Request $request){
	    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/metrocab.json');

	    $firebase  = (new Factory())->withServiceAccount($serviceAccount)->withDatabaseUri(env('FIREBASE_RTDB'))->create();

	    $database = $firebase->getDatabase();
	    
	//	$gameDat  = $database->getReference('/gameons')->push(['startx' => $datas]);

		
	}
	
	
	public function start(Request $request){
	    
	}
	
	
	public function play(Request $request){
	    
	}
	
}
