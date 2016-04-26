<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索工单</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$search_action_url}">
		<input type="hidden" name="submit_search" value="1" />
		<div class="form-row">
			<div class="form-group">
				<label class="vcy-label-none" for="id_sender">派单人：</label>
				<input type="text" class="form-control form-small" id="id_sender" name="sender" placeholder="派单人姓名" value="{$search_by['sender']|escape}" maxlength="54" />
				<span class="space"></span>

				<label class="vcy-label-none" for="id_woid">工单编号：</label>
				<input type="text" class="form-control form-small" id="id_woid" name="woid" placeholder="工单编号" value="{$search_by['woid']|escape}" maxlength="10" />
				<span class="space"></span>
				
				<label class="vcy-label-none" for="id_wostate">工单状态：</label>
				<select id="id_wostate" name="wostate" class="form-control font12">
					<option value="-1">不限</option>
{foreach $wostate_list as $_key => $_name}
					<option value="{$_key}"{if $search_by['wostate']==$_key} selected="selected"{/if}>{$_name}</option>
{/foreach}
				</select>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group">
				<label class="vcy-label-none" for="id_operator">接收人：</label>
				<input type="text" class="form-control form-small" id="id_operator" name="operator" placeholder="接收人姓名" value="{$search_by['operator']|escape}" maxlength="54" />
				<span class="space"></span>

				<label class="vcy-label-none" for="id_ordertime_start">派单日期：</label>
				<div class="input-daterange input-group" id="bs-datepicker-range">
 					<input type="text" class="input-sm form-control" id="id_ordertime_start" name="ordertime_start" placeholder="开始日期" value="{$search_by['ordertime_start']|escape}" />
					<span class="input-group-addon">至</span>
					<input type="text" class="input-sm form-control" id="id_ordertime_end"  name="ordertime_end" placeholder="结束日期" value="{$search_by['ordertime_end']|escape}" />
				</div>
				<span class="space"></span>
				<button type="button" class="btn btn-default form-small form-small-btn margin-left-12" onclick="javascript:_reset();"><i class="fa fa-refresh"></i> 重置搜索表单</button>
				<span class="space"></span>
				<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
			</div>
		</div>
		</form>
	</div>
</div>
<script type="text/javascript">
function _reset() {
	jQuery('#id_sender').val('');
	$('#id_woid').val('');
	$('#id_wostate').val('-1');
	$('#id_operator').val('');
	$('#id_ordertime_start').val('');
	$('#id_ordertime_end').val('');
}
init.push(function () {	
	var options2 = {							
		todayBtn: "linked",
		orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
	}
	$('#bs-datepicker-range').datepicker(options2);
});
</script>
