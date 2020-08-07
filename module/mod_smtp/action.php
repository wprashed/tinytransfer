<?php
use system\UI;

// define module-smtp path
define('MODULE_SMTP_PATH', __DIR__ . DS);

$config_email = UI::database("Config")->where('type', 'email')->first();
if ($config_email == null) {
    UI::database("Config")->insert([
        "type" => "email",
        "smtp_host" => "",
        "smtp_port" => "",
        "auth_addr" => "",
        "auth_pass" => "",
        "from_name" => "",
        "from_addr" => "",
        "to_name" => "",
        "to_addr" => "",
        "subject" => "",
        "body" => "",
    ]);
}

// add admin menu
add_menu_page('Smtp Home', 'Smtp', 'setting_smtp_form', function () {
    return \module\smtp\Admin::index();
}, 'icon-emailFilled');

/* routes */
// form api service send
UI::route('POST /mod_smtp/save', [\module\smtp\Admin::class, 'save']);
UI::route('POST /mod_smtp/test', [\module\smtp\Admin::class, 'test']);
