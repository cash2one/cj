{include file="$tpl_dir_base/header.tpl"}
<form class = "form-horizontal font12" action="{$form_action_url}" method ="post" id="form">
	<div class="panel panel-warning">
		<div class="panel-heading">
			<strong>签到</strong>
		</div>
		<div class="panel-body">

			<div class="form-group font12">
				<label for="late_range" class="col-sm-3 control-label text-right">晚到多久算迟到</label>
				<div class="col-sm-9">
					<input type="number" class="form-control" id="late_range" name="late_range" value="{$late_range}" max="86400" required="required"><p class="help-block">单位：分钟，正整数，默认：10</p>
				</div>
			</div>	<div class="form-group font12">
				<label for="leave_early_range" class="col-sm-3 control-label text-right">早退时间范围</label>
				<div class="col-sm-9">
					<input type="number" class="form-control" id="leave_early_range" name="leave_early_range" value="{$leave_early_range}" max="86400" required="required"><p class="help-block">单位：分钟，正整数，默认：10</p>
				</div>
			</div>
			<!-- <div class="form-group font12">
		<label for="work_begin_hi" class="col-sm-3 control-label text-right"></label>
		<div class="col-sm-9">
			<label><input type="checkbox" value='1' name="ibeacon_set" {if $ibeacon_set == 1}checked{/if}> 启用ibeacon微信摇一摇考勤</label><p class="help-block">启用后，配置ibeacon即可用微信摇一摇完成考勤</p>
		</div>
			</div>	 -->

			<div class="form-group font12">
				<label for="work_end_hi" class="col-sm-3 control-label text-right"></label>
				<div class="col-sm-9">
					<label><input type="checkbox" value='1' name="permission" {if $permission == 1}checked{/if}> 部门主管能查看所属部门所有成员的考勤情况</label>
				</div>
			</div>

			<div class="form-group font12">
				<label for="work_begin_hi" class="col-sm-3 control-label text-right"></label>
				<div class="col-sm-9">
					<input type="submit" class="btn btn-success" value="保存">
				</div>
			</div>

		</div>

</form>
<script>
	$(function(){
		$('#form').submit(function(){
			if($('#late_range').val()<0){
				alert('请输入大于0的数');
				return false;
			}
			if($('#leave_early_range').val()<0){
				alert('请输入大于0的数');
				return false;
			}
			return true;
		});
	});
</script>
{include file="$tpl_dir_base/footer.tpl"}