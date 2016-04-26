{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-body">
		{if $categories}
			<form class="form-horizontal font12" role="form" action="{$form_action_url}" method="post" data-ng-app="ng.poler.plugins.pc">
				<input type="hidden" name="formhash" value="{$formhash}"/>

				<span id="m_uid_choose" style="display: none;"></span>
				<span id="cd_id_choose" style="display: none;"></span>

				{if $ta_id}
					<div class="form-group">
						<label class="control-label   col-sm-2 " for="id_title">创建时间</label>

						<div class="col-sm-9">
							<label class="control-label form-small" for="id_title">{$article['created']|escape}</label>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label   col-sm-2 " for="id_title">更新时间</label>

						<div class="col-sm-9">
							<label class="control-label form-small" for="id_title">{$article['updated']|escape}</label>
						</div>
					</div>
				{/if}
				<div class="form-group">
					<label class="control-label   col-sm-2 text-danger" for="id_label_tc_id">目录*</label>
					<span class="space"></span>

					<div class="col-sm-9">
						<select id="id_tc_id" name="tc_id" class="form-control form-small" data-width="auto"
						        required="required">
							<option value="" selected="selected">请选择目录</option>
							{foreach $categories as $_key => $_val}
								<option value="{$_key}"{if $article['tc_id'] == $_key} selected="selected"{/if}>{$_val['title']}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label   col-sm-2 text-danger" for="id_title">标题*</label>

					<div class="col-sm-9">
						<input type="text" class="form-control form-small" id="id_title" name="title"
						       placeholder="最多输入17个汉字" value="{$article['title']|escape}" maxlength="17"
						       required="required"/>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label  col-sm-2 text-danger" for="id_author">作者*</label>

					<div class="col-sm-9">
						<input type="text" class="form-control form-small" id="id_author" name="author"
						       placeholder="最多输入15个汉字" value="{$article['author']|escape}" maxlength="15"
						       required="required"/>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2" for="id_rights">可见范围</label>

					<div class="col-sm-9" id="contact_container">

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
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2" for="id_content">文章内容</label>

					<div class="col-sm-9">
						{$ueditor_output}
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-6">
						<button type="submit" class="btn btn-primary">{if $ta_id}编辑{else}添加{/if}</button>
						&nbsp;&nbsp;
						<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
					</div>
					<div class="col-sm-3">
						<input type="checkbox" class=" form-small" id="id_send" name="send" value="1"/>&nbsp;&nbsp;发送提醒消息
					</div>
				</div>
			</form>
		{else}
			尚未创建文章目录，请先
			<a href='{$addCategoryUrl}'>添加目录</a>
		{/if}
	</div>
</div>

<script type="text/javascript">
	window._app = "contacts_pc";
	window._root = '{$FM_JSFRAMEWORK}';

	/**
	 * 加载选人组件
	 * @param defaults 默认选择的人员、部门
	 * @param range_limit_data 限制选择的人员和部门范围
	 */
	function _load_user_selector(defaults, range_limit_data) {
		requirejs(["jquery", "views/contacts"], function ($, contacts) {
			$(function () {
				var view = new contacts();
				view.input_type = 'checkbox';
				view.range_limit_contacts_data = range_limit_data;
				view.render({
					"container": "#contact_container",
					"contacts_default_data": defaults
				});
			});
		});
	}

	/**
	 * 获取缓存
	 * @param tc_id 目录ID
	 */
	function _get_cache(tc_id) {
		var cache = $('body').data('tc_id_' + tc_id);
		return cache;
	}

	var categories_is_all = {$categories_is_all};
	var raw_tc_id = {$tc_id}; //最初的目录ID
	var range_limit_data = [];
	{if $ta_id}
	range_limit_data = {$existed_range_limit_json};
	{/if}
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
		for (var i = 0; i < dep_arr.length; i++) {
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
		for (var i = 0; i < m_uid.length; i++) {
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
		for (var i = 0; i < data.length; i++) {
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
	function selectedDepartmentCallBack(data) {
		dep_arr = data;

		// 页面埋入 选择的值
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < data.length; i++) {
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

	jQuery(function () {

		{*_load_user_selector({$existed_rights}, range_limit_data);*}

		{*$('#id_tc_id').on('change', function () {*}
			{*var tc_id = $(this).val();*}
			{*if (!tc_id) {*}
				{*$('#contact_container').html('');*}
				{*return;*}
			{*}*}
			{*var cache = _get_cache(tc_id);*}
			{*if (cache != undefined) *}{*}*}
				{*if (tc_id == raw_tc_id) *}{*}*}
					{*//如果选中的是原目录，则显示文章原有权限*}
					{*_load_user_selector({$existed_rights}, cache);*}
				{*} else *}{*}*}
					{*_load_user_selector(null, cache);*}
				{*}*}
				{*return;*}
			{*}*}

			{*$.ajax({*}
				{*"url": "/api/showroom/get/categoryright",*}
				{*"dataType": "json",*}
				{*"type": "POST",*}
				{*"data": {*}
					{*"tc_id": tc_id*}
				{*},*}
				{*"success": function (data) {*}
					{*if (data.errcode > 0) {*}
						{*alert(data.errmsg);*}
					{*} else {*}
						{*$('body').data('tc_id_' + tc_id, data.result);*}
						{*if (tc_id == raw_tc_id) *}{*}*}
							{*//如果选中的是原目录，则显示文章原有权限*}
							{*_load_user_selector({$existed_rights}, data.result);*}
						{*} else *}{*}*}
							{*_load_user_selector(null, data.result);*}
						{*}*}
					{*}*}
				{*},*}
				{*"error": function () {*}
					{*alert('网络连接错误，请稍候再试');*}
					{*return false;*}
				{*}*}
			{*});*}
		{*});*}
	});
</script>

{include file="$tpl_dir_base/footer.tpl"}
