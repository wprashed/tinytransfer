<?php
namespace admin;

use system\UI;

/**
 * Pages
 */
class Pages
{
    /**
    * index page
    */
    public static function index()
    {
        Base::auth();
        // get query page
        $module = UI::request()->query['module'];

        if (empty($module)) {
            UI::halt("No Module", 404);
            return;
        }
        // get menu
        $menu = UI::getAdminMenu($module);

        if ($menu) {
            // render html
            return UI::render(ADMIN_PATH.'view/page', [
                "title"=>$menu[2],
                "html"=>UI::runHook($menu[4])
            ]);
        } else {
            echo "no page";
        }
    }
}
