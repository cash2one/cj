{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
    <div class="panel-heading"><strong>搜索公告</strong></div>
    <div class="panel-body">
        <form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
            <input type="hidden" name="issearch" value="1" />
            <div class="form-row m-b-20">
                <div class="form-group">
                    <script>
                        init.push(function () { 
                            var options = {                         
                                todayBtn: "linked",
                                orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
                            }
                            $('#bs-datepicker-range').datepicker(options);
                        });
                    </script>
                    <div class="input-daterange input-group" style="width: 290px;   display: inline-table;vertical-align:middle;">
                        <label class="vcy-label-none" for="id_created">更新时间：</label>
                        <div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
                            <input type="text" class="input-sm form-control" id="id_updated_begintime" name="updated_begintime"   placeholder="开始日期" value="{$search_conds['updated_begintime']|escape}" />
                            <span class="input-group-addon">至</span>
                            <input type="text" class="input-sm form-control" id="id_updated_endtime" name="updated_endtime" placeholder="结束日期" value="{$search_conds['updated_endtime']|escape}" />
                        </div>
                    </div>
                    <span class="space"></span>
                    <label class="vcy-label-none" for="id_nt_subject">新闻标题：</label>
                    <input type="text" class="form-control form-small" id="id_title" name="title"  placeholder="输入关键词" value="{$search_conds['title']|escape}" maxlength="30" style="width:170px;"/>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <div class="input-daterange input-group" style="width: 290px;   display: inline-table;vertical-align:middle;">
                        <label class="vcy-label-none" for="id_af_status">　　分类：</label>
                        <div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
                            <select id="id_nca_id" name="nca_id" class="form-control form-small" data-width="auto">
                                <option value="-1">不限</option>
                                <option value="0">未分类</option>

                                {foreach $select_categories as $_key => $_val}
                                <optgroup label="{$_val['name']}">
                                    {if isset($_val['nodes'])}
                                        {foreach $_val['nodes'] as $_sv}
                                            <option value="{$_sv['nca_id']}" {if $search_conds['nca_id'] == $_sv['nca_id']} selected="selected"{/if}>{$_sv['name']}</option>
                                        {/foreach}
                                    {else}
                                        <option value="{$_val['nca_id']}" {if $search_conds['nca_id'] == $_val['nca_id']} selected="selected"{/if}>{$_val['name']}</option>
                                    {/if}
                                </optgroup>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <span class="space"></span>
                    <label class="vcy-label-none" for="id_af_status">　　状态：</label>
                    <select id="id_is_publish" name="is_publish" class="form-control form-small" data-width="auto" style="width:170px;">
                        <option value="-1">不限</option>
                            {foreach $status as $_k=>$_n}
                        <option value="{$_k}"{if $search_conds['is_publish']==$_k} selected="selected"{/if}>{$_n}</option>
                            {/foreach}
                    </select>
                    <button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
                    <button type="button" onclick="location.href='{$list_url}'" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 所有公告</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>

<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}?delete" id="news_form">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-26 "/>
		<col class="t-col-10" />
		<col class="t-col-8" />
		<col class="t-col-10" />
		<col class="t-col-8" />
		<col class="t-col-7" />
		<col class="t-col-14" />
		<col class="t-col-22" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$form_delete_url || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>标题</th>
			<th>公告类型</th>
			<th>消息保密</th>
			<th>阅读人数</th>
			<th>状态</th>
			<th>类型</th>
			<th>最后更新时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">
				{if $form_delete_url}<button type="submit" class="btn btn-danger" id="del_button">批量删除</button>{/if}
				<button type="button" class="btn btn-danger" id="category_btn">批量修改类型</button>
			</td>
			<td colspan="7" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{if $list}
	{foreach $list as $_id => $_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_id}]" value="{$_id}"{if !$form_delete_url} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td style="word-wrap: break-word;word-break: break-all"><a href="{$view_url}{$_id}">{$_data['title']|escape}</a></td>
			<td>{(isset($categories[$_data['nca_id']])) ? ($categories[$_data['nca_id']]['name']) : '未分类'}</td>
			<td>{$_data['_secret']}</td>
			<td><a href="{$read_url}{$_id}">{$_data['read_number']}/{$_data['count_number']}</a></td>
			<td>{$_data['_status']}</td>
			<td>{if $_data['multiple']>0}多条{else}单条{/if}</td>
			<td>{$_data['updated']|escape}</td>
			<td>
				{$base->linkShow($delete_url, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($edit_url, "{$_id}&multiple={$_data['multiple']}", '编辑', 'fa-edit', '')}
			</td>
		</tr>
	{/foreach}
{else}
		<tr>
			<td colspan="9" class="warning">{if $issearch}未搜索到指定条件的公告数据{else}暂无任何公告数据{/if}</td>
		</tr>
{/if}
	</tbody>
</table>
</form>
</div>

<div id="myModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
	<form class="form-horizontal" role="form" method="post" action="{$form_category_url}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<input type="hidden" name="ne_ids" value="" id="ne_ids"/>
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="myModalLabel">选择类型</h4>
			</div>
			<div class="modal-body padding-sm">
				<select id="id_nca_id" name="nca_id" class="form-control form-small" data-width="auto"  required="required">
					<option value="" selected="selected">请选择类型</option>
					{foreach $select_categories as $_key => $_val}
					<optgroup label="{$_val['name']}">
						{if isset($_val['nodes'])}
							{foreach $_val['nodes'] as $_sv}
								<option value="{$_sv['nca_id']}">{$_sv['name']}</option>
							{/foreach}
						{else}
							<option value="{$_val['nca_id']}">{$_val['name']}</option>
						{/if}
					</optgroup>
					{/foreach}
				</select>
			</div>
			<!-- / .modal-body -->
			<div class="modal-footer text-right">
				<button type="submit" class="btn btn-default btn-sm btn-primary">确定</button>									
			</div>
		</div>
		<!-- / .modal-content -->
	</div>
	<!-- / .modal-dialog -->
	</form>
</div>
<!-- /.modal -->

<script type="text/javascript">
//预览
$(function(){
	$('#category_btn').bind('click',function(){
		var ne_ids = [];
		$("input[name*='delete']:checked").each(function(index,item){
			ne_ids.push($(this).val());
			$('#ne_ids').val(ne_ids);
		});
		if(ne_ids.length < 1){
			alert('请选择要修改的公告');
			return;
		}
		$('#myModal').modal();
	});
	$('._delete').on('click', function() {
		var $this = $(this);
		bootbox.confirm({
			message: "确定要删除此公告吗？",
			callback: function(result) {
				if (result) {
					//$("#news_form").submit();
					window.location.href = $this.attr('href');
				}
				return true;
			},
			buttons: {
				confirm: {
					label: '确认',
					className: 'btn-myStyle'
				},
				cancel: {
					label: '取消',
					className: 'btn-default'
				}
			},
			className: "bootbox-sm"
		});
		var $modal_dialog = $('.modal-dialog');
		var m_top = ( $(document).height() - $modal_dialog.height() )/2-300;
		//$modal_dialog.css({ 'margin': m_top + 'px auto' });
        $modal_dialog.css({ 'margin':  '200px auto' });

		return false;
	})

	$('#del_button').on('click', function () {
		bootbox.confirm({
			message: "确定要删除产品吗？",
			callback: function(result) {
				if (result) {
					$("#news_form").submit();
				}
				return true;
			},
			buttons: {
				confirm: {
					label: '确认',
					className: 'btn-myStyle'
				},
				cancel: {
					label: '取消',
					className: 'btn-default'
				}
			},
			className: "bootbox-sm"
		});
		var $modal_dialog = $('.modal-dialog');
		var m_top = ( $(document).height() - $modal_dialog.height() )/2-100;
		$modal_dialog.css({ 'margin': m_top + 'px auto' });
		return false;
	});
});	
</script>

{include file="$tpl_dir_base/footer.tpl"}
