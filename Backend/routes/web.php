<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
    return view('welcome');
});
Route::middleware(['cors'])->group(function () {
    Route::post('/hogehoge', 'Controller@hogehoge');
});
Route::get('/csrf-token', function() {
    return response()->json(['csrf_token' => csrf_token()]);
});
Route::get('/sessionStatus', function() {
    return ['user' => Auth::user() ? Auth::user() : null];
});

Route::get('/toes/sessionStatus', function() {
    return [Auth::guard('second_db')->user() ? Auth::guard('second_db')->user() : null];
});
Route::get('/dms/sessionStatus', function() {
    return [Auth::guard('third_db')->user() ? Auth::guard('third_db')->user() : null];
});

Route::post('ngp/login', 'NgpController@login');
Route::get('ngp/logout', 'NgpController@logout');
Route::get('ngp/getMunicipalities/{id}/{penro_id}', 'NgpController@getMunicipalities');
Route::get('ngp/getBarangays/{id}', 'NgpController@getBarangays');
Route::get('ngp/getWatersheds/{id}', 'NgpController@getWatersheds');
Route::get('ngp/getCommodities', 'NgpController@getCommodities');
Route::post('ngp/saveData/{id}', 'NgpController@saveData');
Route::post('ngp/saveFiles/{table}/{id}/{index}', 'NgpController@saveFiles');
Route::get('ngp/getSpecies', 'NgpController@getSpecies');
Route::get('ngp/getDataByOffice/{id}', 'NgpController@getDataByOffice');
Route::get('ngp/getDataById/{id}', 'NgpController@getDataById');
Route::post('ngp/updateData/{table}/{id}', 'NgpController@updateData');
Route::post('ngp/updateFiles/{table}/{id}/{index}', 'NgpController@updateFiles');
Route::delete('ngp/delete_data', 'NgpController@delete_data');
Route::get('ngp/getDatabaseStatus', 'NgpController@getDatabaseStatus');
Route::get('ngp/getYears', 'NgpController@getYears');
Route::get('ngp/getQuickData/{year}/{fund}', 'NgpController@getQuickData');
Route::get('ngp/getSystemUpdatesFromGitlab', 'NgpController@getSystemUpdatesFromGitlab');
Route::get('ngp/getSystemLogs', 'NgpController@getSystemLogs');
Route::post('ngp/changePassword', 'NgpController@changePassword');
Route::get('ngp/getNameOfPOs', 'NgpController@getNameOfPOs');
Route::get('ngp/getNameOfPOsLoad/{number}', 'NgpController@getNameOfPOsLoad');



Route::post('toes/login', 'ToesController@login');
Route::get('toes/logout', 'ToesController@logout');
Route::get('toes/getPendingTravel/{emp_id}', 'ToesController@getPendingTravel');
Route::get('toes/keepAlive', 'ToesController@keepAlive');
Route::get('toes/viewApprovedTO/{to_id}', 'ToesController@viewApprovedTO');
Route::get('toes/getUserDetails/{to_id}', 'ToesController@getUserDetails');
Route::get('toes/getListOfOffices', 'ToesController@getListOfOffices');
Route::post('toes/saveProfileInformation/{type}', 'ToesController@saveProfileInformation');
Route::get('toes/countTravelOrderThisYear/{emp_id}', 'ToesController@countTravelOrderThisYear');
Route::post('toes/saveTravelInformation', 'ToesController@saveTravelInformation');
Route::post('toes/saveFiles/{id}', 'ToesController@saveFiles');
Route::delete('toes/deleteTo/{id}', 'ToesController@deleteTo');
Route::get('toes/viewPendingTO/{to_id}', 'ToesController@viewPendingTO');
Route::post('toes/updateTravelInformation', 'ToesController@updateTravelInformation');
Route::get('toes/getTravelsForRecommendation/{user}/{acct_type}', 'ToesController@getTravelsForRecommendation');
Route::post('toes/fileExists', 'ToesController@fileExists');
Route::post('toes/recommendTravel', 'ToesController@recommendTravel');
Route::post('toes/deleteTravel', 'ToesController@deleteTravel');
Route::get('toes/getOfficesUnderArdForRecommendation/{user}/{acct_type}', 'ToesController@getOfficesUnderArdForRecommendation');
Route::get('toes/getTravelsForApproval/{user}/{acct_type}', 'ToesController@getTravelsForApproval');
Route::post('toes/approveTravel', 'ToesController@approveTravel');
Route::get('toes/getOffice', 'ToesController@getOffice');
Route::get('toes/readTo', 'ToesController@readTo');
Route::get('toes/weekTOmisoc', 'ToesController@weekTOmisoc');
Route::get('toes/weekTOmisor', 'ToesController@weekTOmisor');
Route::get('toes/weekTOlanao', 'ToesController@weekTOlanao');
Route::get('toes/weekTOcamiguin', 'ToesController@weekTOcamiguin');
Route::get('toes/weekTObukidnon', 'ToesController@weekTObukidnon');
Route::get('toes/weekTOregion', 'ToesController@weekTOregion');
Route::get('toes/readAllTo', 'ToesController@readAllTo');
Route::get('toes/readallByAir', 'ToesController@readallByAir');
Route::get('toes/getTOmisoc', 'ToesController@getTOmisoc');
Route::get('toes/getTOmisocByAir', 'ToesController@getTOmisocByAir');
Route::get('toes/getTObukidnon', 'ToesController@getTObukidnon');
Route::get('toes/getTObukidnonByAir', 'ToesController@getTObukidnonByAir');
Route::get('toes/getTOcamiguin', 'ToesController@getTOcamiguin');
Route::get('toes/getTOcamiguinByAir', 'ToesController@getTOcamiguinByAir');
Route::get('toes/getTOlanao', 'ToesController@getTOlanao');
Route::get('toes/getTOlanaoByAir', 'ToesController@getTOlanaoByAir');
Route::get('toes/getTOmisor', 'ToesController@getTOmisor');
Route::get('toes/getTOmisorByAir', 'ToesController@getTOmisorByAir');
Route::get('toes/getTOregion', 'ToesController@getTOregion');
Route::get('toes/getTOregionByAir', 'ToesController@getTOregionByAir');
Route::get('toes/countApprovedTo/{emp_id}', 'ToesController@countApprovedTo');
Route::get('toes/updateApprovedTravelOrderSeen/{emp_id}', 'ToesController@updateApprovedTravelOrderSeen');
Route::get('toes/getEmpDetailsWithOfficeByOffice/{office_id}', 'ToesController@getEmpDetailsWithOfficeByOffice');
Route::get('toes/getAllOffice', 'ToesController@getAllOffice');
Route::post('toes/updateProfileByAdmin/{type}', 'ToesController@updateProfileByAdmin');





    Route::prefix('dms')->group(function () {
    Route::post('/login', 'DmsAuthController@login');
        Route::get('/logout', 'DmsAuthController@logout');
        Route::get('/me', 'DmsAuthController@me'); // Add this line
        Route::get('/users', 'DmsAuthController@getAllUsers');
        Route::post('/users', 'DmsAuthController@createUser'); // Change from /user to /users
        Route::put('/users/{id}', 'DmsAuthController@updateUser');
        Route::delete('/users/{id}', 'DmsAuthController@deleteUser');
        Route::post('/users/{id}/reset-password', 'DmsAuthController@resetPassword');
    Route::get('/getDueDocuments', 'DmsController@getDueDocuments');



});
