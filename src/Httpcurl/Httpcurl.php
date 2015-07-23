<?php

namespace Httpcurl;

use \Exception;

class Httpcurl
{

    private static $url;
    private static $devAccess;
    private $arrReqestHeader = array();
    private $arrCurlOptions = array();

    /**
     * @assert ("", false) == test
     * @param type $url
     * @param type $devAccess
     * @return \Httpcurl\Httpcurl
     */
    public static function open($url = "", $devAccess = FALSE)
    {
        /*
         * Patch update : 2014-08-07
         * Add Check php version before use libary
         */
        if (!defined('PHP_VERSION_ID'))
        {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }

        if (PHP_VERSION_ID < 50300)
        {
            echo '<h1 style="color: red;text-align: center;">Please upgrade PHP version to 5.3+</h1>';
            exit;
        }
        self::$devAccess = $devAccess;
        self::$url = $url;
        return new Httpcurl();
    }

    public function setRequestHeader($key, $value)
    {
        if (!empty($key) && !empty($value))
        {
            $this->arrReqestHeader[$key] = $value;
        }
        return $this;
    }

    /**
     * New function
     * Add date 2014-08-07
     */
    public function setCurlOption($key, $value, callable $callback = NULL)
    {
        $this->arrCurlOptions[$key] = $value;
        if ($key == CURLOPT_READFUNCTION)
        {
            $callback();
        }
        return $this;
    }

    /*
     * Old function
     * Remove date 2014-09-07.
     */

    public function setCurlOpt($key, $value)
    {
        $this->arrCurlOptions[$key] = $value;
        return $this;
    }

    public function send($arrReq = array())
    {

        /**
         * Setting curl options.
         * Override some option when call function setCurlOption.
         */
        $arrCurlOpts = $this->getDefaultCurl();
        foreach ($this->arrCurlOptions as $optName => $optVal)
        {
            $arrCurlOpts[$optName] = $optVal;
        }


        /**
         * Set request header.
         */
        $arrReqestHeader = array();
        if (is_array($this->arrReqestHeader))
        {
            foreach ($this->arrReqestHeader as $keyHeader => $valHeader)
            {
                /*
                 * Patch update : 2014-05-30
                 * Add SOAP action.
                 */
                if (strtolower($keyHeader) == "soapaction")
                {
                    $arrReqestHeader[] = $keyHeader . ": " . $valHeader;
                }
                else
                {
                    $arrReqestHeader[] = $keyHeader . ": " . $valHeader . ";";
                }
            }
            $arrCurlOpts[10023] = $arrReqestHeader;
        }

        if (is_array($arrReq))
        {
            $ch = array();
            $mh = curl_multi_init();
            $cnt = count($arrReq);
            $key = 0;

            while ($key < $cnt)
            {
                if (strtoupper($arrCurlOpts[CURLOPT_CUSTOMREQUEST]) == 'GET')
                {
                    $_url = self::$url . "?" . http_build_query($arrReq[$key]);
                }
                else
                {
                    $arrCurlOpts[10015] = $arrReq[$key];
                    $_url = self::$url;
                }


                $ch[$key] = curl_init($_url);
                curl_setopt_array($ch[$key], $arrCurlOpts);
                curl_multi_add_handle($mh, $ch[$key]);
                $key++;
            }

            $active = null;
            do
            {
                $mrc = curl_multi_exec($mh, $active);
            }
            while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active && $mrc == CURLM_OK)
            {
                if (curl_multi_select($mh) != -1)
                {
                    do
                    {
                        $mrc = curl_multi_exec($mh, $active);
                    }
                    while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }
            }

            $arrData = array();
            foreach ($ch as $key => $value)
            {
                if (curl_errno($value) === 0)
                {
                    $sContent = curl_multi_getcontent($value);
                    $iHeaderSize = 0;
                    if ($arrCurlOpts[CURLOPT_HEADER])
                    {
                        $iHeaderSize = curl_getinfo($value, CURLINFO_HEADER_SIZE);
                        $arrData['header'][$key] = trim(substr($sContent, 0, $iHeaderSize));
                    }
                    $arrData['content'][$key] = trim(substr($sContent, $iHeaderSize));
                    $arrData['info'][$key] = curl_getinfo($value);
                }
                else
                {
                    print_r(curl_error($value));
                }
                curl_multi_remove_handle($mh, $ch[$key]);
                unset($value);
            }
            curl_multi_close($mh);

            /*
             * Developer Access
             */
            if (self::$devAccess == TRUE)
            {
                $arrData['debug'] = $this->devAccess($arrCurlOpts, $arrData);
            }
            return $arrData;
        }
        else
        {
            throw new Exception("Please check your xml request.");
        }
    }

    protected function getDefaultCurl()
    {
        return array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 180,
            CURLOPT_HEADER => FALSE,
            CURLOPT_HTTPHEADER => array(),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => "",
            CURLOPT_FAILONERROR => FALSE,
            CURLOPT_FILETIME => TRUE,
            CURLOPT_ENCODING => "deflate,gzip"
        );
    }

    protected function devAccess($arrCurlOpts, $arrData = array())
    {
        $aCurlNameOpt = array(
            47 => "POST",
            19913 => "RETURNTRANSFER",
            13 => "TIMEOUT",
            42 => "HEADER",
            10023 => "HTTPHEADER",
            84 => "HTTP_VERSION",
            10015 => "POSTFIELD",
            45 => "FAILONERROR",
            69 => "FILETIME",
            10102 => "ENCODING",
            10005 => "USER&PASSWORD",
        );


        $strCurlOpt = "";
        foreach ($arrCurlOpts as $kOpt => $vOpt)
        {
            $strCurlOpt .= $aCurlNameOpt[$kOpt] . " = " . var_export($vOpt, true) . "\n\r";
        }

        $str = "\n\r=================== [ --- DEVELOPER ACCESS ---- ] =======================\n\r";
        $str .= "---------------- CURL SETTING --------------\n\r";
        $str .= $strCurlOpt;
        return $str;
    }

}
