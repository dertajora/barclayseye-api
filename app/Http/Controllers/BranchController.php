<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log; 
use App\Model\Branch;
use Illuminate\Support\Facades\DB;

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
        $all_data = $request->all();
        
        
        Log::info('Request_Get Nearest Branch_Data Received:'.json_encode($all_data));
        $user_lat = $request->input('lat');
        $user_longi = $request->input('longi');

        // 1
        // $nearest_branch = Branch::all();
        // 2
        // $nearest_branch  = DB::table('branchs')->get();
        // 3
        $nearest_branch = DB::select('select branch_name, address, lat, longi ,(
                                          6371 /*3959*/ * acos (
                                          cos ( radians('.$user_lat.') )
                                          * cos( radians( lat ) )
                                          * cos( radians( longi ) - radians('.$user_longi.') )
                                          + sin ( radians('.$user_lat.') )
                                          * sin( radians( lat ) )
                                        )
                                    ) AS distance from branchs where type = 1 order by distance asc limit 3');
        
        Log::info('Response_Send Nearest Branch_Data Sent:'.json_encode($nearest_branch));
        return response()->json(['result_code' => 1, 'result_message' => 'Data Nearest Branch Sent', 'data' => $nearest_branch]);
    }
    //
}
