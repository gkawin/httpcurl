<?php

/**
 * Description of HttpcurlTest
 *
 * @author Kawin Glomjai <g.kawin@live.com>
 */
use Httpcurl\Httpcurl;
class HttpcurlTest extends PHPUnit_Framework_TestCase
{

    public function testAdd($a, $b, $expected)
    {
        $this->assertEquals($expected, $a + $b);
    }

    

}
