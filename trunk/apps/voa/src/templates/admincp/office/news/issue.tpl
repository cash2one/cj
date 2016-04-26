{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<form class="form-horizontal font12" role="form" action="{$form_action_url}" method="post" data-ng-app="ng.poler.plugins.pc">
				<input type="hidden" name="formhash" value="{$formhash}" />
				<input type="hidden" name="action" value="{$action}" />

				<span id="m_uid_choose" style="display: none;"></span>
				<span id="cd_id_choose" style="display: none;"></span>

				<div id="user_dep_container">
					<hr>
					<div class="row">
						<label class="col-sm-2 text-right padding-sm">微信端可发起公告人员：</label>
						<div id="deps_container" class="col-sm-8">

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

							<br />

							{*{include *}
							{*file="$tpl_dir_base/common_selector_member.tpl"*}
							{*input_type='checkbox'*}
							{*input_name='m_uids[]'*}
							{*input_name_department='cd_ids[]'*}
							{*selector_box_id='deps_container'*}
							{*allow_member=true*}
							{*allow_department=true*}
							{*detault_data=$default_data*}
							{*}*}
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-9">
						<div class="row">
							<div class="col-md-4">
								<button type="submit" class="btn btn-primary  col-md-9" id="draft_btn">添加</button>
							</div>
							<div class="col-md-4">
								<button type="button" class="btn  col-md-9" id="publish_btn">返回</button>
							</div>
						</div>
					</div>
			</form>
		</div>
	</div>
</div>
</div>

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
			cd_id_choose += '<input name="cd_ids[]" value="' + dep_arr[i]['id'] + '" type="hidden">';
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
	var select_uid_name = '';
	if (m_uid.length != 0) {
		m_uid_choose = '';
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

	$(function(){
		$('#publish_btn').on('click', function() {
			window.location.href = "{$url}";
		})
	})
</script>

{include file="$tpl_dir_base/footer.tpl"}