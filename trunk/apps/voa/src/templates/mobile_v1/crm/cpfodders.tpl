{include file='mobile_v1/header_fz.tpl'}

<div class="ui-list-goods">
	<div class="ui-selector ui-selector-line">
		<div class="ui-selector-content">
			<form id="frmchgclassid" method="post" action="/frontend/travel/cpfodders">
				<ul>
					<li class="ui-selector-item ui-border-b ui-center">
						素材库 <i class="ui-icon ui-icon-atalog ui-list-action"></i>
						<select name="classid" id="classid">
						<option value="0">全部</option>
						{foreach $goodsclass as $_v}
						{if !empty($_v['pid'])}
						<option value="{$_v['classid']}"{if $classid == $_v['classid']} selected{/if}>{$_v['classname']}</option>
						{/if}
						{/foreach}
						</select>
					</li>
				</ul>
		   </form>
	   </div>
	</div>
	<div class="ui-list-goods">
	<ul class="ui-list" id="goods"></ul>
    </div>
</div>



{literal}
<script id="goods_tpl" type="text/template">
<%if (_.isEmpty(data)) {
$('#goods').removeClass();
%>
<section class="ui-notice ui-notice-norecord"> <i></i>
    <p>暂无素材内容</p>
    <p class="ui-margin-top-10"><small>请联系企业管理进行添加</small></p>
</section>
<%} else {%>
<%_.each(data, function(item) {
$('#goods').addClass('ui-list');
%>
<li class="ui-border-t ul_href"  data-dataid="<%=item.dataid%>" >
    <div class="ui-list-thumb">
         <span style="background-image:url(<%=item.fodder_url%>)"></span>
    </div>
    <div class="ui-list-info ui-padding-right-0">
         <h4><%=item.subject%></h4>
    </div>
</li>
<%});}%>
</script>
{/literal}

<script type="text/javascript">
var classid = '{$classid}';
{literal}
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist, fz) {
	
	// 调用 ajax 并显示
	var sl = new showlist();
	var data = {};
	if (0 < classid) {
		data['classid'] = classid;
	}
	// 调用 ajax 并显示
	var sl = new showlist();
	sl.show_ajax({'url': '/api/travel/get/fodders', 'data': data}, {
		"dist": $('#goods'), 
		"datakey": "data",
		"cb": function(dom) {
		}
	});
	
    //素材详情
	$('#goods').on('click', '.ul_href', function(e) {
 		 var url = "/frontend/travel/cpfoddersview?goodsid="+$(this).data('dataid');
		 window.location.href=url;  
	}); 
	
	// 选择事件
	$("#classid").on("change", function(e) {
		$("#frmchgclassid").submit();
	});
	
});
{/literal}
</script>


{include file='mobile_v1/footer_fz.tpl'}