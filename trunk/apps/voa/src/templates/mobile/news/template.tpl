{include file='mobile/header.tpl' css_file='app_news.css'}


<div class="news-menu-model">自主新建</div>
<ul class="ui-list ui-list-link ui-list-text ui-border-tb">
	<li class="ui-border-t ui-list-item-link" data-href="/frontend/news/add">
		<div class="ui-list-thumb">
			<span>0</span>
		</div>
		<div class="ui-list-info">
			<h4>自定义添加</h4>
		</div>
	</li>
</ul>

<div class="news-menu-model">选择模板</div>
<div id="templates">

</div>


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
	<ul class="ui-list ui-list-link ui-list-text ui-border-tb" >
		<li class="ui-border-t ui-list-item-link" data-href= "/frontend/news/add/?tem_id=<%=item.ne_id%>">
			<div class="ui-list-thumb">
				<span><%=item.key%></span>
			</div>
			<div class="ui-list-info">
				<h4><%=item.title%></h4>
			</div>
		</li>
	</ul>
	<%})};%>
</script>
<script type="text/javascript">
	require(["zepto", "underscore", "showlist"], function($, _, showlist) {
		//载入流程列表数据
		var st = new showlist();
		st.show_ajax({'url': '/api/news/get/templatelist'}, {
			'dist': $('#templates'),
			'datakey': 'list',
			'cb': function(dom) {
				//点击流程跳转到发送审批页面
				dom.find('.ui-list-text li').on('click',function(){
					location.href= $(this).data('href');
				});
			},
			"tpl": $("#templates_tpl")
		});
		$('.ui-list-text li').on('click', function(){
			location.href= $(this).data('href')
		})
	});
</script>
{/literal}

{include file='mobile/footer.tpl'}