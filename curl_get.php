<?php
//var_dump($_GET['code']);

$url = "https://accounts.google.com/o/oauth2/auth";
 $params = array(
     "response_type" => "code",
     "client_id" => '252624051691-ql9rm7mgt0mdrpujo5c4g5tjva063fet.apps.googleusercontent.com',
     "redirect_uri" => "http://localhost/Quick-videos/curl_get.php",
     "scope" => "https://gdata.youtube.com",
     "access_type" => "offline"
 );
 $request_to = $url . '?' . http_build_query($params);



//  $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, $request_to);
// curl_setopt($ch, CURLOPT_HEADER, true);
// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// $a = curl_exec($ch);


//if(preg_match('#Location: (.*)#', $a, $r))
 //$l = trim($r[1]);


//
//  if(!curl_exec($curl)){
//     die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
// }
 // Close request to clear up some resources
 //curl_close($ch);


 /*$ch = curl_init($request_to);
 curl_setopt($ch, CURLOPT_HEADER, false);
 curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
 curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
 curl_setopt($ch, CURLOPT_TIMEOUT, 60);
 $html = curl_exec($ch);
 $redirectURL = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL );
 curl_close($ch); */








 function get_final_url( $url, $timeout = 5 )
{
  $url = str_replace( "&amp;", "&", urldecode(trim($url)) );

 $cookie = tempnam ("/tmp", "CURLCOOKIE");
$ch = curl_init();
curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
curl_setopt( $ch, CURLOPT_ENCODING, "" );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
$content = curl_exec( $ch );
$response = curl_getinfo( $ch );
curl_close ( $ch );

if ($response['http_code'] == 301 || $response['http_code'] == 302)
{
  ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
  $headers = get_headers($response['url']);

  $location = "";
  foreach( $headers as $value )
  {
      if ( substr( strtolower($value), 0, 9 ) == "location:" )
          return get_final_url( trim( substr( $value, 9, strlen($value) ) ) );
  }
}

if (    preg_match("/window\.location\.replace\('(.*)'\)/i", $content, $value) ||
      preg_match("/window\.location\=\"(.*)\"/i", $content, $value)
)
{
  return get_final_url ( $value[1] );
}
else
{
  return $response['url'];
 }
}
// -----------------------------------------------------------------------------------------
if (!function_exists('get_headers')) {
function get_headers($Url, $Format= 0, $Depth= 0) {
    if ($Depth > 5) return;
    $Parts = parse_url($Url);
    if (!array_key_exists('path', $Parts))   $Parts['path'] = '/';
    if (!array_key_exists('port', $Parts))   $Parts['port'] = 80;
    if (!array_key_exists('scheme', $Parts)) $Parts['scheme'] = 'http';

    $Return = array();
    $fp = fsockopen($Parts['host'], $Parts['port'], $errno, $errstr, 30);
    if ($fp) {
        $Out = 'GET '.$Parts['path'].(isset($Parts['query']) ? '?'.@$Parts['query'] : '')." HTTP/1.1\r\n".
               'Host: '.$Parts['host'].($Parts['port'] != 80 ? ':'.$Parts['port'] : '')."\r\n".
               'Connection: Close'."\r\n";
        fwrite($fp, $Out."\r\n");
        $Redirect = false; $RedirectUrl = '';
        while (!feof($fp) && $InLine = fgets($fp, 1280)) {
            if ($InLine == "\r\n") break;
            $InLine = rtrim($InLine);

            list($Key, $Value) = explode(': ', $InLine, 2);
            if ($Key == $InLine) {
                if ($Format == 1)
                        $Return[$Depth] = $InLine;
                else    $Return[] = $InLine;

                if (strpos($InLine, 'Moved') > 0) $Redirect = true;
            } else {
                if ($Key == 'Location') $RedirectUrl = $Value;
                if ($Format == 1)
                        $Return[$Key] = $Value;
                else    $Return[] = $Key.': '.$Value;
            }
        }
        fclose($fp);
        if ($Redirect && !empty($RedirectUrl)) {
            $NewParts = parse_url($RedirectUrl);
            if (!array_key_exists('host', $NewParts))   $RedirectUrl = $Parts['host'].$RedirectUrl;
            if (!array_key_exists('scheme', $NewParts)) $RedirectUrl = $Parts['scheme'].'://'.$RedirectUrl;
            $RedirectHeaders = get_headers($RedirectUrl, $Format, $Depth+1);
            if ($RedirectHeaders) $Return = array_merge_recursive($Return, $RedirectHeaders);
        }
        return $Return;
    }
    return false;
}}
//-------------------------------------------------------------------//
function curlRedir($url)
{
    $go = curl_init($url);
    curl_setopt ($go, CURLOPT_URL, $url);

    static $curl_loops = 0;
    static $curl_max_loops = 20;

    if ($curl_loops++>= $curl_max_loops)
    {
        $curl_loops = 0;
        return FALSE;
    }

    curl_setopt($go, CURLOPT_HEADER, true);
    curl_setopt($go, CURLOPT_RETURNTRANSFER, true);

    $data = curl_exec($go);
    $pattern = '/self\.location\.href=\'(.+)\';/';
    preg_match($pattern, $data, $matches);

    curl_close($go);
    return $data;
}

$c = get_final_url($request_to);

var_dump($c);
//$res = get_headers($request_to);
//var_dump($res);
 die();
 header("Location: " . $request_to);
