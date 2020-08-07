<?php
namespace system;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Bootstrap
{
    protected static $_database = [];
    protected static $_controller = [];

    /**
     * bootstrap
     * for framework bootstrap.
     */
    public static function start()
    {
        require SYSTEM_PATH . 'functions.php';

        //set timezone
        date_default_timezone_set('Asia/Shanghai');

        //filters
        $_GET = self::stripslashesDeep($_GET);
        $_POST = self::stripslashesDeep($_POST);
        $_COOKIE = self::stripslashesDeep($_COOKIE);
        $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

        //Mailer
        UI::map('mailer', [__CLASS__, 'mailer']);

        //db : database
        UI::map('database', [__CLASS__, 'getDatabase']);

        //halt response
        UI::map('halt', [__CLASS__, 'halt']);

        //404 error
        UI::map('notFound', function () {
            return self::halt('Not Found', '404');
        });

        //get Admin Menu
        UI::map('getAdminMenu', function ($name = "") {
            if (isset($GLOBALS["admin_menu"])) {
                $admin_menu = $GLOBALS["admin_menu"];
                if (empty($name)) {
                    return $admin_menu;
                } else {
                    return isset($admin_menu[$name]) ? $admin_menu[$name] : false;
                }
            } else {
                return [];
            }
        });

        //get widgets
        UI::map('getWidgets', function ($name = "") {
            if (isset($GLOBALS["admin_widgets"])) {
                $admin_widgets = $GLOBALS["admin_widgets"];
                if (empty($name)) {
                    return $admin_widgets;
                } else {
                    return isset($admin_widgets[$name]) ? $admin_widgets[$name] : false;
                }
            } else {
                return [];
            }
        });

        // Database init
        self::databaseInit();
        
        // get all module action.php
        foreach (UI::fs()->listDir(MODULE_PATH) as $path) {
            $config_path = MODULE_PATH . $path . DS . 'config.php';
            if (file_exists($config_path)) {
                $module_config = require $config_path;
                if ($module_config['open'] == true) {
                    require MODULE_PATH . $path . DS . 'action.php';
                }
            }
        }
    }

    /**
     * end
     */
    public static function end()
    {
        if (DEBUG) {
            // Get base info
            $runtime = number_format(microtime(true) - START_TIME, 10);
            $reqs = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
            $mem = number_format((memory_get_usage() - START_MEM) / 1024, 2);

            if (isset($_SERVER['HTTP_HOST'])) {
                $uri = $_SERVER['SERVER_PROTOCOL'] . ' ' . $_SERVER['REQUEST_METHOD'] . ' : ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            } else {
                $uri = 'cmd:' . implode(' ', $_SERVER['argv']);
            }
            $base = [
                'REQUEST' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . $uri,
                'RUNTIME' => number_format($runtime, 6) . 's [ REQS:' . $reqs . 'req/s ] MEM:' . $mem . 'kb FILES:：' . count(get_included_files()),
            ];

            echo $base["RUNTIME"];
        }
    }
    /**
     * stripslashesDeep
     * deep data filters.
     */
    public static function stripslashesDeep($data)
    {
        if (is_array($data)) {
            return array_map([__CLASS__, __FUNCTION__], $data);
        } else {
            return stripslashes($data);
        }
    }

    /**
     * Database
     */
    public static function getDatabase($name = 'Db')
    {
        if (!isset(self::$_database[$name])) {
            UI::fs()->createFolder(TMP_PATH);
            UI::fs()->createFolder(TMP_PATH . "database");
            $db = \system\db\Json\JSONDB::open(TMP_PATH . 'database' . DS . $name . '_' . md5($name) . '.json');
            self::$_database[$name] = $db;
        }
        return self::$_database[$name];
    }

    /**
     * Halt
     * do something before sending response.
     */
    public static function halt($msg = '', $code = 200)
    {
        return UI::response(false)
            ->status($code)
            ->header("Content-Type", "text/html; charset=utf8")
            ->write($msg)
            ->send();
    }

    /**
     * Database Init
     * Create basic data.
     */
    public static function databaseInit()
    {
        $config_account = UI::database("Config")->where('type', 'account')->first();
        if ($config_account == null) {
            UI::database("Config")->insert([
                "type" => "account",
                "admin_name" => "admin",
                "admin_password" => "admin",
            ]);
        }
    }

    /**
     * Mailer
     * send Mailer
     */
    public static function mailer($config = [])
    {
        // $config = [
        //     "smtp_host"=>"",
        //     "smtp_port"=>"",
        //     "auth_addr"=>"",
        //     "auth_pass"=>"",
        //     "from_name"=>"",
        //     "from_addr"=>"",
        //     "to_name"=>"",
        //     "to_addr"=>"",
        //     "subject"=>"",
        //     "body"=>"",
        // ];
        if (
            empty($config['smtp_host']) ||
            empty($config['smtp_port']) ||
            empty($config['auth_addr']) ||
            empty($config['auth_pass']) ||
            empty($config['from_name']) ||
            empty($config['from_addr']) ||
            empty($config['to_addr'])
        ) {
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            //Server settings SMTP debug
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
            $mail->isSMTP(); // Send using SMTP
            $mail->Host = $config['smtp_host']; // Set the SMTP server to send through
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = $config['auth_addr']; // SMTP username
            $mail->Password = $config['auth_pass']; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port = $config['smtp_port']; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom($config['from_addr'], $config['from_name']);
            $mail->addAddress($config['to_addr']); // Add a recipient

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $config['subject'];
            $mail->Body = $config['body'];
            $mail->send();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
