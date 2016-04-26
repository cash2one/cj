{include file='frontend/header.tpl'}

<body id="wbg_bbs_profile">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<header class="mod_bbs_header">
	<div class="center">
		<img src="{$cinstance->avatar($wbs_uid)}" />
		<h1>{$wbs_username}</h1>
	</div>
</header>
<menu class="center">
	<div id="wb_my"{if 'my' == $ac} class="current"{/if}>我的工作</div>
	<div id="wb_share"{if 'share' == $ac} class="current"{/if}>共享工作</div>
	<a href="/thread/newthread">发布工作</a>
</menu>

<div class="center"><div class="mod_touch_slider" style="opacity:0"><div class="tsinner"><div class="sld_bar">
	<ul class="mine" id="thread_list"> <!--我的工作-->
		{include file='frontend/thread/index_li.tpl'}
	</ul>
	<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
	{if 'share' != $ac}<a href="javascript:void(0)" class="mod_list_actions_icon"></a>{/if}
</div></div></div></div>

<script>
var _page = {$page};
{if 'share' == $ac}
{literal}
$one('#wb_my').addEventListener('click', function() {
	window.location.href = '/thread/index?ac=my';
});
{/literal}
{else}
{literal}
$one('#wb_share').addEventListener('click', function() {
	window.location.href = '/thread/index?ac=share';
})
{/literal}
{/if}

{literal}
$onload(function() {
	$one('.mod_touch_slider').style.opacity = 1;

	function _do_refresh(doma, data) {
		var li = doma.parentNode.parentNode;
		var ul = li.parentNode;
		var ts = li.id.substr(2);
		var curLi = null;
		if (-1 < doma.rel.indexOf('/delete')) {
			ul.removeChild(li);
		} else if (-1 < doma.rel.indexOf('ac=up')) {
			var f = _get_first_dom(ul);
			li.id = 't_' + ts;
			ul.insertBefore(li, f);
		} else if (-1 < doma.rel.indexOf('ac=cancel')) {
			$each($all('li', ul), function(_li) {
				var _ts = _li.id.substr(2);
				if (-1 < _li.id.indexOf('t_')) {
					return;
				}

				if (_ts > ts) {
					return;
				}

				curLi = _li;
			});
			li.id = 'b_' + _ts;
			if (null == curLi) {
				ul.appendChild(li);
			} else {
				ul.insertBefore(li, curLi);
			}
		}

	}

	function _get_first_dom(dom) {
		var ar = dom.childNodes;
		for (i = 0; i < ar.length; ++ i) {
			if (1 == ar[i].nodeType) {
				return ar[i];
			}
		}

		return null;
	}

	/** 执行 ajax */
	_do_ajax = function(a) {
		MLoading.show('稍等片刻...');
		$ajax(
			a.rel, 'POST', /** [ajax] url & method */
			{/** [ajax] params */},
			function(ajaxResult) { /** [ajax] callback */
				if (ajaxResult.errno == 0) {
					/** 置顶成功 */
					_do_refresh(a, ajaxResult.data);
				} else {
					0 < ajaxResult.errmsg && alert(ajaxResult.errmsg);
				}

				MLoading.hide();
			},
			true /** [ajax] use json */
		);
	}

	/** 处理 a 链接的 click 事件 */
	function _click_href(e) {
		var ha = e.currentTarget;
		/** is rm */
		if ($hasCls(ha, 'rm')) {
			MDialog.confirm('提示', '您确定要删除当前工作台内容吗?', null, '确定', function(ebtn) {
				_do_ajax(ha);
			}, null, '取消', function(ebtn) {
				$rmCls(ha, 'confirm');
			});
			e.preventDefault();
			return;
		}

		_do_ajax(ha);
	}

	/** ajax href */
	function _refresh_click_event() {
		$each($all('.mod_list_actions_btns>a'), function(a) {
			a.removeEventListener('click', _click_href);
			a.addEventListener('click', _click_href);
		});
	}

	/** 组织post参数 */
	function _get_more_params() {
		return {'page':++ _page};
	}

	/** 刷新列表 */
	function _refresh_list(s) {
		$prepend($one('#thread_list'), s);
	}

	/** 加载更多 */
	var _more = new c_list_more();
	_more.init('show_more', 'thread_list', {}, {
		'params':_get_more_params,
		'callback':_refresh_list
	});

	_refresh_click_event();
});
{/literal}
</script>

{include file='frontend/footer.tpl'}
