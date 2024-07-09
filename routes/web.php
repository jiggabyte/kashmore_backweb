<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // return view('layouts.templater');
    return view('welcome');
});

Route::get('/welcomer', function () {
    $result = ["jigga" => "byte"];
    return response()->json($result, 200);
});

Auth::routes(['verify' => true]);

Route::group(['prefix' => 'admin'], function () {
   // Voyager::routes();
});

Route::get('/payer', 'HomeController@index')->name('payer');

Route::get('/withdraw', 'HomeController@withdraw')->name('withdraw');

Route::get('/bank', 'HomeController@banker')->name('banker');

Route::post('/pay', 'HomeController@payment')->name('pay');

Route::post('/transfer', 'HomeController@transferToken')->name('transfer');

Route::post('/move', 'HomeController@transferBalance')->name('move');

Route::post('/getbanks', 'HomeController@getBankCode')->name('bank-code');

Route::post('/resolve', 'HomeController@resolveAccount')->name('resolver');

Route::post('/create-recipient', 'HomeController@createRecipientCode')->name('reci-code');

Route::post('/withdraw-money', 'HomeController@withdrawMoney')->name('withdraw-money');

Route::get('/withdrawal', 'HomeController@withdrawal')->name('withdrawal');

Route::post('/withdraw-request', 'HomeController@withdrawRequest')->name('withdraw-request');

Route::post('/withdraw-cancel', 'HomeController@withdrawCancel')->name('withdraw-cancel');

Route::post('/withdraw-block', 'HomeController@withdrawBlock')->name('withdraw-block');

Route::post('/withdraw-unblock', 'HomeController@withdrawUnBlock')->name('withdraw-unblock');

Route::post('/withdraw-send', 'HomeController@withdrawSend')->name('withdraw-send');

Route::post('/transfer-manual', 'HomeController@transferManual')->name('transfer-manual');

Route::post('/transfer-confirm', 'HomeController@transferConfirm')->name('transfer-confirm');

Route::get('/privacy', 'HomeController@privacy')->name('privacy');

// Download Route
Route::get('get/{filename}', function($filename)
{
    // Check if file exists in app/storage/file folder
    // $file_path = storage_path() .'/files/'. $filename;
    $file_path = public_path('files/'.$filename.'');
    //exit($file_path);
    if (file_exists($file_path))
    {
        // Send Download
        return Response::download($file_path, $filename, [
            'Content-Length: '. filesize($file_path)
        ]);
    }
    else
    {
        // Error
        return view('welcome', ['error' => 'Requested file does not exist on our server!']);
        //exit('Requested file does not exist on our server!');
    }
})
->where('filename', '[A-Za-z0-9\-\_\.]+');

