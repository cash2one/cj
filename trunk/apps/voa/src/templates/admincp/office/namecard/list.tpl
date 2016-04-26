{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索名片</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">名片创建者：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="创建者" value="{$searchBy['m_username']|escape}" maxlength="54" />
					
					<label class="vcy-label-none" for="id_nc_realname">名片真实姓名：</label>
					<input type="text" class="form-control form-small" id="id_nc_realname" name="nc_realname" placeholder="职务名称" value="{$searchBy['nc_realname']|escape}" maxlength="30" />

					<label class="vcy-label-none" for="id_nc_mobilephone">名片手机号码：</label>
					<input type="tel" class="form-control form-small" id="id_nc_mobilephone" name="nc_mobilephone" placeholder="职务名称" value="{$searchBy['nc_mobilephone']|escape}" maxlength="12" />

					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				</div>
			</div>
		</form>
	</div>
</div>

<form class="form-horizontal" role="form" method="post" action="{$formDeleteUrl}">
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
			<th class="text-left"><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$allowDelete} disabled="disabled"{/if} />删除</label></th>
			<th>创建者</th>
			<th>真实姓名</th>
			<th>手机号</th>
			<th>公司</th>
			<th>职务</th>
			<th>所在群组</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $allowDelete}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $namecardList as $_id=>$_data}
		<tr>
			<td class="text-left"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$allowDelete} disabled="disabled"{/if} /></td>
			<td>{$_data['_username']|escape}</td>
			<td>{$_data['nc_realname']|escape}</td>
			<td>{$_data['nc_mobilephone']|escape}</td>
			<td>{$_data['_company']|escape}</td>
			<td>{$_data['_job']}</td>
			<td>{$_data['_folder']}</td>
			<td>
			{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="_delete"')} | {$base->linkShow($editUrlBase, $_id, '详情', 'fa-edit', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="8" class="warning">{if $issearch}未搜索到指定条件的职务信息{else}暂无职务信息{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>


{include file="$tpl_dir_base/footer.tpl"}