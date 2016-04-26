{include file='admincp/header.tpl'}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜素审批</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$formActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_cd_id">所在部门：</label>
					<select id="id_cd_id" name="cd_id" class="selectpicker bla bla bli bootstrap-select-small" data-width="auto">
						<option value="0">所有部门</option>
{foreach $departmentList as $_cd_id => $_cd}
						<option value="{$_cd_id}"{if $searchBy['cd_id'] == $_cd_id} selected="selected"{/if}>{$_cd['cd_name']|escape}</option>
{/foreach}
					</select>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_m_username">申请人：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="申请人用户名" value="{$searchBy['m_username']|escape}" maxlength="54" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_af_subject">主题：</label>
					<input type="text" class="form-control form-small" id="id_af_subject" name="af_subject" placeholder="申请主题" value="{$searchBy['af_subject']|escape}" maxlength="255" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_af_status">审批状态：</label>
					<select id="id_af_status" name="af_status" class="selectpicker bla bla bli bootstrap-select-small" data-width="auto">
						<option value="-1">不限</option>
{foreach $askforStatusDescriptions as $_k=>$_n}
						<option value="{$_k}"{if $searchBy['af_status']==$_k} selected="selected"{/if}>{$_n}</option>
{/foreach}
					</select>
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				</div>
			</div>
		</form>
	</div>
</div>

<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-12" />
		<col class="t-col-16" />
		<col class="t-col-12" />
		<col />
		<col class="t-col-15" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th>申请人</th>
			<th>申请时间</th>
			<th>部门</th>
			<th>审批标题</th>
			<th>审批状态</th>
			<th>操作</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="6" class="text-right">{if $multi}{$multi}{/if}</td>
		</tr>
	</tfoot>
	<tbody>
{foreach $askforList as $_af_id => $_af}
		<tr>
			<td><a href="{$searchByUidBaseUrl}{$_af['m_uid']}">{$_af['m_username']|escape}</a></td>
			<td>{$_af['_created']}</td>
			<td><a href="{$searchByDepartmentBaseUrl}{$_af['cd_id']}">{$_af['_department']|escape}</a></td>
			<td>{$_af['af_subject']|escape}</td>
			<td class="text-{$_af['_status_class_tag']}">{$_af['_status']}</td>
			<td>{$base->linkShow($viewBaseUrl, $_af_id, '详情', 'view', '')}</td>
		</tr>
{foreachelse}
		<tr class="warning">
			<td colspan="6">{if $issearch}未找到符合条件的审批事件{else}暂无审批事件数据{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>

{include file='admincp/footer.tpl'}