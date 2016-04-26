{include file='mobile/header.tpl' navtitle='会议列表'}
<style>
p.right {
	width: 100px;
	float: right;
	text-align: right;
	color: #4f9c9e;
	font-size: 12px;
	margin-right: 0;
}
p.right.gray {
	color: #999;
	font-size: 14px;
}
.ui-list-info {
	padding-right: 0!important;	
}
</style>
<div class="ui-tab" id="oa-hy-list">
    <ul class="ui-tab-nav ui-border-b">
        <li class="current">待参加</li>
        <li>已结束</li>
    </ul>
    <ul class="ui-tab-content" style="width:300%" id="list_container">
        <li data-dist="join_ul" ><ul class="ui-list ui-list-text" id="join_ul"></ul></li>
        <li data-dist="fin_ul" ><ul class="ui-list ui-list-text" id="fin_ul"></ul></li>
    </ul>
</div>

{literal}

	
    
<script id="list_tpl" type="text/template">
	<%if(_.isEmpty(list)){%>		
		 <section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i></i>
            <p>暂无会议数据</p>
        </section>
	<%}else{%>
	 	<%_.each(list, function(item) {%>
        <li class="ui-border-t" data-href="/meeting/view/<%=item.id%>">
            <div class="ui-list-thumb">
		        <div class="ui-data-bg">
		            <p><%=item.y%>-<%=item.m%></p>
		            <p class="date"><%=item.d%></p>
		        </div>
		    </div>
		    <div class="ui-list-info">
		        <h4 class="ui-nowrap">
		        	<%=item.subject%>
		        	<p class="right gray">
		                <%=item.username%>
	                </p>
		        </h4>
		        <div>
		        	<%=item.room%>  <%=item.bthi%> - <%=item.endhi%>
		        	<p class="right">
		                <span class="ui-icon ui-icon-approve <%=item._class%>"></span>
		                <%=item._mem_status%>
	                </p>
		        </div>
		    </div>
        </li>
        <%});%>
    <%}%>
</script>
<script type="text/javascript">
require(["zepto", "underscore", "showtabs", "frozen"], function($, _, showtabs) {	
	$(function($){
		var st = new showtabs();			
	    st.show({
			"dist": $('#list_container'),
			"tabs": [
				 {
					"name": "待参加",
					"dist": "join_ul",
					"tpl" : '#list_tpl',
					"ajax": {"url": "/api/meeting/get/list","data": {'action': 'join'}},
					"cb": function(dom){
						dom.find('.ui-list-text li').bind('click',function(){
						    location.href= $(this).data('href');
					 	});
					}
				}, 
				{
					"name": "已结束",
					"dist": "fin_ul",
					"tpl" : '#list_tpl',
					"ajax": {"url": "/api/meeting/get/list","data": {'action': 'fin'}},
					"cb": function(dom){
						dom.find('.ui-list-text li').bind('click',function(){
						    location.href= $(this).data('href');
					 	});
					}
				}
			]
		});
	});
});
</script>
 {/literal}   
 
{include file='mobile/footer.tpl'}
