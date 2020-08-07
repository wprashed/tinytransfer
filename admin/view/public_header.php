<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="author" content="BBFPL">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

		<title><?php $this->e($title); ?></title>

		<!-- theme stylesheets -->
		<link rel="stylesheet" href="/admin/assets/css/iconfont.css">
		<link rel="stylesheet" href="/admin/assets/css/theme.css">
		<link rel="stylesheet" href="/admin/assets/css/spop.css">
		<!-- admin stylesheets -->
		<link rel="stylesheet" href="/admin/assets/css/main.css">
		<!-- Javascript -->
		<script src="/admin/assets/js/lib/jquery.min.js"></script>
		<script src="/admin/assets/js/lib/spop.min.js"></script>

	</head>

	<body>

		<nav id="sidebar">
			<div class="container-fluid">
				<a class="logo" href="/admins/console">
					<i class="iconfont icon-logo-cat"></i>
				</a>
				<ul class="nav">
					<?php
                        $navs = \admin\Base::nav();
                        foreach ($navs as $v) {
                            ?>
					<li class="nav-item">
						<a href="<?php $this->e($v["path"]); ?>" class="nav-link <?php $this->e($v["active"]); ?>">
							<i class="iconfont <?php $this->e($v["icon"]); ?>"><span></span></i><?php $this->e($v["name"]); ?>
						</a>
					</li>
					<?php
                        }
                    ?>
				</ul>
			</div>
			<div class="ver">v<?php $this->e(VERSION); ?></div>
		</nav>

		<header id="header">
			<div class="header-content">
				<a href="/admins/sign_out" class="logout tooltip tooltip-left" data-tooltip="Sign Out">
					<i class="iconfont icon-dengchu"></i>
				</a>
			</div>
		</header>
		
		<main class="main-content">
			<div class="container-fluid">
