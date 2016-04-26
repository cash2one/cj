{include file="$tpl_dir_base/header.tpl" css_file="jobtrain.css"}
<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<table class="table-search">
				<tr>
					<td class="i-tt">标题：</td>
					<td><input type="text" class="form-control form-small" name="title" placeholder="输入关键词" value="{$search_conds['title']|escape}" maxlength="30" /></td>
					<td class="i-tt">创建人：</td>
					<td><input type="text" class="form-control form-small" name="m_username" placeholder="输入关键词" value="{$search_conds['m_username']|escape}" maxlength="30" /></td>
					<td class="text-right"><label class="vcy-label-none">内容类型：</label></td>
					<td>
						<select name="type" class="form-control form-small">
							<option value="-1"{if $search_conds['type'] === '-1'} selected{/if}>请选择</option>
							{foreach $types as $k => $v}
							<option value="{$k}"{if $search_conds['type'] == $k} selected{/if}>{$v}</option>
							{/foreach}
						</select>
					</td>
					<td colspan="2"></td>
				</tr>
				<tr>
					<td class="i-tt">更新时间：</td>
					<td>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
							<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
								<input type="text" class="input-sm form-control" id="id_begin_time" name="updated_begintime"   placeholder="开始日期" value="{$search_conds['updated_begintime']|escape}" autocomplete="off" />
								<span class="input-group-addon">至</span>
								<input type="text" class="input-sm form-control" id="id_end_time" name="updated_endtime" placeholder="结束日期" value="{$search_conds['updated_endtime']|escape}" autocomplete="off" />
							</div>
						</div>
						<script>
							init.push(function () {
								var options = {
									todayBtn: "linked",
									orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
								}
								$('#bs-datepicker-range1').datepicker(options);

							});
						</script>
					</td>
					<td class="i-tt">内容分类：</td>
					<td>
						<select name="cid" class="form-control form-small">
							<option value="-1"{if $search_conds['cid'] === '-1'} selected{/if}>请选择</option>
							{foreach $catas as $v}
							<option value="{$v['id']}"{if $search_conds['cid'] == $v['id']} selected{/if}>{if $v['depth']>1}└{/if}{str_repeat('─', $v['depth']-1)} {$v['title']}</option>
							{/foreach}
						</select>
					</td>
					<td class="i-tt">内容状态：</td>
					<td>
						<select name="is_publish" class="form-control form-small">
							<option value="-1"{if $search_conds['is_publish'] === '-1'} selected{/if}>请选择</option>
							<option value="1"{if $search_conds['is_publish'] === '1'} selected{/if}>已发布</option>
							<option value="0"{if $search_conds['is_publish'] === '0'} selected{/if}>草稿</option>
						</select>
					</td>
					<td>
						<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					</td>
					<td>
						<a href="{$list_url}" role="button" class="btn btn-default form-small form-small-btn">全部内容</a>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>

<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			内容列表
		</div>
	</div>

	<form class="form-horizontal" role="form" method="post" action="{$form_del_url}" onsubmit="return listSumbit();">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<table class="table table-striped table-hover table-bordered font12">
		<colgroup>
			<col class="t-col-5" />
			<col class="t-col-15 "/>
			<col class="t-col-10 "/>
			<col class="t-col-10" />
			<col class="t-col-10" />
			<col class="t-col-5" />
			<col class="t-col-5" />
			<col class="t-col-5" />
			<col class="t-col-5" />
			<col class="t-col-15" />
			<col class="t-col-15" />
		</colgroup>
		<thead>
			<tr>
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'ids');"{if !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
				<th>标题</th>
				<th>创建人</th>
				<th>知识分类</th>
				<th>内容类型</th>
				<th>消息保密</th>
				<th>学习人数</th>
				<th>收藏人数</th>
				<th>内容状态</th>
				<th>最后更新时间</th>
				<th>操作</th>
			</tr>
		</thead>
	{if $total > 0}
		<tfoot>
			<tr>
				<td colspan="2">
					<button type="submit" class="btn btn-danger">批量删除</button>
				</td>
				<td colspan="20" class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
	{/if}
		<tbody>
	{if $list}
		{foreach $list as $_id => $_data}
			<tr>
				<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="ids[]" value="{$_id}" /><span class="lbl"> </span></label></td>
				<td>{$base->linkShow($view_url, $_id, $_data['title'], '', '')}</td>
				<td>{$_data['m_username']}</td>
				<td>
				{if $catas[$_data['cid']]['title']}
					{$catas[$_data['cid']]['title']}
				{else}
					<span class="cata_disable" data-container="body" data-toggle="popover" data-placement="top" data-content="“禁用”状态表示该内容所属分类未启用，如需使用，请修改分类设置或重新选择分类。" data-trigger="hover">禁用 <i class="fa fa-question-circle"></i></span>
				{/if}
				</td>
				<td>{$types[$_data['type']]}</td>
				<td>{if $_data['is_secret']}是{else}否{/if}</td>
				<td>
					{if $_data['is_publish']}
					<a href="{$study_list_url}{$_id}">{$_data['study_num']} / {$_data['study_sum']}</a>
					{else}
					{$_data['study_num']} / {$_data['study_sum']}
					{/if}
				</td>
				<td>
					{if $_data['is_publish']}
					{$base->linkShow($coll_list_url, $_id, $_data['coll_num'], '', '')}
					{else}
					{$_data['coll_num']}
					{/if}
				</td>
				<td>{if $_data['is_publish']}已发布{else}草稿{/if}</td>
				<td>{rgmdate($_data['updated'], 'Y/m/d H:i')}</td>
				<td>
					{$base->linkShow($view_url, $_id, '详情', 'fa-eye', '')} |
					{$base->linkShow($edit_url, $_id, '编辑', 'fa-edit', '')} |
					{$base->linkShow($del_url, $_id, '删除', 'fa-times', 'class="text-danger _delete"')}
					
				</td>
			</tr>
		{/foreach}
	{else}
			<tr>
				<td colspan="20" class="text-warning">{if $issearch}未搜索到指定条件的数据{else}暂无任何数据{/if}</td>
			</tr>
	{/if}
		</tbody>
	</table>
	</form>
</div>
<script type="text/javascript">
$('._delete').bind('click', function () {
	if (!confirm("您确认要删除吗？")) {
        return false;
    }else{
    	return true;
    }
});

$('.cata_disable').hover(
  function () {
     $("[data-toggle='popover']").popover();
  },
  function () {
     $("[data-toggle='popover']").popover();
  }
);


function listSumbit(){
	if (!confirm('您确认要删除吗？')){ 
		return false; 
	}else{ 
		return true; 
	}
}

</script>
{include file="$tpl_dir_base/footer.tpl"}