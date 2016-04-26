{include file='mobile/header.tpl' navtitle='选择审批流程'}

<div id="templates"></div>

{literal}
<script id="templates_tpl" type="text/template">
<div id="oa-sp-launch">
	 <ul class="ui-list ui-list-link ui-list-text ui-border-tb">
	    <li class="ui-border-t ui-list-item-link" data-href="/askfor/new/0">
	        <div class="ui-list-thumb">
	            <span>0</span>
	        </div>
	        <div class="ui-list-info">
	            <h4>自由流程</h4>
	        </div>
	    </li>
	</ul>
	<%_.each(data, function(item,index) {%>
	 <ul class="ui-list ui-list-link ui-list-text ui-border-tb">
	    <li class="ui-border-t ui-list-item-link" data-href="/askfor/new/<%=item.aft_id%>">
	        <div class="ui-list-thumb">
	            <span><%=index+1%></span>
	        </div>
	        <div class="ui-list-info">
	            <h4><%=item.name%></h4>
	        </div>
	    </li>
	</ul>
	<%});%>
</div>
</script>


<script type="text/javascript">
require(["zepto", "underscore", "showlist"], function($, _, showlist) {
  	//载入流程列表数据
	var st = new showlist();
	st.show_ajax({'url': '/api/askfor/get/templates'}, {
		'dist': $('#templates'),
		'datakey': 'data',
		'cb': function(dom) {
			//点击流程跳转到发送审批页面
			dom.find('.ui-list-text li').bind('click',function(){
			    location.href= $(this).data('href');
		 	});
		},
		"tpl": $("#templates_tpl")
	});


});
</script>
{/literal}

{include file='mobile/footer.tpl'}