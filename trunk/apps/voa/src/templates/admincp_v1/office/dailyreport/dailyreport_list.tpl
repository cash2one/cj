{include file='admincp/header.tpl'}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜素日报</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_cab_realname_author">提交人：</label>
					<input type="text" class="form-control form-small" id="id_cab_realname_author" name="cab_realname_author" placeholder="输入姓名" value="{$searchBy['cab_realname_author']|escape}" maxlength="54" />
					<span class="space"></span>
<!--
					<label class="vcy-label-none" for="id_dr_subject">标题关键词：</label>
					<input type="text" class="form-control form-small" id="id_dr_subject" name="dr_subject" placeholder="关键词" value="{$searchBy['dr_subject']|escape}" maxlength="30" />
					<span class="space"></span>
-->
					<label class="vcy-label-none" for="id_begintime">提交日期范围：</label>
					<input type="date" class="form-control form-small" id="id_begintime" name="begintime" value="{$searchBy['begintime']|escape}" />
					<label class="vcy-label-none" for="id_endtime"> 至 </label>
					<input type="date" class="form-control form-small" id="id_endtime" name="endtime" value="{$searchBy['endtime']|escape}" />
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_cab_realname_receive">接收人：</label>
					<input type="text" class="form-control form-small" id="id_cab_realname_receive" name="cab_realname_receive" placeholder="输入姓名" value="{$searchBy['cab_realname_receive']|escape}" maxlength="30" />
					<span class="space"></span>
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
		<col class="t-col-15" />
		<col />
		<col class="t-col-15" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} />删除</label></th>
			<th>日报提交人</th>
			<th>所在部门/职务</th>
			<th>标题</th>
			<th>提交时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="4" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /></td>
			<td>{$_data['_realname']|escape}</td>
			<td>{$_data['_department']|escape}<br />{$_data['_job']}</td>
			<td>{$_data['_reporttime_fmt']['m']}-{$_data['_reporttime_fmt']['d']} {$weeknames[$_data['_reporttime_fmt']['w']]}日报</td>
			<td>{$_data['_created']}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="_delete"')} | 
				{$base->linkShow($viewUrlBase, $_id, '日报详情', 'fa-eye', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="6" class="warning">{if $issearch}未搜索到指定条件的日报信息{else}暂无任何日报信息{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>

{include file='admincp/footer.tpl'}