<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});


$app->get('api/version','BranchController@version');
$app->post('api/nearest_branch','BranchController@nearest');
// direction customized from Google Maps API
$app->post('api/direction','DirectionController@get_direction');

// Uber API
$app->get('uber/redirect_uri','UberController@redirect_uri');
$app->get('uber/list_product','UberController@list_uber_product');
$app->get('uber/get_token','UberController@get_token');
$app->get('uber/request_uber','UberController@request_uber');
$app->get('uber/current_request','UberController@current_request');
$app->get('uber/delete_request','UberController@delete_request');

// download data from Barclays API
$app->get('download_branch_from_barclays','BarclaysController@download_data_branch');
$app->get('download_atm_from_barclays','BarclaysController@download_data_atm');

$app->get('laboratorium','UberController@laboratorium');

