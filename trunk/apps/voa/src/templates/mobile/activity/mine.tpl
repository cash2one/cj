{include file='mobile/header.tpl' css_file='app_activity.css'}
<div class="ui-tab">
    <ul class="ui-list ui-list-text"  id="list_active"></ul>
</div>
{literal}
<script id="list_tpl" type="text/template">
<%if(_.isEmpty(data)){%>		
	 <section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i></i>
		<p>暂无活动数据</p>
	</section>
<%}else{%>
	<%_.each(data, function(item) {%>	
        <li class="ui-border-t li_href" data-href="/frontend/activity/view/?acid=<%=item.acid%>">
            <div class="ui-list-info">
                <h4 class="ui-nowrap"><%=item.title%> </h4>
                <p><%=item.uname%> <%=item.updated%></p>
            </div>
            <div class="ui-list-right">
                <h4>
					<%if(item.ctype1==1){%>
                    <span class="ui-reddot hd-ui-reddot hd-ui-reddot-blue"></span>
					<%}%>
					<%if(item.ctype1==2){%>
                    <span class="ui-reddot hd-ui-reddot hd-ui-reddot-yellow"></span>
					<%}%>
					<%if(item.ctype1==3){%>
                    <span class="ui-reddot hd-ui-reddot hd-ui-reddot-grey"></span>
					<%}%>
					<%=item.ctype%> 
                </h4>
                <p class="ui-nowrap"><%if(item.np==0){%>(<%=item.anp%>)<% }else{ %>(<%=item.anp%>/<%=item.np%>)<%}%></p>
            </div>
        </li>
		<%});%>
<%}%>
</script>

<script type="text/javascript">
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist) {

	sl = new showlist();
	sl.show_ajax({'url': '/api/activity/get/list?action='+ac}, {
		"dist": $('#list_active'), 
		"tpl": $("#list_tpl"),
		"datakey": 'data',
		"cb": function(dom) {
		}
	});
	$("#list_active").on("click", ".li_href", function(e) {
		window.location.href = $(this).data("href");
	});
});
</script>
{/literal}
<script type="text/javascript">
var ac='{$ac}';
</script>
{include file='mobile/footer.tpl'}