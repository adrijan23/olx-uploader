<?php

function login($url,$data){
    $fp = fopen("cookie.txt", "w");
    fclose($fp);
    $login = curl_init();
    curl_setopt($login, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($login, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($login, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($login, CURLOPT_TIMEOUT, 40000);
    curl_setopt($login, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($login, CURLOPT_URL, $url);
    curl_setopt($login, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($login, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($login, CURLOPT_POST, TRUE);
    curl_setopt($login, CURLOPT_POSTFIELDS, $data);
    ob_start();
    return curl_exec ($login);
    ob_end_clean();
    curl_close ($login);
    unset($login);    
}

function grab_page($site){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($ch, CURLOPT_URL, $site);
    ob_start();
    return curl_exec ($ch);
    ob_end_clean();
    curl_close ($ch);
}




login("https://www.olx.ba/auth/login","username=office%40infoars.net&password=11ibHds3cd7izCG&zapamtime=on&csrf_token=LhJRlRvQv8wKjRTfOXu0C5zHunEvBB156x7MUWVk");

$reg='/<div class="kind">.*?title="(.*?)"/';
$data=grab_page('https://www.olx.ba/objava/brzaobjava');
preg_match_all($reg, $data, $mainCategories);

$mainCategories= $mainCategories[1];

foreach($mainCategories as $cat){
    
}
$reg='/<a href.*?>(.*?)<\/a>/';
preg_match_all($reg, $data, $matches);

$matches= $matches[1];

array_splice($matches, 0, 76);
array_splice($matches, 2049);

foreach($mainCategories as $cat){
    
}

echo "<pre>";
print_r($matches);

?>