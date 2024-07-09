<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:api')->get(
    '/user',
    function (Request $request) {
        return $request->user();
    }
);

Route::post('/login', 'UserController@login');
Route::post('/logout', 'UserController@logout');
Route::post('/register', 'UserController@register');
Route::post('/verifier', 'UserController@verifyUser');
Route::post('/change-password', 'UserController@changeUserPassword');
Route::post('/resend', 'UserController@resendSms');

Route::post('/forgot', 'UserController@forgot');
Route::group(
    ['middleware' => 'auth:api'],
    function () {
        Route::post('/details', 'UserController@details');
        Route::post('/bet', 'PlayController@saveBet');
        Route::post('/betx', 'PlayController@saveBetX');
        Route::post('/replaygame', 'PlayController@replayBet');

        Route::post('/loadbet', 'PlayController@loadBet');
        Route::post('/loadbetx', 'PlayController@loadBetX');
        Route::post('/loadbety', 'PlayController@loadBetY');
        Route::post('/acceptbet', 'PlayController@acceptBet');
        Route::post('/device-token', 'UserController@saveToken');
        Route::post('/searchbet', 'PlayController@searchBet');
        Route::post('/searchbetx', 'PlayController@searchBetX');
        Route::post('/searchbety', 'PlayController@searchBetY');
        Route::post('/searchuser', 'UserController@searchUser');

        Route::get('/pay', 'UserController@payment');

        Route::get('/transfer', 'UserController@transferToken');

        Route::post('/startgame', 'PlayController@startGame');
        Route::post('/confirm', 'PlayController@confirmStart');
        Route::post('/stopgame', 'PlayController@stopGame');
        Route::post('/removestop', 'PlayController@removeStop');

        Route::post('/cancelgame', 'PlayController@cancelGame');

        Route::post('/startgamex', 'PlayController@startGameX');

        Route::post('/gameover', 'PlayController@gameOver');

        Route::post('/gameoverx', 'PlayController@gameOverX');

        Route::post('/gameon', 'PlayController@gameStart');
        Route::post('/gameplay', 'PlayController@playOn');
        Route::post('/gamerec', 'PlayController@playRec');

        Route::post('/start-timer', 'PlayController@startTimer');

        Route::post('/start-cancel', 'PlayController@startCancel');

        //Route::post('/playone', 'UserController@playOne');
        //Route::post('/playtwo', 'UserController@playTwo');

        Route::post('/whotter', 'PlayController@playWhot');
        Route::post('/timer', 'PlayController@timeOver');
        Route::post('/finish', 'PlayController@finishOver');
        Route::post('/marketer', 'PlayController@playMark');
        Route::post('/marketdeck', 'PlayController@marketDecker');
        Route::post('/onback', 'PlayController@onBack');
        //Route::post('/reload', 'UserController@marketReload');

        Route::post('/get-user-play-on', 'UserController@nodePlayOn');

        Route::post('/withdraw', 'UserController@withdraw');

        Route::post('/gam', 'FirebaseController@index');

        Route::post('/credit-balance', 'UserController@creditDetails');

        Route::post('/transaction', 'UserController@transactionDetails');

        Route::post('/send-mail', 'ContactController@sendMail');

        Route::post('/setonline', 'PlayController@setOnline');

        Route::post('/continua', 'PlayController@setContinue');

        Route::post('/setoffline', 'PlayController@setOffline');

        Route::post('/check-delay', 'PlayController@checkDelay');

        Route::post('/timerx', 'PlayController@timeOverX');

        Route::post('/still-game', 'PlayController@checkStillGame');

        Route::post('/terminate', 'PlayController@terminateOnBack');

        Route::post('/terminatex', 'PlayController@terminateOnX');


        Route::post('/check-version', 'UserController@checkVersion');

        Route::post('/crash', 'UserController@terminateOnCrash');

        Route::post('/init-update', 'UserController@initUpdate');

    }
);
