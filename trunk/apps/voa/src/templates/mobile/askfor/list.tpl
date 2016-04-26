{include file='mobile/header.tpl' navtitle='我的审批'}

<div class="ui-tab">
    <ul class="ui-tab-nav ui-border-b">
        <li class="current">审批中</li>
        <li >已审批</li>
        <li>已驳回</li>
    </ul>
    <ul class="ui-tab-content" style="width:300%" id="list_container">
        <li data-dist="askforing_ul" ><ul class="ui-list ui-list-text" id="askforing_ul"></ul></li>
        <li data-dist="askfored_ul" ><ul class="ui-list ui-list-text" id="askfored_ul"></ul></li>
        <li data-dist="cancel_ul" ><ul class="ui-list ui-list-text" id="cancel_ul"></ul></li>
    </ul>
</div>

{literal}
<script id="list_tpl" type="text/template">
	<%if(_.isEmpty(list)){%>		
		 <section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i></i>
            <p>暂无审批数据</p>
        </section>
	<%}else{%>
	 	<%_.each(list, function(item) {%>
        <li class="ui-border-t" data-href="/askfor/view/<%=item.af_id%>">
	            <div class="ui-list-info">
	                <h4 class="ui-nowrap"><%=item.subject%></h4>
	                <p><%=item._created%></p>
	            </div>
	            <div class="ui-list-right">
	                <h4 class="ui-nowrap"><%=item.afp_username%></h4>
	                <p>
		                <span class="ui-icon ui-icon-approve  <%=item._class%>"></span>
		                <%=item._status%>
	                </p>
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
					"name": "审批中",
					"dist": "askforing_ul",
					"tpl" : '#list_tpl',
					"ajax": {"url": "/api/askfor/get/list","data": {'action': 'askforing'}},
					"cb": function(dom){
						//点击流程跳转到审批详情页面	
						dom.find('.ui-list-text li').bind('click',function(){
						    location.href= $(this).data('href');
					 	});
					}
				}, 
				{
					"name": "已审批",
					"dist": "askfored_ul",
					"tpl" : '#list_tpl',
					"ajax": {"url": "/api/askfor/get/list","data": {'action': 'askfored'}},
					"cb": function(dom){
						//点击流程跳转到审批详情页面	
						dom.find('.ui-list-text li').bind('click',function(){
						    location.href= $(this).data('href');
					 	});
					}
				},
				{
					"name": "已驳回",
					"dist": "cancel_ul",
					"tpl" : '#list_tpl',
					"ajax": {"url": "/api/askfor/get/list","data": {'action': 'refuse_cancel'}},
					"cb": function(dom){
						//点击流程跳转到审批详情页面	
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