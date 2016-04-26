{include file='mobile_v1/header_fz.tpl'}

<div class="ui-list-goods">
	<div class="ui-selector ui-selector-line"><div class="ui-selector-content">
		<form id="frmchgclassid" method="post" action="/frontend/travel/cplist">
		<ul>
			<li class="ui-selector-item ui-border-b ui-center">
				产品列表 <i class="ui-icon ui-icon-atalog ui-list-action"></i>
				<select name="classid" id="classid">
				<option value="0">全部</option>
				{foreach $goodsclass as $_v}
				{if !empty($_v['classid'])}
				<option value="{$_v['classid']}"{if $classid == $_v['classid']} selected{/if}>{$_v['classname']}</option>
				{/if}
				{/foreach}
				</select>
			</li>
		</ul>
		</form>
	</div></div>
	<ul class="ui-grid-halve" id="goods"></ul>
</div>

{literal}
<script id="goods_tpl" type="text/template">
<%if (_.isEmpty(data)) {
$('#goods').removeClass();
%>
<section class="ui-notice ui-notice-norecord"> <i></i>
	<p>暂无数据</p>
</section>
<%} else {%>
<%_.each(data, function(item) {
$('#goods').addClass('ui-grid-halve');
%>
<li>
	<a href="/frontend/travel/cpviewgoods/goodsid/<%=item.dataid%>">
	<div class="ui-list-goods">
		<div class="ui-grid-halve-img">
			<label class="price">&#165; <%=item.price%></label>
			<span style="background-image:url(<%=item.cover%>)"></span>
		</div>
		<div class="title"><%=item.subject%></div>
	</div>
	</a>
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
	sl.show_ajax({'url': '/api/travel/get/goods', 'data': data}, {
		"dist": $('#goods'),
		"datakey": "data",
		"cb": function(dom) {
			// do nothing.
		}
	});

	// 选择事件
	$("#classid").on("change", function(e) {
		$("#frmchgclassid").submit();
	});
});
{/literal}
</script>

{include file='mobile_v1/footer_fz.tpl'}