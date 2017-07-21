<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CredentialController;
use Illuminate\Http\Request;
use Log; 
use App\Model\User;
use Illuminate\Support\Facades\DB;

class UberController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $controller = new CredentialController; 
        $this->client_secret = $controller->client_secret();
        $this->client_id = $controller->client_id();
        $this->server_token = $controller->server_token();
        $this->user_token = $controller->user_token();
    }

    public function laboratorium(){
        
    }
        

    public function redirect_uri(){

        // example of URL for OAuth 2.0
        // https://login.uber.com/oauth/v2/authorize?response_type=code&client_id=ljLnWU62z8AIzYndXedVZJCFmtECs1FY&scope=request%20profile%20history&redirect_uri=http://localhost/barclayseye-api/public/uber/redirect_uri

        // get parameter code from Uber response OAuth after 
        $authorization_code = $_GET['code'];

        # below is code to get token based on authorization code after Uber redirect to our Redirect URI

        // endpoint Uber get Access Token
        $url_token = 'https://login.uber.com/oauth/v2/token';

        $data = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://localhost/barclayseye-api/public/uber/redirect_uri',
            'code' => $authorization_code
        );

        // to make data parameter sent to Uber API
        $post_parameter = http_build_query($data) ;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url_token); 
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_parameter);
        
        $result = curl_exec($curl);
        
        $data = (array) json_decode($result);
        curl_close($curl);

        // trigger function get profile
        $access_token = $data['access_token'];
        $data_user = $this->get_profile($access_token);

        // save or update user to BarclaysEye DB
        $user = User::where('id', '=', $data_user['uuid'] )->first();
        if ($user === null) {
            $user = new User;
            $user->id = $data_user['uuid'];
            $user->first_name = $data_user['first_name'];
            $user->last_name = $data_user['last_name'];
            $user->email = $data_user['email'];
            $user->rider_id = $data_user['rider_id'];
            $user->token = $access_token;
            $user->save();
        }else{
            $user = User::find($data_user['uuid']);
            $user->id = $data_user['uuid'];
            $user->first_name = $data_user['first_name'];
            $user->last_name = $data_user['last_name'];
            $user->email = $data_user['email'];
            $user->rider_id = $data_user['rider_id'];
            $user->token = $access_token;
            $user->save();
        }

        echo "Save user sukses";
   
    }

    public function get_profile($access_token){
        # below is code to get data user from Uber API
        $url_profile = 'https://sandbox-api.uber.com/v1.2/me';

        // endpoint Me Uber
        $headers_profile = array(
            'Authorization: Bearer '.$access_token.'',
            'Content-Type:application/json',
            'Accept-Language:en_EN'
        ); 

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url_profile); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_profile);
        $result = curl_exec($curl);

        $data = (array) json_decode($result);
        curl_close($curl);
        return $data;
    }

    public function get_uber_first_product($lat, $longi){
        // endpoint Uber API to get list product that available on that area
        $service_url = "https://sandbox-api.uber.com/v1.2/products?latitude=".$lat."&longitude=".$longi;

        $headers = array(
            'Authorization:Token '.$this->server_token.'',
            'Content-Type:application/json',
            'Accept-Language:en_EN'
        ); 

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $service_url); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        $data = (array) json_decode($result);
        return $data['products'][0]->product_id;
        
    }

    public function request_uber(Request $request){

        // in sandbox environment we will delete previous Uber request because there is no possibility of Uber booking in sandbox is accepted by Uber
        $this->delete_request();
        Log::info('Delete previous Uber book success');

        // endpoint Uber to get request estimation
        $url_request = 'https://sandbox-api.uber.com/v1.2/requests/estimate';

        $headers = array(
            'Authorization: Bearer '.$this->user_token,
            'Content-Type:application/json',
            'Accept-Language:en_EN'
        );

        $start_latitude = $request->input('lat_start');
        $start_longitude = $request->input('long_start');
        $end_latitude = $request->input('lat_end');
        $end_longitude = $request->input('long_end');

        // call Uber API to get first product ID that available on that area
        $product_id = $this->get_uber_first_product($start_latitude, $start_longitude);

        // parameter needed to sent request estimation to Uber API 
        $data_param = array('product_id' => $product_id, 
                            'start_latitude' => $start_latitude, 
                            'start_longitude' => $start_longitude,
                            'end_latitude' => $end_latitude,
                            'end_longitude' => $end_longitude);

        $parameter = json_encode($data_param);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url_request); 
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameter);

        $result = curl_exec($curl);
        Log::info('Get estimation of Uber Book and get fare_id');

        $data = (array)json_decode($result);
        $data_param['fare_id'] = $data['fare']->fare_id;

        // After sent 'request estimation' request to Uber API, we should confirm booking request with calling another endpoint, 
        // Parameter sent to confirm request similar with parameter for 'request estimation' but with  with additional parameter fare_id
        $data = (array) json_decode($this->confirm_book($data_param));
        Log::info('Request Uber ride from Uber API success');

        // in sandbox environment, we couldn't get data driver because no one processing our request, so we decided to sent dummy driver info to make better user experience
        $book_detail['request_id'] = $data['request_id'];
        $book_detail['product_id'] = $data['product_id']; 
        $book_detail['user_lat'] = $start_latitude;
        $book_detail['user_longi'] = $start_longitude;
        $book_detail['status'] = "accepted"; 
        $book_detail['estimate_arrival_time'] = "15 min"; 
        $book_detail['driver_distance'] = '2 km';
        $book_detail['driver_lat'] = '-6.178797';
        $book_detail['driver_longi'] = '106.792347';
        $book_detail['driver_name'] = 'Michael John Doe';
        $book_detail['driver_phone'] = '+6285742724990';
        $book_detail['vehicle_maker'] = 'Audi';
        $book_detail['vehicle_model'] = 'Q5';
        $book_detail['license_plate'] = 'UK P L 1 T B';

        Log::info('Sent Detail Uber Book to User');
        return response()->json(['result_code' => 1, 'result_message' => 'Request Uber ride from Uber API success', 'data' => $book_detail]);

    }

    // make request using fare all parameter from 'request estimation' response plus parameter fare_id
    public function confirm_book($data_param){

        $url_request = 'https://sandbox-api.uber.com/v1.2/requests';

        $headers = array(
            'Authorization: Bearer '.$this->user_token,
            'Content-Type:application/json',
            'Accept-Language:en_EN'
        );

        $parameter = json_encode($data_param);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url_request); 
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameter);

        $result = curl_exec($curl);

        return $result;
        
    }
    
    // get current Uber request of user
    public function current_request(){
      
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, "https://sandbox-api.uber.com/v1.2/requests/current");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


      $headers = array();
      $headers[] = "Authorization: Bearer ".$this->user_token;
      $headers[] = "Accept-Language: en_US";
      $headers[] = "Content-Type: application/json";
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $result = curl_exec($ch);
      if (curl_errno($ch)) {
          echo 'Error:' . curl_error($ch);
      }
      curl_close ($ch);
      print_r($result);
    }

    // cancel current Uber request of user
    public function delete_request(){
      
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, "https://sandbox-api.uber.com/v1.2/requests/current");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");


      $headers = array();
      $headers[] = "Authorization: Bearer ".$this->user_token;
      $headers[] = "Accept-Language: en_US";
      $headers[] = "Content-Type: application/json";
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $result = curl_exec($ch);
      if (curl_errno($ch)) {
          echo 'Error:' . curl_error($ch);
      }
      print_r($result);

      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close ($ch);
      
    }

    // get list product that available on user area
    public function list_uber_product(){
        // INDIA
        // $service_url = "https://sandbox-api.uber.com/v1.2/products?latitude=28.620136&longitude=77.210700";
        // ID
        $service_url = "https://sandbox-api.uber.com/v1.2/products?latitude=-6.189915&longitude=106.797791";
        // UK
        // $service_url = "https://sandbox-api.uber.com/v1.2/products?latitude=53.485097&longitude=-2.241439";
       
        $headers = array(
            'Authorization:Token '.$this->server_token.'',
            'Content-Type:application/json',
            'Accept-Language:en_EN'
        ); 

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $service_url); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        $data = (array) json_decode($result);
        
        print_r($data);
        curl_close ($ch);
    }
}
