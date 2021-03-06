<?php

use App\SmsLog;
use GuzzleHttp\Client;

if(!function_exists('transactionNumberGenerator')) {

    /**
     * @return string
     */
    function transactionNumberGenerator(){
        $string = "AAD7RYZY9B";

        $last_char=substr($string,-1);
        $rest=substr($string, 0, -1);
        switch ($last_char) {
            case '':
                $next= 'A';
                break;
            case 'Z':
                $next = '0';
                $unique = ++$rest;
                $rest = $unique;
                break;
            case '9':
                $next= 'A';
                break;
            default:
                $next = ++$last_char;
                break;
        }
        $string=$rest.$next;
        return $string;
    }
}

if(!function_exists('lipishaSendSms')) {
    function lipishaSendSms($phone,$message){

        $client = new Client();
        $res = $client->post('https://api.lipisha.com/v2/send_sms', [
            'form_params' => [
                "api_key"=>env('LIPISHA_API_KEY'),
                "api_signature"=>env('LIPISHA_API_SIGNATURE'),
                "account_number"=>"30439",
                "mobile_number"=>"254".substr($phone,-9),
                "message"=>$message,
                "reference"=>"MATSMS".rand(0,999999)
            ]
        ]);

        $result = $res->getBody()->getContents();

        $format_res = json_decode($result,true);
        if ($format_res['status']['status'] = "SUCCESS"){
            $log = new SmsLog();
            $log->reference_no =$format_res['content']['transaction'];
            $log->phone =$format_res['content']['mobile_number'];
            $log->message =$format_res['content']['message'];
            $log->cost =$format_res['content']['cost'];
            $log->save();
        }
    }
}


