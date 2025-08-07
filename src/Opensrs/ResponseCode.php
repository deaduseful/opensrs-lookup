<?php

namespace Deaduseful\Opensrs;

/**
 * Pseudo-enum class for OpenSRS response codes
 * Provides type-safe access to response codes and their statuses
 */
final class ResponseCode
{
    /**
     * Response codes organised by category for better maintainability
     * @var array<string, array<int, string>>
     */
    private static array $responseCodeCategories = [
        'success' => [
            200 => 'success',
            210 => 'domain_available',
            211 => 'domain_taken',
            221 => 'domain_taken_waiting',
            250 => 'action_submitted',
        ],
        'rate_limiting' => [
            300 => 'rate_limit_exceeded',
            310 => 'max_connections_exceeded',
            350 => 'command_limit_exceeded',
        ],
        'errors' => [
            400 => 'internal_server_error',
            405 => 'registry_error',
            410 => 'reseller_auth_error',
            415 => 'registrant_auth_error',
            420 => 'invalid_telephone',
            430 => 'invalid_command',
            435 => 'permission_denied',
            436 => 'unknown_response',
            437 => 'request_in_progress',
            440 => 'quota_exceeded',
            445 => 'nameserver_quota_exceeded',
            447 => 'subuser_limit_exceeded',
            460 => 'missing_required_field',
            465 => 'invalid_data',
            470 => 'invalid_contact_format',
            480 => 'domain_not_found',
            485 => 'domain_already_exists',
            486 => 'entity_in_processing',
            487 => 'domain_not_transferable',
        ],
        'domain_specific' => [
            541 => 'expiration_year_mismatch',
            552 => 'domain_too_young',
            555 => 'domain_already_renewed',
            557 => 'nameserver_locked',
            599 => 'contact_creation_failed',
        ],
        'communication' => [
            702 => 'communication_error',
            703 => 'send_command_failed',
            704 => 'empty_message',
            705 => 'timeout',
        ],
    ];

    public const STATUS_UNKNOWN = 'unknown';

    /**
     * Flattened response codes mapping for quick lookup
     * @var array<int, string>
     */
    private static ?array $responseCodes = null;

    /**
     * Maximum success code
     */
    public const MAXIMUM_SUCCESS_CODE = 299;

    /**
     * Unknown code
     */
    public const UNKNOWN = 999;

    // Success codes
    public const SUCCESS = 200;
    public const DOMAIN_AVAILABLE = 210;
    public const DOMAIN_TAKEN = 211;
    public const DOMAIN_TAKEN_WAITING = 221;
    public const ACTION_SUBMITTED = 250;

    // Rate limiting codes
    public const RATE_LIMIT_EXCEEDED = 300;
    public const MAX_CONNECTIONS_EXCEEDED = 310;
    public const COMMAND_LIMIT_EXCEEDED = 350;

    // Error codes
    public const INTERNAL_SERVER_ERROR = 400;
    public const REGISTRY_ERROR = 405;
    public const RESELLER_AUTH_ERROR = 410;
    public const REGISTRANT_AUTH_ERROR = 415;
    public const INVALID_TELEPHONE = 420;
    public const INVALID_COMMAND = 430;
    public const PERMISSION_DENIED = 435;
    public const UNKNOWN_RESPONSE = 436;
    public const REQUEST_IN_PROGRESS = 437;
    public const QUOTA_EXCEEDED = 440;
    public const NAMESERVER_QUOTA_EXCEEDED = 445;
    public const SUBUSER_LIMIT_EXCEEDED = 447;
    public const MISSING_REQUIRED_FIELD = 460;
    public const INVALID_DATA = 465;
    public const INVALID_CONTACT_FORMAT = 470;
    public const DOMAIN_NOT_FOUND = 480;
    public const DOMAIN_ALREADY_EXISTS = 485;
    public const ENTITY_IN_PROCESSING = 486;
    public const DOMAIN_NOT_TRANSFERABLE = 487;

    // Domain-specific codes
    public const EXPIRATION_YEAR_MISMATCH = 541;
    public const DOMAIN_TOO_YOUNG = 552;
    public const DOMAIN_ALREADY_RENEWED = 555;
    public const NAMESERVER_LOCKED = 557;
    public const CONTACT_CREATION_FAILED = 599;

    // Communication error codes
    public const COMMUNICATION_ERROR = 702;
    public const SEND_COMMAND_FAILED = 703;
    public const EMPTY_MESSAGE = 704;
    public const TIMEOUT = 705;

    /**
     * Get the flattened response codes mapping
     * @return array<int, string>
     */
    public static function getAllCodes(): array
    {
        if (self::$responseCodes === null) {
            self::$responseCodes = [];
            foreach (self::$responseCodeCategories as $category) {
                self::$responseCodes = self::$responseCodes + $category;
            }
            // Add unknown code
            self::$responseCodes[self::UNKNOWN] = self::STATUS_UNKNOWN;
        }
        return self::$responseCodes;
    }

    /**
     * Get response codes by category
     * @param string|null $category Optional category to filter by
     * @return array<int, string>
     */
    public static function getCodesByCategory(?string $category = null): array
    {
        if ($category === null) {
            return self::$responseCodeCategories;
        }
        
        return self::$responseCodeCategories[$category] ?? [];
    }

    /**
     * Get available categories
     * @return array<string>
     */
    public static function getCategories(): array
    {
        return array_keys(self::$responseCodeCategories);
    }

    /**
     * Check if a response code is in a specific category
     * @param int $code
     * @param string $category
     * @return bool
     */
    public static function isInCategory(int $code, string $category): bool
    {
        return isset(self::$responseCodeCategories[$category][$code]);
    }

    /**
     * Get the status for a response code
     * @param int $code
     * @return string
     */
    public static function getStatus(int $code): string
    {
        $responseCodes = self::getAllCodes();
        return $responseCodes[$code] ?? self::STATUS_UNKNOWN;
    }

    /**
     * Check if a response code indicates success
     * @param int $code
     * @return bool
     */
    public static function isSuccess(int $code): bool
    {
        return $code <= self::MAXIMUM_SUCCESS_CODE;
    }

    /**
     * Check if a response code indicates a domain availability response
     * @param int $code
     * @return bool
     */
    public static function isDomainAvailability(int $code): bool
    {
        return in_array($code, [self::DOMAIN_AVAILABLE, self::DOMAIN_TAKEN, self::DOMAIN_TAKEN_WAITING], true);
    }

    /**
     * Check if a response code indicates an error
     * @param int $code
     * @return bool
     */
    public static function isError(int $code): bool
    {
        return !self::isSuccess($code);
    }

    /**
     * Check if a response code indicates a rate limiting issue
     * @param int $code
     * @return bool
     */
    public static function isRateLimit(int $code): bool
    {
        return self::isInCategory($code, 'rate_limiting');
    }

    /**
     * Check if a response code indicates a communication error
     * @param int $code
     * @return bool
     */
    public static function isCommunicationError(int $code): bool
    {
        return self::isInCategory($code, 'communication');
    }

    /**
     * Get the category for a response code
     * @param int $code
     * @return string|null
     */
    public static function getCategory(int $code): ?string
    {
        foreach (self::$responseCodeCategories as $category => $codes) {
            if (isset($codes[$code])) {
                return $category;
            }
        }
        return null;
    }

    /**
     * Validate if a code is a known response code
     * @param int $code
     * @return bool
     */
    public static function isValid(int $code): bool
    {
        $allCodes = self::getAllCodes();
        return isset($allCodes[$code]);
    }

    /**
     * Get all success codes
     * @return array<int>
     */
    public static function getSuccessCodes(): array
    {
        return array_keys(self::$responseCodeCategories['success']);
    }

    /**
     * Get all error codes
     * @return array<int>
     */
    public static function getErrorCodes(): array
    {
        return array_keys(self::$responseCodeCategories['errors']);
    }

    /**
     * Get all rate limiting codes
     * @return array<int>
     */
    public static function getRateLimitCodes(): array
    {
        return array_keys(self::$responseCodeCategories['rate_limiting']);
    }

    /**
     * Get all domain-specific codes
     * @return array<int>
     */
    public static function getDomainSpecificCodes(): array
    {
        return array_keys(self::$responseCodeCategories['domain_specific']);
    }

    /**
     * Get all communication error codes
     * @return array<int>
     */
    public static function getCommunicationErrorCodes(): array
    {
        return array_keys(self::$responseCodeCategories['communication']);
    }

    /**
     * Private constructor to prevent instantiation
     */
    private function __construct()
    {
        // This class should not be instantiated
    }
} 