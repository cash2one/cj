{include file='mobile/header.tpl' css_file='app_news.css'}

<div class="ui-top-content"></div>
<div class="ui-form cate-form" id="templates" ></div>

<script type="text/javascript">
var nca_id = '{$nca_id}';
</script>
{literal}
<script id="templates_tpl" type="text/template">
	<%if(_.isEmpty(list)){%>
		<section class="ui-notice ui-notice-norecord">
			<i></i>
			<p>暂无数据</p>
		</section>
	<%$('#templates').removeClass('ui-list');%>
	<%}else{%>
	<%_.each(list, function(item,index) {%>
		<div class="ui-form-item ui-form-item-order ui-border-b  ui-form-item-link">
			<a href="/frontend/news/category/?nca_id=<%=item.nca_id%>"><%=item.name%></a>
		</div>
	<%})};%>
</script>


<script type="text/javascript">
require(["zepto", "underscore", "showlist"], function($, _, showlist) {		
	//载入流程列表数据

	var st = new showlist();
	st.show_ajax({'url': '/api/news/get/categories',"data": {'nca_id': nca_id}}, {
		'dist': $('#templates'),
		"tpl": $("#templates_tpl")
	});

});
</script>
{/literal}

{include file='mobile/footer.tpl'}