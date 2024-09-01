<?php

class Utils {

    public static function createRandomDirectory() : string {
        $root_tmp = defined('DOWNLOAD_FOLDER') ? DOWNLOAD_FOLDER : '/tmp';
        $seed_len = defined('SEED_LENGTH') ? SEED_LENGTH : null;

        $rand_dir = "random-" . substr(string: uniqid(), offset: 0, length: $seed_len);
        $dirpath = "$root_tmp/$rand_dir";

        if (!mkdir($dirpath))
            throw new Exception('Could not make temporary folder');
        
        return $dirpath;
    }

}