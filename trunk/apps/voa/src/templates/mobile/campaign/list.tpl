{include file='mobile/header.tpl' navtitle="活动中心"}
<link rel="stylesheet" type="text/css" href="/misc/styles/oa_ght.css">
	
	<div  class="ui-searchbar-wrap ui-search-bg">
		<div class="ui-searchbar"> <i class="ui-icon-search"></i>
			<div class="ui-searchbar-text">搜索</div>
			<div class="ui-searchbar-input">
				<input id="keyword" value="" type="text" placeholder="搜索"></div> <i class="ui-icon-close"></i>
			</div>
			<button class="ui-searchbar-cancel">取消</button>
		</div>
		<ul class="ui-list ui-border-tb ui-list-ght ui-list-text">
			<li class="ui-border-t ui-form-item-link">
				<a>
					<div class="ui-list-info">
						<h4>选择活动类型</h4>
					</div>
					<div id="typeValue" class="ui-list-action">全部</div>
					<select id="type">
						<option value="0">全部</option>
					</select>
				</a>
			</li>
		</ul>
		<ul id="list" class="ui-list ui-border-tb ui-list-ght ui-list-text">
			
		</ul>
{literal}
<script id="tpl" type="text/template">
<%_.each(list, function(item) {%>
<li class="ui-border-t" data-id="<%=item.id%>" data-sharetime="<%=item._sharetime%>">
	<div class="ui-list-img">
		<img src="<%=item._cover%>"/>
	</div>
	<div class="ui-list-info">
		<h5 class="ui-nowrap"><%=item.subject%></h5>
		<% if(item.is_custom == 1) {%>
		<p style="clear:both;">
			<button class="ui-btn ui-btn-primary">编辑报名信息</button>
		</p>
		<% } %>
	</div>
</li>
<%});%>
</script>
{/literal}
<script type="text/javascript">
var saleid = "{$saleid}";
{literal}
require(["zepto", "underscore", "showlist"], function($, _, showlist) {
	$('.ui-searchbar').tap(function(){
        $('.ui-searchbar-wrap').addClass('focus');
        $('.ui-searchbar-input input').focus();
    });
    $('.ui-searchbar-cancel').tap(function(){
        $('.ui-searchbar-wrap').removeClass('focus');
    });
    
    var st = new showlist();
    
    st.show_ajax({url: '/api/campaign/get/list'}, {
		dist: $('#list'),
		tpl: $("#tpl"),
		datakey: "list",
		cb: function(dom) {
			if(dom.find('li').length == 0) {
				$('#list').html('<li class="ui-border-t"><em class="mod_empty_notice"><span>没有活动信息</span></em></li>');
			}
		}
	});
    //加载列表
	function load(){
		$('#typeValue').text($('#type option:checked').text());
		$('#list').html('');
		st.reinit({url: '/api/campaign/get/list', data: {typeid:$('#type').val(), keyword: $('#keyword').val()}});
	}
	$('#type, #keyword').change(load);
    
	//加载分类
	$.getJSON('/api/campaign/get/type', function (json){
		for(k in json.result)
		{
			$('#type').append('<option value="'+k+'">'+json.result[k]+'</option>');
		}
	});
	
	//编辑活动信息
	$('#list').on("click","button",function(event){
		var id = $(this).closest('li').data('id');
		location.href = '/frontend/campaign/custom?id=' + id;
	});
	
	//查看列表
	$('#list').on("click", "h5, .ui-list-img",function(event){
		var id = $(this).closest('li').data('id');
		var sharetime = Date.parse(new Date()) / 1000;
		location.href = '/frontend/campaign/view?id=' + id + '&saleid=' + saleid + '&sharetime=' + sharetime;
	});
});
{/literal}
</script>

{include file='mobile/footer.tpl'}