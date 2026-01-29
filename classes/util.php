<?php

declare(strict_types=1);

/**
 * Utility functions for Gazelle
 *
 * This is a file of miscellaneous functions that are called so frequently
 * that it'd be annoying to put them in namespaces.
 */

/**
 * Return true if the given string is a valid integer representation.
 *
 * @param mixed $Str The value to check
 * @return bool True if the value represents an integer
 */
function is_number(mixed $Str): bool
{
    if ($Str === null || $Str === '') {
        return false;
    }
    if (is_int($Str)) {
        return true;
    }
    if (!is_string($Str) && !is_numeric($Str)) {
        return false;
    }
    $Str = (string)$Str;
    if ($Str[0] === '-' || $Str[0] === '+') {
        $Str = substr($Str, 1);
    }
    return $Str !== '' && ltrim($Str, '0123456789') === '';
}

/**
 * Check if a string is a valid date in Y-m-d format
 *
 * @param string $Date The date string to validate
 * @return bool True if valid date
 */
function is_date(string $Date): bool
{
    $parts = explode('-', $Date);
    if (count($parts) !== 3) {
        return false;
    }
    [$Y, $M, $D] = $parts;
    if (!is_numeric($Y) || !is_numeric($M) || !is_numeric($D)) {
        return false;
    }
    return checkdate((int)$M, (int)$D, (int)$Y);
}

/**
 * Check that some given variables (usually in _GET or _POST) are numbers
 *
 * @param array<string, mixed> $Base Array that's supposed to contain all keys to check
 * @param array<int, string> $Keys List of keys to check
 * @param int|string $Error Error code or string to pass to the error() function if a key isn't numeric
 */
function assert_numbers(array &$Base, array $Keys, int|string $Error = 0): void
{
    foreach ($Keys as $Key) {
        if (!isset($Base[$Key]) || !is_number($Base[$Key])) {
            error($Error);
        }
    }
}

/**
 * Return true, false or null, depending on the input value's "truthiness" or "non-truthiness"
 *
 * @param mixed $Value The input value to check for truthiness
 * @return bool|null True if $Value is "truthy", false if it is "non-truthy" or null if $Value was not
 *         a bool-like value
 */
function is_bool_value(mixed $Value): ?bool
{
    if (is_bool($Value)) {
        return $Value;
    }
    if (is_string($Value)) {
        return match (strtolower($Value)) {
            'true', 'yes', 'on', '1' => true,
            'false', 'no', 'off', '0' => false,
            default => null,
        };
    }
    if (is_numeric($Value)) {
        if ($Value == 1) {
            return true;
        } elseif ($Value == 0) {
            return false;
        }
    }
    return null;
}

/**
 * HTML-escape a string for output.
 * This is preferable to htmlspecialchars because it doesn't screw up upon a double escape.
 *
 * @param mixed $Str The string to escape
 * @return string Escaped string
 */
function display_str(mixed $Str): string
{
    if ($Str === null || $Str === false || is_array($Str)) {
        return '';
    }
    $Str = (string)$Str;
    if ($Str !== '' && !is_number($Str)) {
        $Str = Format::make_utf8($Str);
        $Str = mb_convert_encoding($Str, 'HTML-ENTITIES', 'UTF-8');
        $Str = preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/m", '&amp;', $Str);

        $Replace = [
            "'", '"', "<", ">",
            '&#128;', '&#130;', '&#131;', '&#132;', '&#133;', '&#134;', '&#135;', '&#136;',
            '&#137;', '&#138;', '&#139;', '&#140;', '&#142;', '&#145;', '&#146;', '&#147;',
            '&#148;', '&#149;', '&#150;', '&#151;', '&#152;', '&#153;', '&#154;', '&#155;',
            '&#156;', '&#158;', '&#159;'
        ];

        $With = [
            '&#39;', '&quot;', '&lt;', '&gt;',
            '&#8364;', '&#8218;', '&#402;', '&#8222;', '&#8230;', '&#8224;', '&#8225;', '&#710;',
            '&#8240;', '&#352;', '&#8249;', '&#338;', '&#381;', '&#8216;', '&#8217;', '&#8220;',
            '&#8221;', '&#8226;', '&#8211;', '&#8212;', '&#732;', '&#8482;', '&#353;', '&#8250;',
            '&#339;', '&#382;', '&#376;'
        ];

        $Str = str_replace($Replace, $With, $Str);
    }
    return $Str;
}

/**
 * Send a message to an IRC bot listening on SOCKET_LISTEN_PORT
 *
 * @param string $Raw An IRC protocol snippet to send.
 */
function send_irc(string $Raw): void
{
    if (!defined('SOCKET_LISTEN_ADDRESS') || !defined('SOCKET_LISTEN_PORT')) {
        return;
    }

    $IRCSocket = @fsockopen(SOCKET_LISTEN_ADDRESS, SOCKET_LISTEN_PORT);
    if ($IRCSocket === false) {
        return;
    }

    $Raw = str_replace(["\n", "\r"], '', $Raw);
    fwrite($IRCSocket, $Raw);
    fclose($IRCSocket);
}

/**
 * Display a critical error and kills the page.
 *
 * @param int|string $Error Error type. Automatically supported:
 *  403, 404, 0 (invalid input), -1 (invalid request)
 *  If you use your own string for Error, it becomes the error description.
 * @param bool $NoHTML If true, the header/footer won't be shown, just the description.
 * @param string|false $Log If true, the user is given a link to search $Log in the site log.
 */
function error(int|string $Error, bool $NoHTML = false, string|false $Log = false): never
{
    global $Debug;
    require(SERVER_ROOT . '/sections/error/index.php');
    if (isset($Debug) && $Debug instanceof DEBUG) {
        $Debug->profile();
    }
    die();
}

/**
 * Convenience function. See doc in permissions.class.php
 *
 * @param string $PermissionName The permission to check
 * @param int $MinClass Minimum class level required
 * @return bool True if the user has the permission
 */
function check_perms(string $PermissionName, int $MinClass = 0): bool
{
    return Permissions::check_perms($PermissionName, $MinClass);
}

/**
 * Get permissions for a specific user
 *
 * @param int $UserID The user ID
 * @param array<string, bool>|false $CustomPermissions Custom permissions to merge
 * @return array<string, bool> The user's permissions
 */
function get_permissions_for_user(int $UserID, array|false $CustomPermissions = false): array
{
    return Permissions::get_permissions_for_user($UserID, $CustomPermissions);
}

/**
 * Print JSON status result with an optional message and die.
 *
 * @deprecated Use json_print() followed by exit() instead
 * @param string $Status The status ('success' or 'failure')
 * @param mixed $Message The message or data to include
 */
function json_die(string $Status, mixed $Message): never
{
    json_print($Status, $Message);
    die();
}

/**
 * Print JSON status result with an optional message.
 *
 * @param string $Status The status ('success' or 'failure')
 * @param mixed $Message The message or data to include
 */
function json_print(string $Status, mixed $Message): void
{
    header('Content-Type: application/json');
    if ($Status === 'success' && $Message) {
        echo json_encode(['status' => $Status, 'response' => $Message], JSON_THROW_ON_ERROR);
    } elseif ($Message) {
        echo json_encode(['status' => $Status, 'error' => $Message], JSON_THROW_ON_ERROR);
    } else {
        echo json_encode(['status' => $Status, 'response' => []], JSON_THROW_ON_ERROR);
    }
}

/**
 * Return the site's URL including the appropriate URI scheme, including the trailing slash
 *
 * @return string The site URL
 */
function site_url(): string
{
    return 'https://' . SITE_DOMAIN . '/';
}

/**
 * Get the current SQL timestamp
 *
 * @return string SQL formatted datetime string
 */
function sqltime(): string
{
    return date('Y-m-d H:i:s');
}

/**
 * Get a SQL timestamp for a point in the future
 *
 * @param int $Offset Number of seconds to add
 * @return string SQL formatted datetime string
 */
function time_plus(int $Offset): string
{
    return date('Y-m-d H:i:s', time() + $Offset);
}

/**
 * Get a SQL timestamp for a point in the past
 *
 * @param int $Offset Number of seconds to subtract
 * @return string SQL formatted datetime string
 */
function time_minus(int $Offset): string
{
    return date('Y-m-d H:i:s', time() - $Offset);
}
