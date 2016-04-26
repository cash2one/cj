{include file='mobile/header.tpl'}

<div class="ui-top-border"></div>
<ul class="ui-list ui-list-text" id="plist" ></ul>

{literal}
<script id="plist_tpl" type="text/template">
<%if (_.isEmpty(data)) {
$('#plist').removeClass();
%>
<section class="ui-notice ui-notice-norecord"> <i></i>
	<p>暂无数据</p>
</section>
<%} else {%>
<%_.each(data, function(dr, index) {
%>
<li class="ui-border-t ui-form-item-link  ul_href" data-eid="<%=dr.eid%>">
    <div class="ui-list-info">
         <h4 class="ui-nowrap"><% if(dr.b_flag ==1 ){%>待领取的快递<%}else{%>已领取的快递<%}%></h4>
         <p><%=dr._created%></p>
         <% if(dr.flag ==3){%>
         <div class="ui-list-right kdzs-ui-sign">
              <h4 class="ui-nowrap">代领</h4>
         </div>
         <%}%>
    </div>
</li>
<%});}%>
</script>
{/literal}



<script>
var listurl = "/api/express/get/list";
{literal}
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist, fz) {
	// 调用 ajax 并显示
	var sl = new showlist();
	sl.show_ajax({'url': listurl}, {
		"dist": $('#plist'), 
		"datakey": "data",
		"cb": function(dom) {
		}
	});
	
	
	//话题详情
	$('#plist').on('click', '.ul_href', function(e) {
 	    var url = "/frontend/express/view?eid="+$(this).data('eid');
	    window.location.href=url;  
	}); 
	
	
});

{/literal}
</script>


{include file='mobile/footer.tpl'}
