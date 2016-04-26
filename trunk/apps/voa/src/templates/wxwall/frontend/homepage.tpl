{include file='wxwall/frontend/header.tpl'}

<header>
	<div class="row">
		<div class="col-sm-3 wxwall-logo"><span>畅移微信墙</span></div>
		<div class="col-sm-7">
			<ul id="wxwall-tip" class="wxwall-tip">
				<li class="_tip"><cite><span><strong>回复 {$wxwallPostCode} + 你想说的话<br />到微信即可上墙</strong></span></cite></li>
{if $wxwall['ww_message']}
				<li class="_tip"><cite><span><strong class="intro">{$wxwall['_message']|escape}</strong></span></cite></li>
{/if}
				<li class="_tip"><cite><span><strong>扫一扫右侧二维码，关注后<br />回复 {$wxwallPostCode} + 你想说的话 即可上墙</strong></span></cite></li>
			</ul>
		</div>
		<div class="col-sm-2 text-right">
			<img src="static/images/weixin_qrcode.jpg" alt="" class="_qrCode qrCode-logo" />
		</div>
	</div>
</header>

<div id="wxwall-show" class="wxwall-show">
	<div id="wxwall-message" class="wxwall-message">
	</div>
</div>

<div class="toolbar">
	<div class="row">
		<div class="col-sm-6"></div>
		<div class="col-sm-6 text-right">
			<a href="javascript:;" data-toggle="tooltip" class="fullScreen btn btn-primary" title="点击全屏浏览"><i class="fa fa-arrows"></i> <span>全屏</span></a>
		</div>
	</div>
</div>

<div id="qrCodeShow" style="display:none">
	<div class="row">
		<div class="col-sm-6"><img src="static/images/weixin_qrcode.jpg" class="qrCode-big _qrCode" alt="" /></div>
		<div class="col-sm-4"><img src="static/images/weixin_qrcode.jpg" class="qrCode-middle _qrCode" alt="" /></div>
		<div class="col-sm-2"><img src="static/images/weixin_qrcode.jpg" class="qrCode-small _qrCode" alt="" /></div>
	</div>
</div>

<script type="text/javascript">
var	url = '{$wxwallUrl}';
var	ww_id = '{$ww_id}';
var getNewListUrlBase = '{$getNewListUrlBase}';
var qrcodeupdateUrlBase = '{$qrcodeupdateUrlBase}';
var qrcodeUrlBase = '{$qrcodeUrlBase}';
</script>

{literal}
<script type="text/template" id="message-item">
<div class="wxwall-message-item">
	<div class="row">
		<div class="col-sm-2">
			<div class="wxwall-message-face"><img src="<%= m_face %>" alt="<%= m_username %>" class="msg_face" /></div>
		</div>
		<div class="col-sm-10">
			<div class="wxwall-message-content">
				<h4><strong><%= m_username %></strong> 发布于：<time title="<%= wwp_created %>"><%= _created %></time></h4>
				<p><%= wwp_message %></p>
			</div>
		</div>
	</div>
</div>
</script>

<script type="text/javascript">

var	updated	=	0;

function setMessageBoxHeight(){
	var	pageHeight		=	jQuery(window).height();
	var	headerHeight	=	jQuery('header').outerHeight();
	var	toolbarHeight	=	jQuery('.toolbar').outerHeight();
	//var	footerHeight	=	jQuery('footer').outerHeight();
	var	fillHeight		=	40;
	//alert(pageHeight - headerHeight);
	jQuery('#wxwall-show').outerHeight(pageHeight - headerHeight - toolbarHeight - fillHeight);
}
function getMessages() {
	jQuery.ajax({
		dataType:"json",
		cache:true,
		url : getNewListUrlBase + updated,
		success : function(json){
			var	msgItem	=	'';
			jQuery.each(json.data,function(wwp_id,wwp){
				msgItem	+=	txTpl("message-item", {
					m_face: wwp['face'],
					m_username: wwp['username'],
					wwp_message: wwp['message'],
					wwp_created: wwp['wwp_created'],
					_created: wwp['_created']
				});
				if ( updated < wwp['updated'] ) {
					updated	=	wwp['updated'];
				}
			});
			if ( msgItem ) {
				jQuery(msgItem).prependTo('#wxwall-message').hide().slideDown('slow');
			}
		},
		error:function(XMLHttpRequest, textStatus, errorThrown){
			alert(XMLHttpRequest + textStatus + errorThrown);
		}
	});
	window.setTimeout(getMessages, 5000);
}

/**
 * 获取微信墙二维码
 */
function getQrCode(){
	jQuery.ajax({
		dataType:"json",
		url:qrcodeupdateUrlBase + (new Date()/1),
		success:function(json){
			var expire	=	json.data.expire;
			var	qrCodeUrl	=	json.data.qrcodeurl;
			jQuery('._qrCode').attr('src',qrCodeUrl);
			window.setTimeout(getQrCode,1000*expire);
		},
		error:function(){
			window.setTimeout(getQrCode,5000);
		}
	});
}

function qrCodeSize(){
	var	jqQrCodeShow	=	jQuery('#qrCodeShow');
	var	jqMessageBox=	jQuery('#wxwall-show');
	var	box	=	jqMessageBox.offset();
	var	width	=	jqMessageBox.innerWidth();
	var	height	=	jqMessageBox.innerHeight();
	jqQrCodeShow.offset({left:box.left,top:box.top}).css({"width":width,"height":height,"position":"absolute"});
}

jQuery(function(){
	
	getMessages();
	getQrCode();
	
	jQuery('#wxwall-tip').innerfade({
		animationtype: 'slide',
		speed: 750,
		timeout: 5000,
		type: 'sequence',
		containerheight: 'auto'
	});
	jQuery('body').tooltip({
		selector: "[data-toggle=tooltip]",
		container: "body"
	});
	
	setMessageBoxHeight();
	
	$(window).resize(function(){
		setMessageBoxHeight();
	});

	jQuery('._qrCode').click(function(){
		qrCodeSize();
		jQuery('#qrCodeShow').toggle();
	});

	jQuery('.fullScreen').click(function(){
		var	jq	=	jQuery(this);
		jq.toggleClass('cancelFullScreen');
		if ( jq.is('.cancelFullScreen') ) {
			jq.attr('title','点击退出全屏模式');
			jq.attr('data-title','点击退出全屏模式');
			jq.children('span').text('窗口');
			jq.children('i').removeClass('fa-arrows-alt').addClass('fa-arrows');
			WG.fullScreen();
		} else {
			jq.attr('title','点击进入全屏模式');
			jq.children('span').text('全屏');
			jq.children('i').removeClass('fa-arrows').addClass('fa-arrows-alt');
			WG.cancelFullScreen();
		}
		
	});
});
</script>
{/literal}

{include file='wxwall/frontend/footer.tpl'}