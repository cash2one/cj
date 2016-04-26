{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索{$module_plugin['cp_name']|escape}</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="is_search" value="1" />
			<div class="form-row">
				<div class="form-group">
					
					<label class="vcy-label-none" for="id_stp_subject">标题关键词：</label>
					<input type="text" class="form-control form-small" id="id_stp_subject" name="stp_subject" placeholder="标题关键词" value="{$search_by['stp_subject']|escape}" maxlength="30" />
					<span class="space"></span>
					
					<label class="vcy-label-none" for="id_stp_message">内容关键词：</label>
					<input type="text" class="form-control form-small" id="id_stp_message" name="stp_message" placeholder="内容关键词" value="{$search_by['stp_message']|escape}" maxlength="30" />
					
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
				
					<label class="vcy-label-none" for="id_after">发布时间：</label>
					<input type="date" class="form-control form-small" id="id_after" name="after" value="{$search_by['after']|escape}" />
					<label class="vcy-label-none" for="id_before"> 至 </label>
					<input type="date" class="form-control form-small" id="id_before" name="before" value="{$search_by['before']|escape}" />
					<span class="space"></span>
					
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a href="{$list_url}" role="button" class="btn btn-default form-small form-small-btn">全部主题</a>
				</div>
			</div>
		</form>
	</div>
</div>

<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-20" />
		<col />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'mixed');"{if !$form_delete_url || !$total} disabled="disabled"{/if} />删除</label></th>
			<th>发布时间</th>
			<th>内容</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $form_delete_url}<button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-trash-o"></i> 批量删除所选</button>{/if}</td>
			<td colspan="2" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id => $_data}
		<tr>
			<td class="text-left">
	{if $_data['stp_first'] == $is_thread}
				<input type="checkbox" name="mixed[st_ids][{$_data['st_id']}]" value="{$_data['st_id']}"{if !$form_delete_url} disabled="disabled"{/if} />
	{else}
				<input type="checkbox" name="mixed[stp_ids][{$_id}]" value="{$_id}"{if !$form_delete_url} disabled="disabled"{/if} />
	{/if}
			</td>
			<td><span class="help-block">{$_data['_created']}</span><span class="help-block">{$_data['_first']}</span></td>
			<td>
				{if !empty($module_plugin_set['is_secret'])}
				<span class="label label-default">{$_data['m_username']|escape}</span><span class="space"></span>
				{/if}
				{if $_data['stp_subject']}<strong>{$_data['stp_subject']}</strong>{/if}
				{if $_data['_message']}<div>{$_data['_message']}</div>{/if}
			</td>
			<td>
	{if $_data['stp_first'] == $is_thread}
				{$base->linkShow($thread_delete_url_base, $_data['st_id'], '删除主题', 'fa-trash-o', 'class="_delete"')}
				<br />
				{$base->linkShow($view_url_base, $_data['st_id'], '浏览主题', 'fa-eye', '')}
	{else}
				{$base->linkShow($reply_delete_url_base, $_id, '删除回复', 'fa-trash-o', 'class="_delete"')}
				<br />
				{$base->linkShow(false, $_id, '浏览主题', 'fa-eye', '')}
	{/if}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="7" class="warning">未搜索到指定条件的数据</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>

{include file="$tpl_dir_base/footer.tpl"}