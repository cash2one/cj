{include file='mobile/header.tpl' css_file='app_news.css'}

<div class="ui-top-content"></div>
<div class="ui-searchbar-wrap ui-border-b">
	<div class="ui-searchbar ui-border-radius">
		<i class="ui-icon-search"></i>
		<div class="ui-searchbar-text">请搜索公告标题</div>
		<div class="ui-searchbar-input"><input value="{$keyword}" name="keyword" type="text" placeholder="请搜索公告标题" autocapitalize="off"></div>
		<i class="ui-icon-close"></i>
	</div>
	<button class="ui-searchbar-cancel">取消</button>
</div>
<ul class="ui-list ui-list-text" id="templates"></ul>
<div class="news-bottom"></div>
<script type="text/javascript">
var nca_id = '{$nca_id}';
var i = 0;
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
		<%console.log(list)%>
	<%_.each(list, function(items,indexs) {%>
		<% if(!_.isArray(items)){%>
			<%if(i>0){%>
				<li class="ui-gap"></li>
			<%}%>
			<li class="ui-form-item-link">
				<a href="<% if(items.is_publish == 0){%>/frontend/news/add/?ne_id=<%=items.ne_id%><%}else{%>/frontend/news/view/?ne_id=<%=items.ne_id%><%}%>">
				<div class="ui-list-info">
					<h4 class="ui-nowrap"><%=items.title%><% if(items.is_read == 0){%><div class="ui-reddot-s"></div><%}%></h4>
					<p><%=items.updated%></p>
				</div>
				<% if(items.is_publish == 0){%>
				<div class="ui-list-right news-ui-sign">
					<h4 class="ui-nowrap">草稿</h4>
				</div>
				<%}%>
				</a>
			</li>
		<%}else{%>
			<% j = 1%>
			<li class="ui-more-gap">多条公告</li>
			<%_.each(items, function(item, index){%>
				<li class="<% if(j>1){ %>ui-border-t<%}%> ui-form-item-link">
					<a href="<% if(item.is_publish == 0){%>/frontend/news/add/?ne_id=<%=item.ne_id%><%}else{%>/frontend/news/view/?ne_id=<%=item.ne_id%><%}%>">
						<div class="ui-list-info">
							<h4 class="ui-nowrap"><%=item.title%><% if(item.is_read == 0){%><div class="ui-reddot-s"></div><%}%></h4>
							<p><%=item.updated%></p>
						</div>
						<% if(item.is_publish == 0){%>
							<div class="ui-list-right news-ui-sign">
								<h4 class="ui-nowrap">草稿</h4>
							</div>
						<%}%>

						<div class="ui-num"><%=j%></div>
					</a>
				</li>
				<%j++%>
			<%})%>
		<%}%>
		<%i++%>
	<%})};%>
</script>


<script type="text/javascript">
require(["zepto", "underscore", "showlist", 'newslist'], function($, _, showlist, newslist) {
	//载入流程列表数据
	var newslist = new newslist();
	var keyword = '';
	keyword = newslist.init(window.location.href, 'keyword', nca_id);
	var st = new showlist();
	st.show_ajax({'url': '/api/news/get/list',"data": {'nca_id': nca_id, 'keyword':keyword}}, {
		'dist': $('#templates'),
		"tpl": $("#templates_tpl")
	});


});
</script>
{/literal}

{include file='mobile/footer.tpl'}
