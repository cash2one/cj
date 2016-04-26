{include file='mobile_v1/header_fz.tpl'}

<div class="ui-list-goods">
	<div class="ui-selector ui-selector-line"><div class="ui-selector-content">
		<form id="frmchgclassid" method="post" action="/frontend/travel/list">
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
	<a href="/frontend/travel/mycart" class="ui-cart"> <i class="ui-icon ui-icon-cart"><span>{$cart_total}</span></i></a>
</div>

{literal}
<script id="goods_tpl" type="text/template">
<%if (!_.isEmpty(data)) {%>
<%_.each(data, function(item) {%>
<li>
	<a href="/frontend/travel/viewgoods/goodsid/<%=item.dataid%>/page/<%=page%>">
	<div class="ui-list-goods">
		<div class="ui-grid-halve-img">
			<label class="price">&#165; <%=item.price%></label>
			<span style="background-image:url(<%=item.cover%>)"></span>
		</div>
		<div class="title"><%=item.subject%></div>
	</div>
	</a>
</li>
<%});%>
<%} else {%>
<section class="ui-notice ui-notice-norecord"> <i></i>
	<p>暂无数据</p>
</section>
<%}%>
</script>
{/literal}

<script type="text/javascript">
var classid = '{$classid}';
var page = '{$page}';
var read_first = true;
{literal}
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist, fz) {

	// 调用 ajax 并显示
	var sl = new showlist();
	var data = {};
	sl.set_page(page);
	if (0 < classid) {
		data['classid'] = classid;
	}

	// ajax 回调
	function aj_success(data, status, xhr) {

		data["result"]["page"] = sl.get_page();
		return data;
	}

	sl.show_ajax({'url': '/api/travel/get/goods', 'data': data, 'success': aj_success}, {
		"dist": $('#goods'),
		"datakey": "data",
		"cb": function(dom) {
			// 如果 scroll top 小于 2
			if (2 > $(window).scrollTop()) {
				$(window).scrollTop(2);
			}

			// 非第一次
			if (false == read_first) {
				return true;
			}

			// 如果第一页所占的高度小于显示区高度, 则取前一样填充
			read_first = false;
			if (1 < sl.get_page() && $(window).height() > $(document).height()) {
				sl.prev_page();
			}

			return true || dom;
		}
	});

	// 选择事件
	$("#classid").on("change", function(e) {
		$("#frmchgclassid").submit();
		return true || e;
	});
});
{/literal}
</script>

{include file='mobile_v1/footer_fz.tpl'}