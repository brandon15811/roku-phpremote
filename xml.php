<?php
if (file_exists("testxml.xml"))
{
echo file_get_contents("testxml.xml");
exit;
}
$ip = $_COOKIE['ip'];
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,"http://".$ip.":8060/query/apps");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$xml = curl_exec($ch); 
curl_close($ch);
echo $xml;
?>