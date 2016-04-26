{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>基本信息</strong></h3>
	</div>

	<div class="panel-body">
		<div class="form-horizontal font12" role="form">
	
			<div class="form-group">
				<label class="control-label col-sm-2">标题：</label>
				<div class=" col-sm-9 help-block">{$article['title']}</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">创建人：</label>
				<div class=" col-sm-9 help-block">{$article['m_username']}</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">最后更新时间：</label>
				<div class=" col-sm-9 help-block">{rgmdate($article['updated'], 'Y/m/d H:i')}</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-9">
					<div class="row">
						<div class="col-md-4"><a href="{$return_url}" class="btn btn-default col-md-9">返回</a></div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<form id="tabsForm" action="{$form_tabs_url}" method="get">
	<input type="hidden" name="aid" value="{$aid}" />
	<input type="hidden" name="is_study" value="{$is_study}" />
</form>

<ul class="nav nav-tabs font12">
	<li{if $is_study==1} class="active"{/if}>
		<a href="#" data-toggle="tab">
			<span class="badge pull-right"> {$article['study_num']} </span>
			已学习&nbsp;
		</a>
	</li>
	<li{if $is_study==0} class="active"{/if}>
		<a href="#" data-toggle="tab">
			<span class="badge pull-right"> {$article['study_sum']-$article['study_num']} </span>
			未学习&nbsp;
		</a>
	</li>
	<li class="pull-right">
		<button class="btn btn-info form-small form-small-btn margin-left-12" onclick="window.location.href='{$study_export_url}';">导出</button>
	</li>
</ul>
<br />



<div class="table-light">
	<form class="form-horizontal" role="form" method="get" action="{$notify_url}" onsubmit="return listSumbit();">
	<input type="hidden" name="notify" value="1">
	<input type="hidden" name="aid" value="{$article['id']}">
	<table class="table table-striped table-hover table-bordered font12">
		<colgroup>
			{if $is_study==0}
			<col class="t-col-5" />
			{/if}
			<col class="t-col-20 "/>
			<col class="t-col-20 "/>
			<col class="t-col-20" />
			<col class="t-col-20" />
			<col class="t-col-15" />
		</colgroup>
		<thead>
			<tr>
				{if $is_study==0}
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'m_uids');"><span class="lbl">全选</span></label></th>
				{/if}
				<th>姓名</th>
				<th>部门</th>
				<th>职位</th>
				<th>手机</th>
				{if $is_study==1}
				<th>学习时间</th>
				{else}
				<th>操作</th>
				{/if}
			</tr>
		</thead>
	{if !empty($list)}
		<tbody>
		{foreach $list as $_id => $_data}
			<tr>
				{if $is_study==0}
				<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="m_uids[]" value="{$_data['m_uid']}"><span class="lbl"> </span></label></td>
				{/if}
				<td>{$_data['m_username']}</td>
				<td>{$_data['department']}</td>		
				<td>{$_data['job']}</td>
				<td>{$_data['mobile']}</td>
				{if $is_study==1}
				<td>{rgmdate($_data['created'], 'Y/m/d H:i')}</td>
				{else}
				<td><a href="{$notify_url}?notify=1&aid={$article['id']}&m_uids={$_data['m_uid']}" class="_notice">提醒学习</a></td>
				{/if}
			</tr>
		{/foreach}
		</tbody>
		<tfoot>
			<tr>
				{if $is_study==0}
				<td colspan="2">
					<button type="submit" class="btn btn-danger">批量提醒</button>
				</td>
				{/if}
				<td colspan="10" class="text-right vcy-page">{$multi}</td>
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
<script type="text/javascript">
$('.nav-tabs a[data-toggle="tab"]').bind('click', function () {
	$('#tabsForm input[name="is_study"]').val(Math.abs($(this).parent().index()-1));
	$('#tabsForm').submit();
});

$('._notice').bind('click', function () {
	if (!confirm("是否提醒？")) {
        return false;
    }else{
    	return true;
    }
});

function listSumbit(){
	if (!confirm('是否批量提醒？')){ 
		return false; 
	}else{ 
		return true; 
	}
}

</script>

{include file="$tpl_dir_base/footer.tpl"}