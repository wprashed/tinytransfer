<?php
namespace admin;

use system\UI;

/**
 * Console
 */
class Console
{
    /**
    * index page
    */
    public static function index()
    {
        Base::auth();
        $widgets = \admin\Base::widgets();
        
        return UI::render(ADMIN_PATH.'view/console', [
            "title"=>"Console",
            "widgets"=>$widgets
        ]);
    }

    /**
    * chart api
    */
    public static function chart()
    {
        Base::auth();
        $chart = \module\statistics\Statistics::chart();

        return UI::json([
            "code" => 200,
            "data" => $chart
        ]);
    }
}
