{include file="$tpl_dir_base/header.tpl"}

<div id="container_main">
	<div class="table-light">
	<div class="table-header">
			<div class="row">
			    <div class="col-sm-8 actionBar pull-right text-right">
				    <a href="?ac=express_operate" class="btn js-btn-add-cate btn-default"><i class="fa fa-plus"></i>&nbsp;添加快递类型</a>
		</div>
	</div>
</div>

<table class="table table-hover  table-bordered table-striped bootgrid-table" aria-busy="false">
	<colgroup>
		<col>
		<col>
		<col class="t-col-12">
	</colgroup>
	<thead>
		<tr>
			<th>快递类型</th>
			<th>快递费用（元）</th>
			<th>操作</th>
		</tr>
	</thead>

	<tbody>
	    {if $list}
	    {foreach $list as $_id=>$_data}
		<tr data-row-id="0">
			<td>{$_data['exptype']}</td>
			<td>{$_data['expcost']}</td>
			<td>
				<button type="button" class="btn btn-xs btn-default command-edit" onclick="javascript:edit_exp(this)" data-expid="{$_data['expid']}">
					<span class="fa fa-pencil"></span>
				</button> &nbsp;
				<button type="button" class="btn btn-xs btn-default command-delete" onclick="javascript:del_exp(this);" data-expid="{$_data['expid']}">
					<span class="fa fa-trash-o"></span>
				</button>
			</td>
	   	</tr>
	   	{/foreach}
	    {else}
	   	<tr>
			<td colspan="8" class="warning">暂无任何信息</td>
		</tr>
	    {/if}
    </tbody>
    
    {if $total > 0}
	<tfoot>
		<tr>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
	{/if}
	
</table>

<script type="text/javascript">

 function del_exp(t) {
    var url = "?ac=express_delete&expid="+$(t).attr('data-expid');
	window.location.href=url;  
    
 }
 
 function edit_exp(t) {
    var url = "?ac=express_operate&expid="+$(t).attr('data-expid');
	window.location.href=url;  
 }

</script>

{include file="$tpl_dir_base/footer.tpl"}
