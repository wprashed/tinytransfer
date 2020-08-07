<?php

namespace module\tinyTransfer;

use system\UI;

class Upload
{
    public static $file;
    public static $file_path;
    public static $tmp_path;
    public static $chunk;
    public static $chunks;
    public static $security_suffix;

    public static function start($option=[])
    {
        $md5_ymd = md5(date("Y-m-d", time()));

        self::$file = isset($option["file"]) ? $option["file"] : null;
        self::$file_path = (isset($option["file_path"]) ? $option["file_path"] : 'uploads')  . DS . $md5_ymd . DS;
        self::$tmp_path = isset($option["tmp_path"]) ? $option["tmp_path"] . DS : TMP_PATH . "_file".DS;
        self::$chunk = isset($option["chunk"]) ? $option["chunk"] : 0;
        self::$chunks = isset($option["chunks"]) ? $option["chunks"] : 1;
        self::$security_suffix = isset($option["security_suffix"]) ? $option["security_suffix"] : '';

        UI::fs()->createFolder(ROOT_PATH . self::$file_path);

        if (!isset($option["tmp_path"])) {
            UI::fs()->createFolder(TMP_PATH . "_file");
        }

        $cleanup_target_dir = true;
        $max_file_age = 60*60*24;

        $file_name = self::$file["name"];
        $old_name = $file_name;
        $file_path = self::$tmp_path . $file_name;

        // Chunking might be enabled
        $chunk = self::$chunk;
        $chunks = self::$chunks;

        // Delete cache checksum
        if ($cleanup_target_dir) {
            if (!is_dir(self::$tmp_path) || !$dir = opendir(self::$tmp_path)) {
                return [
                    "status"=>'error',
                    "msg"=>"Failed to open temp directory"
                ];
            }
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = self::$tmp_path  . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$file_path}_{$chunk}.part" || $tmpfilePath == "{$file_path}_{$chunk}.parttmp") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp|mp4)$/', $file) && (@filemtime($tmpfilePath) < time() - $max_file_age)) {
                    @unlink($tmpfilePath);
                }
            }

            closedir($dir);
        }

        if (!$out = @fopen("{$file_path}_{$chunk}.parttmp", "wb")) {
            return [
                "status"=>'error',
                "msg"=>"Failed to open output stream"
            ];
        }
        if (!empty(self::$file)) {
            if (self::$file["error"] || !is_uploaded_file(self::$file["tmp_name"])) {
                return [
                    "status"=>'error',
                    "msg"=>"Failed to move uploaded file"
                ];
            }
            // Read binary input stream and append it to temp file
            if (!$in = @fopen(self::$file["tmp_name"], "rb")) {
                return [
                    "status"=>'error',
                    "msg"=>"Failed to open input stream"
                ];
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                return [
                    "status"=>'error',
                    "msg"=>"Failed to open input stream"
                ];
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($out);
        @fclose($in);
        rename("{$file_path}_{$chunk}.parttmp", "{$file_path}_{$chunk}.part");
        $index = 0;
        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$file_path}_{$index}.part")) {
                $done = false;
                break;
            }
        }
 
        //Upload all files Execute merge files
        if ($done) {
            $path_info = pathinfo($file_name);
            $hash_str = substr(md5($path_info['basename']), 8, 16);
            $hash_name = time() . $hash_str . '.' .$path_info['extension'];
            $upload_path = ROOT_PATH . self::$file_path .$hash_name . self::$security_suffix;

            if (!$out = @fopen($upload_path, "wb")) {
                return [
                    "status"=>'error',
                    "msg"=>"Failed to open output stream"
                ];
            }
            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = @fopen("{$file_path}_{$index}.part", "rb")) {
                        break;
                    }
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                    @fclose($in);
                    @unlink("{$file_path}_{$index}.part");
                }
                flock($out, LOCK_UN);
            }
            @fclose($out);

            // oss aws

            $response = [
                "status"=>'success',
                'old_name'=>$old_name,
                'hash_name'=>$hash_name,
                'file_path'=>self::$file_path .$hash_name,
                'file_suffixes'=>$path_info['extension'],
                'file_size'=>self::$file['size'],
                'security_suffix'=>self::$security_suffix
            ];
            
            return $response;
        } else {
            return [
                "status"=>'pending',
                "chunk"=>$chunk,
                "chunks"=>$chunks,
            ];
        }
    }
}
