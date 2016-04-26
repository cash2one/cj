{include file='mobile/header.tpl' navtitle='审批记录'}

    <div class="ui-tab">
        <ul class="ui-tab-nav ui-border-b">
            <li class="current">未处理</li>
            <li>已处理</li>           
        </ul>
         <ul class="ui-tab-content" style="width:300%" id="list_container">
	        <li data-dist="approving_ul" ><ul class="ui-list ui-list-text" id="approving_ul"></ul></li>
	        <li data-dist="approved_ul" ><ul class="ui-list ui-list-text" id="approved_ul"></ul></li>
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
                <p><%=item.username%>&nbsp;&nbsp;<%=item._created%></p>
            </div>
            <div class="ui-list-right">
                <h4 class="ui-nowrap"></h4>
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
					"name": "未处理",
					"dist": "approving_ul",
					"tpl" : '#list_tpl',
					"ajax": {"url": "/api/askfor/get/list","data": {'action': 'approving'}},
					"cb": function(dom){
						//点击流程跳转到审批详情页面	
						dom.find('.ui-list-text li').bind('click',function(){
						    location.href= $(this).data('href');
					 	});
					}
				}, 
				{
					"name": "已处理",
					"dist": "approved_ul",
					"tpl" : '#list_tpl',
					"ajax": {"url": "/api/askfor/get/list","data": {'action': 'approved'}},
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