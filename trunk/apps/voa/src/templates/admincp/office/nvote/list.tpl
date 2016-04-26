{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索投票</strong></div>
	<div class="panel-body" style="padding-bottom: 0">
		<form class="form-inline vcy-from-search" role="form" action="{$search_action_url}" data-ng-app="ng.poler.plugins.pc">
			<input type="hidden" name="issearch" value="1"/>

			<span id="m_uid_choose" style="display: none;"></span>

			<div class="form-row m-b-20">
				<div class="form-group">
					<script>
						init.push(function () {
							var options2 = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range1').datepicker(options2);
						});
					</script>
					<div class="input-daterange input-group"
					     style="width: 290px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
						<label class="vcy-label-none" for="id_begin_date">日期：</label>

						<div class="input-daterange input-group"
						     style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							<input type="text" class="input-sm form-control" id="id_begin_date" name="start_date"
							       placeholder="开始日期" value="{$search_conds['start_date']|escape}"/>
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="id_end_date" name="end_date"
							       placeholder="结束日期" value="{$search_conds['end_date']|escape}"/>
						</div>
					</div>
					<label class="vcy-label-none" for="id_vo_status">状态：</label>
					<select name="vote_status" id="id_vo_status" class="form-control form-small" data-width="auto">
						<option value="">全部</option>
						<option value="1" {if $search_conds['vote_status'] == 1}selected="selected" {/if}>进行中的投票调研
						</option>
						<option value="2" {if $search_conds['vote_status'] == 2}selected="selected" {/if}>已完成的投票调研
						</option>
					</select>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_show_name">投票方式：</label>
					<select name="show_name" id="id_show_name" class="form-control form-small" data-width="auto">
						<option value="">全部</option>
						<option value="2"
						        {if $search_conds['show_name'] == voa_d_oa_nvote::SHOW_NAME_NO}selected="selected" {/if}>
							匿名投票
						</option>
						<option value="1"
						        {if $search_conds['show_name'] == voa_d_oa_nvote::SHOW_NAME_YES}selected="selected" {/if}>
							实名投票
						</option>
					</select>

				</div>

			</div>
			<div class="form-row  m-b-20">
				<div class="form-group">
					<label class="vcy-label-none" for="id_subject">关键字：</label>
					<input type="text" class="form-control form-small" id="id_subject" name="subject"
					       value="{$search_conds['subject']}" maxlength="54"/>
					<span class="space"></span>
					<label class="vcy-label-none">发起人：</label>
				</div>
				<div class="form-group">
					<div id="contact_container">

						<!-- angular 选人组件 begin -->
						<div class="angularjs-area" data-ng-controller="ChooseShimCtrl">
							<a class="btn btn-defaul"
							   data-ng-click="selectPerson('submit_uids','selectedMuidCallBack')">选择人员</a>
						</div>
						<!-- angular 选人组件 end -->

						<pre id="m_uid_deafult_data" style="width: 100px; height: 100px; margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

						{*{include*}
						{*file="$tpl_dir_base/common_selector_member.tpl"*}
						{*input_type='checkbox'*}
						{*input_name='submit_uids[]'*}
						{*selector_box_id='contact_container'*}
						{*default_data={$submit_usernames}*}
						{*allow_member=true*}
						{*allow_department=false*}
						{*}*}
					</div>
				</div>

				<label class="vcy-label-none" for="is_admin">仅查询管理员发起：<input type="checkbox" id="is_admin"
				                                                             name="is_admin"
				                                                             {if $search_conds['is_admin'] == 1}checked="checked" {/if}
				                                                             value="1"/> </label>
				<span class="space"></span>
				<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i
							class="fa fa-search"></i> 搜索
				</button>
				<span class="space"></span>
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
	<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
		<input type="hidden" name="formhash" value="{$formhash}"/>
		<table class="table table-striped table-bordered table-hover font12">
			<colgroup>
				<col class="t-col-5"/>
				<col/>
				<col class="t-col-12"/>
				<col class="t-col-12"/>
				<col class="t-col-10"/>
				<col class="t-col-10"/>
				<col class="t-col-12"/>
				<col class="t-col-8"/>
			</colgroup>
			<thead>
			<tr>
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px"
				                                                     onchange="javascript:checkAll(this,'delete');"{if !$form_delete_url || !$total} disabled="disabled"{/if} /><span
								class="lbl">全选</span></label></th>
				<th class="text-left">标题</th>
				<th>开始时间</th>
				<th>结束时间</th>
				<th>状态</th>
				<th>发起人</th>
				<th>投票方式</th>
				<th>操作</th>
			</tr>
			</thead>
			{if $total > 0}
				<tfoot>
				<tr>
					<td colspan="2" class="text-left">{if $form_delete_url}
							<button type="submit" class="btn btn-danger">批量删除</button>
						{/if}</td>
					<td colspan="6" class="text-right vcy-page">{$multi}</td>
				</tr>
				</tfoot>
			{/if}
			<tbody>
			{foreach $list as $_id=>$_data}
				<tr>
					<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[]" class="px"
					                                                      value="{$_data['id']}"{if !$form_delete_url} disabled="disabled"{/if} /><span
									class="lbl"> </span></label></td>
					<td class="text-left">{$_data['subject']|escape}</td>
					<td>{$_data['_start_time']}</td>
					<td>{$_data['_end_time']}</td>
					<td>{$_data['_status']|escape}</td>
					<td>{$usernames[$_data['submit_id']]|escape}</td>
					<td>{$_data['_is_show_name']|escape}</td>
					<td>{$base->linkShow($view_url, $_data['id'], '详情', 'fa-eye', '')}</td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="8" class="warning">{if $search_conds}未搜索到指定条件的投票调研{else}暂无任何投票调研{/if}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</form>
</div>

<script type="text/javascript">

	var submit_uids = [];
	var m_uid_choose = '';

	// 默认值
	submit_uids = {$search_conds['submit_uids']};
	if (submit_uids.length != 0) {
		m_uid_choose = '';
		var select_uid_name = '';
		for (var i = 0; i < submit_uids.length; i ++) {
			m_uid_choose += '<input name="submit_uids[]" value="' + submit_uids[i]['m_uid'] + '" type="hidden">';
			select_uid_name += submit_uids[i]['m_username'] + ' ';
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

		submit_uids = data;

		// 页面埋入 选择的值
		m_uid_choose = '';
		var select_uid_name = '';
		for (var i = 0; i < data.length; i ++) {
			m_uid_choose += '<input name="submit_uids[]" value="' + data[i]['m_uid'] + '" type="hidden">';
			select_uid_name += submit_uids[i]['m_username'] + ' ';
		}
		$('#m_uid_choose').html(m_uid_choose);

		// 展示
		if (select_uid_name != '') {
			$('#m_uid_deafult_data').html(select_uid_name).show();
		} else {
			$('#m_uid_deafult_data').hide();
		}
	}

</script>

{include file="$tpl_dir_base/footer.tpl"}