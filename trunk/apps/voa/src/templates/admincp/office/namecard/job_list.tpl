{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$formActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">创建者：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="创建者" value="{$searchBy['m_username']|escape}" maxlength="54" />
					
					<label class="vcy-label-none" for="id_ncj_name">职务名称：</label>
					<input type="text" class="form-control form-small" id="id_ncj_name" name="ncj_name" placeholder="职务名称" value="{$searchBy['ncj_name']|escape}" maxlength="30" />

					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				</div>
			</div>
		</form>
	</div>
</div>

<form class="form-horizontal" role="form" method="post" action="{$editActionUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-10" />
		<col />
		<col class="t-col-20" />
		<col class="t-col-12" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$editActionUrl} disabled="disabled"{/if} />删除</label></th>
			<th>职务名称</th>
			<th>创建者</th>
			<th>名片数</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td class="text-left">{if $editActionUrl}<button type="submit" class="btn btn-primary">更新</button>{/if}</td>
			<td colspan="3" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$editActionUrl} disabled="disabled"{/if} /></td>
			<td><input type="text" class="form-control form-small" name="edit[{$_id}][ncj_name]" placeholder="职务名称" value="{$_data['ncj_name']|escape}" maxlength="30"{if !$editActionUrl} readonly="readonly"{/if} /></td>
			<td>{$_data['_username']|escape}</td>
			<td>{$_data['ncj_num']}</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="4" class="warning">{if $issearch}未搜索到指定条件的职务信息{else}暂无职务信息{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>

{include file="$tpl_dir_base/footer.tpl"}