{include file='mobile/header.tpl' css_file='app_sale.css'}

<div class="ui-tab">
    <div class="ui-btn-group-tiled ui-padding-bottom-0 ui-padding-top-0 sale-ui-title">
		<p class="ui-btn-lg ui-btn-primary clearfix btn-width btn-width-customer ui-selector _sel">
            筛选
            <i id="arrow1" class="label-tag sale-label-tag label-tag-down"></i>
        </p>
        <div class="sale-title-border">
            <div class="sale-title-border">
            </div>
        </div>
        <a id="create_customer" href="/frontend/sale/coustmer_add" class="ui-btn-lg ui-btn-primary clearfix btn-width btn-width-customer">新建客户 +</a>
    </div>
	<div class="ui-select-content ui-form" style="display: none">
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
				<input type="hidden" id="uids" name="uids" value="" />
			</div>
			<div class="ui-btn-group-tiled ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
				<button type="button" id="cancel" name="cancel" class="ui-btn-lg">取消</button>
				<button  id="sure" name="sure" class="ui-btn-lg ui-btn-primary">确定</button>
			</div>
	</div>
    <ul class="ui-list ui-list-text" id="list_active"></ul>
    <br />
     <br />
    <br />
	{include file='mobile/navibar.tpl'}

</div>
{literal}
<script id="list_tpl" type="text/template">
<%if(_.isEmpty(list)){%>		
	 <section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i></i>
		<p>暂无数据</p>
	</section>
<%}else{%>
	<%_.each(list, function(item) {%>	
        <li class="ui-border-t li_href" data-href="/frontend/sale/coustmer_view?scid=<%=item.scid%>">
            <div class="ui-list-info">
                <h4 class="ui-nowrap"><%=item.company%> </h4>
                <p><%=item.name%> <%=item.updated%></p>
            </div>
            <div class="ui-list-right">
                <h4>		
                    <span class="sale-ui-reddot" style="background-color: <%=item.color%>"></span>
			<%=item.ctype%> 
                </h4>  
            </div>
        </li>
		<%});%>
<%}%>
</script>

<script type="text/javascript">
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist) {
	var sl = new showlist();

	//初始化数据列表
	list(sl, '', '', true);
	//筛选条件
	$("._sel").tap(function(e) {
		$('.ui-select-content').toggle().css('z-index', 9999);
		$('#arrow1').toggleClass('label-tag-down');
	});
	//取消筛选条件
	$("#cancel").on("click", function(e) {
		$('.ui-select-content').toggle();
		$('#arrow1').toggleClass('label-tag-down');
	});
	//确定筛选条件
	$('#sure').on('click', function(){
		var status_id = $('#select_status').val();
		var source_id = $('#select_source').val();

		list(sl, status_id, source_id, false);

		$('#cancel').trigger('click');
	})
});

//获取数据列表
function list(sl, status_id, source_id, is_first) {
	var ajax = {'url': '/api/sale/get/coustmer_list?status='+status_id+'&source='+source_id};
	if (is_first) {
		sl.show_ajax(ajax, {
			"dist": $('#list_active'),
			"tpl": $("#list_tpl"),
			"cb": function(dom) {
				//点击跳转到客户详情页
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
