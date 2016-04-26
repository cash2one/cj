{include file='mobile/header.tpl' navtitle="客户列表"}
<style>
.ui-text-color-primary a {
	color: #26a7c7!important;
}
</style>
<link rel="stylesheet" type="text/css" href="/misc/styles/oa_ght.css">
	
	<ul class="ui-list ui-border-tb ui-list-ght ui-list-text">
		<li class="ui-border-t ui-form-item-link">
			<a>
				<div class="ui-list-info">
					<h4>所属活动</h4>
				</div>
				<div id="actidValue" class="ui-list-action">全部</div>
				<select id="actid">
					<option value="0">全部</option>
				</select>
			</a>
		</li>
	</ul>
    <div id="list">
		
	</div>
{literal}
<script id="tpl" type="text/template">
<%_.each(list, function(item) {%>
<div class="ui-list-ght-customer">
	<div class="ui-form">
		<div class="ui-form-item ui-form-item-order ui-border-b">
			<label>所属活动</label>
			<p class="ui-text-color-primary"><a href="/frontend/campaign/view?id=<%=item.actid%>&saleid=<%=item._saleid%>&sharetime=<%=item._time%>"><%=item.subject%></a></p>
			<% if(item.is_sign == 0){ %>
				<i class="ui-icon ui-icon-delect" data-id="<%=item.id%>"></i>
			<% } %>
		</div>
		<div class="ui-list-ght-item">
			<div class="ui-list-action ui-label-box">
				<% if(item.is_sign == 1){ %>
					<span class="ui-label ui-label-default ui-label-primary">已签到</span>
				<% }else{ %>
					<span class="ui-label ui-label-default ui-label-warning">已报名</span>
				<% } %>
			</div>
			<div class="ui-form-item ui-form-item-order">
				<label>姓&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;名</label>
				<p><%=item.name%></p>
			</div>
			<div class="ui-form-item ui-form-item-order">
				<label>联系方式</label>
				<p><%=item.mobile%></p>
			</div>
			<div class="ui-form-item ui-form-item-order">
				<label>报名时间</label>
				<p><%=item._created%></p>
			</div>
		</div>
	</div>
</div>
<%});%>
</script>
{/literal}
<script type="text/javascript">
{literal}
require(["zepto", "underscore", "showlist"], function($, _, showlist) {
	
    
    var st = new showlist();
    st.show_ajax({url: '/api/campaign/get/cuserlist'}, {
		dist: $('#list'),
		tpl: $("#tpl"),
		datakey: "list",
		cb: function(dom) {
			if(dom.find('.ui-list-ght-customer').length == 0) {
				$('#list').html('<div class="ui-list-ght-customer">\
									<div class="ui-form">\
										<div class="ui-form-item ui-form-item-order ui-border-b">\
											还没有任何客户\
										</div>\
									</div>\
								</div>');
			}
		}
	});
    //加载列表
	function load(){
		$('#actidValue').text($('#actid option:checked').text());
		st.reinit({url: '/api/campaign/get/cuserlist', data: {actid: this.value}});
	}
	$('#actid').change(load);
    
	//加载活动select
	$.getJSON('/api/campaign/get/simplelist', function (json){
		for(k in json.result)
		{
			//为了保证倒序才这么写,json会自动按正序排,后台设了排序无效
			$('#actid option').eq(0).after('<option value="'+k+'">'+json.result[k]+'</option>');
		}
	});
	
	//删除
	$('#list').on('click', '.ui-icon-delect', function (){
		var id = $(this).data('id');
		if(!confirm("确定要删除吗?")) {
			return false;
		}
		var div = $(this).closest('.ui-list-ght-customer');
		$.getJSON('/api/campaign/get/cuserlist', {act:'delete', id: id}, function (json){
			if(json.errcode == 0) {
				$.tips({content: '删除成功'});
				div.remove();
			}else{
				$.tips({content: '删除失败:'+json.errmsg});
			}
		});
	});
});
{/literal}
</script>

{include file='mobile/footer.tpl'}