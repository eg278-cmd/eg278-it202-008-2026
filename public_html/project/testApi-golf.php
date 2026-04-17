<?php
// UCID: eg278 04/16/2026

$host = getenv("GOLF_API_KEY");
$key =  getenv("GOLF_API_KEY");
$endpoint = getenv("GOLF_API_ENDPOINT");

$url = "https://$host$endpoint";
echo "URL: $url<br>";

$headers = [ "X-RapidAPI-Key: $key", 
             "X-RapidAPI-Host: $host"
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers
]);

$reponse = curl_exec($curl);
$error = curl_error($curl);
//curl_close($curl);

if ($error)  {
    echo "<pre>";
    print_r(json_decode($response, true));
    echo "</pre>";
}
?>