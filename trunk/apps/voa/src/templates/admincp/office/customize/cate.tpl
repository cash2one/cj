{include file="$tpl_dir_base/header.tpl"}

<div id="container_main">
	<div class="table-light">
	<div class="table-header">
			<div class="row">
			    <div class="col-sm-8 actionBar pull-right text-right">
			      <form class="form-inline vcy-from-search" role="form" method="post" action="{$searchUrl}">
				    <input type="hidden" name="issearch" value="1" />
				    <a href="?ac=cate&act=cate_edit" class="btn js-btn-add-cate btn-default">
					<i class="fa fa-plus"></i>&nbsp;添加分类
				    </a>
				    <span class="space"></span>
			        <input type="text" class="form-control form-small" id="classname" name="classname" placeholder="分类名称" value="" maxlength="30">
			        <button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
		       	 </form>
		</div>
	</div>
</div>

<table class="table table-hover  table-bordered table-striped bootgrid-table" aria-busy="false">
	<colgroup>
		<col>
		<col class="t-col-12">
	</colgroup>
	<thead>
		<tr>
			<th data-column-id="classname" class="text-left">
				<a href="javascript:void(0);" class="column-header-anchor sortable">
					<span class="text">分类名称</span><span class="icon glyphicon "></span>
				</a>
			</th>
			<th data-column-id="commands" class="text-left">
				<a href="javascript:void(0);" class="column-header-anchor ">
					<span class="text">操作</span><span class="icon glyphicon "></span>
				</a>
			</th>
		</tr>
	</thead>

	<tbody>
		{foreach $list as $_id=>$_data}
		<tr data-row-id="0">
			<td class="text-left">{$_data['classname']}</td>
			<td class="text-left">
				<button type="button" class="btn btn-xs btn-default command-edit" onclick="javascript:edit_cate(this)" data-classid="{$_data['classid']}">
					<span class="fa fa-pencil"></span>
				</button> &nbsp;
				<button type="button" class="btn btn-xs btn-default command-delete" onclick="javascript:del_cate(this);" data-classid="{$_data['classid']}">
					<span class="fa fa-trash-o"></span>
				</button>
			</td>
	   	</tr>
		{foreachelse}
		<tr>
			<td colspan="8" class="warning">{if $issearch}未搜索到指定条件的分类信息{else}暂无产品分类信息{/if}</td>
		</tr>
	   {/foreach}

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

 function del_cate(t) {
    var url = "?ac=cate&act=cate_delete&classid="+$(t).attr('data-classid');
	window.location.href=url;  
    
 }
 
 function edit_cate(t) {
    var url = "?ac=cate&act=cate_edit&classid="+$(t).attr('data-classid');
	window.location.href=url;  
 }

</script>

{include file="$tpl_dir_base/footer.tpl"}
