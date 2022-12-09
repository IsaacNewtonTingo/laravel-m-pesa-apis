<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    //daraja option
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

    public function stkPush(Request $request)
    {
        $fullName = $request->fullName;
        $phoneNumber = $request->phoneNumber;
        $amount = $request->amount;
        //------------generate access token-----------

        $consumer_key = 'dM1AQniOznQkoFohuPGXowgMALOcUwsr';
        $consumer_secret = 'l31P1jJLbwhKkHzy';
        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);
        $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $access_token_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization:Basic ' . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $access_token_response = curl_exec($curl);
        $error = curl_error($curl);

        if ($error) {

        } else {
            $access_token = json_decode($access_token_response);
            $new_access_token = $access_token->access_token;

            //------------initiate stk push---------------

            $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            $business_shortcode = 174379;
            $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
            $timestamp = Carbon::rawParse('now')->isoFormat('YYYYMMDDHHmmss');
            $password = base64_encode($business_shortcode . $passkey . $timestamp);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $new_access_token));

            $body = array(
                "BusinessShortCode" => $business_shortcode,
                "Password" => $password,
                "Timestamp" => $timestamp,
                "TransactionType" => "CustomerPayBillOnline",
                "Amount" => $amount,
                "PartyA" => $phoneNumber,
                "PartyB" => 174379,
                "PhoneNumber" => $phoneNumber,
                "CallBackURL" => "https://42e8-105-27-98-86.ngrok.io/api/callback",
                "AccountReference" => "CompanyXLTD",
                "TransactionDesc" => "Payment of X"
            );

            $data_string = json_encode($body);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);


            if ($err) {
                return $err;
            } else {
                return $response;
            }

        }
    }

    public function callback(Request $request)
    {
        $body = $request->Body;
        Log::info("Data recieved");
    }

    public function checkPayment(Request $request)
    {
        $checkoutRequestID = $request->checkoutRequestID;

        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
        $business_shortcode = 174379;
        $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
        $timestamp = Carbon::rawParse('now')->isoFormat('YYYYMMDDHHmmss');
        $password = base64_encode($business_shortcode . $passkey . $timestamp);
        $body =
            [
                "BusinessShortCode" => "174379",
                "Password" => $password,
                "Timestamp" => $timestamp,
                "CheckoutRequestID" => $checkoutRequestID
            ]
        ;

        $request_body = json_encode($body);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer qB1LYj4DiNPwrQbhGUM54AwFKQ4a'));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);



        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);


        if ($err) {
            return $err;
        } else {
            return $response;
        }


    }


    //Tiny pesa options
    public function makePayment()
    {
        $url = "https://www.tinypesa.com/api/v1/express/initialize";


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt(
            $curl,
        CURLOPT_HTTPHEADER,
            array("Content-Type: application/x-www-form-urlencoded", "ApiKey: UgytELO94K8")
        );
        $data = array(
            'amount' => 50,
            'msisdn' => '0724753175',
            'account_no' => '200'
        );

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);


        if ($err) {
            return $err;
        } else {
            return $response;
        }

    }
}