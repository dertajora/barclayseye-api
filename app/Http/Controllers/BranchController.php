<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log; 
use App\Model\Branch;

class BranchController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function version(){
        Log::info('Get Version');
        return response()->json(['version' => '1.0', 'state' => 'Development', 'year' => 2017]);
    }

    public function nearest(Request $request){

        // print_r($request->route());
        Log::info('T2 : Inquiry to Parner');
        // some function to call API Partner
        $response_partner = "dummy";
        Log::info('T3 : Response from Parner');

        return response()->json(['name' => 'Pelangi', 'state' => 'Sukses']);
    }
    //
}
