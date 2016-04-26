{include file='frontend/header.tpl'}

<body id="wbg_gzt_feedback">
<script src="{$wbs_javascript_path}/MOA.hsliderchooser.js"></script>

<form name="proj_{$ac}" id="proj_{$ac}" method="post" action="/project/progress/{$p_id}?handlekey=post" autocomplete="off">
	<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
	<input type="hidden" name="referer" value="{$refer}" />
	<h2>完成进度：</h2>
	<div class="mod_common_list_style prog">
		<input name="progress" id="progress" value="{$proc['pm_progress']}" type="hidden" /><!--进度选择后会同步至此-->
		<div class="center"><section><ol></ol></section></div>
	</div>

	<h2>具体进展：</h2>
	<div class="mod_common_list_style describe">
		<textarea name="message" placeholder="可填写阶段性成果" required></textarea>
	</div>
	
	<div class="foot numbtns single">
		<input type="reset" value="返回" onclick = "javascript:history.go(-1);"/>
		<input type="submit"  name="sbtpost" id="sbtpost" value="添加我的进度" />
	</div>
</form>

{include file='frontend/footer_nav.tpl'}

<script>
var defaultProgressIndex = 0;
var _procvs = ['0<i>%</i>'];
var _frm_name = 'proj_{$ac}';
{foreach $procvs as $k => $v}
_procvs.push({$v} + '<i>%</i>');
{if $v == $proc['pm_progress']}defaultProgressIndex = {$k} + 1;{/if}
{/foreach}

{literal}
function errorhandle_post(url, msg, js) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg, js) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}

function scCallback(currIdx) {
	$each($all('section li'), function(li) {
		$rmCls(li, 'left');
		$rmCls(li, 'right');
	});
	var l = $one('section li:nth-of-type('+(currIdx+1)+')'),
		r = $one('section li:nth-of-type('+(currIdx+3)+')');
	$addCls(l, 'left');
	$addCls(r, 'right');
	l = null;
	r = null;

	var selectedValue = $one('section li:nth-of-type('+(currIdx+2)+')').innerHTML;
	selectedValue = /^(\d+)/.exec(selectedValue)[0];
	if ( parseInt(selectedValue) > 0 ) selectedValue += '%';
	$one('.prog input[type=hidden]').value = selectedValue;
}

require(['dialog', 'members', 'business'], function() {
	$onload(function() {
		$one('form').onsubmit = function(e) {
			var ta = $one('textarea');
			e.preventDefault();
			if (!ta.value || !$trim(ta.value).length) {
				MDialog.notice('请填写具体进展!');
				e.preventDefault();
				return false;
			}

			ajax_form_lock = true;
			MLoading.show('稍等片刻...');
			MAjaxForm.submit(_frm_name, function(result) {
				MLoading.hide();
			});

			return false;
		};

		$one('body').scrollTop = -1;
		window.scrollTo(0, -1);
		HSliderChooser({
			nums: _procvs,
			containerContext: '.prog section',
			innerContext: 'ol',
			itemContext: 'li',
			itemWidth: 84,
			currentStyleClass: 'curr',
			defaultIndex: defaultProgressIndex,
			callback: scCallback
		});

		scCallback(defaultProgressIndex);
	});
});
{/literal}
</script>


{include file='frontend/footer.tpl'}
