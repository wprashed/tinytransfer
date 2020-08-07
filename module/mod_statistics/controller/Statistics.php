<?php

namespace module\statistics;

use system\UI;

/**
 * Statistics
 */
class Statistics
{
    /**
     * insert
     */
    private static function insert($name="", $value=1)
    {
        $id = UI::database("Statistics")->insert([
            "type" => $name,
            "value" => $value,
        ]);
        if ($id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * update
     */
    private static function update($name="", $value=1)
    {
        $id = UI::database("Statistics")->where('type', $name)->update([
            "value" => $value,
        ]);
        if ($id) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * get
     */
    public static function get($name="")
    {
        // get info
        $info = UI::database("Statistics")->where('type', $name)->first();
        if ($info) {
            return $info["value"];
        } else {
            return 0;
        }
    }

    /**
     * inc
     */
    public static function inc($name="")
    {
        // get info
        $info = UI::database("Statistics")->where('type', $name)->first();
        if ($info) {
            self::update($name, $info["value"]+1);
        } else {
            self::insert($name);
        }
    }


    /**
     * start insert
     */
    private static function start_insert($table="", $time=0, $page="")
    {
        $db = UI::database($table);
        $ip = UI::request()->ip;

        if (empty(!$page)) {
            $db = $db->where("page", $page);
        }
        // get info
        $statistics_ip = $db->where("ip", $ip)->sortBy('time', 'desc')->first();
        $data =[
            "time"=>time(),
            "ip"=>$ip
        ];
        if (empty(!$page)) {
            $data["page"] = $page;
        }

        if ($statistics_ip) {
            if (time() > $statistics_ip["time"] + $time) {
                UI::database($table)->insert($data);
            }
        } else {
            UI::database($table)->insert($data);
        }
    }

    /**
     * start
     */
    public static function start($page="")
    {
        self::start_insert("StatisticsIp", 3600);
        self::start_insert("StatisticsPv", 60, $page);
    }

    /**
     * pv
     */
    public static function pv()
    {
        $num = UI::database("StatisticsPv")->count();
        return $num;
    }

    /**
     * Get Prev Week
     */
    private static function getPrevWeek()
    {
        $dates=[];
        for ($i = 0; $i <= 7; ++$i) {
            array_push($dates, date('Y-m-d', strtotime("-".$i." day")));
        }
        return array_reverse($dates);
    }

    /**
     * chart
     */
    public static function chart()
    {
        $a_week_ago = time() - 60*60*24*7;
        $pvs = UI::database("StatisticsPv")->where('time', '>', $a_week_ago)->where('time', '<', time())->get();
        if (!$pvs) {
            $data = [[],[],[],[],[],[],[]];
        } else {
            $data = [];
        }
        $week = self::getPrevWeek();
        foreach ($week as $w) {
            foreach ($pvs as $v) {
                if (!isset($data[$w])) {
                    $data[$w] = [];
                }
                if ($w == date("Y-m-d", $v["time"])) {
                    array_push($data[$w], $v["ip"]);
                }
            }
        }

        $values = [];
        foreach ($data as $v) {
            array_push($values, count($v));
        }

        return [
            "labels"=>$week,
            "values"=>$values
        ];
    }
}
