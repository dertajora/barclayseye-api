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

        // Insert some stuff
        DB::table('branchs')->insert($data_branch);
        return response()->json(['version' => '1.0', 'state' => 'Development', 'year' => 2017]);
    }

    public function check_param($lat, $longi, $type){
      $param =  array();
      if ($lat == null || $longi == null || $type == null) {
          $message = "Invalid Parameter : ";
          if ($lat == null) {
              array_push($param,"Latitude");
          }

          if ($longi == null) {
              array_push($param,"Longitude");
          }

          if ($type == null) {
              array_push($param,"Type Cabang");
          }
          
          $param = implode(",", $param);
          $message = $message.$param;
          return $message;
      }else{
        return false;
      }
      
    }

    public function nearest(Request $request){
        $all_data = $request->all();
        
        
        Log::info('Request_Get Nearest Branch_Data Received:'.json_encode($all_data));
        $user_lat = $request->input('lat');
        $user_longi = $request->input('longi');
        $type = $request->input('type');

        $error_param = $this->check_param($user_lat, $user_longi, $type);

        if ($error_param != false) {
            Log::info('Response_Invalid Parameter:'.json_encode($error_param));
            return response()->json(['result_code' => 2, 'result_message' => $error_param, 'data' => '']);
        }

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
                                    ) AS distance from branchs where type = '.$type.' order by distance asc limit 3');
        if ($type == 1) {
            Log::info('Response_Send Nearest Branch_Data Sent:'.json_encode($nearest_branch));
            return response()->json(['result_code' => 1, 'result_message' => 'Data Nearest Branch Sent', 'data' => $nearest_branch]);
        }else if($type == 2){
            Log::info('Response_Send Nearest ATM_Data Sent:'.json_encode($nearest_branch));
            return response()->json(['result_code' => 1, 'result_message' => 'Data Nearest ATM Sent', 'data' => $nearest_branch]);
        }
        
    }
    //
}
