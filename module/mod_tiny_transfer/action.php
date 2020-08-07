<?php
use system\UI;

// define module-TinyTransfer path
define('MODULE_TINYTRANSFER_PATH', __DIR__ . DS);
// insert transfer setting
$tiny_transfer_setting = UI::database("Config")->where('type', 'tiny_transfer_setting')->first();
if ($tiny_transfer_setting == null) {
    UI::database("Config")->insert([
        "type" => "tiny_transfer_setting",
        'title' => "TinyTransfer",
        'logo' => "\/module\/mod_tiny_transfer\/assets\/img\/logo.png",
        'description' => "Tiny Transfer is the simplest way to send your files around the world",
        'keywords' => "Tiny,Transfer,file,send,zip,upload,download"
    ]);
}
// widgets
add_widgets("pv", function () {
    return \module\tinyTransfer\Statistics::pv();
});
add_widgets("transfer", function () {
    return \module\tinyTransfer\Statistics::transfer();
});
add_widgets("email", function () {
    return \module\tinyTransfer\Statistics::email();
});
add_widgets("download", function () {
    return \module\tinyTransfer\Statistics::download();
});

// add admin menu
add_menu_page('Transfer Home', 'Transfer', 'tiny_transfer_list', function () {
    return \module\tinyTransfer\Admin::index();
}, 'icon-liebiao');
// List api
UI::route('POST /tiny_transfer_list/handle', [\module\tinyTransfer\Admin::class, 'handle']);
/* List End */

/* Setting */
// Setting Menu
add_menu_page('Transfer Setting', 'Setting', 'tiny_transfer_setting', function () {
    return \module\tinyTransfer\AdminSetting::index_page();
}, 'dot');
// Setting api
UI::route('POST /tiny_transfer_setting/upload', [\module\tinyTransfer\AdminSetting::class, 'upload_file']);
UI::route('GET|POST|DELETE|PUT /tiny_transfer_setting/handle', [\module\tinyTransfer\AdminSetting::class, 'handle']);
/* Setting End */

// Template Menu
add_menu_page('Transfer Template', 'Template', 'tiny_transfer_template', function () {
    return \module\tinyTransfer\Admin::template();
}, 'dot');


// routes
UI::route('GET /', [\module\tinyTransfer\Index::class, 'home']);
UI::route('GET /@id', [\module\tinyTransfer\Index::class, 'download_page']);
UI::route('GET /download/@token', [\module\tinyTransfer\Index::class, 'download_file']);
UI::route('POST /mod_tiny_transfer/download_page_list', [\module\tinyTransfer\Index::class, 'download_page_list']);
UI::route('POST /mod_tiny_transfer/download_link', [\module\tinyTransfer\Index::class, 'download_link']);
UI::route('POST /mod_tiny_transfer/verify', [\module\tinyTransfer\Index::class, 'download_verify']);
UI::route('POST /mod_tiny_transfer/upload', [\module\tinyTransfer\Index::class, 'upload_file']);
UI::route('POST /mod_tiny_transfer/transfer', [\module\tinyTransfer\Index::class, 'transfer']);
