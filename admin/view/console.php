<?php require __DIR__ . "/public_header.php";?>

<link rel="stylesheet" href="/admin/assets/css/frappe-charts.css">
<script src="/admin/assets/js/lib/frappe-charts.min.iife.js"></script>

<div class="columns widgets">
	<?php
        foreach ($widgets as $v) {
            ?>
		<div class="column col-3">
			<div class="widget-item flex flex-between">
				<div class="widget-icon flex flex-middle">
					<i class="iconfont <?php $this->e($v["icon"]); ?>"></i>
				</div>
				<div class="data-info text-right">
					<div class="desc"><?php $this->e($v["name"]); ?></div>
					<div class="value">
						<span class="number"><?php $this->e($v["value"], "0"); ?></span>
					</div>
				</div>
			</div>
		</div>
	<?php
        }
    ?>
</div>

<div class="chart-panel">
	<div class="chart-head">IP Statistics</div>
	<div class="chart-body">
		<div id="chart-canvas"></div>
	</div>
</div>

<script src="/admin/assets/js/console.js"></script>

<?php require __DIR__ . "/public_footer.php";?>