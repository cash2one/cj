{include file="$tpl_dir_base/header.tpl"}
<link href="{$CSSDIR}bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$JSDIR}bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="{$JSDIR}bootstrap-datetimepicker.zh-CN.js"></script>
<div class="panel panel-default font12">
	<div class="panel-heading"><strong>记录搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}" id= "id-form-search" data-ng-app="ng.poler.plugins.pc">
			<input type="hidden" name="issearch" value="1" />

			<span id="cd_id_choose" style="display: none;"></span>

			<div class="form-row">
				<div class="form-group" style="width: 100%;">
                    <label class="vcy-label-none col-md-3" for="id_m_username" style="display:block;width:75px;top:10px;">部门：</label>
                    <div class="col-md-7" style="width:auto;left:-28px;top:3px;">

                        <!-- angular 选人组件 begin -->
                        <div class="dep_deafult_data " data-ng-controller="ChooseShimCtrl">
							<a class="btn btn-defaul pull-left"
							   data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')" style="background: #fff">
								<i class="fa fa-plus" style="color:#46b8da"></i> 部门
							</a>
							<pre id="dep_deafult_data" style="margin-left: 5px;"></pre>
                        </div>
                        <!-- angular 选人组件 end -->
						<style type="text/css">
							#dep_deafult_data{
								margin-top: 10px;font-size: 12px;letter-spacing: 1px;background-color: #f4b04f;display:none;border-radius: 5px;padding: 2px 5px;color: #ffffff;float: left;border: 1px solid #f4b04f;margin: 5px 5px 0 0;
							}
							#dep_deafult_data:after{
								content: "X";
								cursor: pointer;
							}
						</style>
                    </div>

					<script>
						init.push(function () {
							var options2 = {
								language: 'zh-CN',
								format: 'yyyy-mm-dd',
								startView: 2,
								minView: 2,
								autoclose: true
							};
							$('#id_signtime_min').datetimepicker(options2);
							$('#id_signtime_max').datetimepicker(options2);
						});
					</script>
					<label class="vcy-label-none" for="id_signtime_min">上报时间：</label>
					<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
						<input type="text" class="input-sm form-control" readonly style="cursor:default;" id="id_signtime_min" name="signtime_min"  placeholder="开始日期" {if empty($searchBy['signtime_min'])}value="{$begin_d}" {else if} value="{$searchBy['signtime_min']|escape}" {/if}/>
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" readonly style="cursor:default;" id="id_signtime_max" name="signtime_max" placeholder="结束日期" {if empty($searchBy['signtime_max'])}value="{$end_d}"{else if}value="{$searchBy['signtime_max']|escape}"{/if} />
					</div>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_m_username">姓名：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="考勤人用户名" value="{$searchBy['m_username']|escape}" maxlength="54" />
					<span class="space"></span>
					<!--
					<input type="date" class="form-control form-small" id="id_signtime_min" name="signtime_min" value="{$searchBy['signtime_min']|escape}" />
					<label class="vcy-label-none" for="id_signtime_max"> 至 </label>
					<input type="date" class="form-control form-small" id="id_signtime_max" name="signtime_max" value="{$searchBy['signtime_max']|escape}" /> -->
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<button type="button" id="id-download" class="btn btn-warning form-small form-small-btn margin-left-12"><i class="fa fa-cloud-download"></i> 导出</button>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>
	<table class="table table-striped table-hover font12 table-bordered">
		<colgroup>
			<col class="t-col-13" />
			<col class="t-col-15" />
			<col class="t-col-13" />

			<col />
			<col class="t-col-13" />
		</colgroup>
		<thead>
		<tr>
			<th>姓名</th>
			<th>所属部门</th>
			<th>上报时间</th>

			<th>上报位置</th>
			<th>考勤详情</th>
		</tr>
		</thead>
		{if $total > 0}
			<tfoot>
			<tr>
				<td colspan="5" class="text-right vcy-page">{$multi}</td>
			</tr>
			</tfoot>
		{/if}
		<tbody>

		{foreach $list as $_id=>$_data}

			<tr>
				<td>{$_data['m_username']}</td>
				<td>{$_data['cd_name']}</td>
				<td>{$_data['_signtime']|escape}</td>

				<td class="text-left">{$_data['sl_address']}</td>
				<td>{$base->linkShow($detailUrlBase, $_id, '查看详情', 'fa-eye', '')}</td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="5" class="warning">{if $issearch}未搜索到指定条件的签到记录{else}暂无任何签到记录{/if}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>
<script type="text/javascript">

	/* 选人组件 */
	var dep_arr = [];
	var cd_id_choose = '';

	/* 选人组件默认值 */
	// 部门默认值
	dep_arr = {$searchBy['dep_default']};
	if (dep_arr.length != 0) {
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < dep_arr.length; i ++) {
			cd_id_choose += '<input name="cd_id[]" value="' + dep_arr[i]['id'] + '" type="hidden">';
			select_dep_name += dep_arr[i]['cd_name'] + ' ';
		}
		$('#cd_id_choose').html(cd_id_choose);

		// 展示
		if (select_dep_name != '') {
			$('#dep_deafult_data').html(select_dep_name).show();
		} else {
			$('#dep_deafult_data').hide();
		}
	}
	$(document).on("click", "#dep_deafult_data", function(){
		dep_arr = [];
		$('#cd_id_choose').html('');
		$('#id-form-search').submit();
	});
	// 选择部门回调
	function selectedDepartmentCallBack(data){
		if (data.length > 1) {
			alert('只能选一个部门');

			return false;
		}
		dep_arr = data;

		// 页面埋入 选择的值
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < data.length; i ++) {
			cd_id_choose += '<input name="cd_id[]" value="' + data[i]['id'] + '" type="hidden">';
			select_dep_name += data[i]['name'] + ' ';
		}
		$('#cd_id_choose').html(cd_id_choose);

		// 展示
		if (select_dep_name != '') {
			$('#dep_deafult_data').html(select_dep_name).show();
		} else {
			$('#dep_deafult_data').hide();
		}
	}

	jQuery(function(){
		jQuery('#id-download').click(function(){
			if (jQuery('#__dump__').length == 0) {
				jQuery('body').append('<iframe id="__dump__" name="__dump__" src="about:blank" style="width:0;height:0;padding:0;margin:0;border:none;"></iframe>');
			}
			jQuery('#id-form-search').append('<input type="hidden" id="id-dump-input" name="is_dump" value="1" />').attr('target', '__dump__').submit();
			jQuery('#id-form-search').removeAttr('target');
			jQuery('#id-dump-input').remove();
		});
	});
</script>
{include file="$tpl_dir_base/footer.tpl"}