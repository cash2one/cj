{include file='cyadmin/header.tpl'}
<script>
$(function() {
$('.fa-lock').parents('a').click(function() {
    if (confirm('确定操作?')) {
        return true;
    } else {
        return false;
    }
});
$('#sandbox-container .input-daterange').datepicker({
todayHighlight: true
});
});
</script>
<div id="content-wrapper">
{include file='cyadmin/content/link/menu.tpl'}
<div class="panel panel-default">

<div class="panel-heading">链接列表 <button type="button" class="close"><span class="glyphicon glyphicon glyphicon-chevron-down"></span></button></div>
  <div class="panel-body">

  <form   class="form-horizontal" action="" method="get">
	 <div class="form-group " style="margin:20px 0">
  	        <label class="control-label col-sm-1" for="title">时间</label>
       <div class="col-md-4" id="sandbox-container">
            <div class="input-daterange input-group" id="datepicker">
            <input type="text" class="input-sm form-control" value="{$conds['date_start']}" name="date_start">
            <span class="input-group-addon">to</span>
            <input type="text" class="input-sm form-control" value="{$conds['date_end']}" name="date_end">
            </div>
        </div>
        <label class="control-label col-sm-1">关键字</label>
        <div class="col-sm-2">
            <input type="text" name="keyword" value="{$conds['keyword']}" placeholder="" class="input-sm form-control">
            <span class="help-block"></span>
        </div>
</div>
<div class="form-group" style="margin:20px 0">
	<label class="control-label col-sm-1">状态</label>
        <div class="col-sm-2">
            <select class="input-sm form-control" name="is_publish">
                <option value="">请选择</option> 
                <option value="1" {if $conds['is_publish'] == 1}selected{/if}>已发布</option>
				<option value="2" {if $conds['is_publish'] == 2}selected{/if}>草稿</option>
              </select>
            <span class="help-block"></span>
        </div>
        <div class="col-sm-2"></div>
        <label class="control-label col-sm-1">链接类型</label>
        <div class="col-sm-2">
            <select class="input-sm form-control" name="linktype">
                <option value="">请选择</option> 
                <option value="1" {if $conds['linktype'] == 1}selected{/if}>文字链接</option>
				<option value="2" {if $conds['linktype'] == 2}selected{/if}>图片链接</option>
              </select>
            <span class="help-block"></span>
        </div>
        
        
        

        <input type="hidden" name="isserach" value="1">
        <button name="submit" value="1" type="submit" class="btn btn-primary  input-sm">检 索</button>
        
</div>
   
    </form>
<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}?delete">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col class="t-col-5" />
		<col />
		<col class="t-col-10" />
		<col class="t-col-10" />
		<col class="t-col-10" />
		<col class="t-col-18" />
		<col class="t-col-17" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$form_delete_url || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>标题</th>
			<th>链接类型</th>
			<th>状态</th>
			<th>排序</th>
			<th>最后更新时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="1">{if $form_delete_url}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{if $link_list}
	{foreach $link_list as $_id => $_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_id}]" value="{$_data['lid']}"{if !$form_delete_url} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td><a href="/content/link/view/?lid={$_data['lid']}">{$_data['linkname']|escape}</a></td>
			<td align="center">{$_data['type']}</td>
			<td align="center">{$_data['status']}</td>
			<td align="center">{$_data['lsort']}</td>
			
			<td align="center">{$_data['time']|escape}</td>
			<td align="center">
				 {$base->show_link($edit_url, $_data['lid'], '编辑', 'fa-edit')}
				 {$base->show_link($delete_url, $_data['lid'], '删除', 'fa-delete')}
			</td>
		</tr>
	{/foreach}
{else}
		<tr>
			<td colspan="7" class="warning">{if $issearch}未搜索到指定条件的数据{else}暂无任何数据{/if}</td>
		</tr>
{/if}
	</tbody>
</table>
</form>
</div>
</div>
{include file='cyadmin/footer.tpl'}