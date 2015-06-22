#httpcurl

This program was Chain design pattern.

## Installation

1. enter command : composer require xmltravflex/httpcurl dev-master
2. after install, write your require 'vendor/autoload.php'.

##Example
 - $oHttp = Httpcurl/Httpcurl::open('url');
 - $oHttp->setRequestHeader('Content-Type','text/xml','SOAPAction':'urlsoape/action');
 - $oHttp->setCurlOption(CURLOPT_HEADER,true);
 - $response = $oHttp->send(array($requestxmlstring));

##Avaiable methods.
 - static : open($url,$isDebug=false)
 - public : setRequestHeader(param1,param2);
 - public : setCurlOption(param1,param2);
 - public : send(array param);
