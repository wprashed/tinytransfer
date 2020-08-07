<?php
namespace admin;

use system\UI;

/**
 * AdminBase
 */
class Base
{
    /**
     * is login
     *
     * @return true/false
     */
    public static function isLogin()
    {
        if (get_session("admin") == "true") {
            return true;
        } else {
            del_session("admin");
            return false;
        }
    }

    /**
     * authorize
     *
     * @return If success will return current user id
     */
    public static function auth()
    {
        if (!self::isLogin()) {
            return UI::redirect('/admins');
        }
    }

    /**
     * left nav
     *
     * @return array
     */
    public static function nav()
    {
        $url = UI::request()->url;
        $nav = [];

        foreach (UI::getAdminMenu() as $v) {
            if ($v[5]==true) {
                array_push($nav, [
                    "name" => $v[0],
                    "path" => "/admins/pages?module=" . $v[1],
                    "icon" => $v[3],
                ]);
            }
        }

        array_push($nav, [
            "name" => "Account",
            "path" => "/admins/account",
            "icon" => "icon-zhanghu",
        ]);

        foreach ($nav as &$v) {
            $v["active"] = "";
            if (strpos($url, $v["path"]) !== false) {
                $v["active"] = "active";
            }
        }

        return $nav;
    }

    /**
     * sub menu
     *
     * @return array
     */
    public static function subMenu($name = "")
    {
        $url = UI::request()->url;

        $data = [
            "settings" => [
                [
                    "name" => "Account",
                    "path" => "/admins/account",
                ],
            ],
        ];

        foreach ($data[$name] as &$v) {
            $v["active"] = "";
            if ($v["path"] == $url) {
                $v["active"] = "active";
            }
        }
        return $data[$name];
    }


    /**
     * widgets
     *
     * @return array
     */
    public static function widgets()
    {
        $widgets = [];
        foreach (UI::getWidgets() as $v) {
            array_push($widgets, UI::runHook($v));
        }
        return $widgets;
    }
}
