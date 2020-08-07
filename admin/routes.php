<?php
use system\UI;

// sign_in
UI::route('GET|POST /admins', [\admin\Account::class, 'sign_in']);

// sign_out
UI::route('GET /admins/sign_out', [\admin\Account::class, 'sign_out']);

// console page
UI::route('GET /admins/console', [\admin\Console::class, 'index']);
UI::route('GET /admins/console/chart', [\admin\Console::class, 'chart']);

// settings page
UI::route('GET|POST /admins/account', [\admin\Account::class, 'account']);

// module page
UI::route('GET /admins/pages', [\admin\Pages::class, 'index']);
