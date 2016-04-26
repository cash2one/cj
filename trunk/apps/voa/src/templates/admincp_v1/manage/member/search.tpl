{include file='admincp/header.tpl'}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜素员工</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search font12" role="form" action="{$formActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id-username">用户名：</label>
					<input type="text" class="form-control form-small" id="id-username" name="m_username" placeholder="姓名" value="{$searchBy['m_username']|escape}" maxlength="54" />
					<span class="space"></span>
					
					<label class="vcy-label-none font12 margin-left-12" for="id-mobilephone">手机：</label>
					<input type="text" class="form-control form-small" id="id-mobilephone" name="m_mobilephone" placeholder="手机号" value="{$searchBy['m_mobilephone']|escape}" maxlength="12" />
					<span class="space"></span>

					<label class="vcy-label-none font12" for="id-job">　职位：</label>
					<select id="id-job" name="cj_id" class="selectpicker bla bla bli bootstrap-select-small" data-width="auto">
						<option value="-1">请选择……</option>
	{foreach $jobList as $_cj_id => $_job}
						<option value="{$_cj_id}"{if $_cj_id == $searchBy['cj_id']} selected="selected"{/if}>{$_job['cj_name']|escape}</option>
	{/foreach}
					</select>
	
					<label class="vcy-label-none font12 margin-left-12" for="id-department">部门：</label>
					<select id="id-department" name="cd_id" class="selectpicker bla bla bli bootstrap-select-small" data-width="auto">
						<option value="-1">请选择……</option>
	{foreach $departmentList as $_cd_id => $_department}
						<option value="{$_cd_id}"{if $_cd_id == $searchBy['cd_id']} selected="selected"{/if}>{$_department['cd_name']}</option>
	{/foreach}
					</select>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 查询</button>
				</div>
			</div>
		</form>
	</div>
</div>

{if $issearch}
<div class="row">
	<div class="vcy-ao-titlegroup">
		<div class="col-sm-10 vcy-ao-title">
			<span class="font12">
			<a href="{$memberListUrl}">全部员工</a>
			&raquo;
			查询结果：
			</span>
		</div>
		<div class="col-sm-2 vcy-ao-link text-right">
			{if $dump_url}<a href="{$dump_url}" class="btn btn-info btn-xs role="button"{if !$member_list} disabled="disabled"{/if}"><i class="fa fa-exchange"></i> 导出结果</a>{/if}
		</div>
	</div>
</div>

{include file='admincp/manage/member/list_common.tpl'}

{/if}

{include file='admincp/footer.tpl'}