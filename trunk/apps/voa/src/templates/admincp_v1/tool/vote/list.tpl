{include file='admincp/header.tpl'}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜素投票</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">评选发起人：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="发起者" value="{$searchBy['m_username']|escape}" maxlength="54" />
					
					<label class="vcy-label-none" for="id_v_subject">评选主题：</label>
					<input type="text" class="form-control form-small" id="id_v_subject" name="v_subject" placeholder="标题关键词" value="{$searchBy['v_subject']|escape}" maxlength="30" />

					<label class="vcy-label-none" for="id_v_begintime">评选日期：</label>
					<input type="date" class="form-control form-small" id="id_v_begintime" name="v_begintime" value="{$searchBy['v_begintime']|escape}" />
					<label class="vcy-label-none" for="id_v_endtime"> 至 </label>
					<input type="date" class="form-control form-small" id="id_v_endtime" name="v_endtime" value="{$searchBy['v_endtime']|escape}" />

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
		<col class="t-col-5" />
		<col class="t-col-12" />
		<col />
		<col class="t-col-10" />
		<col class="t-col-15" />
		<col class="t-col-10" />
		<col class="t-col-10" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase} disabled="disabled"{/if} />删除</label></th>
			<th>发起人</th>
			<th>主题</th>
			<th>状态</th>
			<th>开始结束时间</th>
			<th>允许投票</th>
			<th>投票人次</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /></td>
			<td>{$_data['m_username']|escape}</td>
			<td>{$_data['v_subject']|escape}</td>
			<td>
				<strong>{$_data['_status']}</strong><br />
				{$_data['_set_status_urls']}
			</td>
			<td>{$_data['_begintime']}<br />{$_data['_endtime']}</td>
			<td>{$_data['_friend']}</td>
			<td>{$_data['v_voters']}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="_delete"')} | {$base->linkShow($editUrlBase, $_id, '编辑', 'fa-edit', '')}<br />
				{$base->linkShow($viewUrlBase, $_id, '评选详情', 'fa-eye', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="8" class="warning">{if $issearch}未搜索到指定条件的评选信息{else}暂无任何评选信息{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>

{include file='admincp/footer.tpl'}