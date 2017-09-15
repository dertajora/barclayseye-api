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

// Get API Version
$app->get('api/version','BranchController@version');

// Get nearest Barclays ATM or Branch 
$app->post('api/nearest_branch','BranchController@nearest');

// Get customized direction based on Google Maps API
$app->post('api/direction','DirectionController@get_direction');

// Request Uber ride from Uber API
$app->post('api/request_uber','UberController@request_uber');

// Estimate Uber fare
$app->post('api/estimate_uber','UberController@estimate_fare');

// Save user token after give authorization to BarclaysEye App
$app->get('api/redirect_uri','UberController@redirect_uri');

// Uber API
$app->get('uber/list_product','UberController@list_uber_product');
$app->get('uber/get_token','UberController@get_token');
$app->get('uber/current_request','UberController@current_request');
$app->get('uber/delete_request','UberController@delete_request');

// Download data from Barclays API
$app->get('download_branch_from_barclays','BarclaysController@download_data_branch');
$app->get('download_atm_from_barclays','BarclaysController@download_data_atm');

// Learning Space
$app->get('laboratorium','UberController@laboratorium');

