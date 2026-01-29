<?php

declare(strict_types=1);

/**
 * Database Encryption Class
 *
 * Provides AES-128-CBC encryption/decryption for sensitive database fields.
 * Uses APCu for key storage to keep encryption keys out of the database.
 */
class DBCrypt
{
    private const CIPHER_METHOD = 'AES-128-CBC';

    /**
     * Encrypts input text for use in database
     *
     * @param string $plaintext The text to encrypt
     * @return string|false Encrypted string (base64 encoded) or false if DB key not accessible
     */
    public static function encrypt(string $plaintext): string|false
    {
        if (!apcu_exists('DBKEY')) {
            return false;
        }

        $key = apcu_fetch('DBKEY');
        if ($key === false) {
            return false;
        }

        $ivSize = openssl_cipher_iv_length(self::CIPHER_METHOD);
        if ($ivSize === false) {
            return false;
        }

        $iv = openssl_random_pseudo_bytes($ivSize);
        $encrypted = openssl_encrypt(
            $plaintext,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            return false;
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypts input text from database
     *
     * @param string $ciphertext The encrypted text (base64 encoded)
     * @return string|false Decrypted string or false if DB key not accessible
     */
    public static function decrypt(string $ciphertext): string|false
    {
        if (!apcu_exists('DBKEY')) {
            return false;
        }

        $key = apcu_fetch('DBKEY');
        if ($key === false) {
            return false;
        }

        $ivSize = openssl_cipher_iv_length(self::CIPHER_METHOD);
        if ($ivSize === false) {
            return false;
        }

        $decoded = base64_decode($ciphertext, true);
        if ($decoded === false) {
            return false;
        }

        $iv = substr($decoded, 0, $ivSize);
        $encryptedData = substr($decoded, $ivSize);

        return openssl_decrypt(
            $encryptedData,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * Check if the database encryption key is available
     *
     * @return bool True if the key exists in APCu
     */
    public static function isKeyAvailable(): bool
    {
        return apcu_exists('DBKEY');
    }

    /**
     * Store the database encryption key in APCu
     *
     * @param string $key The encryption key
     * @param int $ttl Time to live in seconds (0 = forever)
     * @return bool True on success
     */
    public static function storeKey(string $key, int $ttl = 0): bool
    {
        return apcu_store('DBKEY', $key, $ttl);
    }

    /**
     * Remove the database encryption key from APCu
     *
     * @return bool True on success
     */
    public static function clearKey(): bool
    {
        return apcu_delete('DBKEY');
    }
}
