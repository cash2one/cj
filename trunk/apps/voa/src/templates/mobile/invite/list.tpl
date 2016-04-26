{include file='mobile/header.tpl' css_file='' navtitle='我的邀请'}

    <style type="text/css">
        .ui-btn-green{
           background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0.5, rgba(17, 165, 17, 0.84)), to(rgba(17, 165, 17, 0.84)));
        }
        .ui-btn-danger{
            background-color: #f75549;
            background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0.5,#fc6156),to(#f75549));
        }
    </style>
<div class="ui-tab">
    <ul class="ui-tab-nav ui-border-b">
        <li class="current">审批邀请</li>
        <li>直接邀请</li>
    </ul>
    <ul class="ui-tab-content" style="width:300%" id="list_container">
        <li data-dist="check_ul"><ul class="ui-list ui-list-text" id="check_ul"></ul></li>
        <li data-dist="normal_ul"><ul class="ui-list ui-list-text" id="normal_ul"></ul></li>
    </ul>
</div>

<script type="text/javascript">
    var domain = "{$domain2}";
</script>

{literal}
<script id="templates_tpl" type="text/template">
	<%if(_.isEmpty(list)){%>
		<section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i style="background-image:url(<%=domain%>/misc/images/nocontent.png);height:128px;"></i>
    <p>暂无邀请人员数据</p>
		</section>
	<%}else{%>
	<%_.each(list, function(item,index) {%>
		<li class="ui-border-t ui-form-item-link" data-href="/frontend/invite/view/?per_id=<%=item.per_id%>">
			
				<div class="ui-list-info">
					<h4 class="ui-nowrap"><%=item.name%></h4>       
					<p><%=item.updated%>&nbsp;
                        <%if(_.isEmpty(item.gz_state)){%>
                            <button class="ui-btn ui-btn-<%=item.color%>"><%=item.approval_state%></button>
                        <%}else{%>
                            <button class="ui-btn ui-btn-<%=item.color_gz%>"><%=item.gz_state%></button>    
                         <%}%>		
					</p>
				</div>		
		</li>
	<%})};%>
</script>

<script type="text/javascript">
    require(["zepto", "underscore", "showtabs", "frozen"], function($, _, showtabs) {
        $(function($){
            var st = new showtabs();
            st.show({
                "dist": $('#list_container'),
                "tabs": [
                    {
                        "name": "审批邀请",
                        "dist": "check_ul",
                        "tpl" : '#templates_tpl',
                        "datakey" : "list",
                        "ajax": {"url": "/api/invite/get/list","data": {'approval_state': 0}},
                        "cb": function(dom){
                            
                            dom.find('.ui-list-text li').bind('click',function(){
                                location.href= $(this).data('href');
                            });
                        }
                    },
                    {
                        "name": "直接邀请",
                        "dist": "normal_ul",
                        "tpl" : '#templates_tpl',
                        "datakey" : "list",
                        "ajax": {"url": "/api/invite/get/list","data": {'approval_state': 3}},
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