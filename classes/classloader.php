<?php

declare(strict_types=1);

/**
 * Load classes automatically when they're needed
 *
 * @param string $ClassName class name
 */
spl_autoload_register(function (string $ClassName): void {
    $FilePath = SERVER_ROOT . '/classes/' . strtolower($ClassName) . '.class.php';
    if (!file_exists($FilePath)) {
        // Handle classes with non-standard naming
        $FileName = match ($ClassName) {
            'MASS_USER_BOOKMARKS_EDITOR' => 'mass_user_bookmarks_editor.class',
            'MASS_USER_TORRENTS_EDITOR' => 'mass_user_torrents_editor.class',
            'MASS_USER_TORRENTS_TABLE_VIEW' => 'mass_user_torrents_table_view.class',
            'TEXTAREA_PREVIEW' => 'textarea_preview.class',
            'TORRENT', 'BENCODE_DICT', 'BENCODE_LIST' => 'torrent.class',
            default => throw new RuntimeException("Couldn't import class $ClassName"),
        };
        $FilePath = SERVER_ROOT . "/classes/$FileName.php";
    }
    require_once($FilePath);
});
