{include file='mobile_v1/header_fz.tpl'}

<div class="ui-myresult">
	<section class="ui-selector ui-selector-line">
		<div class="ui-selector-content">
			<ul>
				<li class="ui-selector-item ui-border-b">
					<a href="/frontend/travel/cpindex" class="ui-back-a"> <i class="ui-back"></i></a>
					<h3 class="clearfix">我的业绩</h3>
				</li>
			</ul>
		</div>
	</section>

	<div class="ui-form">
		<div class="ui-form-item ui-form-item-order ui-border-b  ui-form-item-link">
			<a href="javascript:;" id="a_text">今日业绩</a>
            <select id="turnover" name="turnover">
			     <option value="today">今日业绩</option>
			     <option value="yesterday">昨日业绩</option>
			     <option value="week">近七天业绩</option>
			     <option value="month">近30天业绩</option>
		    </select> 
		</div>
		<div class="ui-form-item ui-form-item-order ui-border-b ui-center ui-form-total">
		   <h2 id="total">0.00<span>元</span></h2>
		</div>
	</div>
	<div class="ui-form" id="list"></div>
</div>

{literal}
<script id="list_tpl" type="text/template">
<%if (!_.isEmpty(list)) {
$('#list').addClass('ui-form');
%>
<%_.each(list, function(item) {%>
<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
	<label for="#">x<%=item.num%> <%=(item.price / 100)%>元</label>
	<p><%=item.goods_name%></p>
</div>
<%});%>
<%} else {
$('#list').removeClass();
%>
<section class="ui-notice ui-notice-norecord"> <i></i>
	<p>暂无数据</p>
</section>
<%}%>
</script>

<script type="text/javascript">
var total=0.00;
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist, fz) {
	var el = $.loading({
		content: '加载中...'
	});
	
	// 调用 ajax 并显示
	var sl = new showlist();
	sl.show_ajax({'url': '/api/travel/get/ordergoods', 'success': aj_success}, {
		"dist": $('#list'),
		'cb': function(dom) {
			el.loading("hide");
		}
	});
	
   // ajax 回调
	function aj_success(data, status, xhr) {
		if (_.has(data.result, "tj")) {
		    total = data.result["tj"]["price"] / 100;
			$("#total").html((data.result["tj"]["price"] / 100) + '<span>元</span>');
		}else{
			$("#total").html(total+'<span>元</span>');
		}
		
		return data;
	}
	
	$("#turnover").on("change", function(e) {
		$(this).find('option').each(function (){
			if ($(this).prop('selected')) {
					$('#a_text').text($(this).text()); 
					var ty =  $(this).val();
				    // 调用 ajax 并显示
			        sl.reinit({
					       'url': '/api/travel/get/ordergoods?ty='+ty, 
					       'success': aj_success});
		    }
		});
	});

});


  



</script>
{/literal}

{include file='mobile_v1/footer_fz.tpl'}