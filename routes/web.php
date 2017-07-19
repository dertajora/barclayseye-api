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
$app->get('api/get_direction','DirectionController@get_direction');


// download data from Barclays API
$app->get('download_branch_from_barclays','BarclaysController@download_data_branch');
$app->get('download_atm_from_barclays','BarclaysController@download_data_atm');

