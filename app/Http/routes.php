<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'MiscController@index');


Route::get('/{veranstaltung}/aks', 'AkListController@index');
Route::get('/{veranstaltung}/aks/{ak_slug}', 'AkListController@show');
Route::get('/ak/{ak_id}', 'AkListController@showById');


Route::get('/api/v1/{ver_k}/login', 'BackEndController@login');


Route::get('/api/v1/{ver_k}/ak/{id}', 'PublicApiController@getAk');


Route::put('/api/v1/{ver_k}/ak/{id}', 'BackEndController@modifyAk');
//apply=###
//(data)
//von=&bis=&all=
Route::post('/api/v1/{ver_k}/ak', 'BackEndController@createAk');


Route::delete('/api/v1/{ver_k}/raumbuchung/{id}', 'BackEndController@deleteRaumbuchung');


Route::post('/api/v1/{ver_k}/raumbuchung', 'BackEndController@createRaumbuchung');


Route::get('/api/v1/{ver_k}/zielgruppen', 'PublicApiController@getStundenplanNames');
Route::get('/api/v1/{ver_k}/stundenplan', 'PublicApiController@getStundenplan');
Route::get('/api/v1/veranstaltungen', 'PublicApiController@getVeranstaltungen');





