{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" action="{$form_action_url}" id="add-form" method="post" data-ng-app="ng.poler.plugins.pc">
			<input type="hidden" name="formhash" value="{$formhash}" />

			<span id="m_uid_choose" style="display: none;"></span>
			<span id="cd_id_choose" style="display: none;"></span>

				<div class="form-group">
					<label class="control-label col-sm-2 text-danger" for="id_title">目录标题*</label>
					<div class="col-sm-9">
						<input type="text" class="form-control form-small" id="id_title" name="title" placeholder="最多输入15个汉字" value="{$category['title']|escape}" maxlength="15"  required="required"/>			
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2" for="id_lable_rights">可见范围</label>
					<div class=" col-sm-9"  id="contact_container">
						<!-- angular 选人组件 begin -->
						<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
							<a class="btn btn-defaul" data-ng-click="selectPerson('m_uid','selectedMuidCallBack')">选择人员</a>
						</div>
						<!-- angular 选人组件 end -->

						<pre id="m_uid_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

						<br />

						<!-- angular 选人组件 begin -->
						<div class="angularjs-area " data-ng-controller="ChooseShimCtrl">
							<a class="btn btn-defaul" data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选择部门</a>
						</div>
						<!-- angular 选人组件 end -->

						<pre id="dep_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

					</div>
					<span class="space"></span>					
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-9">
						<button type="submit" class="btn btn-primary">{if $tc_id}编辑{else}添加{/if}</button>
						&nbsp;&nbsp;
						<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
					</div>
				</div>
		</form>
	</div>
</div>

<script type="text/javascript">
window._app = "contacts_pc";
window._root = '{$FM_JSFRAMEWORK}';
</script>
<script type="text/javascript" src="{$FM_JSFRAMEWORK}lib/requirejs/require.js"></script>
<script type="text/javascript" src="{$FM_JSFRAMEWORK}config.js"></script>
<script type="text/javascript">

	/* 选人组件 */
	var m_uid = [];
	var dep_arr = [];
	var m_uid_choose = '';
	var cd_id_choose = '';

	/* 选人组件默认值 */
	// 部门默认值
	dep_arr = {$cd_ids};
	if (dep_arr.length != 0) {
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < dep_arr.length; i ++) {
			cd_id_choose += '<input name="deps[]" value="' + dep_arr[i]['id'] + '" type="hidden">';
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
	// 可审批人默认值
	m_uid = {$m_uids};
	if (m_uid.length != 0) {
		m_uid_choose = '';
		var select_uid_name = '';
		for (var i = 0; i < m_uid.length; i ++) {
			m_uid_choose += '<input name="contacts[]" value="' + m_uid[i]['m_uid'] + '" type="hidden">';
			select_uid_name += m_uid[i]['m_username'] + ' ';
		}
		$('#m_uid_choose').html(m_uid_choose);

		// 展示
		if (select_uid_name != '') {
			$('#m_uid_deafult_data').html(select_uid_name).show();
		} else {
			$('#m_uid_deafult_data').hide();
		}
	}

	// 可发送人员选择回调
	function selectedMuidCallBack(data, id) {
		m_uid = data;

		// 页面埋入 选择的值
		m_uid_choose = '';
		var select_uid_name = '';
		for (var i = 0; i < data.length; i ++) {
			m_uid_choose += '<input name="contacts[]" value="' + data[i]['m_uid'] + '" type="hidden">';
			select_uid_name += data[i]['m_username'] + ' ';
		}
		$('#m_uid_choose').html(m_uid_choose);

		// 展示
		if (select_uid_name != '') {
			$('#m_uid_deafult_data').html(select_uid_name).show();
		} else {
			$('#m_uid_deafult_data').hide();
		}
	}

	// 选择部门回调
	function selectedDepartmentCallBack(data){
		dep_arr = data;

		// 页面埋入 选择的值
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < data.length; i ++) {
			cd_id_choose += '<input name="deps[]" value="' + data[i]['id'] + '" type="hidden">';
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

requirejs(["jquery", "views/contacts"], function( $, contacts) {
	{*$(function () {*}
		{*var view = new contacts();*}
		{*view.input_type = 'checkbox';*}
		{*view.render({*}
			{*"container": "#contact_container",*}
			{*"contacts_default_data": {$existed_rights}*}
		{*});*}
	{*});*}
});
</script>

{include file="$tpl_dir_base/footer.tpl"}
