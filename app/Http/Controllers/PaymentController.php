<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function generateAccessToken()
    {
        $consumer_key = 'dM1AQniOznQkoFohuPGXowgMALOcUwsr';
        $consumer_secret = 'l31P1jJLbwhKkHzy';
        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);

        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization:Basic ' . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curl_response = curl_exec($curl);
        $access_token = json_decode($curl_response);
        return $access_token->access_token;
    }

    public function stkPush()
    {
        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $business_shortcode = 174379;
        $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
        $timestamp = Carbon::rawParse('now')->isoFormat('YYYYMMDDHHmmss');
        $password = base64_encode($business_shortcode . $passkey . $timestamp);
        $amount = 1;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer IQfcteM6a00ZZmxl4v7tBLVxvJWZ'));

        $curl_post_data = array(
            "BusinessShortCode" => $business_shortcode,
            "Password" => $password,
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $amount,
            "PartyA" => 254724753175,
            "PartyB" => 174379,
            "PhoneNumber" => 254724753175,
            "CallBackURL" => "https://b720-105-27-98-86.ngrok.io/api/callback",
            "AccountReference" => "CompanyXLTD",
            "TransactionDesc" => "Payment of X"
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);

        return $curl_response;

    }
}