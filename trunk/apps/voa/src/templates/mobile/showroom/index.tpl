{include file='mobile/header.tpl'}
	<!--
    <div  class="ui-searchbar-wrap ui-search-bg">
        <div class="ui-searchbar"> <i class="ui-icon-search"></i>
            <div class="ui-searchbar-text">搜索</div>
            <div class="ui-searchbar-input">
                <input value="" type="tel" placeholder="搜索" autocapitalize="on"></div> <i class="ui-icon-close"></i>
        </div>
        <button class="ui-searchbar-cancel">取消</button>
    </div> -->
    
   <div class="ui-form" id="list"></div>

{literal}
<script id="list_tpl" type="text/template">
	<%if(_.isEmpty(list)){%>		
	<section class="ui-notice ui-notice-norecord">
	     <i></i>
	     <p>暂无数据</p>
	</section>
	<%$('#list').removeClass('ui-form');%>
	<%}else{%>
	<%_.each(list, function(item,index) {%>
	 <div class="ui-form-item ui-form-item-order ui-border-b  ui-form-item-link">
        <a href="/frontend/showroom/articles/?tc_id=<%=item.tc_id%>"><%=item.title%></a>
    </div>
	<%})};%>
</script>
<script type="text/javascript">
require(["zepto", "underscore", "showlist"], function($, _, showlist) {		
  	//载入目录列表数据
	var st = new showlist();
	st.show_ajax({'url': '/api/showroom/get/category'}, {
		'dist': $('#list'),
		"tpl": $("#list_tpl"),
	});
	
});
</script>
{/literal}

{include file='mobile/footer.tpl'}