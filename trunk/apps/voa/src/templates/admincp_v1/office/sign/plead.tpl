{include file='admincp/header.tpl'}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜素申诉记录</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">申诉人：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="申诉人用户名" value="{$searchBy['m_username']|escape}" maxlength="54" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_sp_status">处理状态：</label>
					<select id="id_sp_status" name="sp_status" class="selectpicker bla bla bli bootstrap-select-small font12" data-width="auto">
						<option value="-1">不限</option>
{foreach $signPleadStatus as $_k => $_n}
	{if $signPleadStatusSet['remove'] != $_k}
						<option value="{$_k}"{if $searchBy['sp_status']==$_k} selected="selected"{/if}>{$_n}</option>
	{/if}
{/foreach}
					</select>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_sr_type">申诉年月：</label>
					<select id="id_sp_year" name="sp_year" class="selectpicker bla bla bli bootstrap-select-small font12" data-width="auto">
						<option value="-1">不限</option>
{foreach $yearSelect as $_k => $_n}
						<option value="{$_k}"{if $searchBy['sp_year']==$_k} selected="selected"{/if}>{$_n}</option>
{/foreach}
					</select>
					年
					<span class="space"></span>
					<select id="id_sp_month" name="sp_month" class="selectpicker bla bla bli bootstrap-select-small font12" data-width="auto">
						<option value="-1">不限</option>
{foreach $monthSelect as $_k => $_n}
						<option value="{$_k}"{if $searchBy['sp_month']==$_k} selected="selected"{/if}>{$_n}</option>
{/foreach}
					</select>
					月
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				</div>
			</div>
		</form>
	</div>
</div>

<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-10" />
		<col class="t-col-15" />
		<col />
		<col class="t-col-15" />
		<col class="t-col-10" />
		<col class="t-col-15" />
		<col class="t-col-10" />
	</colgroup>
	<thead>
		<tr>
			<th>申诉年月</th>
			<th>申诉人</th>
			<th>申诉主题</th>
			<th>申诉时间</th>
			<th>处理状态</th>
			<th>处理时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="7" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td>{$_data['_date']|escape}</td>
			<td>{$_data['m_username']|escape}</td>
			<td>{$_data['_subject']}</td>
			<td>{$_data['_created']}</td>
			<td>{$_data['_status']}</td>
			<td>{$_data['_updated']}</td>
			<td>
	{if $_data['sp_status'] == $signPleadStatusSet['done']}
				{$base->linkShow($pleadopUrlBase, $_id, '再次处理', 'fa-crop', '')}
	{else}
				{$base->linkShow($pleadopUrlBase, $_id, '处理', 'fa-crop', '')}
	{/if}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="7" class="warning">{if $issearch}未搜索到指定条件的申诉记录{else}暂无任何申诉记录{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>

{include file='admincp/footer.tpl'}