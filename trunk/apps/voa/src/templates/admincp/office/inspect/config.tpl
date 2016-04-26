{include file="$tpl_dir_base/header.tpl"}

<link href="{$CSSDIR}category.css" rel="stylesheet" type="text/css" />
<!-- 巡店设置 -->
<div class="panel table-light" id="inspect-config-list">
	<div class="panel-heading clearfix">
	    <form class="form-inline" role="form">
	        <div class="form-group zoom" >
	            <a href="javascript:;" title="全部展开">全部展开</a>
	            |
	            <a href="javascript:;" title="全部收起">全部收起</a>
	        </div>
	        <!--  
	        <div class="form-group">
	            <label class="sr-only">搜索关键词</label>
	            <input type="" class="form-control" id="" placeholder="输入关键词">
	        </div>
	        
	        <button type="submit" class="btn btn-info">
	            <span class="fa  fa-search form-control-feedback"></span>
	            搜索
	        </button>
	        -->
	    </form>
	</div>

	<div class="category">
	    <div class="hd cf">
	        <div class="fold"></div>
	        <div class="order">显示排序</div>
	        <div class="name">巡店项目</div>
	        <div class="operation">操作</div>
	    </div>
	    <dl class="cate-item" id="item_list_dl">
	    	{foreach $p2c[0] as $_k => $_v}
	        <dt class="cf first">
	            <div class="btn-toolbar opt-btn cf" >
	            	{$base->linkShow('javascript:;', '', '删除', 'fa-times', "class='text-danger _delete' data-url='$deleteUrlBase&id=$_v'")}
	                {$base->linkShow($editUrl, "&id=$_v", '编辑', 'fa-edit', '')}
	            </div>
	            <div class="fold"><i class="icon-unfold"></i></div>
	            <div class="order"><input type="text" name="sort" class="text input-mini" value="{$list[$_v]['insi_ordernum']}" /></div>
	            <div class="name">
	                <span class="tab-sign"></span>
	                <input type="text" name="catename" class="text" value="{$list[$_v]['insi_name']}" />
	                <a class="add-sub-cate" title="添加" href="{$editUrl}&pid={$_v}">
	                    <i class="icon-add"></i>
	                </a>
	                <span class="help-inline msg"></span>
	            </div>
	        </dt>
	        {if !empty($p2c[$_v])}
	        <dd>
	        	{foreach $p2c[$_v] as $_ck => $_cv}
	            <dl class="cate-item">
	                <dt class="cf">
	                    <div class="btn-toolbar opt-btn cf" >
	                    	{$base->linkShow('javascript:;', '', '删除', 'fa-times', "class='text-danger _delete' data-url='$deleteUrlBase&id=$_cv'")}
	                        {$base->linkShow($editUrl, "&pid=$_v&id=$_cv", '编辑', 'fa-edit', '')}
	                    </div>
	                    <div class="fold"><i></i></div>
	                    <div class="order"><input type="text" name="sort" class="text input-mini" value="{$list[$_cv]['insi_ordernum']}" /></div>
	                    <div class="name">
	                        <span class="tab-sign"></span>
	                        <input type="text" name="catename" class="text" value="{$list[$_cv]['insi_describe']}" />
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
	                    <a class="add-sub-cate" title="添加" href="{$editUrl}">
	                        <i class="icon-add2"></i>
	                        <span class="help-inline msg">添加一级目录</span>
	                    </a>
	                </div>
	            </dt>
	        </dd>
	    </dl>
	</div>
</div>

<script>
{literal} 
(function($) {
	// 分类展开收起
	$("#item_list_dl dd").prev().find(".fold i").addClass("icon-unfold").click(function() {
		var self = $(this);
		if(self.hasClass("icon-unfold")) {
		    self.closest("dt").next().slideUp("fast", function() {
		        self.removeClass("icon-unfold").addClass("icon-fold");
		    });
		} else {
		    self.closest("dt").next().slideDown("fast", function() {
		        self.removeClass("icon-fold").addClass("icon-unfold");
			});
	    }
	});
	
	// 删除
	$('#item_list_dl a._delete').click(function() {
		var self = $(this);
		if (!confirm('确定要删除该评分项吗?')) {
			return true;
		}
		
		window.location.href = self.data('url');
	});
})(jQuery);
{/literal}
</script>

{include file="$tpl_dir_base/footer.tpl"}
