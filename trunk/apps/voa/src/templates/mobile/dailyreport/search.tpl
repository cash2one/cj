{if $ac == 'mine'}{$navtitle = '我发出的报告'}{else}{$navtitle = '我收到的报告'}{/if}
{include file='mobile/header.tpl' css_file='app_dailyreport.css'}

<div class="ui-selector ui-selector-line">
	<div class="ui-selector-content">
		<ul>
			<li class=" ui-selector-item ui-border-b">
				<h3><p id="option_p">{if empty($type)}全部{else}{$type_val}{/if}</p></h3>
				<select id="daily_type" name="daily_type" data-lis-oldselidx="15" onchange="change()">
				<option value="0" {if $type == 0} selected{/if}>全部</option>
{foreach $dailyType as $k => $v}
				{if $v[1] == 1}<option value="{$k}"{if $type == $k} selected{/if}>{$v[0]|escape}</option>{/if}
{/foreach}
				</select>
			</li>
		</ul>
	</div>

<div class="frozen-module-dom">
	<div class="ui-form ui-border-t" id="drlist"></div>
</div>

{literal}
<script id="drlist_tpl" type="text/template">
<%if (_.isEmpty(data)) {
$('#drlist').removeClass();
%>
<section class="ui-notice ui-notice-norecord"> <i></i>
	<p>暂无报告数据</p>
</section>
<%} else {%>
<%_.each(data, function(dr, index) {
$('#drlist').addClass('ui-form ui-border-t');
%>
<div class="ui-form-item ui-form-item-order ui-form-item-link<%if (data.length > index + 1) {%> ui-border-b<%}%>">
	<a href="/dailyreport/view/<%=dr.dr_id%>">
	<%if('mine' !=ac){%>
	<%=dr.username%>&nbsp;
	<%}%>

	<%if(dr.dr_subject.length == 0){%>
	<%=dr.reporttime%>
	<%}else{%>
	<%=dr.dr_subject%>
	<%}%>

	</a>
</div>
<%});}%>
</script>
{/literal}

<script>
var ac = "{$ac}";
var listurl = "/api/dailyreport/get/list?type={$type}&action={$ac}&limit=20";
{literal}
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist, fz) {
	// 调用 ajax 并显示
	var sl = new showlist();
	sl.show_ajax({'url': listurl}, {
		"dist": $('#drlist'),
		"datakey": "data",
		"cb": function(dom) {

		}
	});
});

function change(){
	var op_key = $('#daily_type').val();
	var op_val;
	$('#daily_type option').each(function(index, opt) {
		if ($(opt).prop("selected")) {
			op_val = $(opt).text();
 			$('#option_p').text(op_val);
		}
	});
	window.location.href = '/frontend/dailyreport/search?type='+op_key+'&ac='+ac+'&type_val='+op_val;
}

{/literal}
</script>

{include file='mobile/footer.tpl'}