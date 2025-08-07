<?php

include __DIR__ . '/../bootstrap.php';

use Deaduseful\Opensrs\ResponseParser;
use Deaduseful\Opensrs\ResponseCode;
use PHPUnit\Framework\TestCase;

class ResponseParserTest extends TestCase
{
    public function testAvailableFormatResult()
    {
        $content = file_get_contents(__DIR__ . '/data/available.xml');
        $response = new ResponseParser();
        $result = $response->parseResult($content)->getResult()->toArray();
        $actual = json_encode($result);
        $expected = '{"response":"Domain taken","code":211,"status":"domain_taken","attributes":{"status":"taken"}}';
        $this->assertEquals($expected, $actual);
    }

    public function testTransferabilityFormatResult()
    {
        $content = file_get_contents(__DIR__ . '/data/transferability.xml');
        $response = new ResponseParser();
        $result = $response->parseResult($content)->getResult()->toArray();
        $actual = json_encode($result);
        $expected = '{"response":"Query successful","code":200,"status":"success","attributes":{"transferrable":"0","noservice":"0","reason":"Domain status clientTransferProhibited does not allow for transfer"}}';
        $this->assertEquals($expected, $actual);
    }

    public function testSuggestFormatResult()
    {
        $content = file_get_contents(__DIR__ . '/data/suggest.xml');
        $response = new ResponseParser();
        $result = $response->parseResult($content)->getResult()->toArray();
        $actual = json_encode($result);
        $expected = '{"response":"Command completed successfully","code":200,"status":"success","attributes":{"lookup":{"count":"3","is_success":"1","response_text":"Command completed successfully.","items":[{"domain":"example.com","status":"taken"},{"status":"taken","domain":"example.net"},{"domain":"example.org","reason":"Reserved Name","status":"taken"}],"response_code":"200"},"premium":{"response_code":"200","items":[{"price":"2395.00","domain":"examplec.com","status":"available"},{"status":"available","domain":"exampleof.com","price":"1999.00"},{"status":"available","domain":"exampleb.com","price":"695.00"},{"status":"available","price":"2295.00","domain":"examplea.com"},{"price":"2695.00","domain":"examplecom.com","status":"available"},{"domain":"examplei.com","price":"300.00","status":"available"},{"status":"available","domain":"examplejs.com","price":"495.00"},{"status":"available","domain":"exampleinc.com","price":"2695.00"},{"status":"available","domain":"exampleltd.com","price":"1895.00"},{"price":"2795.00","domain":"exampleone.com","status":"available"},{"domain":"lesson.org","price":"1380.00","status":"available"}],"count":"11","response_text":"Command Successful","is_success":"1"},"suggestion":{"items":[{"status":"available","domain":"yourexample.org"},{"domain":"exampleonline.org","status":"available"},{"domain":"yourexampleonline.com","status":"available"},{"status":"available","domain":"yourmodel.org"},{"domain":"yourmodelonline.com","status":"available"},{"status":"available","domain":"allexample.org"},{"status":"available","domain":"yourexampleonline.org"},{"status":"available","domain":"examplecenter.org"},{"domain":"allexample.net","status":"available"},{"status":"available","domain":"yourexampleonline.net"},{"status":"available","domain":"allexampleonline.com"},{"status":"available","domain":"examplecenter.net"},{"status":"available","domain":"yourexamplecenter.com"},{"status":"available","domain":"your-example.org"},{"domain":"example-online.org","status":"available"},{"status":"available","domain":"your-example.net"},{"status":"available","domain":"all-example.com"},{"status":"available","domain":"example-online.net"},{"status":"available","domain":"your-example-online.com"},{"domain":"example-center.com","status":"available"},{"status":"available","domain":"allmodel.org"},{"domain":"yourmodelonline.org","status":"available"},{"status":"available","domain":"allmodel.net"},{"status":"available","domain":"yourmodelonline.net"},{"status":"available","domain":"allmodelonline.com"},{"status":"available","domain":"yourmodelcenter.com"},{"status":"available","domain":"topexample.org"},{"domain":"allexampleonline.org","status":"available"},{"status":"available","domain":"yourexamplecenter.org"},{"domain":"examplestore.org","status":"available"},{"status":"available","domain":"your-model.org"},{"status":"available","domain":"model-online.org"},{"status":"available","domain":"topexample.net"},{"domain":"allexampleonline.net","status":"available"},{"domain":"topexampleonline.com","status":"available"},{"domain":"yourexamplecenter.net","status":"available"},{"domain":"allexamplecenter.com","status":"available"},{"status":"available","domain":"examplestore.net"},{"domain":"your-model-online.com","status":"available"},{"status":"available","domain":"yourcaseonline.org"},{"status":"available","domain":"all-example.org"},{"domain":"your-example-online.org","status":"available"},{"domain":"example-center.org","status":"available"},{"domain":"yourcaseonline.net","status":"available"},{"status":"available","domain":"allcaseonline.com"},{"domain":"problemonline.org","status":"available"},{"domain":"all-example.net","status":"available"},{"status":"available","domain":"your-example-online.net"},{"domain":"all-example-online.com","status":"available"},{"status":"available","domain":"example-center.net"}],"response_code":"200","count":"50","is_success":"1","response_text":"Command Successful"},"personal_names":{"response_code":"200","items":[{"status":"available","domain":"example.ourmail.com"},{"status":"available","domain":"example.cyberbud.com"},{"domain":"example.thecomputer.com","status":"available"}],"is_success":"1","count":"3","response_text":"Command completed successfully."}}}';
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that covers the exact production execution path
     * This simulates the ResponseParser using ResponseCode::getStatus()
     */
    public function testResponseParserWithResponseCodeIntegration(): void
    {
        // Simulate the exact XML from the production error
        $content = '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<!DOCTYPE OPS_envelope SYSTEM "ops.dtd">
<OPS_envelope>
 <header>
  <version>0.9</version>
  </header>
 <body>
  <data_block>
   <dt_assoc>
    <item key="response_code">200</item>
    <item key="protocol">XCP</item>
    <item key="request_response_time">2.802</item>
    <item key="action">REPLY</item>
    <item key="is_success">1</item>
    <item key="response_text">Command completed successfully</item>
    <item key="attributes">
     <dt_assoc>
      <item key="lookup">
       <dt_assoc>
        <item key="response_code">200</item>
        <item key="is_success">1</item>
        <item key="count">34</item>
        <item key="items">
         <dt_array>
          <item key="0">
           <dt_assoc>
            <item key="status">available</item>
            <item key="domain">example.com</item>
           </dt_assoc>
          </item>
         </dt_array>
        </item>
       </dt_assoc>
      </item>
     </dt_assoc>
    </item>
   </dt_assoc>
  </data_block>
 </body>
</OPS_envelope>';

        $response = new ResponseParser();
        $result = $response->parseResult($content)->getResult();
        
        // Verify the result matches what we expect
        $this->assertEquals(200, $result->toArray()['code']);
        $this->assertEquals('success', $result->toArray()['status']);
        $this->assertEquals('Command completed successfully', $result->toArray()['response']);
        
        // Verify that ResponseCode integration works correctly
        $this->assertTrue(ResponseCode::isSuccess(200));
        $this->assertEquals('success', ResponseCode::getStatus(200));
        $this->assertTrue(ResponseCode::isValid(200));
    }

    /**
     * Test that covers error response parsing
     */
    public function testResponseParserWithErrorCode(): void
    {
        // Simulate an error response
        $content = '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<!DOCTYPE OPS_envelope SYSTEM "ops.dtd">
<OPS_envelope>
 <header>
  <version>0.9</version>
  </header>
 <body>
  <data_block>
   <dt_assoc>
    <item key="response_code">400</item>
    <item key="protocol">XCP</item>
    <item key="action">REPLY</item>
    <item key="is_success">0</item>
    <item key="response_text">Internal server error</item>
   </dt_assoc>
  </data_block>
 </body>
</OPS_envelope>';

        $response = new ResponseParser();
        
        // This should throw an exception because 400 is not a success code
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Internal server error');
        $this->expectExceptionCode(400);
        
        $response->parseResult($content);
    }

    /**
     * Test that covers domain availability response parsing
     */
    public function testResponseParserWithDomainAvailabilityCode(): void
    {
        // Simulate a domain availability response
        $content = '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<!DOCTYPE OPS_envelope SYSTEM "ops.dtd">
<OPS_envelope>
 <header>
  <version>0.9</version>
  </header>
 <body>
  <data_block>
   <dt_assoc>
    <item key="response_code">210</item>
    <item key="protocol">XCP</item>
    <item key="action">REPLY</item>
    <item key="is_success">1</item>
    <item key="response_text">Domain available</item>
   </dt_assoc>
  </data_block>
 </body>
</OPS_envelope>';

        $response = new ResponseParser();
        $result = $response->parseResult($content)->getResult();
        
        // Verify the result matches what we expect
        $this->assertEquals(210, $result->toArray()['code']);
        $this->assertEquals('domain_available', $result->toArray()['status']);
        $this->assertEquals('Domain available', $result->toArray()['response']);
        
        // Verify that ResponseCode integration works correctly
        $this->assertTrue(ResponseCode::isSuccess(210));
        $this->assertTrue(ResponseCode::isDomainAvailability(210));
        $this->assertEquals('domain_available', ResponseCode::getStatus(210));
    }
}
