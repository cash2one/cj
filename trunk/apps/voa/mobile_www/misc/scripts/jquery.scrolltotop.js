document.write('<div id="scrolltotopBtn" class="scrolltotop"><a href="javascript:;" title="返回到页面顶部"><i>返回到页面顶部</i></a></div>');
jQuery(function(){
	jQuery(window).scroll(function(){
		if ( jQuery(window).scrollTop() > 51 ) {
			jQuery("#scrolltotopBtn").fadeIn(100).click(function(){
				jQuery(window).scrollTop(0);
			});
		} else {
			jQuery("#scrolltotopBtn").fadeOut(100);
		}
	});
});