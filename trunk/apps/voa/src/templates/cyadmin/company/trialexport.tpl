{include file='cyadmin/header.tpl'}

<div class="panel panel-default">
	<div class="panel-heading">
		<h4>试用期延期操作记录导出</h4>

		<div class="panel-body">
			<form class="form-horizontal" action="{$form_url}" method="post">

				<input hidden="hidden" name="formhash" value="{$formhash}">

				<script>
					$(function () {
						$('#sandbox-container .input-daterange').datepicker({
							todayHighlight: true
						});
					});
				</script>

				<div class="form-group ">
					<label class="control-label col-sm-1">日期区间</label>

					<div class="col-md-4" id="sandbox-container">
						<div class="input-daterange input-group" id="datepicker">
							<input type="text"
							       class="input-sm form-control"
							       name="date_start">

							<span class="input-group-addon">to</span>

							<input type="text"
							       class="input-sm form-control"
							       name="date_end">
						</div>
					</div>

					<label class="control-label col-sm-1"></label>

					<div class="col-sm-3">
						<a class="btn btn-default" href= "/enterprise/export">重 置</a>
						<button name="export" value="export" type="submit" class="btn btn-warning">导 出
						</button>
					</div>

				</div>

				<code>PS:第一个输入框是选择大于这个时间的数据,第二个是小于这个时间的数据,时间可以交叉也可以不交叉,也可以其中一个为空</code>

			</form>
		</div>
	</div>
</div>


{include file='cyadmin/footer.tpl'}
