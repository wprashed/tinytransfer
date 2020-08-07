<?php
namespace admin;

use system\UI;

/**
 * AdminLogin
 */
class Account
{
    /**
     * sign_in
     */
    public static function sign_in()
    {
        if (UI::request()->method == "GET") {
            if (!Base::isLogin()) {
                // render view
                return UI::render(ADMIN_PATH . 'view/sign_in');
            } else {
                // redirect console page
                UI::redirect('/admins/console');
            }
        } else {
            self::sign_in_post();
        }
    }

    /**
     * sign_in_post
     */
    private static function sign_in_post()
    {
        $name = UI::safe()->esc_attr(UI::request()->data['name']);
        $password = UI::safe()->esc_attr(UI::request()->data['password']);

        $admin = UI::database("Config")->where('admin_name', $name)->first();

        if ($admin == null) {
            return UI::json([
                "code" => 201,
            ]);
        }
        if ($admin['admin_password'] == $password) {
            // set session
            set_session("admin", "true");

            return UI::json([
                "code" => 200,
            ]);
        } else {
            return UI::json([
                "code" => 201,
            ]);
        }
    }

    /**
     * sign_out
     */
    public static function sign_out()
    {
        // del session
        del_session("admin");
        UI::redirect('/admins');
    }

    /**
     * index account page
     */
    public static function account()
    {
        Base::auth();

        if (UI::request()->method == "GET") {
            return UI::render(ADMIN_PATH . 'view/account', [
                "title" => "Settings Account",
            ]);
        } else {
            self::account_save();
        }
    }
    
    /**
     * account save
     */
    public static function account_save()
    {
        Base::auth();
        
        $name = UI::safe()->esc_attr(UI::request()->data['name']);
        $password = UI::safe()->esc_attr(UI::request()->data['password']);

        $admin = UI::database("Config")->where('admin_name', $name)->first();
        if ($admin != null) {
            UI::database("Config")->where('admin_name', $name)->update([
                'admin_password' => $password,
            ]);
            return UI::json([
                "code" => 200,
            ]);
        } else {
            return UI::json([
                "code" => 201,
            ]);
        }
    }
}
