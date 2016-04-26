{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" action="{$form_action_url}" method="post" data-ng-app="ng.poler.plugins.pc" onsubmit="return checkForm();">
			<input type="hidden" name="formhash" value="{$formhash}" />
			{if $result.id}<input type="hidden" name="id" value="{$result.id}">{/if}
			<input type="hidden" name="pid" value="{$pid}">
			<span id="cd_id_choose" style="display: none;"></span>
			<span id="m_uid_choose" style="display: none;"></span>
			
			<div class="form-group">
				<label class="control-label col-sm-2">父级分类*</label>
				<div class="col-sm-10"><p class="form-control-static">{if $parent['title']}{$parent['title']}{else}无{/if}</p></div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">标题*</label>
				<div class="col-sm-10">
					<input type="text" class="form-control form-small" name="title" placeholder="不超过10个汉字" maxlength="10" required="required" value="{$result.title}"/>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">排序号*</label>
				<div class="col-sm-10">
					<input type="text" class="form-control form-small" name="orderid" placeholder="数字越小，排在越前面" maxlength="64"  required="required" value="{if $result.orderid}{$result.orderid}{else}0{/if}"/>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">适用范围*</label>
				<div class="col-sm-10">
					<div>
						<input type="hidden" class="form-small" id="is_all" name="is_all" value="{$result['is_all']}"/>
						<button type="button" class="btn{if $result['is_all'] == 1} btn-primary{/if}" id="all_btn">全公司</button>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<button type="button" class="btn{if $result['is_all'] == 0} btn-primary{/if}" id="specified_btn">指定对象</button>
					</div>

					<div id="user_dep_container"{if $result['is_all'] == 1} style="display: none"{/if}>
						<hr>
						<div class="row">
							<label class="col-sm-2 text-right padding-sm">选择部门：</label>

							<div id="deps_container" class="col-sm-8">

								<div class="angularjs-area " data-ng-controller="ChooseShimCtrl">
									<a class="btn btn-defaul" data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选择部门</a>
								</div>

								{if $result['pid']!=0 && $parent['departments']}
								<p class="form-control-static">
									父级：
									{foreach $parent['departments'] as $_k => $_v}
										{$_v['cd_name']}&nbsp;&nbsp;
									{/foreach}
								</p>
								{/if}

								<pre id="dep_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

								{*{include *}
									{*file="$tpl_dir_base/common_selector_member.tpl"*}
									{*input_type='checkbox'*}
									{*input_name_department='cd_ids[]'*}
									{*selector_box_id='deps_container'*}
									{*allow_member=false*}
									{*allow_department=true	*}
								{*}*}
							</div>
						</div>
						<br>
						<div class="row">
							<label class="col-sm-2 text-right padding-sm">选择人员：</label>
							<div id="users_container" class="col-sm-8">

								<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
									<a class="btn btn-defaul" data-ng-click="selectPerson('m_uid','selectedMuidCallBack')">选择人员</a>
								</div>

								{if $result['pid']!=0 && $parent['members']}
								<p class="form-control-static">
									父级：
									{foreach $parent['members'] as $_k => $_v}
										{$_v['m_username']}&nbsp;&nbsp;
									{/foreach}
								</p>
								{/if}

								<pre id="m_uid_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

								{*{include *}
									{*file="$tpl_dir_base/common_selector_member.tpl"*}
									{*input_type='checkbox'*}
									{*input_name='m_uids[]'*}
									{*selector_box_id='users_container'*}
									{*allow_member=true*}
									{*allow_department=false*}
								{*}*}
							</div>
						</div>
					</div>

					{if $result.id}<p class="form-control-static text-warning">修改分类适用范围，将会影响之前已经发布的培训内容，请注意！</p>{/if}

				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">启用分类*</label>

				<div class="col-sm-10">
					<label class="radio-inline"><input type="radio" name="is_open" value="1"{if $result['is_open'] == 1} checked="checked"{/if}> 是</label>
					<label class="radio-inline"><input type="radio" name="is_open" value="0"{if $result['is_open'] == 0} checked="checked"{/if}> 否</label>

					<p class="form-control-static text-warning">未启用的分类在添加内容时不能选择且不在企业号前台进行展示！</p>
				</div>

			</div>

			<div class="form-group" id="btn-box">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-primary">保存</button>
					<button type="button" onclick="javascript:history.go(-1);" class="btn btn-default col-sm-offset-1">返回</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
/* 选人组件 */
var dep_arr = [];
var cd_id_choose = '';
var m_uid = [];
var m_uid_choose = '';

var p_m_uids = '{$parent.m_uids}';
var p_cd_ids = '{$parent.cd_ids}';
var p_is_all = '{$parent.is_all}';

{if !empty($result.cd_ids)}
dep_arr = {$result.departments};
if (dep_arr.length != 0) {
	cd_id_choose = '';
	var select_dep_name = '';
	for (var i = 0; i < dep_arr.length; i ++) {
		cd_id_choose += '<input name="cd_ids[]" value="' + dep_arr[i]['id'] + '" type="hidden">';
		select_dep_name += dep_arr[i]['cd_name'] + ' ';
	}
	$('#cd_id_choose').html(cd_id_choose);
	if (select_dep_name != '') {
		$('#dep_deafult_data').html(select_dep_name).show();
	} else {
		$('#dep_deafult_data').hide();
	}
}
{/if}

{if !empty($result.m_uids)}
m_uid = {$result.members};
if (m_uid.length != 0) {
	m_uid_choose = '';
	var select_m_uid_name = '';
	for (var i = 0; i < m_uid.length; i ++) {
		m_uid_choose += '<input name="m_uids[]" value="' + m_uid[i]['m_uid'] + '" type="hidden">';
		select_m_uid_name += m_uid[i]['m_username'] + ' ';
	}
	$('#m_uid_choose').html(m_uid_choose);
	if (select_m_uid_name != '') {
		$('#m_uid_deafult_data').html(select_m_uid_name).show();
	} else {
		$('#m_uid_deafult_data').hide();
	}
}
{/if}

// 选择部门回调
function selectedDepartmentCallBack(data){
	dep_arr = data;

	// 页面埋入 选择的值
	cd_id_choose = '';
	var select_dep_name = '';
	for (var i = 0; i < data.length; i ++) {
		cd_id_choose += '<input name="cd_ids[]" value="' + data[i]['id'] + '" type="hidden">';
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

// 可发送人员选择回调
function selectedMuidCallBack(data, id) {
	m_uid = data;
	// 页面埋入 选择的值
	m_uid_choose = '';
	var select_m_uid_name = '';
	for (var i = 0; i < data.length; i ++) {
		m_uid_choose += '<input name="m_uids[]" value="' + data[i]['m_uid'] + '" type="hidden">';
		select_m_uid_name += data[i]['m_username'] + ' ';
	}
	$('#m_uid_choose').html(m_uid_choose);
	// 展示
	if (select_m_uid_name != '') {
		$('#m_uid_deafult_data').html(select_m_uid_name).show();
	} else {
		$('#m_uid_deafult_data').hide();
	}
}

$(function(){
	//当阅读对象为指定对象时
	$('#specified_btn').bind('click', function () {
		$('#user_dep_container').show();
		$(this).addClass('btn-primary');
		$('#all_btn').removeClass('btn-primary');
		$('#is_all').val(0);
	});
	//当阅读对象为全公司时
	$('#all_btn').bind('click', function () {
		$('#user_dep_container').hide();
		$(this).addClass('btn-primary');
		$('#specified_btn').removeClass('btn-primary');
		$('#is_all').val(1);
	});

});

function checkForm(){
	if( parseInt($("#is_all").val()) == 0 && $("input[name='cd_ids[]']").val() == undefined && $("input[name='m_uids[]']").val() == undefined ) {
		alert('请选择部门或者人员');
		return false;
	}

	{if $parent && $parent['is_all'] != 1}
	// 检查子分类范围
	var p_m_arr = p_m_uids.split(',');
	var p_cd_arr = p_cd_ids.split(',');
	var ret = true;
	if(parseInt($("#is_all").val()) != parseInt(p_is_all)) {
		alert('不得大于父级分类的范围');
		return false;
	}
	$("input[name='cd_ids[]']").each(function(index) {
		if(!in_array(p_cd_arr, $(this).val())) {
			ret = false;
			return ret;
		}
	});
	if(ret == false){
		alert('部门不得大于父级分类的范围');
		return false;
	}
	$("input[name='m_uids[]']").each(function(index) {
		if(!in_array(p_m_arr, $(this).val())) {
			ret = false;
			return ret;
		}
	});
	if(ret == false){
		alert('人员不得大于父级分类的范围');
		return false;
	}
	{/if}

	return true;
}

</script>
{include file="$tpl_dir_base/footer.tpl"}