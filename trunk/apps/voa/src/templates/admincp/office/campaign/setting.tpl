{include file="$tpl_dir_base/header.tpl"}
<style>
#control button{
	margin-left: 40px;
}
</style>

<div class="table-light" style="width: 800px;">
	<div class="table-header">
		<div class="table-caption font12">
			活动分类
		</div>
	</div>
	<form id="type_form" class="form-horizontal" role="form" method="post" action="javascript:;">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<table id="type_table" class="table table-striped table-bordered table-hover font12">
	{foreach $cats as $k => $v}
		<tr>
			<td>排序号</td>
			<td>
				<input type="hidden" name="id[]" value="{$v.id}"/>
				<input type="text" class="form-control order_sort" name="order_sort[]" value="{$v.order_sort}"/>
			</td>
			<td>活动类型</td>
			<td><input type="text" class="form-control title" name="title[]" value="{$v.title}"/></td>
			<td>{if $k > 1}<button rel="{$v.id}" type="button" class="btn delete">删除</button>{/if}</td>
		</tr>
	{/foreach}
	</table>
	</form>
</div>
<div id="control">
	<button id="add" type="button" class="btn" style="margin-left:10px;">新增</button>
	<button id="save" type="button" class="btn  btn-primary">保存</button>
	<button id="return" type="button" class="btn">取消</button>
</div>
<script>
{literal}
$(function (){
	//新增分类
	$('#add').click(function (){
		var clone = $('#type_table tr:first').clone(true);
		clone.find('input:first').val(0);
		clone.find('input.order_sort').val(50);
		clone.find('input.title').val('请输入分类名');
		clone.find('button').attr('rel', 0);
		$('#type_table').append(clone);
	});
	
	//保存
	$('#save').click(function (){
		var data = $('#type_form').serialize();
		$.post('?act=save', data, function(json){
			if(json.state) {
				$('td>button[rel=0]').each(function (i, e){
					$(e).closest('tr').find('input:first').val(json.info[i]);
					$(e).attr('rel', json.info[i]);
				});
				alert('保存成功');
			}else{
				alert('保存失败');
			}
		}, 'json');
	});
	
	//删除
	$('button.delete').click(function (){
		var id = $(this).attr('rel');
		var tr = $(this).closest('tr');
		if(id == 0) {
			//新增过,未保存的删除时直接移除
			return tr.remove();
		}
		
		if(!confirm('确定删除分类：'+tr.find('input.title').val() + '？')) {
			return false;
		}
		
		//第一次删除
		del(id, tr);
	});
});
//删除分类,第一次删除,如果其下有活动会提醒一次,用户确认后发起强制删除
function del(id, tr, force)
{
	var api = '?act=delete&id='+id;
	if(force) api += '&force=1';
	$.getJSON(api, function (json){
		if(json.state == 1) {
			tr.remove();
		}else if(json.state == 2) {
			if(!confirm(json.info)) {
				return false;
			}
			//第二次,强制删除
			if(!force) del(id, tr, 1);
		}else {
			alert('删除失败:'+json.info)
		}
	});
}
</script>
{/literal}
{include file="$tpl_dir_base/footer.tpl"}
