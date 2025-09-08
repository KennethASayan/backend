<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which contains the "web" middleware group. Now create something great!
|
*/

// ----------------------
// Public routes
// ----------------------
Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['cors'])->group(function () {
    Route::post('/hogehoge', 'Controller@hogehoge');
});

// CSRF Token endpoint
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

// Session check routes
Route::get('/sessionStatus', function () {
    return ['user' => Auth::user() ?: null];
});

Route::get('/toes/sessionStatus', function () {
    return [Auth::guard('second_db')->user() ?: null];
});

Route::get('/dms/sessionStatus', function () {
    return [Auth::guard('third_db')->user() ?: null];
});

// ----------------------
// NGP Module
// ----------------------
Route::prefix('ngp')->group(function () {
    Route::post('/login', 'NgpController@login');
    Route::get('/logout', 'NgpController@logout');
    Route::get('/getMunicipalities/{id}/{penro_id}', 'NgpController@getMunicipalities');
    Route::get('/getBarangays/{id}', 'NgpController@getBarangays');
    Route::get('/getWatersheds/{id}', 'NgpController@getWatersheds');
    Route::get('/getCommodities', 'NgpController@getCommodities');
    Route::post('/saveData/{id}', 'NgpController@saveData');
    Route::post('/saveFiles/{table}/{id}/{index}', 'NgpController@saveFiles');
    Route::get('/getSpecies', 'NgpController@getSpecies');
    Route::get('/getDataByOffice/{id}', 'NgpController@getDataByOffice');
    Route::get('/getDataById/{id}', 'NgpController@getDataById');
    Route::post('/updateData/{table}/{id}', 'NgpController@updateData');
    Route::post('/updateFiles/{table}/{id}/{index}', 'NgpController@updateFiles');
    Route::delete('/delete_data', 'NgpController@delete_data');
    Route::get('/getDatabaseStatus', 'NgpController@getDatabaseStatus');
    Route::get('/getYears', 'NgpController@getYears');
    Route::get('/getQuickData/{year}/{fund}', 'NgpController@getQuickData');
    Route::get('/getSystemUpdatesFromGitlab', 'NgpController@getSystemUpdatesFromGitlab');
    Route::get('/getSystemLogs', 'NgpController@getSystemLogs');
    Route::post('/changePassword', 'NgpController@changePassword');
    Route::get('/getNameOfPOs', 'NgpController@getNameOfPOs');
    Route::get('/getNameOfPOsLoad/{number}', 'NgpController@getNameOfPOsLoad');
});

// ----------------------
// TOES Module
// ----------------------
Route::prefix('toes')->group(function () {
    Route::post('/login', 'ToesController@login');
    Route::get('/logout', 'ToesController@logout');
    Route::get('/getPendingTravel/{emp_id}', 'ToesController@getPendingTravel');
    Route::get('/keepAlive', 'ToesController@keepAlive');
    Route::get('/viewApprovedTO/{to_id}', 'ToesController@viewApprovedTO');
    Route::get('/getUserDetails/{to_id}', 'ToesController@getUserDetails');
    Route::get('/getListOfOffices', 'ToesController@getListOfOffices');
    Route::post('/saveProfileInformation/{type}', 'ToesController@saveProfileInformation');
    Route::get('/countTravelOrderThisYear/{emp_id}', 'ToesController@countTravelOrderThisYear');
    Route::post('/saveTravelInformation', 'ToesController@saveTravelInformation');
    Route::post('/saveFiles/{id}', 'ToesController@saveFiles');
    Route::delete('/deleteTo/{id}', 'ToesController@deleteTo');
    Route::get('/viewPendingTO/{to_id}', 'ToesController@viewPendingTO');
    Route::post('/updateTravelInformation', 'ToesController@updateTravelInformation');
    Route::get('/getTravelsForRecommendation/{user}/{acct_type}', 'ToesController@getTravelsForRecommendation');
    Route::post('/fileExists', 'ToesController@fileExists');
    Route::post('/recommendTravel', 'ToesController@recommendTravel');
    Route::post('/deleteTravel', 'ToesController@deleteTravel');
    Route::get('/getOfficesUnderArdForRecommendation/{user}/{acct_type}', 'ToesController@getOfficesUnderArdForRecommendation');
    Route::get('/getTravelsForApproval/{user}/{acct_type}', 'ToesController@getTravelsForApproval');
    Route::post('/approveTravel', 'ToesController@approveTravel');
    Route::get('/getOffice', 'ToesController@getOffice');
    Route::get('/readTo', 'ToesController@readTo');
    Route::get('/weekTOmisoc', 'ToesController@weekTOmisoc');
    Route::get('/weekTOmisor', 'ToesController@weekTOmisor');
    Route::get('/weekTOlanao', 'ToesController@weekTOlanao');
    Route::get('/weekTOcamiguin', 'ToesController@weekTOcamiguin');
    Route::get('/weekTObukidnon', 'ToesController@weekTObukidnon');
    Route::get('/weekTOregion', 'ToesController@weekTOregion');
    Route::get('/readAllTo', 'ToesController@readAllTo');
    Route::get('/readallByAir', 'ToesController@readallByAir');
    Route::get('/getTOmisoc', 'ToesController@getTOmisoc');
    Route::get('/getTOmisocByAir', 'ToesController@getTOmisocByAir');
    Route::get('/getTObukidnon', 'ToesController@getTObukidnon');
    Route::get('/getTObukidnonByAir', 'ToesController@getTObukidnonByAir');
    Route::get('/getTOcamiguin', 'ToesController@getTOcamiguin');
    Route::get('/getTOcamiguinByAir', 'ToesController@getTOcamiguinByAir');
    Route::get('/getTOlanao', 'ToesController@getTOlanao');
    Route::get('/getTOlanaoByAir', 'ToesController@getTOlanaoByAir');
    Route::get('/getTOmisor', 'ToesController@getTOmisor');
    Route::get('/getTOmisorByAir', 'ToesController@getTOmisorByAir');
    Route::get('/getTOregion', 'ToesController@getTOregion');
    Route::get('/getTOregionByAir', 'ToesController@getTOregionByAir');
    Route::get('/countApprovedTo/{emp_id}', 'ToesController@countApprovedTo');
    Route::get('/updateApprovedTravelOrderSeen/{emp_id}', 'ToesController@updateApprovedTravelOrderSeen');
    Route::get('/getEmpDetailsWithOfficeByOffice/{office_id}', 'ToesController@getEmpDetailsWithOfficeByOffice');
    Route::get('/getAllOffice', 'ToesController@getAllOffice');
    Route::post('/updateProfileByAdmin/{type}', 'ToesController@updateProfileByAdmin');
});

// ----------------------
// DMS Module
// ----------------------
Route::prefix('dms')->group(function () {
    // Public routes
    Route::post('/login', 'DmsAuthController@login');
    Route::post('/forgot-password', 'DmsAuthController@forgotpassword');
    Route::post('/reset-passwords', 'DmsAuthController@resetPasswords');
    Route::get('/reset-password/{token}', 'DmsAuthController@showResetForm')->name('password.reset');

    
    Route::middleware('auth:sanctum')->group(function () {
         Route::get('/users', 'DmsAuthController@getAllUsers');
                    Route::get('/me', 'DmsAuthController@me');
        Route::post('/logout', 'DmsAuthController@logout');
        Route::post('/user', 'DmsAuthController@createUser');
        Route::put('/users/{id}', 'DmsAuthController@updateUser');
        Route::delete('/users/{id}', 'DmsAuthController@deleteUser');
        Route::post('/users/{id}/reset-password', 'DmsAuthController@resetPassword');
    });
});

