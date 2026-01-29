<?php

declare(strict_types=1);

/**
 * Miscellaneous utility functions class
 *
 * Static utility methods for common operations throughout Gazelle.
 * Updated for PHP 8.5 compatibility.
 */
class Misc
{
    /**
     * Send an email
     *
     * @param string $To The email address to send it to
     * @param string $Subject The email subject
     * @param string $Body The email body
     * @param string $From The user part of the user@SITE_DOMAIN email address
     * @param string $ContentType text/plain or text/html
     */
    public static function send_email(
        string $To,
        string $Subject,
        string $Body,
        string $From = 'noreply',
        string $ContentType = 'text/plain'
    ): void {
        $Headers = "MIME-Version: 1.0\r\n";
        $Headers .= "Content-type: $ContentType; charset=utf-8\r\n";
        $Headers .= 'From: ' . SITE_NAME . ' <' . $From . '@' . SITE_DOMAIN . ">\r\n";
        $Headers .= 'Reply-To: ' . $From . '@' . SITE_DOMAIN . "\r\n";
        $Headers .= "X-Mailer: Project Gazelle\r\n";
        $Headers .= 'Message-Id: <' . Users::make_secret() . '@' . SITE_DOMAIN . ">\r\n";
        $Headers .= "X-Priority: 3\r\n";
        mail($To, $Subject, $Body, $Headers, "-f $From@" . SITE_DOMAIN);
    }

    /**
     * Sanitize a string to be allowed as a filename
     *
     * @param string $EscapeStr The string to escape
     * @return string The string with all banned characters removed
     */
    public static function file_string(string $EscapeStr): string
    {
        return str_replace(['"', '*', '/', ':', '<', '>', '?', '\\', '|'], '', $EscapeStr);
    }

    /**
     * Send a PM from $FromId to $ToId
     *
     * @param int|array<int> $ToID ID of user to send PM to
     * @param int $FromID ID of user to send PM from, 0 to send from system
     * @param string $Subject The message subject
     * @param string $Body The message body
     * @param string $ConvID The conversation ID
     * @return int|null The conversation ID
     */
    public static function send_pm(
        int|array $ToID,
        int $FromID,
        string $Subject,
        string $Body,
        string $ConvID = ''
    ): ?int {
        $UnescapedSubject = $Subject;
        $UnescapedBody = $Body;
        $Subject = db_string($Subject);
        $Body = DBCrypt::encrypt($Body);

        if ($ToID === 0 || (is_array($ToID) && in_array(0, $ToID, true))) {
            return null;
        }

        $QueryID = G::$DB->get_query_id();

        if ($ConvID === '') {
            G::$DB->query("
                INSERT INTO pm_conversations (Subject)
                VALUES ('$Subject')");
            $ConvID = (string)G::$DB->inserted_id();

            $toIdValue = is_array($ToID) ? $ToID[0] : $ToID;
            G::$DB->query("
                INSERT INTO pm_conversations_users
                    (UserID, ConvID, InInbox, InSentbox, SentDate, ReceivedDate, UnRead)
                VALUES
                    ('$toIdValue', '$ConvID', '1','0','" . sqltime() . "', '" . sqltime() . "', '1')");

            if ($FromID === $toIdValue) {
                G::$DB->query("
                    UPDATE pm_conversations_users
                    SET InSentbox = '1'
                    WHERE ConvID = '$ConvID'");
            } elseif ($FromID !== 0) {
                G::$DB->query("
                    INSERT INTO pm_conversations_users
                        (UserID, ConvID, InInbox, InSentbox, SentDate, ReceivedDate, UnRead)
                    VALUES
                        ('$FromID', '$ConvID', '0','1','" . sqltime() . "', '" . sqltime() . "', '0')");
            }
            $ToID = is_array($ToID) ? $ToID : [$ToID];
        } else {
            $ToID = is_array($ToID) ? $ToID : [$ToID];
            G::$DB->query("
                UPDATE pm_conversations_users
                SET
                    InInbox = '1',
                    UnRead = '1',
                    ReceivedDate = '" . sqltime() . "'
                WHERE UserID IN (" . implode(',', $ToID) . ")
                    AND ConvID = '$ConvID'");

            G::$DB->query("
                UPDATE pm_conversations_users
                SET
                    InSentbox = '1',
                    SentDate = '" . sqltime() . "'
                WHERE UserID = '$FromID'
                    AND ConvID = '$ConvID'");
        }

        G::$DB->query("
            INSERT INTO pm_messages
                (SenderID, ConvID, SentDate, Body)
            VALUES
                ('$FromID', '$ConvID', '" . sqltime() . "', '$Body')");

        foreach ($ToID as $ID) {
            G::$DB->query("
                SELECT COUNT(ConvID)
                FROM pm_conversations_users
                WHERE UnRead = '1'
                    AND UserID = '$ID'
                    AND InInbox = '1'");
            $result = G::$DB->next_record();
            $UnRead = $result[0] ?? 0;
            G::$Cache->cache_value("inbox_new_$ID", $UnRead);
        }

        G::$DB->query("
            SELECT Username
            FROM users_main
            WHERE ID = '$FromID'");
        $result = G::$DB->next_record();
        $SenderName = $result[0] ?? 'System';

        foreach ($ToID as $ID) {
            G::$DB->query("
                SELECT COUNT(ConvID)
                FROM pm_conversations_users
                WHERE UnRead = '1'
                    AND UserID = '$ID'
                    AND InInbox = '1'");
            $result = G::$DB->next_record();
            $UnRead = $result[0] ?? 0;
            G::$Cache->cache_value("inbox_new_$ID", $UnRead);

            if (class_exists('NotificationsManager')) {
                NotificationsManager::send_push($ID, "Message from $SenderName, Subject: $UnescapedSubject", $UnescapedBody, site_url() . 'inbox.php', NotificationsManager::INBOX);
            }
        }

        G::$DB->set_query_id($QueryID);

        return (int)$ConvID;
    }

    /**
     * Create a forum thread
     *
     * @param int $ForumID The forum ID
     * @param int $AuthorID ID of the user creating the post
     * @param string $Title Thread title
     * @param string $PostBody Post content
     * @return int -1 on error, -2 on user not existing, thread id on success
     */
    public static function create_thread(int $ForumID, int $AuthorID, string $Title, string $PostBody): int
    {
        if (!$ForumID || !$AuthorID || !is_number($AuthorID) || !$Title || !$PostBody) {
            return -1;
        }

        $QueryID = G::$DB->get_query_id();

        G::$DB->query("
            SELECT Username
            FROM users_main
            WHERE ID = $AuthorID");
        if (!G::$DB->has_results()) {
            G::$DB->set_query_id($QueryID);
            return -2;
        }

        $ThreadInfo = ['IsLocked' => 0, 'IsSticky' => 0];

        G::$DB->query("
            INSERT INTO forums_topics
                (Title, AuthorID, ForumID, LastPostTime, LastPostAuthorID, CreatedTime)
            VALUES
                ('$Title', '$AuthorID', '$ForumID', '" . sqltime() . "', '$AuthorID', '" . sqltime() . "')");
        $TopicID = (int)G::$DB->inserted_id();
        $Posts = 1;

        G::$DB->query("
            INSERT INTO forums_posts
                (TopicID, AuthorID, AddedTime, Body)
            VALUES
                ('$TopicID', '$AuthorID', '" . sqltime() . "', '$PostBody')");
        $PostID = G::$DB->inserted_id();

        G::$DB->query("
            UPDATE forums
            SET
                NumPosts  = NumPosts + 1,
                NumTopics = NumTopics + 1,
                LastPostID = '$PostID',
                LastPostAuthorID = '$AuthorID',
                LastPostTopicID = '$TopicID',
                LastPostTime = '" . sqltime() . "'
            WHERE ID = '$ForumID'");

        G::$DB->query("
            UPDATE forums_topics
            SET
                NumPosts = NumPosts + 1,
                LastPostID = '$PostID',
                LastPostAuthorID = '$AuthorID',
                LastPostTime = '" . sqltime() . "'
            WHERE ID = '$TopicID'");

        G::$DB->set_query_id($QueryID);

        return $TopicID;
    }

    /**
     * Check if the suffix of $Haystack is $Needle
     */
    public static function ends_with(string $Haystack, string $Needle): bool
    {
        return str_ends_with($Haystack, $Needle);
    }

    /**
     * Check if the prefix of $Haystack is $Needle
     */
    public static function starts_with(string $Haystack, string $Needle): bool
    {
        return str_starts_with($Haystack, $Needle);
    }

    /**
     * Variant of in_array() with trailing wildcard support
     *
     * @param string $Needle The string to find
     * @param array<string> $Haystack The array to search in
     * @return bool True if found
     */
    public static function in_array_partial(string $Needle, array $Haystack): bool
    {
        static $Searches = [];

        if (array_key_exists($Needle, $Searches)) {
            return $Searches[$Needle];
        }

        foreach ($Haystack as $String) {
            if (str_ends_with($String, '*')) {
                if (str_starts_with($Needle, substr($String, 0, -1))) {
                    $Searches[$Needle] = true;
                    return true;
                }
            } elseif ($Needle === $String) {
                $Searches[$Needle] = true;
                return true;
            }
        }

        $Searches[$Needle] = false;
        return false;
    }

    /**
     * Check that keys in a request array are set
     *
     * @param array<string, mixed> $Request The array to check
     * @param array<string>|null $Keys The keys to ensure are set
     * @param bool $AllowEmpty Allow empty values
     * @param int|string $Error The error code
     */
    public static function assert_isset_request(
        array $Request,
        ?array $Keys = null,
        bool $AllowEmpty = false,
        int|string $Error = 0
    ): void {
        if ($Keys !== null) {
            foreach ($Keys as $K) {
                if (!isset($Request[$K]) || (!$AllowEmpty && $Request[$K] === '')) {
                    error($Error);
                }
            }
        } else {
            foreach ($Request as $R) {
                if (!isset($R) || (!$AllowEmpty && $R === '')) {
                    error($Error);
                }
            }
        }
    }

    /**
     * Given an array of tags, return an array of their IDs
     *
     * @param array<string> $TagNames Array of tag names
     * @return array<int, string> Tag IDs as keys, names as values
     */
    public static function get_tags(array $TagNames): array
    {
        $TagIDs = [];

        foreach ($TagNames as $Index => $TagName) {
            $Tag = G::$Cache->get_value("tag_id_$TagName");
            if (is_array($Tag)) {
                unset($TagNames[$Index]);
                $TagIDs[$Tag['ID']] = $Tag['Name'];
            }
        }

        if (count($TagNames) > 0) {
            $QueryID = G::$DB->get_query_id();
            G::$DB->query("
                SELECT ID, Name
                FROM tags
                WHERE Name IN ('" . implode("', '", $TagNames) . "')");
            $SQLTagIDs = G::$DB->to_array();
            G::$DB->set_query_id($QueryID);

            foreach ($SQLTagIDs as $Tag) {
                $TagIDs[$Tag['ID']] = $Tag['Name'];
                G::$Cache->cache_value('tag_id_' . $Tag['Name'], $Tag, 0);
            }
        }

        return $TagIDs;
    }

    /**
     * Gets the alias of a tag
     */
    public static function get_alias_tag(string $BadTag): string
    {
        $QueryID = G::$DB->get_query_id();
        G::$DB->query("
            SELECT AliasTag
            FROM tag_aliases
            WHERE BadTag = '$BadTag'
            LIMIT 1");
        if (G::$DB->has_results()) {
            $result = G::$DB->next_record();
            $AliasTag = $result[0] ?? $BadTag;
        } else {
            $AliasTag = $BadTag;
        }
        G::$DB->set_query_id($QueryID);
        return $AliasTag;
    }

    /**
     * Write a message to the system log
     */
    public static function write_log(string $Message): void
    {
        $QueryID = G::$DB->get_query_id();
        G::$DB->query("
            INSERT INTO log (Message, Time)
            VALUES ('" . db_string($Message) . "', '" . sqltime() . "')");
        G::$DB->set_query_id($QueryID);
    }

    /**
     * Sanitize a tag for database input and display
     */
    public static function sanitize_tag(string $Str): string
    {
        $Str = strtolower($Str);
        $Str = preg_replace('/[^a-z0-9:.]/', '', $Str) ?? '';
        $Str = preg_replace('/(^[.,]*)|([.,]*$)/', '', $Str) ?? '';
        $Str = htmlspecialchars($Str, ENT_QUOTES, 'UTF-8');
        return db_string(trim($Str));
    }

    /**
     * HTML escape an entire array for output
     *
     * @param array<mixed> $Array The array to escape
     * @param bool|array<string> $Escape Escape options
     * @return array<mixed> Escaped array
     */
    public static function display_array(array $Array, bool|array $Escape = []): array
    {
        foreach ($Array as $Key => $Val) {
            if ((!is_array($Escape) && $Escape === true) || (is_array($Escape) && !in_array($Key, $Escape, true))) {
                $Array[$Key] = display_str($Val);
            }
        }
        return $Array;
    }

    /**
     * Search for a key/value pair in an array
     *
     * @param array<mixed> $Array The array to search
     * @param string $Key The key to look for
     * @param mixed $Value The value to match
     * @return array<array<mixed>> Results
     */
    public static function search_array(array $Array, string $Key, mixed $Value): array
    {
        $Results = [];

        if (isset($Array[$Key]) && $Array[$Key] === $Value) {
            $Results[] = $Array;
        }

        foreach ($Array as $subarray) {
            if (is_array($subarray)) {
                $Results = array_merge($Results, self::search_array($subarray, $Key, $Value));
            }
        }

        return $Results;
    }

    /**
     * Search for $Needle in separated string
     */
    public static function search_joined_string(
        string $Haystack,
        string $Needle,
        string $Separator = '|',
        bool $Strict = true
    ): bool {
        return in_array($Needle, explode($Separator, $Haystack), $Strict);
    }

    /**
     * Check if torrent data is in binary format
     */
    public static function is_new_torrent(string &$Data): bool
    {
        return str_contains(substr($Data, 0, 10), ':');
    }

    /**
     * Display recommend widget
     *
     * @param int $ID The item ID
     * @param string $Type The item type
     * @param bool $Hide Whether to hide initially
     */
    public static function display_recommend(int $ID, string $Type, bool $Hide = true): void
    {
        $hideStyle = $Hide ? ' style="display: none;"' : '';
        ?>
        <div id="recommendation_div" data-id="<?=$ID?>" data-type="<?=$Type?>"<?=$hideStyle?> class="center">
            <div style="display: inline-block;">
                <strong>Recommend to:</strong>
                <select id="friend" name="friend">
                    <option value="0" selected="selected">Choose friend</option>
                </select>
                <input type="text" id="recommendation_note" placeholder="Add note..." />
                <button id="send_recommendation" disabled="disabled">Send</button>
            </div>
            <div class="new" id="recommendation_status"><br /></div>
        </div>
<?php
    }

    /**
     * Check if a URL is valid
     */
    public static function is_valid_url(string $URL): bool
    {
        return (bool)preg_match('|^https?://[a-z0-9-]+(\.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $URL);
    }
}
