{include file='mobile/header.tpl' navtitle='目录列表' body_id=''}

<div class="ui-top-content"></div>

<script type="text/template" id="tpl-dir">
<% if (_.isEmpty(list)) { %>
	<section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i></i>
		<p>暂无培训目录，请到管理后台创建</p>
	</section>
<% } else { %>
	<% _.each(list, function(item) { %>
		<ul class="ui-list ui-list-link ui-list-text ui-border-tb">
			<li data-href="02.1_发起审批.html">
				<div class="ui-list-info">
					<h4>报名流程</h4>
				</div>
			</li>
		</ul>
	<% }); %>
<% } %>
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

{include file='mobile/footer.tpl'}