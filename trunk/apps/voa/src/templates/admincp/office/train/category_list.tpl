{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索目录</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}" data-ng-app="ng.poler.plugins.pc">
			<input type="hidden" name="issearch" value="1"/>

			<span id="m_uid_choose" style="display: none;"></span>
			<span id="cd_id_choose" style="display: none;"></span>

			<div class="form-row m-b-20">
				<div class="form-group">
					<label class="vcy-label-none" for="id_ao_username">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;目录标题：</label>
					<input type="text" class="form-control form-small" id="id_title" name="title"
					       value="{$searchBy['title']|escape}" maxlength="54"/>
					<span class="space"></span>


					<span class="space"></span>


					<label class="vcy-label-none" for="id_ao_rights">可见范围：</label>

					<div class="input-daterange input-group "
					     style="min-width: 290px;display: inline-table;vertical-align:middle;" id="contact_container">

						<!-- angular 选人组件 begin -->
						<div class="angularjs-area " data-ng-controller="ChooseShimCtrl">
							<a class="btn btn-defaul"
							   data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选择部门</a>
						</div>
						<!-- angular 选人组件 end -->

						<pre id="dep_deafult_data" style="width: 250px; height: 50px;margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

						<br />

						<!-- angular 选人组件 begin -->
						<div class="angularjs-area" data-ng-controller="ChooseShimCtrl">
							<a class="btn btn-defaul" data-ng-click="selectPerson('m_uid','selectedMuidCallBack')">选择人员</a>
						</div>
						<!-- angular 选人组件 end -->

						<pre id="m_uid_deafult_data" style="width: 250px; height: 50px;margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

					</div>

					<span class="space"></span>

					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i
								class="fa fa-search"></i> 搜索
					</button>
					<span class="space"></span>
					<a class="btn btn-default form-small form-small-btn" role="button" href="{$listAllUrl}">全部记录</a>
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
	<form class="form-horizontal" role="form" method="post" action="{$formDeleteUrl}">
		<input type="hidden" name="formhash" value="{$formhash}"/>
		<table class="table table-striped table-bordered table-hover font12">
			<colgroup>
				<col class="t-col-5"/>
				<col/>
				<col class="t-col-10"/>
				<col class="t-col-20"/>
				<col class="t-col-20"/>
				<col class="t-col-15"/>
			</colgroup>
			<thead>
			<tr>
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px"
				                                                     onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} /><span
								class="lbl">全选</span></label></th>
				<th class="text-left">目录标题</th>
				<th>文章数量</th>
				<th>创建时间</th>
				<th>更新时间</th>
				<th>操作</th>
			</tr>
			</thead>
			{if $total > 0}
				<tfoot>
				<tr>
					<td colspan="2" class="text-left">{if $deleteUrlBase}
							<button type="submit" class="btn btn-danger">批量删除</button>
						{/if}</td>
					<td colspan="4" class="text-right vcy-page">{$multi}</td>
				</tr>
				</tfoot>
			{/if}
			<tbody>
			{foreach $list as $_id=>$_data}
				<tr>
					<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]"
					                                                      class="px"
					                                                      value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span
									class="lbl"> </span></label></td>
					<td class="text-left">{$_data['title']|escape}</td>
					<td>{$_data['article_num']}</td>
					<td>{$_data['created']|escape}</td>
					<td>{$_data['updated']|escape}</td>
					<td>
						{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} |
						{$base->linkShow($editUrlBase, $_id, '编辑', 'fa-edit')}
					</td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="6" class="warning">{if $issearch}未搜索到指定条件的目录{else}暂无任何目录{/if}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</form>
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
	dep_arr = {$deps};
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
	m_uid = {$contacts};
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

	{*requirejs(["jquery", "views/contacts"], function( $, contacts) {*}
	{*$(function () {*}
	{*var view = new contacts();*}
	{*view.input_type = 'checkbox';*}
	{*view.render({*}
	{*"container": "#contact_container",*}
	{*"contacts_default_data": {$existed_rights}*}
	{*});*}
	{*});*}
	{*});*}
</script>
{include file="$tpl_dir_base/footer.tpl"}

