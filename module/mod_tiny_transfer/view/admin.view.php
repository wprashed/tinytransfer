<link rel="stylesheet" href="/module/mod_tiny_transfer/assets/css/admin.css">

<div class="card">
	<div class="card-body">
		<ul class="tab">
			<li class="tab-item active">
				<a><i class="iconfont icon-wenjian"></i> Transfer List</a>
			</li>
		</ul>
		<div class="flex flex-end mb-2">
			<?php
				if ($expiredCount>0) {
			?>
				<a class="btn btn-primary clearing-expired-btn" data-num="<?php $this->e($expiredCount, 0); ?>">Clearing Expired(<?php $this->e($expiredCount, 0); ?>)</a>
			<?php
				}
			?>
		</div>

		<?php
            if (count($data)>0) {
                ?>
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th>Type</th>
					<th>Files</th>
					<th class="col-5">Content</th>
					<th>Expires After</th>
					<th>Time</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    foreach ($data as $v) {
                        ?>
					<tr>
						<td>
							<?php
            					if ($v["type"]=="link") {
                			?>
								<a class="type-icon tooltip" data-tooltip="Link"><i class="iconfont icon-lianjie"></i></a>
							<?php
            					}else{
                			?>
								<a class="type-icon tooltip" data-tooltip="Mail"><i class="iconfont icon-youxiang"></i></a>
							<?php
                            } ?>
						</td>
						<td>
							<?php $this->e($v["files"]); ?> files
						</td>
						<td>
							<?php
                                if ($v["type"]=="email") {
                                    ?>
								<div>
									<label>Recipient</label>
									<div>
										<?php
                                            foreach ($v["form_recipient"] as $vv) {
                                                ?>
											<span class="chip"><?php $this->e($vv); ?></span>
										<?php
                                            } ?>
									</div>
								</div>
								<div>
									<label>Sender</label>
									<div>
										<span class="chip"><?php $this->e($v["form_sender"]); ?></span>
									</div>
								</div>
							<?php
                                } ?>
							<div>
								<?php
                                	if ($v["form_message"]!="") {
                                ?>
									<label>Message</label>
									<p class="pl-2"><?php $this->e($v["form_message"]); ?></p>
								<?php
									} 
								?>
							</div>
						</td>
						<td><?php $this->e($v["expires_after"]); ?> day</td>
						<td><time datetime="<?php $this->e($v["time"]); ?>"><?php $this->e($v["time"]); ?></time></td>
					</tr>
				<?php
                    } ?>
			</tbody>
		</table>
		<?php
            } else {
                ?>
			<div class="empty-data">
				<i></i>
			</div>
		<?php
            } ?>
	</div>

	<div class="flex flex-center">
		<nav>
			<?php $this->h($page);?>
		</nav>
	</div>
</div>
<script src="/module/mod_tiny_transfer/assets/js/admin.list.js"></script>