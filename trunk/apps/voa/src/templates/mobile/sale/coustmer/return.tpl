{include file='mobile/header.tpl' css_file='app_sale.css'}
<div class="ui-tab">
    <ul class="ui-tab-nav ui-border-b">
        <li id="coustmer_view" >资料</li>
        <li class="current">回访</li>
    </ul>
    <ul class="ui-tab-content">
        <li  id="list_active">
        </li>
    </ul>
	<script id="list_tpl" type="text/template">
		<% if(_.isEmpty(list)){ %>
			 <section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i></i>
				<p>暂无数据</p>
			</section>
		<% }else{ %>
			<% _.each(list, function(item) { %>	
				<ul class="ui-list ui-list-text " data-href="">
				<li class="ui-border-b" li_href">
					<div class="ui-list-info">
						<h4 class="ui-nowrap"><%=item.companyshortname%> </h4>
						<p><%=item.name%> <%=item.time%></p>
					</div>
					<div class="ui-list-right sale-ui-border-r">
						<h4 style="border-color: <%=item.color%>">
							<%=item.source%> 
						</h4>
					</div>
				</li>
			<li>
					<h4><%=item.content%></h4>
				</li>
					<% if(!_.isEmpty(item.image)){ %>
						<div class="_show_gallery" >
							<div class="upload clearfix" >
							<% _.each(item.image, function(image) { %>
								<div class="ui-badge-wrap">
									<img src="<%=image._thumb%>" data-big="<%=image._big%>" alt="" border="0" />
								</div>
							<%});%>
							</div>
						</div>
					<%}%>
				<li>
					<i class="ui-icon sale-ui-address"></i>
					<p class="sale-ui-address-p"><%=item.address%></p>
				</li>
			</ul>
				<%});%>
		<%}%>
</script>
    <br />
    <br />
    <br />
{include file='mobile/navibar.tpl'}
</div>
<input type="hidden" id="scid" name="scid" value="{$scid}" />
{literal}
<script type="text/javascript">
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist) {

	var scid = $("#scid").val();
	sl = new showlist();
	sl.show_ajax({'url': '/api/sale/get/trajectory_list?scid='+scid}, {
		"dist": $('#list_active'), 
		"tpl": $("#list_tpl"), 
		"cb": function(dom) {
			
		}
	});
	$("#coustmer_view").on("click", function(e) {
		window.location.href = "/frontend/sale/coustmer_view?scid="+scid;
	});
});
</script>
{/literal}
{include file='mobile/footer.tpl' SHOWIMG=1}