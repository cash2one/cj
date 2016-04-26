{include file='mobile/header.tpl'}

<div class="ui-top-border"></div>
<ul class="ui-list ui-list-text" id="list"></ul>
<script type="text/javascript">
	var tc_id = '{$tc_id}';
</script>
{literal}
<script id="list_tpl" type="text/template">
	<%if(_.isEmpty(list)){%>
	<section class="ui-notice ui-notice-norecord">
	     <i></i>
	     <p>暂无数据</p>
	</section>
	<%$('#list').removeClass('ui-list')%>
	<%}else{%>
	<%_.each(list, function(item,index) {%>
	 <li class="ui-border-t ui-form-item-link">
        <a href="/frontend/showroom/detail/?id=<%=item.ta_id%>">
            <div class="ui-list-info">
                <h4 class="ui-nowrap"><%=item.title%><%if(!item.read){%><div class="ui-reddot-s"></div><%}%></h4>
                <p><%=item.updated%></p>
            </div>
        </a>
    </li>
	<%})};%>
</script>
<script type="text/javascript">
require(["zepto", "underscore", "showlist"], function($, _, showlist) {
  	//载入目录列表数据
	var st = new showlist();
	st.show_ajax({'url': '/api/showroom/get/article',data: {tc_id: tc_id}}, {
		'dist': $('#list'),
		"tpl": $("#list_tpl")
	});

});
</script>
{/literal}

{include file='mobile/footer.tpl'}