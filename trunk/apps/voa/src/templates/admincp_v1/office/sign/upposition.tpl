{include file='admincp/header.tpl'}
{include file='admincp/office/sign/sign_list_common.tpl'}
<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜素签到记录</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">签到人：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="签到人用户名" value="{$searchBy['m_username']|escape}" maxlength="54" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_signtime_min">上报时间：</label>
					<input type="date" class="form-control form-small" id="id_signtime_min" name="signtime_min" value="{$searchBy['signtime_min']|escape}" />
					<label class="vcy-label-none" for="id_signtime_max"> 至 </label>
					<input type="date" class="form-control form-small" id="id_signtime_max" name="signtime_max" value="{$searchBy['signtime_max']|escape}" />
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				</div>
			</div>
		</form>
	</div>
</div>

<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-13" />
		<col class="t-col-13" />
		<col class="t-col-15" />
		<col />
		<col class="t-col-13" />
	</colgroup>
	<thead>
		<tr>
			<th>上报人</th>
			<th>上报时间</th>
			<th>IP 地址</th>
			<th>上报位置</th>
			<th>签到详情</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="5" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>

{foreach $list as $_id=>$_data}

		<tr>
			<td>{$_data['m_username']}</td>
			<td>{$_data['_signtime']|escape}</td>
			<td>{$_data['sl_ip']}</td>
			<td>{$_data['sl_address']}</td>
			<td>{$base->linkShow($detailUrlBase, $_id, '查看详情', 'fa-eye', '')}</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="5" class="warning">{if $issearch}未搜索到指定条件的签到记录{else}暂无任何签到记录{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>

{include file='admincp/footer.tpl'}
