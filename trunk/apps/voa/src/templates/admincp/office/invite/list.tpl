{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<script>
						init.push(function () {
							var options = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range1').datepicker(options);

							$('.one-delete').on('click',function(e){
								e.preventDefault();
								if(confirm("你确定要删除么？")){
									window.location.href = e.currentTarget.href;
								};
							});
						});
					</script>
					<div class="input-daterange input-group" style="width: 290px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
						<label class="vcy-label-none" for="id_created">邀请时间：</label>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							<input type="text" class="input-sm form-control" id="id_invite_begintime" name="invite_begintime"   placeholder="开始日期" value="{$search_conds['invite_begintime']|escape}" />
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="id_invite_endtime" name="invite_endtime" placeholder="结束日期" value="{$search_conds['invite_endtime']|escape}" />
						</div>
					</div>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_name">　姓名：</label>
					<input type="text" class="form-control form-small" style="width: 100px;" id="id_name" name="name" placeholder="输入姓名" value="{$search_conds['name']|escape}" maxlength="30" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_email"> 邮箱：</label>
					<input type="text" class="form-control form-small" id="id_email" name="email" placeholder="输入邮箱" value="{$search_conds['email']|escape}" />

					<span class="space"></span>
					<label class="vcy-label-none" for="id_nt_subject"> 邀请人：</label>

					<div class ="form-group">
						<select id="primary_id" name="m_uids" class="form-control form-small" data-width="auto">
							<option value="">不限</option>
							{foreach $primary_id_list as $k => $v}
								<option value="{$v['id']}" {if $search_m_uid['id'] == $v['id']}selected="selected"{/if}>{$v['name']}</option>
							{/foreach}
						</select>
					</div >
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_af_status">邀请方式：</label>
					<!--{$approval_state|var_dump}-->
					<select id="id_approval_state" name="approval_state" class="form-control form-small" data-width="auto">
						{foreach $approval_state as $_k=>$_n}
				<option value="{$_k}"{if $search_conds['approval_state']==$_k} selected="selected"{/if}>{$_n}</option>
						{/foreach}
					</select>
					
					<span class="space"></span>
					
					
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a href="{$list_url}" role="button" class="btn btn-default form-small form-small-btn">全部记录</a>
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

<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}?delete">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-8 "/>
		<col class="t-col-11" />
		<col class="t-col-18" />
		<col class="t-col-10" />
		<col class="t-col-10" />
		<col class="t-col-16" />
		<col class="t-col-17" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$form_delete_url || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>姓名</th>
			<th>手机</th>
			<th>邮箱</th>
			<th>邀请方式</th>
			<th>邀请人</th>
			<th>邀请时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">
				{if $form_delete_url}<button type="submit" class="btn btn-danger">批量删除</button>{/if}
			</td>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{if $list}
	{foreach $list as $_id => $_data}
		<tr>			
			<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_id}]" value="{$_id}"{if !$form_delete_url} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td style="word-wrap: break-word;word-break: break-all"><a href="{$view_url}{$_id}">{$_data['name']|escape}</a></td>
			<td>{$_data['phone']}</td>
			<td>{$_data['email']}</td>
			<td>{$_data['is_approval_state']}</td>
			<td>{$_data['invite_uid']}</td>
			<td>{$_data['created']|escape}</td>
			<td>
				{$base->linkShow($view_url, $_id, '查看详情', 'fa-view', '')}&nbsp;
				{$base->linkShow($delete_url, $_id, '删除', 'fa-times', 'class="text-danger  one-delete"')} 
				
			</td>
		</tr>
	{/foreach}
{else}
		<tr>
			<td colspan="8" class="warning">{if $issearch}未搜索到指定条件的邀请数据{else}暂无任何邀请数据{/if}</td>
		</tr>
{/if}
	</tbody>
</table>
</form>
</div>

{include file="$tpl_dir_base/footer.tpl"}
