<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CredentialController;
use Illuminate\Http\Request;
use Log; 
use App\Model\Branch;
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
        $this->get_profile($access_token);
        
    }

    public function get_profile($access_token){
        # below is code to get data user from Uber API
        $url_profile = 'https://api.uber.com/v1.2/me';

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
    }


    public function list_uber_product(){
        $service_url = "https://api.uber.com/v1.2/products?latitude=-6.189915&longitude=106.797791";
       
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
        print_r($result);
        curl_close($curl);
    }

    public function request_estimate(){
        // endpoint Uber get Access Token
        $url_request = 'https://api.uber.com/v1.2/requests/estimate';

        $headers = array(
            'Authorization: Bearer '.$this->user_token,
            'Content-Type:application/json',
            'Accept-Language:en_EN'
        );

        // to make data parameter sent to Uber API
        $data_param = array('product_id' => '89da0988-cb4f-4c85-b84f-aac2f5115068' , 
                            'start_latitude' => '-6.178797', 
                            'start_longitude' => '106.792347',
                            'end_latitude' => '-6.189963',
                            'end_longitude' => '106.798663');
        $parameter = json_encode($data_param);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url_request); 
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameter);

        $result = curl_exec($curl);
        
        print_r($result);
    }
    
}
