<!doctype html>
<html lang="en">

<head>
	<!-- Meta Tags -->
	<meta charset="UTF-8">
	<meta name="description" content="<?php $this->e($setting["description"]); ?>">
	<meta name="keywords" content="<?php $this->e($setting["keywords"]); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;" name="viewport" />

	<!-- Theme stylesheets -->
	<link rel="stylesheet" href="./module/mod_tiny_transfer/assets/css/iconfont.css">
	<link rel="stylesheet" href="./module/mod_tiny_transfer/assets/css/theme.css">
	<link rel="stylesheet" href="./module/mod_tiny_transfer/assets/css/scrollbar.css">
	<link rel="stylesheet" href="./module/mod_tiny_transfer/assets/css/dropdown.css">
	<link rel="stylesheet" href="./module/mod_tiny_transfer/assets/css/tiny-transfer-form.css">
	<link rel="stylesheet" href="./module/mod_tiny_transfer/assets/css/main.css">
	
	<title><?php $this->e($setting["title"]); ?></title>
</head>

<body>

	<main class="main-area">
		
		<!-- tinyTransfer start -->
		<div class="tiny-transfer" 
		data-type="<?php $this->e($type);?>" 
		<?php if ($verify==true) { ?>data-verify="true"<?php } ?> 
		<?php if (!empty($id)) { ?>data-id="<?php $this->e($id);?>"<?php } ?> 
		<?php if ($expires==true) { ?>data-expires="true"<?php } ?> 
		>
		</div>
		<!-- tinyTransfer end -->

		<!-- content start -->
		<div class="content flex flex-middle">
			
			<div class="section-heading">
				<h2 class="section-title flex flex-center flex-middle">
					<i class="logo"></i>
					<span>Tiny Transfer</span>
				</h2>
				<p>
					Tiny Transfer is the simplest way to send your files around the world
				</p>
			</div>
		</div>
		<!-- content end -->
	</main>

	

	<!-- Javascript Plugins -->
	<script src="./module/mod_tiny_transfer/assets/js/lib/jquery.min.js"></script>
	<script src="./module/mod_tiny_transfer/assets/js/lib/template.js"></script>
	<script src="./module/mod_tiny_transfer/assets/js/lib/ui/core.js"></script>
	<script src="./module/mod_tiny_transfer/assets/js/lib/ui/touch.js"></script>
	<script src="./module/mod_tiny_transfer/assets/js/lib/ui/scrollbar.js"></script>
	<script src="./module/mod_tiny_transfer/assets/js/lib/ui/uploader.js"></script>
	<script src="./module/mod_tiny_transfer/assets/js/lib/ui/dropdown.js"></script>
	<script src="./module/mod_tiny_transfer/assets/js/tinyTransfer.js"></script>

	<!-- Javascript Plugins -->
	<script src="./module/mod_tiny_transfer/assets/js/lib/underscore-min.js"></script>
	<script src="./module/mod_tiny_transfer/assets/js/main.js"></script>
</body>

</html>