{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索试卷</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_nt_subject">　试卷名称：</label>
					<input type="text" class="form-control form-small" id="id_name" name="name" placeholder="输入关键词" value="{$search_conds['name']|escape}" maxlength="30" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_nt_subject">　试卷状态：</label>
					<select name="status" class="form-control form-small">
						<option value="-1"{if $search_conds['status'] === '-1'} selected{/if}>请选择</option>
						<option value="2"{if $search_conds['status'] === '2'} selected{/if}>已开始</option>
						<option value="3"{if $search_conds['status'] === '3'} selected{/if}>已结束</option>
						<option value="4"{if $search_conds['status'] === '4'} selected{/if}>已终止</option>
					</select>
					<span class="space"></span>
					<div class="input-daterange input-group" style="width: 290px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
						<label class="vcy-label-none" for="id_created">考试时间：</label>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							<input type="text" class="input-sm form-control" id="id_begin_time" name="begin_time" placeholder="开始日期" value="{$search_conds['begin_time']|escape}" autocomplete="off" />
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="id_end_time" name="end_time" placeholder="结束日期" value="{$search_conds['end_time']|escape}" autocomplete="off" />
						</div>
					</div>
					<script>
						init.push(function () {
							var options = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range1').datepicker(options);

						});
					</script>
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a href="{$tjlist_url}" role="button" class="btn btn-default form-small form-small-btn">全部试卷</a>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			试卷列表
		</div>
	</div>

<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}?delete">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col class="t-col-26 "/>
		<col class="t-col-10" />
		<col class="t-col-20" />
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-19" />
	</colgroup>
	<thead>
		<tr>
			<th>试卷名称</th>
			<th>考试时间</th>
			<th>考试范围</th>
			<th>参与人数</th>
			<th>试卷状态</th>
			<th>操作</th>
		</tr>
	</thead>
	<tbody>
{if $list}
	{foreach $list as $_id => $_data}
		<tr>
			<td>{$_data['name']|escape}</td>
			<td>
				{if !empty($_data['begin_time'])}
					{rgmdate($_data['begin_time'], 'Y/m/d H:i')} -
					{rgmdate($_data['end_time'], 'Y/m/d H:i')}
				{else}
				-
				{/if}</td>
			<td>
				{if $_data['departments']}<p>{$_data['departments']}</p>{/if}
				{if $_data['members']}<p>{$_data['members']}</p>{/if}
			</td>
			<td>
				<p>未参与{$_data['tj']['not_join']}</p>
				<p>已参与{$_data['tj']['join']}</p>
			</td>
			<td>{$_data['status_show']}</td>
			<td>
				{$base->linkShow($tjdetail_url, $_id, '查看统计', 'fa-edit', '')}
			</td>
		</tr>
	{/foreach}
	</tbody>
	<tfoot>
		<tr>
			<td colspan="10" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{else}
	<tfoot>
		<tr>
			<td colspan="10" class="warning">{if $issearch}未搜索到指定条件的试卷数据{else}暂无任何试卷数据{/if}</td>
		</tr>
	</tfoot>
{/if}
	
</table>
</form>
</div>
{include file="$tpl_dir_base/footer.tpl"}
