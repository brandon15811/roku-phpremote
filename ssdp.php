<?php
if (!function_exists('http_parse_headers')) {
function http_parse_headers( $header )
    {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach( $fields as $field ) {
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if( isset($retVal[$match[1]]) ) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }
}
/*$bc_string = 'M-SEARCH * HTTP/1.1
Host: 239.255.255.250:1900
Man: "ssdp:discover"
ST:upnp:rootdevice
MX:3

';*/
$bc_string = 'M-SEARCH * HTTP/1.1
Host: 239.255.255.250:1900
Man: "ssdp:discover"
ST: roku:ecp
MX:3

';
$bc_string = str_replace("\n","\r\n",$bc_string);
$sock = socket_create(AF_INET, SOCK_DGRAM, 0);
if($sock < 0)
{
	echo "socket_create() failed, error: " . strerror($sock);
}

$opt_ret = socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1); ;
if($opt_ret < 0)
{
	echo "socket_set_option() failed, error: " . strerror($opt_ret);
}
$i = 6;
$jsona = array();
while ($i > 0)
{
	$send_ret = socket_sendto($sock, $bc_string, strlen($bc_string), 0, '239.255.255.250', 1900);
	if($send_ret < 0)
	{
		echo "socket_sendto() failed, error: " . strerror($send_ret);
		break;
	} else {
		$input = socket_read($sock, 2048);
		if (strstr($input, "roku:ecp"))
		{
			$header = http_parse_headers($input);
			/* echo "<pre>";
			//print_r($header);
			echo "</pre><br>"; */
			$uuid = $header['Usn'];
			$uuid = str_replace("uuid:roku:ecp:", "", $uuid);
			// var_export($uuid);
			$ip = parse_url($header['Location']);
			if (!array_key_exists($uuid, $jsona))
			{
			$jsont = array($uuid => $ip['host'].":".$ip['port']);
			$jsona = array_merge($jsont, $jsona);
			}
			/* echo "<pre>";
			print_r($ip);
			echo "</pre><br>"; */
		}
		sleep(1);
	}
	$i--;
}
socket_close($sock);
echo json_encode($jsona);
// print_r($jsona);
?>
