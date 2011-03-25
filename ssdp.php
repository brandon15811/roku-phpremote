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
ST: ssdp:all
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
/*while ($i > 0)
{*/
	$send_ret = socket_sendto($sock, $bc_string, strlen($bc_string), 0, '239.255.255.250', 1900);
	if($send_ret < 0)
	{
		echo "socket_sendto() failed, error: " . strerror($send_ret);
		break;
	} else {
		$input = socket_read($sock, 1);
		exit;
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
//}
socket_close($sock);
//echo json_encode($jsona);
// print_r($jsona);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="index,follow" name="robots" />
<meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type" />
<link href="pics/homescreen.png" rel="apple-touch-icon" />
<meta name = "viewport" content = "user-scalable=no, width=device-width">
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script src="javascript/functions.js" type="text/javascript"></script>
<script src="cookie.js" type="text/javascript"></script>
<script type="text/javascript">
function gup( name )
{
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return results[1];
}
if (gup('ip'))
	{
	setCookie('ip',gup('ip'),3650);
	ip=getCookie('ip');
	}
else if (!checkCookie())
	{
	window.location='config.html'
	}
else
	{
	ip=getCookie('ip');
	}
function press(type,cmd)
{
	xmlhttp=new XMLHttpRequest();
	if (type=="query")
	{
		php=getCookie('php')
		if (php="on")
		{
			console.log(php)
			url="xml.php"
		} else {
			url="http://"+ip+":8060/" + type + "/" + cmd
		}
		xmlhttp.open("GET",url,true);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
			parser=new DOMParser();
			xmlDoc=parser.parseFromString(xmlhttp.responseText,"text/xml");
			applist(xmlDoc);
			}
		}
	xmlhttp.send();
	}
	else
	{
	xmlhttp.open("POST","http://"+ip+":8060/" + type + "/" + cmd,true);
	xmlhttp.send();
	}
}
</script>
</head>


<body>
<div id="topbar" class="black">
	<div id="title">Roku Remote</div>
	<div id="leftnav">
		<a href="index.html"><img alt="home" src="images/home.png" /></a></div>
	<div id="rightbutton">
		<a href="config.html">Config</a></div>
</div>


<div id="content">
<span class="graytitle">Players</span>
<ul class="pageitem" id='applist'>
<?php
while (list($sn, $ip) = each($jsona)) {
    //echo "Key: $key; Value: $value<br />\n";
	//var_dump($player);
	$ip = str_replace(":8060", "", $ip);
	echo "<li class='menu'><a href='index.html?ip=".$ip."'><span class='name'>".$ip."</span><span class='arrow'></span></a></li>";
}
?>
</ul>
</div>
</body>
</html>