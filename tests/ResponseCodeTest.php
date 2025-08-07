<?php

include __DIR__ . '/../bootstrap.php';

use Deaduseful\Opensrs\ResponseCode;
use PHPUnit\Framework\TestCase;

class ResponseCodeTest extends TestCase
{
    public function testGetStatusForValidCodes(): void
    {
        $this->assertEquals('success', ResponseCode::getStatus(ResponseCode::SUCCESS));
        $this->assertEquals('domain_available', ResponseCode::getStatus(ResponseCode::DOMAIN_AVAILABLE));
        $this->assertEquals('domain_taken', ResponseCode::getStatus(ResponseCode::DOMAIN_TAKEN));
        $this->assertEquals('internal_server_error', ResponseCode::getStatus(ResponseCode::INTERNAL_SERVER_ERROR));
        $this->assertEquals('timeout', ResponseCode::getStatus(ResponseCode::TIMEOUT));
    }

    public function testGetStatusForInvalidCode(): void
    {
        $this->assertEquals('unknown', ResponseCode::getStatus(99999));
    }

    public function testIsSuccess(): void
    {
        $this->assertTrue(ResponseCode::isSuccess(ResponseCode::SUCCESS));
        $this->assertTrue(ResponseCode::isSuccess(ResponseCode::DOMAIN_AVAILABLE));
        $this->assertTrue(ResponseCode::isSuccess(ResponseCode::DOMAIN_TAKEN));
        $this->assertFalse(ResponseCode::isSuccess(ResponseCode::INTERNAL_SERVER_ERROR));
        $this->assertFalse(ResponseCode::isSuccess(ResponseCode::TIMEOUT));
    }

    public function testIsDomainAvailability(): void
    {
        $this->assertTrue(ResponseCode::isDomainAvailability(ResponseCode::DOMAIN_AVAILABLE));
        $this->assertTrue(ResponseCode::isDomainAvailability(ResponseCode::DOMAIN_TAKEN));
        $this->assertTrue(ResponseCode::isDomainAvailability(ResponseCode::DOMAIN_TAKEN_WAITING));
        $this->assertFalse(ResponseCode::isDomainAvailability(ResponseCode::SUCCESS));
        $this->assertFalse(ResponseCode::isDomainAvailability(ResponseCode::INTERNAL_SERVER_ERROR));
    }

    public function testIsError(): void
    {
        $this->assertFalse(ResponseCode::isError(ResponseCode::SUCCESS));
        $this->assertFalse(ResponseCode::isError(ResponseCode::DOMAIN_AVAILABLE));
        $this->assertTrue(ResponseCode::isError(ResponseCode::INTERNAL_SERVER_ERROR));
        $this->assertTrue(ResponseCode::isError(ResponseCode::TIMEOUT));
    }

    public function testIsRateLimit(): void
    {
        $this->assertTrue(ResponseCode::isRateLimit(ResponseCode::RATE_LIMIT_EXCEEDED));
        $this->assertTrue(ResponseCode::isRateLimit(ResponseCode::MAX_CONNECTIONS_EXCEEDED));
        $this->assertTrue(ResponseCode::isRateLimit(ResponseCode::COMMAND_LIMIT_EXCEEDED));
        $this->assertFalse(ResponseCode::isRateLimit(ResponseCode::SUCCESS));
        $this->assertFalse(ResponseCode::isRateLimit(ResponseCode::INTERNAL_SERVER_ERROR));
    }

    public function testIsCommunicationError(): void
    {
        $this->assertTrue(ResponseCode::isCommunicationError(ResponseCode::COMMUNICATION_ERROR));
        $this->assertTrue(ResponseCode::isCommunicationError(ResponseCode::SEND_COMMAND_FAILED));
        $this->assertTrue(ResponseCode::isCommunicationError(ResponseCode::EMPTY_MESSAGE));
        $this->assertTrue(ResponseCode::isCommunicationError(ResponseCode::TIMEOUT));
        $this->assertFalse(ResponseCode::isCommunicationError(ResponseCode::SUCCESS));
        $this->assertFalse(ResponseCode::isCommunicationError(ResponseCode::INTERNAL_SERVER_ERROR));
    }

    public function testGetCategory(): void
    {
        $this->assertEquals('success', ResponseCode::getCategory(ResponseCode::SUCCESS));
        $this->assertEquals('rate_limiting', ResponseCode::getCategory(ResponseCode::RATE_LIMIT_EXCEEDED));
        $this->assertEquals('errors', ResponseCode::getCategory(ResponseCode::INTERNAL_SERVER_ERROR));
        $this->assertEquals('domain_specific', ResponseCode::getCategory(ResponseCode::DOMAIN_TOO_YOUNG));
        $this->assertEquals('communication', ResponseCode::getCategory(ResponseCode::TIMEOUT));
        $this->assertNull(ResponseCode::getCategory(99999));
    }

    public function testIsValid(): void
    {
        $this->assertTrue(ResponseCode::isValid(ResponseCode::SUCCESS));
        $this->assertTrue(ResponseCode::isValid(ResponseCode::DOMAIN_AVAILABLE));
        $this->assertTrue(ResponseCode::isValid(ResponseCode::INTERNAL_SERVER_ERROR));
        $this->assertTrue(ResponseCode::isValid(ResponseCode::TIMEOUT));
        $this->assertFalse(ResponseCode::isValid(99999));
    }

    public function testGetCodesByCategory(): void
    {
        $successCodes = ResponseCode::getCodesByCategory('success');
        $this->assertArrayHasKey(ResponseCode::SUCCESS, $successCodes);
        $this->assertArrayHasKey(ResponseCode::DOMAIN_AVAILABLE, $successCodes);
        $this->assertArrayHasKey(ResponseCode::DOMAIN_TAKEN, $successCodes);
        
        $errorCodes = ResponseCode::getCodesByCategory('errors');
        $this->assertArrayHasKey(ResponseCode::INTERNAL_SERVER_ERROR, $errorCodes);
        $this->assertArrayHasKey(ResponseCode::REGISTRY_ERROR, $errorCodes);
        
        $this->assertEquals([], ResponseCode::getCodesByCategory('nonexistent'));
    }

    public function testGetCategories(): void
    {
        $categories = ResponseCode::getCategories();
        $this->assertContains('success', $categories);
        $this->assertContains('rate_limiting', $categories);
        $this->assertContains('errors', $categories);
        $this->assertContains('domain_specific', $categories);
        $this->assertContains('communication', $categories);
    }

    public function testGetAllCodes(): void
    {
        $allCodes = ResponseCode::getAllCodes();
        $this->assertArrayHasKey(ResponseCode::SUCCESS, $allCodes);
        $this->assertArrayHasKey(ResponseCode::DOMAIN_AVAILABLE, $allCodes);
        $this->assertArrayHasKey(ResponseCode::INTERNAL_SERVER_ERROR, $allCodes);
        $this->assertArrayHasKey(ResponseCode::TIMEOUT, $allCodes);
        $this->assertArrayHasKey(ResponseCode::UNKNOWN, $allCodes);
    }

    public function testGetSpecificCodeArrays(): void
    {
        $successCodes = ResponseCode::getSuccessCodes();
        $this->assertContains(ResponseCode::SUCCESS, $successCodes);
        $this->assertContains(ResponseCode::DOMAIN_AVAILABLE, $successCodes);
        $this->assertContains(ResponseCode::DOMAIN_TAKEN, $successCodes);
        
        $errorCodes = ResponseCode::getErrorCodes();
        $this->assertContains(ResponseCode::INTERNAL_SERVER_ERROR, $errorCodes);
        $this->assertContains(ResponseCode::REGISTRY_ERROR, $errorCodes);
        
        $rateLimitCodes = ResponseCode::getRateLimitCodes();
        $this->assertContains(ResponseCode::RATE_LIMIT_EXCEEDED, $rateLimitCodes);
        $this->assertContains(ResponseCode::MAX_CONNECTIONS_EXCEEDED, $rateLimitCodes);
        
        $communicationCodes = ResponseCode::getCommunicationErrorCodes();
        $this->assertContains(ResponseCode::COMMUNICATION_ERROR, $communicationCodes);
        $this->assertContains(ResponseCode::TIMEOUT, $communicationCodes);
        
        $domainSpecificCodes = ResponseCode::getDomainSpecificCodes();
        $this->assertContains(ResponseCode::DOMAIN_TOO_YOUNG, $domainSpecificCodes);
        $this->assertContains(ResponseCode::DOMAIN_ALREADY_RENEWED, $domainSpecificCodes);
    }

    public function testIsInCategory(): void
    {
        $this->assertTrue(ResponseCode::isInCategory(ResponseCode::SUCCESS, 'success'));
        $this->assertTrue(ResponseCode::isInCategory(ResponseCode::RATE_LIMIT_EXCEEDED, 'rate_limiting'));
        $this->assertTrue(ResponseCode::isInCategory(ResponseCode::INTERNAL_SERVER_ERROR, 'errors'));
        $this->assertTrue(ResponseCode::isInCategory(ResponseCode::TIMEOUT, 'communication'));
        $this->assertFalse(ResponseCode::isInCategory(ResponseCode::SUCCESS, 'errors'));
        $this->assertFalse(ResponseCode::isInCategory(99999, 'success'));
    }

    /**
     * Test that covers the specific execution path that was failing in production
     * This test ensures the static property initialization works correctly
     */
    public function testStaticPropertyInitialization(): void
    {
        // Test that getAllCodes() can be called multiple times without errors
        $codes1 = ResponseCode::getAllCodes();
        $codes2 = ResponseCode::getAllCodes();
        
        // Both calls should return the same result
        $this->assertEquals($codes1, $codes2);
        
        // Test that getStatus() works after getAllCodes() has been called
        $status1 = ResponseCode::getStatus(ResponseCode::SUCCESS);
        $status2 = ResponseCode::getStatus(ResponseCode::DOMAIN_AVAILABLE);
        
        $this->assertEquals('success', $status1);
        $this->assertEquals('domain_available', $status2);
        
        // Test that isValid() works correctly
        $this->assertTrue(ResponseCode::isValid(ResponseCode::SUCCESS));
        $this->assertFalse(ResponseCode::isValid(99999));
    }

    /**
     * Test that covers the exact scenario from the production error
     * This simulates the ResponseParser calling getStatus() with a valid code
     */
    public function testProductionExecutionPath(): void
    {
        // Simulate the exact scenario from the production error
        // ResponseParser calls getStatus() with code 200
        $responseCode = 200;
        $status = ResponseCode::getStatus($responseCode);
        
        $this->assertEquals('success', $status);
        $this->assertTrue(ResponseCode::isSuccess($responseCode));
        $this->assertTrue(ResponseCode::isValid($responseCode));
    }

    /**
     * Test that covers multiple rapid calls to ensure no race conditions
     */
    public function testMultipleRapidCalls(): void
    {
        // Make multiple rapid calls to simulate high-load scenario
        for ($i = 0; $i < 10; $i++) {
            $status = ResponseCode::getStatus(ResponseCode::SUCCESS);
            $this->assertEquals('success', $status);
            
            $isValid = ResponseCode::isValid(ResponseCode::SUCCESS);
            $this->assertTrue($isValid);
            
            $allCodes = ResponseCode::getAllCodes();
            $this->assertArrayHasKey(ResponseCode::SUCCESS, $allCodes);
        }
    }
} 