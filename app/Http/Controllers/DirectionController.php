<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log; 
use Illuminate\Support\Facades\DB;

class DirectionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    // function to decode unicode string from Google Maps API
    // please read https://gist.github.com/dertajora/0138995354b1c963515924928ddf517c for the detail explanation
    public function decode_unicode($str){
        // use to delete all unicode string found on str variable, unicode is detected by \uxxxx
        $new_str = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                        }, $str);
        // when there are new line in sentence using div, we will insert ". " string before it
        $prefix = ". ";
        $new_str = substr_replace($new_str, $prefix, strpos($new_str, "<div"), 0);
       
        if (substr($new_str, 0, strlen($prefix)) == $prefix) {
           $new_str = substr($new_str, strlen($prefix));
        } 
        // remove all html tags
        $string_fixed = strip_tags($new_str);

        return $string_fixed;
    }

    public function get_direction(){

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        ); 

        $mode = "driving";

        $service_url = 'https://maps.googleapis.com/maps/api/directions/json?mode=walking&origin=53.2835727000,-0.3338594000&destination=52.1284000000,-0.2876890000&key=AIzaSyCcODVGYqPIqoosgKH-nBbA_CWYc2LjT_U';
        
        $result_from_api = file_get_contents($service_url, false, stream_context_create($arrContextOptions));
        
        
        $result = (array)json_decode($result_from_api);

        $data = $result['routes'][0]->legs;

        $response['mode'] = $mode; 
        $response['copyrights'] = $result['routes'][0]->copyrights; 
        $response['distance'] = $data[0]->distance->text; 
        $response['duration'] = $data[0]->duration->text; 
        $response['start_address'] = $data[0]->start_address; 
        $response['start_lat'] = $data[0]->start_location->lat; 
        $response['start_longi'] = $data[0]->start_location->lng; 
        $response['end_address'] = $data[0]->end_address; 
        $response['end_lat'] = $data[0]->end_location->lat; 
        $response['end_longi'] = $data[0]->end_location->lng; 

        $guidance_raw = $data[0]->steps;
        $list_step = array();
        
        echo "<pre>";
        foreach ($guidance_raw as $data) {
            $step['distance'] = $data->distance->text; 
            $step['duration'] = $data->duration->text;
            $step['start_lat'] = $data->start_location->lat;
            $step['start_longi'] = $data->start_location->lng; 
            $step['end_lat'] = $data->end_location->lat;
            $step['end_longi'] = $data->end_location->lng;
            $step['instruction'] =  $this->decode_unicode($data->html_instructions);
            array_push($list_step,$step);
        }

        $response['steps'] = $list_step;

        Log::info('Get data from Google Maps API success');
        
        return response()->json(['result_code' => 1, 'result_message' => 'Sent Data Guidance Success']);
    }

    

    

    
}