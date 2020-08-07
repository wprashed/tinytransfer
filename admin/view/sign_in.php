<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="author" content="BBFPL">
	<title>Sign in</title>
	<!-- Theme Css -->
	<link rel="stylesheet" href="/admin/assets/css/theme.css">
	<link rel="stylesheet" href="/admin/assets/css/spop.css">
	<link rel="stylesheet" href="/admin/assets/css/login.css">
</head>

<body>
	<div class="container flex flex-center">
		<div class="card-wrapper">
			<div class="card-body">
				<h4 class="card-title">Sign in </h4>
				<form class="form-horizontal" id="loginForm" action="/admins">
					<div class="form-group">
						<div class="col-12">
							<label class="form-label" for="name">Name</label>
						</div>
						<div class="col-12">
							<input id="name" type="text" class="form-input" name="name" required autofocus>
						</div>
					</div>
					<div class="form-group">
						<div class="col-12">
							<label class="form-label" for="password">Password</label>
						</div>
						<div class="col-12">
							<input id="password" type="password" class="form-input" name="password" required data-eye>
						</div>
					</div>
					<div class="form-group no-margin">
						<button type="submit" class="btn btn-primary btn-block">
							Login
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- Javascript Plugins --> 
	<script src="/admin/assets/js/lib/jquery.min.js"></script>
	<script src="/admin/assets/js/lib/spop.min.js"></script>
	<script src="/admin/assets/js/login.js"></script>
</body>

</html>