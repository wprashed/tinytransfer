<?php

namespace module\tinyTransfer;

use system\UI;

/**
 * Statistics
 */
class Statistics
{
    /**
     * pv
     */
    public static function pv()
    {
        return [
            "icon" => "icon-icon",
            "name" => "Page View",
            "value" => (string)(\module\statistics\Statistics::pv())
        ];
    }

    /**
     * transfer
     */
    public static function transfer()
    {
        return [
            "icon" => "icon-fasong",
            "name" => "Transfer",
            "value" => (string)(\module\statistics\Statistics::get("transfer"))
        ];
    }

    /**
     * email
     */
    public static function email()
    {
        return [
            "icon" => "icon-xianxingyoujian",
            "name" => "Send Email",
            "value" => (string)(\module\statistics\Statistics::get("email"))
        ];
    }

    /**
     * download
     */
    public static function download()
    {
        return [
            "icon" => "icon-xiazai",
            "name" => "Download",
            "value" => (string)(\module\statistics\Statistics::get("download"))
        ];
    }
}
