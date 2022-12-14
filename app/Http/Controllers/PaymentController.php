<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    //daraja option
    public function generateAccessToken()
    {
        $consumer_key = '';
        $consumer_secret = '';
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
        $access_token = $this->generate_access_token();

        $phone_number = $request->phone_number;
        $amount = $request->amount;

        //------------initiate stk push---------------

        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $business_shortcode = 174379;
        $passkey = env('MPESA_PASS_KEY');
        $timestamp = Carbon::rawParse('now')->isoFormat('YYYYMMDDHHmmss');
        $password = base64_encode($business_shortcode . $passkey . $timestamp);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token));

        $body = array(
            "BusinessShortCode" => $business_shortcode,
            "Password" => $password,
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $amount,
            "PartyA" => $phone_number,
            "PartyB" => 174379,
            "PhoneNumber" => $phone_number,
            "CallBackURL" => "https://00f5-105-27-98-86.ngrok.io/api/mpesa-response",
            "AccountReference" => "Jubilee Life Insurance",
            "TransactionDesc" => "Insurance cover payment"
        );

        $data_string = json_encode($body);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $new_response = json_decode($response);


        curl_close($curl);


        if ($err) {
            return $err;
        } else {
            Log::info('----------stk push successfull----------');


            // $checkout_request_ID = $new_response->CheckoutRequestID;
            // $merchant_request_ID = $new_response->MerchantRequestID;

            // $pending_payment = new PendingPayments;

            // $pending_payment->phone_number = $phone_number;
            // $pending_payment->amount = $request->amount;
            // $pending_payment->user_id = $request->user_id;

            // $pending_payment->checkout_request_ID = $checkout_request_ID;
            // $pending_payment->merchant_request_ID = $merchant_request_ID;

            // $pending_payment->save();


            return $new_response;

        }
    }

    public function callback(Request $request)
    {
        if ($request->Body['stkCallback']['ResultCode'] === 0) {
            //successfull payment 
            $phone_number = $request->Body['stkCallback']['CheckoutRequestID']['CallbackMetadata']['Item'][4]['Value'];
            Log::info($phone_number);
        } else {
            //unsuccessfull payment
            Log::info("---Payment wasn't successfull---");

        }
    }

    public function checkPayment(Request $request)
    {
        $checkoutRequestID = $request->checkoutRequestID;

        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
        $business_shortcode = 174379;
        $passkey = env('MPESA_PASS_KEY');
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