<?php

declare(strict_types=1);

/**
 * MySQL wrapper class
 *
 * This class provides an interface to mysqli. You should always use this class instead
 * of the mysql/mysqli functions, because this class provides debugging features and a
 * bunch of other cool stuff.
 *
 * Everything returned by this class is automatically escaped for output. This can be
 * turned off by setting $Escape to false in next_record or to_array.
 *
 * Updated for PHP 8.5 compatibility with typed properties and return types.
 */

if (!extension_loaded('mysqli')) {
    die('Mysqli Extension not loaded.');
}

/**
 * Escape a string for database use
 *
 * @param string $String The string to escape
 * @param bool $DisableWildcards Whether to escape % and _ wildcards
 * @return string The escaped string
 */
function db_string(string $String, bool $DisableWildcards = false): string
{
    global $DB;

    $String = $DB->escape_str($String);

    if ($DisableWildcards) {
        $String = str_replace(['%', '_'], ['\\%', '\\_'], $String);
    }

    return $String;
}

/**
 * Escape an array of strings for database use
 *
 * @param array<string, mixed> $Array The array to escape
 * @param array<int, string> $DontEscape Keys to skip escaping
 * @param bool $Quote Whether to wrap values in quotes
 * @return array<string, mixed> The escaped array
 */
function db_array(array $Array, array $DontEscape = [], bool $Quote = false): array
{
    foreach ($Array as $Key => $Val) {
        if (!in_array($Key, $DontEscape, true)) {
            if ($Quote) {
                $Array[$Key] = "'" . db_string(trim((string)$Val)) . "'";
            } else {
                $Array[$Key] = db_string(trim((string)$Val));
            }
        }
    }
    return $Array;
}

class DB_MYSQL
{
    public mysqli|false $LinkID = false;
    protected mysqli_result|false $QueryID = false;
    protected ?array $Record = [];
    protected int $Row = 0;
    protected int $Errno = 0;
    protected string $Error = '';

    /** @var array<int, array{0: string, 1: float, 2: array<string>|null}> */
    public array $Queries = [];
    public float $Time = 0.0;

    protected string $Database = '';
    protected string $Server = '';
    protected string $User = '';
    protected string $Pass = '';
    protected int $Port = 0;
    protected string $Socket = '';

    public function __construct(
        string $Database = SQLDB,
        string $User = SQLLOGIN,
        string $Pass = SQLPASS,
        string $Server = SQLHOST,
        int $Port = SQLPORT,
        string $Socket = SQLSOCK
    ) {
        $this->Database = $Database;
        $this->Server = $Server;
        $this->User = $User;
        $this->Pass = $Pass;
        $this->Port = $Port;
        $this->Socket = $Socket;
    }

    public function halt(string $Msg): never
    {
        global $Debug, $argv;

        $DBError = 'MySQL: ' . $Msg . ' SQL error: ' . $this->Errno . ' (' . $this->Error . ')';

        if ($this->Errno === 1194) {
            send_irc('PRIVMSG ' . ADMIN_CHAN . ' :' . $this->Error);
        }

        if (isset($Debug) && $Debug instanceof DEBUG) {
            $Debug->analysis('!dev DB Error', $DBError, 3600 * 24);
        }

        if (DEBUG_MODE || check_perms('site_debug') || isset($argv[1])) {
            echo '<pre>' . display_str($DBError) . '</pre>';
            if (DEBUG_MODE || check_perms('site_debug')) {
                print_r($this->Queries);
            }
            die();
        } else {
            error('-1');
        }
    }

    public function connect(): void
    {
        if (!$this->LinkID) {
            $this->LinkID = mysqli_connect(
                $this->Server,
                $this->User,
                $this->Pass,
                $this->Database,
                $this->Port,
                $this->Socket
            );

            if (!$this->LinkID) {
                $this->Errno = mysqli_connect_errno();
                $this->Error = mysqli_connect_error() ?? '';
                $this->halt('Connection failed (host:' . $this->Server . ':' . $this->Port . ')');
            }
        }
        mysqli_set_charset($this->LinkID, 'utf8mb4');
    }

    public function query(string $Query, int $AutoHandle = 1): mysqli_result|bool|int
    {
        global $Debug;

        // Store warnings from previous query
        if ($this->QueryID instanceof mysqli_result) {
            $this->warnings();
        }

        $QueryStartTime = microtime(true);
        $this->connect();

        // Handle MySQL deadlocks with retry logic
        for ($i = 1; $i < 6; $i++) {
            $this->QueryID = mysqli_query($this->LinkID, $Query);
            $errno = mysqli_errno($this->LinkID);
            if (!in_array($errno, [1213, 1205], true)) {
                break;
            }
            if (isset($Debug) && $Debug instanceof DEBUG) {
                $Debug->analysis('Non-Fatal Deadlock:', $Query, 3600 * 24);
            }
            trigger_error("Database deadlock, attempt $i");
            sleep($i * random_int(2, 5));
        }

        $QueryEndTime = microtime(true);
        $this->Queries[] = [$Query, ($QueryEndTime - $QueryStartTime) * 1000, null];
        $this->Time += ($QueryEndTime - $QueryStartTime) * 1000;

        if (!$this->QueryID) {
            $this->Errno = mysqli_errno($this->LinkID);
            $this->Error = mysqli_error($this->LinkID);

            if ($AutoHandle) {
                $this->halt("Invalid Query: $Query");
            } else {
                return $this->Errno;
            }
        }

        $this->Row = 0;

        if ($AutoHandle) {
            return $this->QueryID;
        }

        return true;
    }

    public function query_unb(string $Query): void
    {
        $this->connect();
        mysqli_real_query($this->LinkID, $Query);
    }

    public function inserted_id(): int|string
    {
        if ($this->LinkID instanceof mysqli) {
            return mysqli_insert_id($this->LinkID);
        }
        return 0;
    }

    /**
     * @param int $Type MYSQLI_ASSOC, MYSQLI_NUM, or MYSQLI_BOTH
     * @param bool|array<string> $Escape True to escape all, false for none, or array of keys to NOT escape
     * @return array<string|int, mixed>|null
     */
    public function next_record(int $Type = MYSQLI_BOTH, bool|array $Escape = true): ?array
    {
        if ($this->LinkID instanceof mysqli && $this->QueryID instanceof mysqli_result) {
            $this->Record = mysqli_fetch_array($this->QueryID, $Type);
            $this->Row++;

            if (!is_array($this->Record)) {
                $this->QueryID = false;
                return null;
            }

            if ($Escape !== false) {
                $this->Record = Misc::display_array($this->Record, $Escape);
            }

            return $this->Record;
        }
        return null;
    }

    public function close(): void
    {
        if ($this->LinkID instanceof mysqli) {
            if (!mysqli_close($this->LinkID)) {
                $this->halt('Cannot close connection or connection did not open.');
            }
            $this->LinkID = false;
        }
    }

    /**
     * Returns the number of rows in the result set
     */
    public function record_count(): int|string
    {
        if ($this->QueryID instanceof mysqli_result) {
            return mysqli_num_rows($this->QueryID);
        }
        return 0;
    }

    /**
     * Returns true if the query exists and there were records found
     */
    public function has_results(): bool
    {
        return $this->QueryID instanceof mysqli_result && $this->record_count() !== 0;
    }

    public function affected_rows(): int|string
    {
        if ($this->LinkID instanceof mysqli) {
            return mysqli_affected_rows($this->LinkID);
        }
        return 0;
    }

    public function info(): ?string
    {
        if ($this->LinkID instanceof mysqli) {
            return mysqli_get_host_info($this->LinkID);
        }
        return null;
    }

    public function escape_str(string $Str): string
    {
        $this->connect();

        if ($this->LinkID instanceof mysqli) {
            return mysqli_real_escape_string($this->LinkID, $Str);
        }

        return addslashes($Str);
    }

    /**
     * Creates an array from a result set
     *
     * @param string|int|false $Key Column to use as array key, or false for numeric keys
     * @param int $Type MYSQLI_ASSOC, MYSQLI_NUM, or MYSQLI_BOTH
     * @param bool|array<string> $Escape Escape options
     * @return array<string|int, array<string|int, mixed>>
     */
    public function to_array(string|int|false $Key = false, int $Type = MYSQLI_BOTH, bool|array $Escape = true): array
    {
        $Return = [];

        if (!$this->QueryID instanceof mysqli_result) {
            return $Return;
        }

        while ($Row = mysqli_fetch_array($this->QueryID, $Type)) {
            if ($Escape !== false) {
                $Row = Misc::display_array($Row, $Escape);
            }
            if ($Key !== false) {
                $Return[$Row[$Key]] = $Row;
            } else {
                $Return[] = $Row;
            }
        }
        mysqli_data_seek($this->QueryID, 0);

        return $Return;
    }

    /**
     * Loops through the result set, collecting the $ValField column into an array with $KeyField as keys
     *
     * @return array<string|int, mixed>
     */
    public function to_pair(string|int $KeyField, string|int $ValField, bool $Escape = true): array
    {
        $Return = [];

        if (!$this->QueryID instanceof mysqli_result) {
            return $Return;
        }

        while ($Row = mysqli_fetch_array($this->QueryID)) {
            if ($Escape) {
                $Key = display_str($Row[$KeyField]);
                $Val = display_str($Row[$ValField]);
            } else {
                $Key = $Row[$KeyField];
                $Val = $Row[$ValField];
            }
            $Return[$Key] = $Val;
        }
        mysqli_data_seek($this->QueryID, 0);

        return $Return;
    }

    /**
     * Loops through the result set, collecting the $Key column into an array
     *
     * @return array<int, mixed>
     */
    public function collect(string|int $Key, bool $Escape = true): array
    {
        $Return = [];

        if (!$this->QueryID instanceof mysqli_result) {
            return $Return;
        }

        while ($Row = mysqli_fetch_array($this->QueryID)) {
            $Return[] = $Escape ? display_str($Row[$Key]) : $Row[$Key];
        }
        mysqli_data_seek($this->QueryID, 0);

        return $Return;
    }

    public function set_query_id(mysqli_result|false &$ResultSet): void
    {
        $this->QueryID = $ResultSet;
        $this->Row = 0;
    }

    public function get_query_id(): mysqli_result|false
    {
        return $this->QueryID;
    }

    public function beginning(): void
    {
        if ($this->QueryID instanceof mysqli_result) {
            mysqli_data_seek($this->QueryID, 0);
            $this->Row = 0;
        }
    }

    /**
     * This function determines whether the last query caused warning messages
     * and stores them in $this->Queries.
     */
    public function warnings(): void
    {
        $Warnings = [];

        if ($this->LinkID instanceof mysqli && mysqli_warning_count($this->LinkID)) {
            $e = mysqli_get_warnings($this->LinkID);
            if ($e) {
                do {
                    if ($e->errno === 1592) {
                        // Skip: Unsafe statement written to binary log
                        continue;
                    }
                    $Warnings[] = 'Code ' . $e->errno . ': ' . display_str($e->message);
                } while ($e->next());
            }
        }

        if (count($this->Queries) > 0) {
            $this->Queries[count($this->Queries) - 1][2] = $Warnings;
        }
    }
}
