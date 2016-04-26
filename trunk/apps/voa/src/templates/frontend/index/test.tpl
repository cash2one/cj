{include file='frontend/header_z.tpl'}


<section id="seca" class="section_container">
	<h2 class="title ui-border-b"><a href="index.html"  class="ui-arrowlink">Frozen UI</a>弹窗 </h2>
	<div class="ui-center">
	    <div class="ui-btn" id="btn1">模板创建弹窗</div>
	    <div class="ui-btn" id="btn2">DOM创建弹窗</div>
	</div>
	<div class="ui-dialog">
	    <div class="ui-dialog-cnt">
	        <div class="ui-dialog-bd">
	            <div>
	            <h4>标题</h4>
	            <div>内容</div></div>
	        </div>
	        <div class="ui-dialog-ft ui-btn-group">
	            <button type="button" data-role="button"  class="select" id="dialogButton<%=i%>">关闭</button> 
	        </div>
	    </div>        
	</div>
</section>
<section id="secb" class="section_container" style="display:none;">
	<div id="test" class="ui-tab"></div>
</section>

{literal}
<script id="test_tpl" type="text/template">
	<ul class="ui-tab-nav ui-border-b">
		<li class="current">热门推荐</li>
		<li>全部表情</li>
		<li>表情</li>
	</ul>
	<ul class="ui-tab-content" style="width:300%">
		<li class="current"><p>内容</p><p>内容</p><p>内容</p><p>内容</p></li>
		<li>
			<ul class="ui-list ui-list-text ui-list-link ui-border-b">
				<%_.each(data, function(item) {%>
				<li class="ui-border-t">
					<h4 class="ui-nowrap">dddd<%=item.subject%></h4>
				</li>
				<%});%>
			</ul>
		</li>
		<li><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p></li>
	</ul>
</script>

<script type="text/javascript">
require(["zepto", "underscore", "showtpl", "frozen"], function($, _, show, fz) {
	// 调用 ajax 并显示
	var st = new show();
	st.show_ajax({'url': '/api/project/get/list'}, {
		"dist": $('#test'), 
		"tpl": $("#test_tpl"), 
		"cb": function(dom) {
			alert(dom.html());
		}
	});
	// 页面切换
	$("#seca").click(function() {
		$("#secb").css({'display': 'block'});
		$("#secb").addClass('ani_start slider_right_in');

		setTimeout(function() { // 动画结束时重置class
			$("#seca").css({'display': 'none'});
        	$("#secb").removeClass('ani_start slider_right_in');
		}, 350);
		
		// tab
		var tab = new window.fz.Scroll('.ui-tab', {
	        role: 'tab',
	        autoplay: false,
	        interval: 3000
	    });

	    tab.on('beforeScrollStart', function(from, to) {
	        console.log(from, to);
	    });

	    tab.on('scrollEnd', function(curPage) {
	        console.log(curPage);
	    });
	});
	/**$("#secb").click(function() {
		$("#seca").css({'display': 'block'});
		$("#seca").addClass('ani_start slider_right_in');

		setTimeout(function() { // 动画结束时重置class
			$("#secb").css({'display': 'none'});
        	$("#seca").removeClass('ani_start slider_right_in');
		}, 350);
	});*/
});
</script>
{/literal}

{include file='frontend/footer_z.tpl'}