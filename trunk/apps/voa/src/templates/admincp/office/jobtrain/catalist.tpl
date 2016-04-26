{include file="$tpl_dir_base/header.tpl"}
<link href="{$CSSDIR}category.css" rel="stylesheet" type="text/css" />


<div class="panel table-light" id="inspect-config-list">
	<div class="category">

		<div class="hd cf">
	        <div class="fold"></div>
	        <div class="order">显示排序</div>
	        <div class="name">分类名称</div>
	        <div class="operation">操作</div>
	    </div>

	    <dl class="cate-item" id="item_list_dl" index="{count($catas)}">
	    	{foreach $catas as $_k => $_v}
	        <dt class="cf first" index="{$_v['id']}">
	            <div class="btn-toolbar opt-btn cf" >
	            	{$base->linkShow($cataedit_url, $_v['id'], '编辑', 'fa-edit')}
					{$base->linkShow($cataview_url, $_v['id'], '查看详情', 'fa-eye')}
	            	{$base->linkShow($catadel_url, $_v['id'], '删除', 'fa-times', 'class="text-danger _delete"')}
	            </div>
	            <div class="fold"></div>
	            <div class="order">{$_v['orderid']}</div>
	            <div class="name">
	                <span class="tab-sign"></span>{$_v['title']}
	                <a href="{$cataadd_url}?pid={$_v['id']}" class="add-sub-cate add_second_cate" title="添加">
	                    <i class="icon-add"></i><span class="help-inline msg" style="display:none"> 添加二级分类</span>
	                </a>
	                <span class="help-inline msg"></span>
	            </div>
	        </dt>
	        {if !empty($_v['childs'])}
	        <dd index={count($_v['childs'])}>
	        	{foreach $_v['childs'] as $_ck => $_cv}
	            <dl class="cate-item">
	                <dt class="cf">
	                    <div class="btn-toolbar opt-btn cf" >
	                    	{$base->linkShow($cataedit_url, $_cv['id'], '编辑', 'fa-edit')}
	                    	{$base->linkShow($cataview_url, $_cv['id'], '查看详情', 'fa-eye')}
	                    	{$base->linkShow($catadel_url, $_cv['id'], '删除', 'fa-times', 'class="text-danger _delete"')}
	                    </div>
	                    <div class="fold"><i></i></div>
	                    <div class="order text-right">{$_cv['orderid']}</div>
	                    <div class="name">
	                        <span class="tab-sign"></span>{$_cv['title']}
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


	<div class="category">
	    <dl class="cate-item">
	        <dd>
	            <dt class="cf">
	                <div class="name">
	                    <span class="new tab-sign"></span>
	                    <a class="add-sub-cate" href="{$cataadd_url}?pid=0" title="添加" >
	                        <i class="icon-add2"></i>
	                        <span class="help-inline msg">添加一级分类</span>
	                    </a>
	                </div>
	            </dt>
	        </dd>
	    </dl>
	</div>


</div>
<script type="text/javascript">
$('._delete').bind('click', function () {
	if (!confirm("您确认要删除吗？")) {
        return false;
    }else{
    	return true;
    }
});

$('.add_second_cate').hover(
  function () {
    $(this).children("span").show();
  },
  function () {
    $(this).children("span").hide();
  }
);

</script>
{include file="$tpl_dir_base/footer.tpl"}