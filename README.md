# httpcurl
#How to use

This program was Chain design pattern.

#Install
composer require xmltravflex/httpcurl dev-master
after install, write your require 'vendor/autoload.php';

#Example
$oHttp = Httpcurl/Httpcurl::open('url');\n
$oHttp->setRequestHeader('Content-Type','text/xml','SOAPAction':'urlsoape/action');\n
$oHttp->setCurlOption(CURLOPT_HEADER,true);\n
$response = $oHttp->send(array($requestxmlstring));\n

#Avaiable methods.
static : open($url,$isDebug=false)\n
public : setRequestHeader(param1,param2);\n
public : setCurlOption(param1,param2);\n
public : send(array param);
