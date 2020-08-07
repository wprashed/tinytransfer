<?php require __DIR__ . "/public_header.php";?>


<div class="card flex flex-center">
	<div class="card-body">
		<ul class="tab">
			<li class="tab-item active">
			  <a >Account</a>
			</li>
		</ul>
        <div class="flex flex-center">
            <div class="col-7 col-xl-12">
				<div class="columns col-oneline">
					<div class="column col-6">
						<form class="form-horizontal" id="settingAccountForm" action="/admins/account">
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
									<label class="form-label" for="password">New Password</label>
								</div>
								<div class="col-12">
									<input id="password" type="password" class="form-input" name="password" required>
								</div>
							</div>
							<div class="col-12"><hr></div>
							<div class="form-group flex flex-center">
								<button type="submit" class="btn btn-primary">
									<i class="iconfont icon-save"></i> Save Changes
								</button>
							</div>
						</form>
					</div>
					<div class="column col-6">
						<div class="account-info">
							<p class="mb-2">Update management account</p>
							<p class="small text-muted mb-2">you have to meet all of the following requirements:</p>
							<ul class="small text-muted pl-4 mb-0">
								<li>User name cannot be empty</li>
								<li>password cannot be empty</li>
								<li>At least one number</li>
								<li>Can’t be the same as a previous password</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<script src="/admin/assets/js/account.js"></script>

<?php require __DIR__ . "/public_footer.php";?>