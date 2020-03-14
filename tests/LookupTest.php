<?php

include '../vendor/autoload.php';
include '../config.php';

use Deaduseful\Opensrs\Lookup;
use PHPUnit\Framework\TestCase;

class lookupTest extends TestCase
{

    public function testLookup()
    {
        $query = 'example.com';
        $lookup = new Lookup();
        $result = $lookup->lookup($query);
        $this->assertIsString($result['response']);
        $this->assertIsInt($result['code']);
        $this->assertIsString($result['status']);
    }

    public function testAvailable()
    {
        $query = 'example.com';
        $lookup = new Lookup();
        $result = $lookup->available($query);
        $this->assertFalse($result);
    }

    public function testCheckTransfer()
    {
        $query = 'example.com';
        $lookup = new Lookup();
        $result = $lookup->checkTransfer($query);
        $this->assertFalse($result);
    }

    public function testSuggest()
    {
        $searchString = 'example';
        $tlds = ['.com', '.net', '.org'];
        $lookup = new Lookup();
        $result = $lookup->suggest($searchString, $tlds);
        $this->assertIsArray($result);
    }

    public function testAvailableFormatResult()
    {
        $lookup = new Lookup();
        $content = file_get_contents('available.xml');
        $result = $lookup->formatResult($content);
        $actual = json_encode($result);
        $expected = '{"response":"Domain taken","code":211,"status":"unknown","attributes":{"status":"taken"}}';
        $this->assertEquals($expected, $actual);
    }

    public function testTransferabilityFormatResult()
    {
        $lookup = new Lookup();
        $content = file_get_contents('transferability.xml');
        $result = $lookup->formatResult($content);
        $actual = json_encode($result);
        $expected = '{"response":"Query successful","code":200,"status":"success","attributes":{"transferrable":"0","noservice":"0","reason":"Domain status clientTransferProhibited does not allow for transfer"}}';
        $this->assertEquals($expected, $actual);
    }

    public function testSuggestFormatResult()
    {
        $lookup = new Lookup();
        $content = file_get_contents('suggest.xml');
        $result = $lookup->formatResult($content);
        $actual = json_encode($result);
        $expected = '{"response":"Command completed successfully","code":200,"status":"success","attributes":{"lookup":{"count":"3","is_success":"1","response_text":"Command completed successfully.","items":[{"domain":"example.com","status":"taken"},{"status":"taken","domain":"example.net"},{"domain":"example.org","reason":"Reserved Name","status":"taken"}],"response_code":"200"},"premium":{"response_code":"200","items":[{"price":"2395.00","domain":"examplec.com","status":"available"},{"status":"available","domain":"exampleof.com","price":"1999.00"},{"status":"available","domain":"exampleb.com","price":"695.00"},{"status":"available","price":"2295.00","domain":"examplea.com"},{"price":"2695.00","domain":"examplecom.com","status":"available"},{"domain":"examplei.com","price":"300.00","status":"available"},{"status":"available","domain":"examplejs.com","price":"495.00"},{"status":"available","domain":"exampleinc.com","price":"2695.00"},{"status":"available","domain":"exampleltd.com","price":"1895.00"},{"price":"2795.00","domain":"exampleone.com","status":"available"},{"domain":"lesson.org","price":"1380.00","status":"available"}],"count":"11","response_text":"Command Successful","is_success":"1"},"suggestion":{"items":[{"status":"available","domain":"yourexample.org"},{"domain":"exampleonline.org","status":"available"},{"domain":"yourexampleonline.com","status":"available"},{"status":"available","domain":"yourmodel.org"},{"domain":"yourmodelonline.com","status":"available"},{"status":"available","domain":"allexample.org"},{"status":"available","domain":"yourexampleonline.org"},{"status":"available","domain":"examplecenter.org"},{"domain":"allexample.net","status":"available"},{"status":"available","domain":"yourexampleonline.net"},{"status":"available","domain":"allexampleonline.com"},{"status":"available","domain":"examplecenter.net"},{"status":"available","domain":"yourexamplecenter.com"},{"status":"available","domain":"your-example.org"},{"domain":"example-online.org","status":"available"},{"status":"available","domain":"your-example.net"},{"status":"available","domain":"all-example.com"},{"status":"available","domain":"example-online.net"},{"status":"available","domain":"your-example-online.com"},{"domain":"example-center.com","status":"available"},{"status":"available","domain":"allmodel.org"},{"domain":"yourmodelonline.org","status":"available"},{"status":"available","domain":"allmodel.net"},{"status":"available","domain":"yourmodelonline.net"},{"status":"available","domain":"allmodelonline.com"},{"status":"available","domain":"yourmodelcenter.com"},{"status":"available","domain":"topexample.org"},{"domain":"allexampleonline.org","status":"available"},{"status":"available","domain":"yourexamplecenter.org"},{"domain":"examplestore.org","status":"available"},{"status":"available","domain":"your-model.org"},{"status":"available","domain":"model-online.org"},{"status":"available","domain":"topexample.net"},{"domain":"allexampleonline.net","status":"available"},{"domain":"topexampleonline.com","status":"available"},{"domain":"yourexamplecenter.net","status":"available"},{"domain":"allexamplecenter.com","status":"available"},{"status":"available","domain":"examplestore.net"},{"domain":"your-model-online.com","status":"available"},{"status":"available","domain":"yourcaseonline.org"},{"status":"available","domain":"all-example.org"},{"domain":"your-example-online.org","status":"available"},{"domain":"example-center.org","status":"available"},{"domain":"yourcaseonline.net","status":"available"},{"status":"available","domain":"allcaseonline.com"},{"domain":"problemonline.org","status":"available"},{"domain":"all-example.net","status":"available"},{"status":"available","domain":"your-example-online.net"},{"domain":"all-example-online.com","status":"available"},{"status":"available","domain":"example-center.net"}],"response_code":"200","count":"50","is_success":"1","response_text":"Command Successful"},"personal_names":{"response_code":"200","items":[{"status":"available","domain":"example.ourmail.com"},{"status":"available","domain":"example.cyberbud.com"},{"domain":"example.thecomputer.com","status":"available"}],"is_success":"1","count":"3","response_text":"Command completed successfully."}}}';
        $this->assertEquals($expected, $actual);
    }
}