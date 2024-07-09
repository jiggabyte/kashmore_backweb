<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class Firebase extends Model
{
    //
    public $database;
    public $dbname = 'gameons';

    function __construct() {
        $serviceAccount = ServiceAccount::fromJsonFile($base_path('betgames-52bf4-firebase-adminsdk-vqjtd-e6f455fbe5.json'));

        $firebase = (new Factory)
                    ->withServiceAccount($serviceAccount)
                    ->withDatabaseUri('https://betgames-52bf4.firebaseio.com/')
                    ->create();
        $this->database = $firebase->getDatabase();
    }

    public function get(int $userID = NULL){
       if (empty($userID) || !isset($userID)) { return FALSE; }
       if ($this->database->getReference($this->dbname)->getSnapshot()->hasChild($userID)){
           return $this->database->getReference($this->dbname)->getChild($userID)->getValue();
       } else {
           return FALSE;
       }
   }


   public function insert(array $data) {
       if (empty($data) || !isset($data)) { return FALSE; }
       foreach ($data as $key => $value){
           $this->database->getReference()->getChild($this->dbname)->getChild($key)->set($value);
       }
       return TRUE;
   }
   public function delete(int $userID) {
       if (empty($userID) || !isset($userID)) { return FALSE; }
       if ($this->database->getReference($this->dbname)->getSnapshot()->hasChild($userID)){
           $this->database->getReference($this->dbname)->getChild($userID)->remove();
           return TRUE;
       } else {
           return FALSE;
       }
   }





}
