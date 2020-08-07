<?php

namespace module\tinyTransfer;

use system\UI;
use system\helpers\Jwt;

/**
 * Index
 */
class Index
{
    /**
     * Send Email
     */
    private static function send_email($data=[])
    {
        $email = UI::database("Config")->where('type', 'email')->first();
        
        if ($email != null) {
            $is_send = UI::mailer([
                "smtp_host"=>$email["smtp_host"],
                "smtp_port"=>$email["smtp_port"],
                "auth_addr"=>$email["auth_addr"],
                "auth_pass"=>$email["auth_pass"],
                "from_name"=>$email["from_name"],
                "from_addr"=>$email["from_addr"],
                "to_addr"=>$data["to_addr"],
                "subject"=>$data["subject"],
                "body"=>$data["body"],
            ]);
            if ($is_send) {
                \module\statistics\Statistics::inc("email");
                return [
                    "code" => 200,
                    "msg" => "Send Success",
                ];
            } else {
                return [
                    "code" => 201,
                    "msg" => "Send Failure",
                ];
            }
        } else {
            return [
                "code" => 201,
                "msg" => "SMTP settings error",
            ];
        }
    }

    /**
     * Recipient Get Email
     */
    private static function recipient_get_email($id="")
    {
        $domain = UI::request()->scheme.'://'.UI::request()->host;
        // Get Transfer Data
        $transfer_info = UI::database("Transfer")->find($id);
        if (!$transfer_info) {
            return false;
        }
        // Get Files Data
        $files = [];
        $files_size=0;
        $transfer_info["files"] = explode(',', $transfer_info["files"]);
        foreach ($transfer_info["files"] as $v) {
            $file = UI::database("Files")->find($v);
            if ($file) {
                $files_size = $files_size + $file["file_size"];
                $file["file_size"] = Admin::formatBytes($file["file_size"]);
                array_push($files, $file);
            }
        }
        $expires_after = $transfer_info["time"] + $transfer_info["expires_after"]*86400;
        $data=[
            "logo"=>"",
            "type"=>"",
            "files"=>$files,
            "files_num"=>count($files),
            "files_size"=>Admin::formatBytes($files_size),
            "expires_time"=>date("F j, Y", $expires_after),
            "recipient"=>"",
            "recipient_email"=>[],
            "message"=>$transfer_info["form_message"],
            "download_link"=>$domain.'/'.$transfer_info["_id"],
        ];

        // recipient email
        if ($transfer_info["type"] == "email") {
            $data["recipient_email"] = explode(',', $transfer_info["form_recipient"]);
        }
        $setting = UI::database("Config")->where('type', 'tiny_transfer_setting')->first();
        if ($setting) {
            $data["logo"] = $domain.$setting["logo"];
        }

        foreach ($data["recipient_email"] as $re) {
            $data["recipient"] = $re;
            $send_info = [
                "to_addr"=>$re,
                "subject" => $transfer_info["form_sender"]." sent you files via ".$setting["title"],
                "body" => self::email_template($data),
            ];
            self::send_email($send_info);
        }
    }
    /**
     * index page
     */
    public static function home()
    {
        \module\statistics\Statistics::start("home");
        
        $tiny_transfer_setting = UI::database("Config")->where('type', 'tiny_transfer_setting')->first();
        return UI::render(MODULE_TINYTRANSFER_PATH . 'view/index.view', [
            "type"=>"upload",
            "verify"=>false,
            "id"=>"",
            "expires"=>false,
            "setting" => $tiny_transfer_setting,
        ]);
    }

    /**
     * email_template fun
     */
    public static function email_template($data=[])
    {
        return UI::fetch(MODULE_TINYTRANSFER_PATH . 'view/email.template.view', $data);
    }

    /**
     * email_template fun
     */
    public static function download_page($id="")
    {
        \module\statistics\Statistics::start("download_page");
        
        $tiny_transfer_setting = UI::database("Config")->where('type', 'tiny_transfer_setting')->first();
        $data = [
            "type"=>"download",
            "verify"=>false,
            "id"=>$id,
            "expires"=>false,
            "setting" => $tiny_transfer_setting,
        ];
        $info = UI::database("Transfer")->find($id);

        if (!$info) {
            UI::redirect('/');
        }

        if (!empty($info["password"])) {
            $data["verify"] = true;
        }

        $expires_after = $info["time"] + $info["expires_after"]*86400;
        if (time() > $expires_after) {
            $data["expires"] = true;
        }
        
        return UI::render(MODULE_TINYTRANSFER_PATH . 'view/index.view', $data);
    }

    /**
     * verify fun
     */
    public static function download_verify()
    {
        $id = UI::safe()->esc_attr(UI::request()->data["id"]);
        $password = UI::safe()->esc_attr(UI::request()->data["password"]);
        $info = UI::database("Transfer")->find($id);
        if ($info) {
            if ($info["password"] == $password) {
                return UI::json([
                    "code" => 200
                ]);
            } else {
                return UI::json([
                    "code" => 201,
                    "msg" => "password error",
                ]);
            }
        } else {
            return UI::json([
                "code" => 201,
                "msg" => "id error",
            ]);
        }
    }

    /**
     * download list
     */
    public static function download_page_list()
    {
        $data = UI::request()->data;
        $id = UI::safe()->esc_attr($data["id"]);
        $password = UI::safe()->esc_attr($data["password"]);

        $info = UI::database("Transfer")->find($id);

        if ($info["password"] != "" && $info["password"] != $password) {
            return UI::json([
                "code" => 201,
                "msg" => "password error",
            ]);
        }

        // Get Files Data
        $files = [];
        $info["files"] = explode(',', $info["files"]);
        foreach ($info["files"] as $v) {
            $file = UI::database("Files")->find($v);
            if ($file) {
                $file["file_size"] = Admin::formatBytes($file["file_size"]);
                array_push($files, [
                    "id"=>$file["_id"],
                    "name"=>$file["old_name"],
                    "ext"=>$file["file_suffixes"],
                    "size"=>$file["file_size"],
                ]);
            }
        }
        
        return UI::json([
            "code" => 200,
            "data" => [
                "expires_after"=>$info["expires_after"],
                "files"=>$files
            ],
        ]);
    }

    /**
     * download link
     */
    public static function download_link()
    {
        $r = UI::request()->data;
        $data = [
            "id" => UI::safe()->esc_attr($r["id"]),
            "type" => UI::safe()->esc_attr($r["type"]),
            "file_id" => isset($r["file_id"]) ? UI::safe()->esc_attr($r["file_id"]) : "",
            "password" => isset($r["password"]) ? UI::safe()->esc_attr($r["password"]) : ""
        ];

        $info = UI::database("Transfer")->find($data["id"]);

        if ($info["password"] != "" && $info["password"] != $data["password"]) {
            return UI::json([
                "code" => 201,
                "msg" => "password error",
            ]);
        }

        $jwt = JWT::encode([
            "iss" => $data,
            "iat" => time(),
            "exp" => time() + 3600
        ], "BBFPL");

        $domain = UI::request()->scheme.'://'.UI::request()->host;

        return UI::json([
            "code" => 200,
            "data" => $domain."/download/".$jwt,
        ]);
    }

    /**
     * download file
     */
    public static function download_file($token="")
    {
        try {
            $decoded = JWT::decode($token, "BBFPL", array('HS256'));
            $decoded = object_to_array($decoded);
            $data = $decoded["iss"];
            $id = $data["id"];
            $info = UI::database("Transfer")->find($id);
            // Password determination
            if ($info["password"] != "" && $info["password"] != $data["password"]) {
                exit("Error");
            }

            // Get Files Data
            $files = [];
            // Download single files or all files
            if ($data["type"] == "single") {
                $file = UI::database("Files")->find($data["file_id"]);
                if ($file) {
                    array_push($files, $file);
                }
            } else {
                $info_files = explode(',', $info["files"]);
                foreach ($info_files as $v) {
                    $file = UI::database("Files")->find($v);
                    if ($file) {
                        array_push($files, $file);
                    }
                }
            }
            
            // Create a zip directory
            UI::fs()->createFolder(TMP_PATH . '_zip');
            $zip_path = TMP_PATH . '_zip' . DS . $id . '.zip';
            $count = 0;
            // Determining the existence of a file/Create a zip file
            if (!file_exists($zip_path)) {
                $zip = null;
                foreach ($files as $name => $file) {
                    // Whether or not a ZipArchive object is instantiated.
                    if (!$zip) {
                        $zip = new \ZipArchive();
                        // Open the zip package
                        $zip->open($zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                    }
                    // Add the file to the zip package.
                    $zip->addFile(ROOT_PATH.DS.$file["file_path"], $file["old_name"]);
                    $count++;
                }
                if ($zip) {
                    // Close zip package
                    $zip->close();
                }
            }

            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=' . md5($id).'.zip');
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");
            header('Content-Length: ' . filesize($zip_path));
    
            ob_clean();
            flush();
    
            @readfile($zip_path);
            unlink($zip_path);
            \module\statistics\Statistics::inc("download");
            exit();
        } catch (\Exception $e) {
            exit("Error");
        }
    }

    /**
     * uploader api
     */
    public static function upload_file()
    {
        UI::fs()->createFolder(ROOT_PATH . 'uploads');

        $files = UI::request()->files;
        $data = UI::request()->data;

        $file_info = \module\tinyTransfer\Upload::start([
            "file"=>$files['file'],
            "chunk"=>$data["chunk"],
            "chunks"=>$data["chunks"],
            "security_suffix"=>''
        ]);

        if ($file_info["status"]=="success") {
            $file_info["time"] = time();
            $res = UI::database("Files")->insert($file_info);
            return UI::json([
                "code" => 200,
                "data"=>$res["_id"]
            ]);
        } else {
            return UI::json([
                "code" => 201,
                "data"=>$file_info
            ]);
        }
    }

    
    /**
     * get link/send email api
     */
    public static function transfer()
    {
        $data = UI::request()->data;
        $insert_data = [
            "time" => time(),
            "uuid"=>UI::safe()->esc_attr($data["uuid"]),
            "type"=>UI::safe()->esc_attr($data["type"]),
            "files"=>UI::safe()->esc_attr($data["files"]),
            "expires_after"=>UI::safe()->esc_attr($data["expires_after"]),
            "password"=>UI::safe()->esc_attr($data["password"]),
            "form_message"=>UI::safe()->esc_attr($data["form"]["message"])
        ];

        if (UI::safe()->esc_attr($data["type"])=="email") {
            $insert_data["form_recipient"] = UI::safe()->esc_attr($data["form"]["recipient"]);
            $insert_data["form_sender"] = UI::safe()->esc_attr($data["form"]["sender"]);
        }

        // Insert Data
        $res = UI::database("Transfer")->insert($insert_data);

        // Output results
        if ($res) {
            \module\statistics\Statistics::inc("transfer");

            if (UI::safe()->esc_attr($data["type"]) == "email") {
                //Send an email notification
                self::recipient_get_email($res["_id"]);
            }
            return UI::json([
                "code" => 200,
                "id" => $res["_id"],
            ]);
        } else {
            return UI::json([
                "code" => 201,
                "msg" => "create error",
            ]);
        }
    }
}
