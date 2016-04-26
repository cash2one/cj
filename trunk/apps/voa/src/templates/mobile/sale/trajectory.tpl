{include file='mobile/header.tpl' css_file='app_sale.css'}
<div class="ui-tab">
    <div class="ui-btn-group-tiled ui-padding-bottom-0 ui-padding-top-0 sale-ui-title">
        <p class="ui-btn-lg ui-btn-primary clearfix btn-width ui-selector _sel">
            筛选
            <i id="arrow1" class="label-tag sale-label-tag label-tag-down"></i>
        </p>
        <div class="sale-title-border">
            <div class="sale-title-border">
            </div>
        </div>
        <p id="m_selector" class="ui-btn-lg ui-btn-primary clearfix btn-width">
            <a  class="ui-icon-add" style="width:100%;height:100%;color:#fff;">选人
            <i class="label-tag sale-label-tag label-tag-down"></i></a>
        </p>
        <div class="sale-title-border">
            <div class="sale-title-border">
            </div>
        </div>
        <a href="/frontend/sale/trajectory_add" id="add" class="ui-btn-lg ui-btn-primary clearfix btn-width">新建轨迹 +</a>
    </div>
	<div class="ui-select-content ui-form" style="display: none;">
		<div class="ui-form sale-ui-nowrap">
				{cyoa_select
					title='客户来源'
					attr_name='source'
					attr_options=$sources
					attr_id='select_source'
					div_class='ui-form-item ui-form-item-link'
				}
				{cyoa_select
					title='销售阶段'
					attr_name='stid'
					attr_options=$statuses
					attr_id='select_status'
					div_class='ui-form-item ui-border-t ui-form-item-link'
				}
				<input type="hidden" id="cm_uid" name="cm_uid" value="" />
			</div>
			<div class="ui-btn-group-tiled ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
				<button type="button" id="cancel" name="cancel" class="ui-btn-lg">取消</button>
				<button  id="sure" name="sure" class="ui-btn-lg ui-btn-primary">确定</button>
			</div>
	</div>
	<div id="list_active">
	</div>
	<div>
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
						<h4 class="ui-nowrap"><%=item.company%> </h4>
						<p><%=item.name%> <%=item.time%></p>
					</div>
					<div class="ui-list-right sale-ui-border-r">
						<h4 style="border-color: <%=item.color%>">
							<%=item.source%> 
						</h4>
						<p><%=item.companyshortname%></p>
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
{literal}
<script type="text/javascript">
require(["zepto", "underscore", "showlist", "submit", "addrbook", "frozen"], function($, _, showlist, submit, addrbook) {

	var sl = new showlist();
	list(sl, '', '', '', true);

	$("._sel").tap(function(e) {
		$('.ui-select-content').toggle().css('z-index', 9999);
		$('#arrow1').toggleClass('label-tag-down');
	});

	$("#cancel").on("click", function(e) {
		$('.ui-select-content').toggle();
		$('#arrow1').toggleClass('label-tag-down');
	});

	//确定筛选条件
	$('#sure').on('click', function() {
		var status_id = $("#select_status").val();
		var source_id = $("#select_source").val();
		var cm_uid = $("#cm_uid").val();
		list(sl, status_id, source_id, cm_uid, false);
		$('#cancel').trigger('click');
	});
	
	var ab = new addrbook();
	ab.show({
			"dist": $("#addrbook"),
			"src": $("#m_selector"), // 触发对象,
			"ac" : "byuser",
			"tabs": {
				"user": {
					"name": "选择用户",
					"input": $("#cm_uid")
				}
			},
			"cb": function() {
				var status_id = $("#select_status").val();
				var source_id = $("#select_source").val();
				var cm_uid = $("#cm_uid").val();
				list(sl, status_id, source_id, cm_uid, false);
			}
	});
});

//获取数据列表
function list(sl, status_id, source_id, cm_uid, is_first) {
	var ajax = {'url': '/api/sale/get/trajectory_list?status_id='+status_id+'&source='+source_id+'&m_uid='+cm_uid};
	if (is_first) {
		sl.show_ajax(ajax, {
			"dist": $('#list_active'),
			"tpl": $("#list_tpl")
		});
	} else {
		sl.reinit(ajax);
	}
}
</script>
{/literal}
{include file='mobile/footer.tpl' SHOWIMG=1}
