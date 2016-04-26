{include file='admincp/header.tpl'}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜素公告</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">

					<label class="vcy-label-none" for="id_nt_subject">　标题关键词：</label>
					<input type="text" class="form-control form-small" id="id_nt_subject" name="nt_subject" placeholder="输入关键词" value="{$search_by['nt_subject']|escape}" maxlength="30" />
					<span class="space"></span>
					
					<label class="vcy-label-none" for="id_nt_author">发布人：</label>
					<input type="text" class="form-control form-small" id="id_nt_author" name="nt_author" placeholder="输入姓名" value="{$search_by['nt_author']|escape}" maxlength="54" />
					<span class="space"></span>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					
					
					<label class="vcy-label-none" for="id_nt_created_after">发布时间范围：</label>
					<input type="date" class="form-control form-small" id="id_nt_created_after" name="nt_created_after" value="{$search_by['nt_created_after']|escape}" />
					<label class="vcy-label-none" for="id_nt_created_before"> 至 </label>
					<input type="date" class="form-control form-small" id="id_nt_created_before" name="nt_created_before" value="{$search_by['nt_created_before']|escape}" />
					<span class="space"></span>
					
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a href="{$list_url}" role="button" class="btn btn-default form-small form-small-btn">全部记录</a>
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
		<col class="t-col-12" />
		<col />
		<col class="t-col-20" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$delete_url_base || !$total} disabled="disabled"{/if} />删除</label></th>
			<th>发布人</th>
			<th>标题</th>
			<th>发布时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $delete_url_base}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="3" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id => $_data}
		<tr>
			<td class="text-left"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$delete_url_base} disabled="disabled"{/if} /></td>
			<td>{$_data['nt_author']|escape}</td>
			<td>{$_data['nt_subject']|escape}</td>
			<td>{$_data['_created']}</td>
			<td>
				{$base->linkShow($delete_url_base, $_id, '删除', 'fa-times', 'class="_delete"')} | 
				{$base->linkShow($view_url_base, $_id, '编辑', 'fa-edit', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="5" class="warning">{if $issearch}未搜索到指定条件的公告数据{else}暂无任何公告数据{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>

{include file='admincp/footer.tpl'}