{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>查看统计</strong></h3>
	</div>

	<div class="panel-body">
		<div class="form-horizontal font12" role="form">
	
			<div class="form-group">
				<label class="control-label col-sm-2">试卷名称：</label>
				<div class=" col-sm-2 help-block">{$paper['name']}</div>
				<label class="control-label col-sm-2">考试时间：</label>
				<div class=" col-sm-2 help-block">
					{if !empty($paper['begin_time'])}
						<p>{rgmdate($paper['begin_time'], 'Y/m/d H:i')}</p>
						<p>{rgmdate($paper['end_time'], 'Y/m/d H:i')}</p>
					{/if}

				</div>
				<label class="control-label col-sm-2">考试时长：</label>
				<div class=" col-sm-2 help-block">{$paper['paper_time']}</div>
				
			</div>

			<div class="form-group">
				
				<label class="control-label col-sm-2">考试总分：</label>
				<div class=" col-sm-2 help-block">{$paper['total_score']}</div>
				<label class="control-label col-sm-2">及格分数：</label>
				<div class=" col-sm-2 help-block">{$paper['pass_score']}</div>
				<label class="control-label col-sm-2">考试范围：</label>
				<div class=" col-sm-2 help-block">
					{if $paper['is_all']}
						全公司
					{else}
						{if $range_departments}<p>{$range_departments}</p>{/if}
						{if $range_members}<p>{$range_members}</p>{/if}
					{/if}
				</div>
				
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-9">
					<div class="row">
						<div class="col-md-4"><a href="{$tjlist_url}" class="btn btn-default col-md-9">返回</a></div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<form id="tabsForm" action="{$form_tabs_url}" method="get">
	<input type="hidden" name="id" value="{$id}" />
	<input type="hidden" name="partin" value="{$partin}" />
</form>

<ul class="nav nav-tabs font12">
	<li{if $partin==0} class="active"{/if}>
		<a href="javascript:;" data-toggle="tab">
			<span class="badge pull-right"> {if $partin==0}{$total}{else}{$total_s}{/if} </span>
			已参与&nbsp;
		</a>
	</li>
	<li{if $partin==1} class="active"{/if}>
		<a href="javascript:;" data-toggle="tab">
			<span class="badge pull-right"> {if $partin==1}{$total}{else}{$total_s}{/if} </span>
			未参与&nbsp;
		</a>
	</li>
	<li class="pull-right">
		<button class="btn btn-info form-small form-small-btn margin-left-12" onclick="window.location.href='{$tjexport_url}&status={$partin}';">导出</button>
	</li>
</ul>
<br />



{if $partin==0}
<div class="table-light">
	<table class="table table-striped table-hover table-bordered font12">
		<colgroup>
			<col class="t-col-10" />
			<col class="t-col-10 "/>
			<col class="t-col-20" />
			<col class="t-col-20" />
			<col class="t-col-10" />
			<col class="t-col-10" />
			<col class="t-col-10" />
			<col class="t-col-10" />
		</colgroup>
		<thead>
			<tr>
				<th>姓名</th>
				<th>部门</th>
				<th>开始时间</th>
				<th>结束时间</th>
				<th>用时</th>
				<th>分数</th>
				<th>状态</th>
				<th>操作</th>
			</tr>
		</thead>
	{if !empty($data)}
		<tbody>
		{foreach $data as $_id => $_data}
			<tr>
				<td>{$members[$_data['m_uid']]['m_username']}</td>
				<td>{$departments[$members[$_data['m_uid']]['cd_id']]['cd_name']}</td>		
				<td>{rgmdate($_data['my_begin_time'], 'Y/m/d H:i')}</td>
				<td>{rgmdate($_data['my_end_time'], 'Y/m/d H:i')}</td>		
				<td>{$_data['my_time']}分</td>
				<td>{$_data['my_score']}</td>
				<td>{if $_data['my_is_pass'] == 0}未通过{else}已通过{/if}</td>
				<td>{$base->linkShow($view_answer_url, $_id, '查看答卷', 'fa-eye', 'target="_blank"')}</td>
			</tr>
		{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="8" class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
	{else}
		<tbody>
			<tr>
				<td colspan="10" class="warning">暂无任何数据</td>
			</tr>
		</tbody>
	{/if}
	</table>
</div>
{/if}


{if $partin==1}
<div class="table-light">
	<form class="form-horizontal" role="form" method="post" action="{$form_notify_url}?delete"  onsubmit="return listSumbit();">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<table class="table table-striped table-hover table-bordered font12">
		<colgroup>
			<col class="t-col-10" />
			<col class="t-col-20 "/>
			<col class="t-col-20" />
			<col class="t-col-20" />
			<col class="t-col-20" />
			<col class="t-col-10" />
		</colgroup>
		<thead>
			<tr>
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'notify');"{if !$form_notify_url} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
				<th>姓名</th>
				<th>部门</th>
				<th>创建时间</th>
				<th>状态</th>
				<th>操作</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				
			</tr>
		</tfoot>
	{if !empty($data)}
		<tbody>
		{foreach $data as $_id => $_data}
			<tr>
				<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="notify[{$_id}]" value="{$_id}"{if !$form_notify_url} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
				<td>{$members[$_data['m_uid']]['m_username']}</td>
				<td>{$departments[$members[$_data['m_uid']]['cd_id']]['cd_name']}</td>		
				<td>{rgmdate($_data['created'], 'Y/m/d H:i')}</td>
				<td>未开始</td>
				<td>
					{if $paper['status']!=2&&time()<$paper['end_time']}
						{$base->linkShow($notify_url, $_id, '再次提醒', 'fa-user', 'class="_notice"')}
					{else}
						<span class="help-block"><i class="fa fa-user"></i> 再次提醒</span>
					{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2">
					{if $paper['status']!=2&&time()<$paper['end_time']}
						{if $form_notify_url}<button type="submit" class="btn btn-danger">考试提醒</button>{/if}
					{else}
						{if $form_notify_url}<button type="button" disabled="disabled" class="btn btn-danger">考试提醒</button>{/if}
					{/if}
				</td>
				<td colspan="4" class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
	{else}
		<tbody>
			<tr>
				<td colspan="10" class="warning">暂无任何数据</td>
			</tr>
		</tbody>
	{/if}
	</table>
	</form>
</div>
{/if}


<script type="text/javascript">
$('.nav-tabs a[data-toggle="tab"]').bind('click', function () {
	$('#tabsForm input[name="partin"]').val($(this).parent().index());
	$('#tabsForm').submit();
});

$('._notice').bind('click', function () {
	if (!confirm("是否确认再次推送提醒？")) {
        return false;
    }else{
    	return true;
    }
});

function listSumbit(){
	if (!confirm('是否确认再次推送提醒？')){ 
		return false; 
	}else{ 
		return true; 
	}
}

</script>

{include file="$tpl_dir_base/footer.tpl"}