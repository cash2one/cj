{include file='mobile/header.tpl' css_file='app_sale.css'}
<div class="ui-tab">
    <div class="ui-btn-group-tiled ui-padding-bottom-0 ui-padding-top-0 sale-ui-title">
         <p class="ui-btn-lg ui-btn-primary clearfix btn-width ">
				<span>时间</span>
			<i class="label-tag sale-label-tag label-tag-down"></i>
			<select name="time" id="time">
				<option value ="0">全部</option>
				<option value ="1">本周</option>
				<option value ="2">本月</option>
				<option value ="3">本季度</option>
				<option value ="4">本年</option>
			</select>
        </p>
        <div class="sale-title-border">
            <div class="sale-title-border">
            </div>
        </div>
		 <p class="ui-btn-lg ui-btn-primary clearfix btn-width">
				<span>客户状态</span>
			<i class="label-tag sale-label-tag label-tag-down"></i>
			<select name="types" id="types">
				<option value ="0">全部</option>
				{foreach $types as $k => $v}
				<option value ="{$k}">{$v}</option>
				{/foreach}>

			</select>
        </p>
        <div class="sale-title-border">
            <div class="sale-title-border">
            </div>
        </div>
        <a href="/frontend/sale/business_add" id="add" class="ui-btn-lg ui-btn-primary clearfix btn-width">添加商机 +</a>
    </div>
    <ul class="ui-list ui-list-text" id="list_active" >
       
    </ul>
	{literal}
	<script id="list_tpl" type="text/template">
	<%if(_.isEmpty(list)){%>		
		 <section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i></i>
			<p>暂无数据</p>
		</section>
	<%}else{%>
		<%_.each(list, function(item) {%>	
			 <li class="ui-border-t li_href" data-href="/frontend/sale/business_view?bid=<%=item.bid%>">
				<div class="ui-list-info">
					<h4 class="ui-nowrap"><%=item.title%></h4>
					<p><%=item.companyshortname%></p>
				</div>
				<div class="ui-list-right sale-price">
					<h4 class="ui-nowrap"><%=item.amount%>元</h4>
					<p><%=item.name%> <%=item.updated%></p>
				</div>
			</li>
			<%});%>
	<%}%>
	</script>
	{/literal}
    <br />
    <br />
    <br />
	{include file='mobile/navibar.tpl'}

</div>
{literal}
<script type="text/javascript">
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist) {

	var sl = new showlist();
	list(sl, '', '', true);

	$('#time').change(function(){
		var time = $("#time").val();
		var type = $("#types").val();
		list(sl,time, type, false);

	});
	$('#types').change(function(){
		var time = $("#time").val();
		var type = $("#types").val();
		list(sl,time, type, false);

	});
});

//获取数据列表
function list(sl, time, type, is_first) {
	var ajax = {'url': '/api/sale/get/business_list?time='+time+'&type='+type};
	if (is_first) {
		sl.show_ajax(ajax, {
			"dist": $('#list_active'),
			"tpl": $("#list_tpl"),
			"cb": function(dom) {
				//点击跳转到商机详情页
				$("#list_active").find('.li_href').on('click',function(){
					window.location.href= $(this).data('href');
				});
			}
		});
	} else {
		sl.reinit(ajax);
	}
}
</script>
{/literal}
<script type="text/javascript" src="/misc/scripts//h5mod/select.js"></script>
{include file='mobile/footer.tpl'}
