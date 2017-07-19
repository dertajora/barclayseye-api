<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log; 
use App\Model\Branch;
use Illuminate\Support\Facades\DB;

class BarclaysController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function download_data_branch(){

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        ); 

        $service_url = 'https://atlas.api.barclays/open-banking/v1.3/branches';
        $result_from_api = file_get_contents($service_url, false, stream_context_create($arrContextOptions));
        $result = (array)json_decode($result_from_api);
        
        $list_branch = $result['data'];
        $data_branch = array();
        $i= 0;

        foreach ($list_branch as $branch) {
            $data_branch[$i]['BranchIdentification'] = $branch->BranchIdentification;
            $data_branch[$i]['StreetName'] = $branch->Address->StreetName;
            $data_branch[$i]['BuildingNumberOrName'] = $branch->Address->BuildingNumberOrName;
            $data_branch[$i]['TownName'] = $branch->Address->TownName;
            if (isset($branch->Address->PostCode)) {
                $data_branch[$i]['PostCode'] = $branch->Address->PostCode;
            }else{
                $data_branch[$i]['PostCode'] = '-';
            }
            
            if (isset($branch->Address->CountrySubDivision)) {
                $data_branch[$i]['CountrySubDivision'] = $branch->Address->CountrySubDivision ;
            }else{
                $data_branch[$i]['CountrySubDivision'] = "-";
            }
            
            $data_branch[$i]['Country'] = $branch->Address->Country;
            $data_branch[$i]['Latitude'] = $branch->GeographicLocation->Latitude;
            $data_branch[$i]['Longitude'] = $branch->GeographicLocation->Longitude;
            if (isset($branch->BranchPhoto)) {
                $data_branch[$i]['BranchPhoto'] = $branch->BranchPhoto;
            }else{
                $data_branch[$i]['BranchPhoto'] = "-";
            }
            
            $data_branch[$i]['TelephoneNumber'] = $branch->TelephoneNumber;
            $data_branch[$i]['Type'] = 1;
            $i++;
        }

        // insert data branches to database
        DB::table('branches')->insert($data_branch);

        Log::info('Download Data Branch Success');
        return response()->json(['result_code' => 1, 'result_message' => 'Download Data Branchs from Barclays API Success']);
    }

    public function download_data_atm(){

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  
        
        $service_url = 'https://atlas.api.barclays:443/open-banking/v1.3/atms';
        $result_from_api = file_get_contents($service_url, false, stream_context_create($arrContextOptions));
        $result = (array)json_decode($result_from_api);
        
        $list_atm = $result['data'];
        $data_atm = array();
        $i= 0;
        
        foreach ($list_atm as $branch) {
            $data_atm[$i]['BranchIdentification'] = $branch->ATMID;
            $data_atm[$i]['StreetName'] = $branch->Address->StreetName;

            if (isset($branch->Address->BuildingNumberOrName)) {
                $data_atm[$i]['BuildingNumberOrName'] = $branch->Address->BuildingNumberOrName;
            }else{
                $data_atm[$i]['BuildingNumberOrName'] = '-';
            }

            if (isset($branch->Address->TownName)) {
                $data_atm[$i]['TownName'] = $branch->Address->TownName;
            }else{
                $data_atm[$i]['TownName'] = '-';
            }

            if (isset($branch->Address->PostCode)) {
                $data_atm[$i]['PostCode'] = $branch->Address->PostCode;
            }else{
                $data_atm[$i]['PostCode'] = '-';
            }
            
            if (isset($branch->Address->CountrySubDivision)) {
                $data_atm[$i]['CountrySubDivision'] = $branch->Address->CountrySubDivision ;
            }else{
                $data_atm[$i]['CountrySubDivision'] = "-";
            }
            
            $data_atm[$i]['Country'] = $branch->Address->Country;
            $data_atm[$i]['Latitude'] = $branch->GeographicLocation->Latitude;
            $data_atm[$i]['Longitude'] = $branch->GeographicLocation->Longitude;
            
            $data_atm[$i]['Type'] = 2;
            $i++;
        }

        // insert data ATM to database
        DB::table('branches')->insert($data_branch);

        Log::info('Download Data ATM Success');
        return response()->json(['result_code' => 1, 'result_message' => 'Download Data Branchs from Barclays API Success']);
    }

    

    
}
