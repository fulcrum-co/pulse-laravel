<?php

declare(strict_types=1);

namespace App\Services\Domain;

/**
 * PhoneNumberFormatterService
 *
 * Centralized phone number formatting and validation for all communication
 * services (SMS, voice, WhatsApp). Ensures consistent E.164 formatting
 * across the application.
 */
class PhoneNumberFormatterService
{
    /**
     * Default country code for phone numbers without explicit country.
     */
    private const DEFAULT_COUNTRY_CODE = '1'; // United States

    /**
     * Minimum phone number length after removing country code.
     */
    private const MIN_DIGITS = 10;

    /**
     * Maximum phone number length.
     */
    private const MAX_DIGITS = 15;

    /**
     * Format phone number to E.164 standard.
     *
     * Converts various phone number formats to the E.164 standard
     * format used by telecommunications APIs (+[country code][number]).
     *
     * @param  string  $number  Raw phone number in any format
     * @return string E.164 formatted phone number
     *
     * @example
     *   formatPhoneNumber('(202) 555-0173') returns '+12025550173'
     *   formatPhoneNumber('2025550173') returns '+12025550173'
     *   formatPhoneNumber('+44 20 7946 0958') returns '+442079460958'
     */
    public function formatPhoneNumber(string $number): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $number);

        // If number is empty after cleaning, return as-is
        if (empty($cleaned)) {
            return $number;
        }

        // If US number without country code (10 digits)
        if (strlen($cleaned) === 10) {
            $cleaned = self::DEFAULT_COUNTRY_CODE . $cleaned;
        }

        return '+' . $cleaned;
    }

    /**
     * Validate phone number format.
     *
     * Checks if a phone number has a valid length for telecommunications.
     *
     * @param  string  $number  Phone number to validate
     * @return bool True if valid, false otherwise
     */
    public function isValidPhoneNumber(string $number): bool
    {
        $cleaned = preg_replace('/[^0-9]/', '', $number);

        $digitCount = strlen($cleaned);

        return $digitCount >= self::MIN_DIGITS && $digitCount <= self::MAX_DIGITS;
    }

    /**
     * Extract only digits from phone number.
     *
     * Useful for database storage or comparison.
     *
     * @param  string  $number  Phone number
     * @return string Digits only
     */
    public function extractDigits(string $number): string
    {
        return preg_replace('/[^0-9]/', '', $number);
    }

    /**
     * Get the country code from a phone number.
     *
     * Attempts to extract the country code prefix.
     * Returns default if unable to determine.
     *
     * @param  string  $number  E.164 formatted phone number
     * @return string Country code (without + symbol)
     */
    public function getCountryCode(string $number): string
    {
        // Remove + if present
        $number = ltrim($number, '+');

        // Remove all non-digits
        $number = preg_replace('/[^0-9]/', '', $number);

        // Common country codes are 1-3 digits
        // This is a simple heuristic and may not be 100% accurate
        if (str_starts_with($number, '1') && strlen($number) === 11) {
            return '1'; // US/Canada
        } elseif (str_starts_with($number, '44') && strlen($number) >= 11) {
            return '44'; // UK
        } elseif (str_starts_with($number, '33') && strlen($number) >= 11) {
            return '33'; // France
        } elseif (str_starts_with($number, '49') && strlen($number) >= 11) {
            return '49'; // Germany
        }

        // Default fallback
        return self::DEFAULT_COUNTRY_CODE;
    }

    /**
     * Remove country code from phone number.
     *
     * @param  string  $number  E.164 formatted phone number
     * @return string Local number without country code
     */
    public function removeCountryCode(string $number): string
    {
        $countryCode = $this->getCountryCode($number);
        $digits = $this->extractDigits($number);

        // Remove country code from the beginning
        if (str_starts_with($digits, $countryCode)) {
            return substr($digits, strlen($countryCode));
        }

        return $digits;
    }

    /**
     * Check if phone number matches another in value.
     *
     * Compares two phone numbers by extracting and comparing digits,
     * ignoring formatting differences.
     *
     * @param  string  $number1  First phone number
     * @param  string  $number2  Second phone number
     * @return bool True if numbers match
     */
    public function phoneNumbersMatch(string $number1, string $number2): bool
    {
        $digits1 = $this->extractDigits($number1);
        $digits2 = $this->extractDigits($number2);

        return $digits1 === $digits2;
    }
}
