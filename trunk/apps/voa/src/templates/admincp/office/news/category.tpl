{include file="$tpl_dir_base/header.tpl"}
<div class="alert">
	<button type="button" class="close" data-dismiss="alert">×</button><strong>提示：</strong>可最多创建3个一级菜单,每个一级菜单下最多可创建 5 个二级菜单。发布最多24小时内会更新到新的菜单。
	</div>
<link href="{$CSSDIR}category.css" rel="stylesheet" type="text/css" />
<form class="form-horizontal font12" role="form" method="post" action="{$form_action_url}" id="cat_form">
	<input type="hidden" name="formhash" value="{$formhash}" />
<div class="panel table-light" id="inspect-config-list">
	<div class="category">		
	    <div class="hd cf">
	        <div class="fold"></div>
	        <div class="order">显示排序</div>
	        <div class="name">菜单名称</div>
	        <div class="operation">操作</div>
	    </div>
		<dl class = "cate-item">
			<dt class="cf first" index="{$fixed['nca_id']}">

			<div class="fold"></div>
			<div class="order"><input type="text" name="fixed[orderid]" class="text input-mini" value="{$fixed['orderid']}" disabled/></div>
			<div class="name">
				<span class="tab-sign"></span>
				<input type="text" name="fixed[name]" class="text" value="{$fixed['name']}"  required="required"/>
				<input type="checkbox" name="new" class="add-sub-cate" id="fixed"  {if $fixed['checked'] == 1}checked{/if} value="fix"> 启用
			</div>
			</dt>
		</dl>
	    <dl class="cate-item" id="item_list_dl" index="{count($categories)}">

	    	{foreach $categories as $_k => $_v}
	        <dt class="cf first" index="{$_v['nca_id']}">
	            <div class="btn-toolbar opt-btn cf" >
	            	<a href="javascript:;" class='text-danger _delete delete_first'><i class="fa fa-times"></i> 删除</a>
	            </div>
	            <div class="fold"></div>
	            <div class="order"><input type="text" name="cat[{$_v['nca_id']}][orderid]" class="text input-mini" value="{$_v['orderid']}" /></div>
	            <div class="name">
	                <span class="tab-sign"></span>
	                <input type="text" name="cat[{$_v['nca_id']}][name]" class="text" value="{$_v['name']}"  required="required"/>
	                <a class="add-sub-cate add_second_cate" title="添加">
	                    <i class="icon-add"></i>
	                </a>
	                <span class="help-inline msg"></span>
	            </div>
	        </dt>
	        {if !empty($_v['nodes'])}
	        <dd index={count($_v['nodes'])}>
	        	{foreach $_v['nodes'] as $_ck => $_cv}
	            <dl class="cate-item">
	                <dt class="cf">
	                    <div class="btn-toolbar opt-btn cf" >
	                    	<a href="javascript:;" class='text-danger _delete delete_second'><i class="fa fa-times"></i> 删除</a>
	                    </div>
	                    <div class="fold"><i></i></div>
	                    <div class="order text-right"><input type="text" name="cat[{$_v['nca_id']}][nodes][{$_cv['nca_id']}][orderid]" class="text input-mini" value="{$_cv['orderid']}" /></div>
	                    <div class="name">
	                        <span class="tab-sign"></span>
	                        <input type="text" name="cat[{$_v['nca_id']}][nodes][{$_cv['nca_id']}][name]" class="text" value="{$_cv['name']}"  required="required"/>
	                        <span class="help-inline msg"></span>
	                    </div>
	                </dt>
	            </dl>
	        	{/foreach}
	        </dd>
	        {/if}
	        {/foreach}
	    </dl>
	</div>
	<div class="category" id="add_first_cate">
	    <dl class="cate-item">
	        <dd>
	            <dt class="cf">
	                <div class="name">
	                    <span class="new tab-sign"></span>
	                    <a class="add-sub-cate" title="添加" >
	                        <i class="icon-add2"></i>
	                        <span class="help-inline msg">添加一级菜单</span>
	                    </a>
	                </div>
	            </dt>
	        </dd>
	    </dl>
	</div>
	<div class="form-group padding-sm" style="margin-bottom:0">
			<div class="col-sm-offset-1 col-sm-2">
				<input type="hidden" name="is_publish" id="is_publish" value="0">
				<button type="submit" class="btn btn-primary" id="save_btn">保存</button>
				&nbsp;&nbsp;
				<button type="submit" class="btn" id="publish_btn">发布</button>
				
			</div>
		</div>
</div>
</form>

<script id="add_first_cate_tpl" type="text/template">
	<dt class="cf first" index="<%= index %>">
        <div class="btn-toolbar opt-btn cf" >
        	<a href="javascript:;" class="text-danger _delete delete_first"><i class="fa fa-times"></i>删除</a>
        </div>
        <div class="fold"></div>
        <div class="order"><input type="text" name="cat[<%= index %>][orderid]" class="text input-mini" value="<%=order %>" /></div>
        <div class="name">
            <span class="tab-sign"></span>
            <input type="text" name="cat[<%= index %>][name]" class="text" value="" placeholder="菜单名称"  required="required"/>
            <a class="add-sub-cate add_second_cate" title="添加">
                <i class="icon-add"></i>
            </a>
            <span class="help-inline msg"></span>
        </div>
    </dt>
</script>
<script id="add_second_cate_tpl" type="text/template">
	<dl class="cate-item">
        <dt class="cf">
            <div class="btn-toolbar opt-btn cf" >
            	<a href="javascript:;" class="text-danger _delete delete_second"><i class="fa fa-times"></i>删除</a>
            </div>
            <div class="fold"><i></i></div>
            <div class="order text-right"><input type="text" name="cat[<%= index %>][nodes][<%= index2 %>][orderid]" class="text input-mini" value="1" /></div>
            <div class="name">
                <span class="tab-sign"></span>
                <input type="text" name="cat[<%= index %>][nodes][<%= index2 %>][name]" class="text" value="" placeholder="菜单名称" required="required"/>
                <span class="help-inline msg"></span>
            </div>
        </dt>
    </dl>
</script>

<script type="text/javascript">
{literal} 
//如果已有三个一级类型，则不显示添加一级类型按钮
function toggle_add_first_cate() {
	var length = $('#item_list_dl').children('dt').length;
		$('#add_first_cate').show();
		if (length > 2) {
			$('#add_first_cate').hide();
		} else {
			$('#add_first_cate').show();
		}

}
//如果已有五个二级类型，则不显示添加二级类型按钮
function toggle_add_second_cate(obj) {
	var length = $(obj).next('dd').children().length;
	if (length > 4) {
		$(obj).find('.add_second_cate').hide();
	} else {
		$(obj).find('.add_second_cate').show();
	}
}
//添加一级类型
function add_first_cate_row(){
	var index = $('#item_list_dl').attr('index');
	index = parseInt(index)+1;
	var length = $('#item_list_dl').children('dt').length;
	var str = txTpl("add_first_cate_tpl",{index: 'new_'+index,order:length});
	$('#item_list_dl').attr('index',index).append(str);
}
//添加二级类型
function add_second_cate_row(obj){
	var index = $(obj).attr('index');
	var next = $(obj).next('dd');
	var index2 = next.attr('index');
	index2 = (index2==undefined) ? 1 : (parseInt(index2)+1)
	var str = txTpl("add_second_cate_tpl",{index: index, index2: 'new_'+index2});	
	if (next.length == 0) {
		$(obj).after('<dd index=1>'+str+'</dd>');
	} else {
		$(obj).next('dd').attr('index',index2).append(str);
	}	
}

(function($) {
	//第一个一级分类移除删除按钮
	$('#item_list_dl').children().first().find('.btn-toolbar').html('');
	//显示隐藏添加一级类型按钮
	toggle_add_first_cate();
	//显示隐藏添加二级类型按钮
	$('#item_list_dl').children('dt').each(function(index,item){
		toggle_add_second_cate(item);
	});
	//添加一级类型
	$('#add_first_cate').bind('click',function(){
		add_first_cate_row();
		toggle_add_first_cate();
	});
	//添加二级类型
	$(document).on('click', '.add_second_cate', function () { 
		var obj = $(this).parents('dt').first();
		add_second_cate_row(obj);
		toggle_add_second_cate(obj);
	});
	//删除一级类型
	$(document).on('click', '.delete_first', function () {
		if (confirm('删除此菜单，本菜单及子菜单下所属公告将变为未分类，确定删除吗？')) {
			var obj = $(this).parents('dt').first();
			obj.next('dd').remove();
			obj.remove();
			toggle_add_first_cate();
		}
		
	});
	//删除二级类型
	$(document).on('click', '.delete_second', function () {
		if (confirm('删除此菜单，本菜单下所属公告将变为未分类，确定删除吗？')) {
			var obj = $(this).parents('dd').first().prev('dt');
			$(this).parents('dl').first().remove();
			toggle_add_second_cate(obj);
		}
	});
	
	$('#publish_btn').bind('click',function(){

		var length = $('#item_list_dl').children('dt').length;
		if($('#fixed').prop('checked') == true){
			if(length > 2){
				alert('最多只能有三个菜单');
				return false;
			}
		}
		$('#is_publish').val(1);
		$('#cat_form').submit();
	});

	$('#cat_form').submit(function(){
		var length = $('#item_list_dl').children('dt').length;
		if($('#fixed').prop('checked') == true){
			if(length > 2){
				alert('最多只能有三个菜单');
				return false;
			}
		}
		return true;
	});

	})(jQuery);
{/literal}
</script>

{include file="$tpl_dir_base/footer.tpl"}
