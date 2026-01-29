<?php

declare(strict_types=1);

/**
 * Global state container class
 *
 * Provides access to shared resources like database, cache, and user info.
 * This is a transitional pattern - ideally these would be dependency injected.
 */
class G
{
    public static ?DB_MYSQL $DB = null;
    public static ?CACHE $Cache = null;
    public static ?array $LoggedUser = null;

    /**
     * Initialize global state from legacy global variables
     */
    public static function initialize(): void
    {
        global $DB, $Cache, $LoggedUser;

        if ($DB instanceof DB_MYSQL) {
            self::$DB = $DB;
        }
        if ($Cache instanceof CACHE) {
            self::$Cache = $Cache;
        }
        // Use reference for LoggedUser to maintain compatibility
        self::$LoggedUser = &$LoggedUser;
    }
}
