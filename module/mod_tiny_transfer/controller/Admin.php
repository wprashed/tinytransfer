<?php
namespace module\tinyTransfer;

use system\UI;

/**
 * Admin
 */
class Admin
{
    // Get File Size
    public static function formatBytes($size)
    {
        $units = [' B', ' KB', ' MB', ' GB', ' TB'];
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . $units[$i];
    }
    // Get File Ext
    private static function getExt($filename)
    {
        $str = strrev($filename);
        return strtoupper(strrev(strchr($str, '.', true)));
    }

    // Get Files
    private static function getFiles($ids="")
    {
        $ids = explode(',', $ids);
        $files=[];

        foreach ($ids as $id) {
            $file = UI::database("Files")->find($id);
            if ($file) {
                $is_img = stripos('GIF|JPEG|PNG|BMP|JPG|SVG', $file["file_suffixes"]) !== false ? true : false;
                array_push($files, [
                    "id" => $file["_id"],
                    "name" => $file["old_name"],
                    "file_size" => self::formatBytes($file["file_size"]),
                    "file_suffixes" => $file["file_suffixes"],
                    "color" =>UI::color()->textToColor($file["file_suffixes"]),
                    "is_img" =>$is_img
                ]);
            }
        }
        return $files;
    }

    /**
     * index page
     */
    public static function index()
    {
        // Transfer list
        $data = UI::database("Transfer")->sortBy('time', 'desc')->get();
        //expired files
        $expired = [];
        // foreach
        foreach ($data as &$v) {
            if (time() > ($v["time"] + $v["expires_after"]*60*60*24)) {
                array_push($expired, $v);
            }

            if ($v["type"]=="email") {
                $v["form_recipient"] = explode(',', $v["form_recipient"]);
            }

            $v["time"] = date("F j, Y", $v["time"]);
            $v["files"] = count(self::getFiles($v["files"]));
        }

        $pagination = UI::pagination($data, 10, [
            'simple' => false,
            'allCounts' => false,
            'page' => UI::request()->query['page'],
        ]);

        $totalCount = $pagination->totalCount;
        // Get Data
        $items = $pagination->getItem();
        // Page render
        $page = $pagination->render();

        return UI::fetch(MODULE_TINYTRANSFER_PATH . 'view/admin.view', [
            "title" => "Files",
            "page" => $page,
            "data" => $items,
            "totalCount" => $totalCount,
            "expiredCount"=>count($expired)
        ]);
    }

    /**
     * index page
     */
    public static function template()
    {
        $domain = UI::request()->scheme.'://'.UI::request()->host;
        $data=[
            "logo"=>$domain."/module/mod_tiny_transfer/assets/img/logo.png",
            "type"=>"",
            "files"=>[
                [
                    "_id"=>"5f111b37f0546",
                    "old_name"=>"400x400.jpg",
                    "hash_name"=>"15949565991d9aac97aaeba050.jpg",
                    "file_path"=>"uploads\24565b6b3c63abcfc339a5768b850768\15949565991d9aac97aaeba050.jpg",
                    "file_suffixes"=>"jpg",
                    "file_size"=>"9.04 KB",
                    "security_suffix"=>"",
                    "time"=>time(),
                ]
            ],
            "files_num"=>2,
            "files_size"=>"300 kb",
            "expires_time"=>date("F j, Y", time()),
            "recipient"=>"",
            "recipient_email"=>[],
            "message"=>"hello message",
            "download_link"=>$domain.'/'.time(),
        ];
        $html = UI::fetch(MODULE_TINYTRANSFER_PATH . 'view/email.template.view', $data);

        return UI::fetch(MODULE_TINYTRANSFER_PATH . 'view/admin.template.view', [
            "html" => $html
        ]);
    }


    /**
     * handle
     */
    public static function handle()
    {
        if (UI::request()->method == "POST") {
            self::clear();
        }
    }

    // clear
    private static function clear()
    {
        // Query the database
        // Transfer list
        $data = UI::database("Transfer")->sortBy('time', 'desc')->get();
        //expired files
        $expired = [];
        // remove Transfer
        foreach ($data as $v) {
            if (time() > ($v["time"] + $v["expires_after"]*60*60*24)) {
                $files = explode(',', $v["files"]);
                foreach ($files as $f) {
                    array_push($expired, $f);
                }
                UI::database("Transfer")->where('_id', $v["_id"])->delete();
            }
        }
        // remove Files
        foreach ($expired as $v) {
            $file = UI::database("Files")->find($v);
            UI::fs()->deleteFile(ROOT_PATH.$file["file_path"]);
            UI::database("Files")->where('_id', $file["_id"])->delete();
        }

        return UI::json([
            "code" => 200,
            "msg" => "Clear successfully",
        ]);
    }
}
