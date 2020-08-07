<?php

namespace module\smtp;

use system\UI;

/**
 * admin
 */
class Admin
{
    /**
     * index
     */
    public static function index()
    {
        // get email info
        $email = UI::database("Config")->where('type', 'email')->first();
  
        return UI::fetch(MODULE_SMTP_PATH . 'view/admin.view', [
            "data" => $email,
        ]);
    }

    /**
     * setting email
     */
    public static function save()
    {
        $data = UI::request()->data;
        $config_email = UI::database("Config")->where('type', 'email')->first();
        if ($config_email != null) {
            UI::database("Config")->where('type', 'email')->update([
                "smtp_host" => UI::safe()->esc_attr($data["smtp_host"]),
                "smtp_port" => UI::safe()->esc_attr($data["smtp_port"]),
                "auth_addr" => UI::safe()->esc_attr($data["auth_addr"]),
                "auth_pass" => UI::safe()->esc_attr($data["auth_pass"]),
                "from_name" => UI::safe()->esc_attr($data["from_name"]),
                "from_addr" => UI::safe()->esc_attr($data["from_addr"])
            ]);
            return UI::json([
                "code" => 200,
                "msg" => "Update successfully!",
            ]);
        } else {
            return UI::json([
                "code" => 201,
                "msg" => "Update failed!",
            ]);
        }
    }

    /**
     * test email
     */
    public static function test()
    {
        $data = UI::request()->data;
        $email = UI::database("Config")->where('type', 'email')->first();
        if ($email != null) {
            $is_send = UI::mailer([
                "smtp_host"=>$email["smtp_host"],
                "smtp_port"=>$email["smtp_port"],
                "auth_addr"=>$email["auth_addr"],
                "auth_pass"=>$email["auth_pass"],
                "from_name"=>$email["from_name"],
                "from_addr"=>$email["from_addr"],
                "to_addr"=>UI::safe()->esc_attr($data["to_addr"]),
                "subject"=>UI::safe()->esc_attr($data["subject"]),
                "body"=>UI::safe()->esc_attr($data["body"]),
            ]);

            if ($is_send) {
                return UI::json([
                    "code" => 200,
                    "msg" => "Send Success",
                ]);
            } else {
                return UI::json([
                    "code" => 201,
                    "msg" => "Send Failure",
                ]);
            }
        } else {
            return UI::json([
                "code" => 201,
                "msg" => "SMTP settings error",
            ]);
        }
    }
}
