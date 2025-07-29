<?php
define("API_KEY", "7388816590:AAFAaiUknhp5ZF12USXZBM96fsimlYr2XLQ");
define("ADMIN_ID", "5468750558"); // @sollyev

function bot($method, $data = []){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    } else {
        return json_decode($res,true);
    }
}