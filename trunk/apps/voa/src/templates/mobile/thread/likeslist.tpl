{include file='mobile/header.tpl'}

<div id="bbs-follow-detaile">
	<ul class="ui-list ui-list-text ui-border-tb">
		<li class="ui-border-t ui-list-item-link" id="view_li"
			data-tid="{$thread['tid']}">
			<p class="ui-nowrap">{$thread['subject']}</p>
		</li>
	</ul>

	<ul class="ui-list ui-border-no ui-list-follow" id="plist"></ul>

	{literal}
    <script id="plist_tpl" type="text/template">
    <%if (_.isEmpty(data)) {
        $('#plist').removeClass();
    %>
    <%} else {%>
    <%_.each(data, function(dr, index) {
        $('#drlist').addClass('ui-form ui-border-t');
    %>
    <li <%if (data.length > 1) {if(index != 0){ %>  class ="ui-border-t"   <%}}%> >
        <div class="ui-avatar-s">
             <img src="<%=dr.face%>" />
        </div>
        <div class="ui-list-info">
             <h4 class="ui-nowrap"><%=dr.username%></h4>
        </div>
        <div class="ui-list-action"><%=dr._created%></div>
    </li> 
    <%});}%>
	</script>
	{/literal}
</div>

<script>
var listurl = "/api/thread/get/likeslist?tid={$thread['tid']}";
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
	$('#view_li').on('click', function(e) {
		 var url = "/frontend/thread/viewthread/?&tid="+$(this).data('tid');
	 	 window.location.href=url;  

	});
});

{/literal}
</script>

{include file='mobile/footer.tpl'}
