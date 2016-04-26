{include file="$tpl_dir_base/header.tpl"}

<form class="form-horizontal font12" role="form" method="post"
      action="{$formActionUrl}" data-ng-app="ng.poler.plugins.pc">
	<input type="hidden" name="formhash" value="{$formhash}"/>

	<span id="m_uid_choose" style="display: none;"></span>
	<span id="cd_id_choose" style="display: none;"></span>

	<div class="panel panel-warning">
		<div class="panel-heading">
			<strong>快递助手</strong>
		</div>
		<div class="panel-body">
			<div class="form-group font12">
				<label for="upload_image_min_count"
				       class="col-sm-3 control-label text-right">设置快递接收人/部门</label>

				<div class="pull-left" id="contact_container">

					<!-- angular 选人组件 begin -->
					<div class="angularjs-area " data-ng-controller="ChooseShimCtrl">
						<a class="btn btn-defaul" data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选择部门</a>
					</div>
					<!-- angular 选人组件 end -->

					<pre id="dep_deafult_data" style="width: 500px; height: 50px;margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

					<br />

					<!-- angular 选人组件 begin -->
					<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
						<a class="btn btn-defaul" data-ng-click="selectPerson('m_uid','selectedMuidCallBack')">选择人员</a>
					</div>
					<!-- angular 选人组件 end -->

					<pre id="m_uid_deafult_data" style="width: 500px; height: 50px;margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

					{*{include*}
					{*file="$tpl_dir_base/common_selector_member.tpl"*}
					{*input_type='checkbox'*}
					{*input_name='m_uids[]'*}
					{*selector_box_id='contact_container'*}
					{*input_name_department='cd_ids[]'*}
					{*allow_member=1*}
					{*allow_department=1*}
					{*default_data=$default_data*}
					{*}*}
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-3 col-sm-9">
					<button type="submit" class="btn btn-primary">保存</button>
					&nbsp;&nbsp; <a href="javascript:history.go(-1);" role="button"
					                class="btn btn-default">返回</a>
				</div>
			</div>
		</div>
	</div>
</form>

<script type="text/javascript">

	/* 选人组件 */
	var m_uid = [];
	var dep_arr = [];
	var m_uid_choose = '';
	var cd_id_choose = '';

	/* 选人组件默认值 */
	// 部门默认值
	dep_arr = {$default_data_cdids};
	if (dep_arr.length != 0) {
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < dep_arr.length; i ++) {
			cd_id_choose += '<input name="cd_ids[]" value="' + dep_arr[i]['id'] + '" type="hidden">';
			select_dep_name += dep_arr[i]['name'] + ' ';
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
	m_uid = {$default_data_uids};
	if (m_uid.length != 0) {
		m_uid_choose = '';
		var select_uid_name = '';
		for (var i = 0; i < m_uid.length; i ++) {
			m_uid_choose += '<input name="m_uids[]" value="' + m_uid[i]['m_uid'] + '" type="hidden">';
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
			m_uid_choose += '<input name="m_uids[]" value="' + data[i]['m_uid'] + '" type="hidden">';
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

</script>

{include file="$tpl_dir_base/footer.tpl"}
