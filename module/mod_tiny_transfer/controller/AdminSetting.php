<?php

namespace module\tinyTransfer;

use system\UI;

/**
 * AdminSetting
 */
class AdminSetting
{

    /**
     * index page
     */
    public static function index_page()
    {
        // initialization
        $tiny_transfer_setting = UI::database("Config")->where('type', 'tiny_transfer_setting')->first();
        if ($tiny_transfer_setting == null) {
            UI::database("Config")->insert([
                "type" => "tiny_transfer_setting",
                "logo" => "",
                "title" => "",
                "description" => "",
                "keywords" => "",
                "footer" => "",
            ]);
        }

        // Query the database
        $res = UI::database("Config")->where('type', 'tiny_transfer_setting')->first();

        // fetch
        return UI::fetch(MODULE_TINYTRANSFER_PATH . 'view/admin.setting', [
            "v" => $res,
        ]);
    }

    /**
     * handle
     */
    public static function handle()
    {
        if (UI::request()->method == "PUT") {
            self::save();
        }
    }

    // setting save
    private static function save()
    {
        // Query the database
        $find = UI::database("Config")->where('type', 'tiny_transfer_setting')->first();
        if ($find) {
            
            // Update data
            $res = UI::database("Config")->where('type', 'tiny_transfer_setting')->update([
                'title' => UI::safe()->esc_attr(UI::request()->data['title']),
                'logo' => UI::safe()->esc_attr(UI::request()->data['logo']),
                'description' => UI::safe()->esc_attr(UI::request()->data['description']),
                'keywords' => UI::safe()->esc_attr(UI::request()->data['keywords']),
            ]);

            // Output results
            if ($res) {
                return UI::json([
                    "code" => 200,
                    "msg" => "update success",
                ]);
            } else {
                return UI::json([
                    "code" => 201,
                    "msg" => "update error",
                ]);
            }
        } else {
            // Output results
            return UI::json([
                "code" => 201,
                "msg" => "No data.",
            ]);
        }
    }


    /**
     * upload file
     */
    public static function upload_file()
    {
        $file = UI::request()->files['file'];

        UI::fs()->createFolder(ROOT_PATH . 'uploads');
        UI::fs()->createFolder(ROOT_PATH . 'uploads/admin');

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filepath = sprintf('/uploads/admin/%s.%s', uniqid(), $ext);

        //save file to /uploads/admin
        if (!move_uploaded_file($file['tmp_name'], ROOT_PATH . $filepath)) {
            return UI::json([
                "code" => 201,
                "msg" => 'Failed to move uploaded file.',
            ]);
        } else {
            return UI::json([
                "code" => 200,
                "data" => $filepath,
            ]);
        }
    }
}
