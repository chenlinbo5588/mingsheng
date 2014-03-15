<?php
    
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
/* 
 * 使用螺丝帽借口发送短信
 */
function send_message($message = '' ,$mnumber = '') {
    $res = array('error'=>1);
    if (!$message || !$mnumber) {
        $res['msg'] = 'No message or number.';
        return $res;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://sms-api.luosimao.com/v1/send.json");

    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);     
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE); 
    curl_setopt($ch, CURLOPT_SSLVERSION , 3);

    curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD  , 'api:key-b761c24f77fc5d77769d5a442ccacc10');


    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mnumber,'message' => $message));

    $res = curl_exec( $ch );
    curl_close( $ch );
    return $res;
}

/* 
 * 使用螺丝帽借口获取账号余额
 */
function get_balance() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL , "https://sms-api.luosimao.com/v1/status.json");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);     
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE); 
    curl_setopt($ch, CURLOPT_SSLVERSION , 3);

    curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD  , 'api:key-b761c24f77fc5d77769d5a442ccacc10');

    $res =  curl_exec( $ch );
    curl_close( $ch ); 
    return $res;
}
