/**
 * H5 下拉选择框控件
 * Create By Deepseath
 * $Author$
 * $Id$
 */

require(["zepto"], function ($) {

	/**
	 * 监听页面的下拉选择框的值变化
	 */
	$('body').on('change', 'select', function () {

		// 当前的下拉选择框对象
		var $t = $(this);
		// 用于显示选择项目的对象
		var $p = $t.prev('p');
		var $selected_show = $p.find('span');
		if ($selected_show.length == 0) {
			$selected_show = $p;
		}

		// 完全回调模式
		var callbackall = $t.attr('data-callbackall');
		if (callbackall && callbackall != 'undefined') {
			try {
				if (typeof(eval(callbackall)) == "function") {
					window[callbackall]($t, $selected_show);
				}
			} catch (e) {
				alert('下拉选择框组件的完整回调函数“' + callbackall + '”未定义或执行错误');
				return false;
			}

			return true;
		}

		// 已选择的文本，仅用于显示
		var text = '';
		// 分隔符号
		var comma = '';
		// 当前选择的值
		var value = new Array();
		// 遍历选项以提取选择的文本
		$.each($t.find('option'), function (i, t) {
			var $cur = $(t);
			if ($cur.prop('selected')) {
				value.push($cur.val());
				text += comma + $cur.text();
				comma = ', ';
				// 不是多选，则直接退出
				if ($t.prop('multiple') == 'undefined') {
					return true;
				}
			}
		});

		// 显示已选择的项目内容
		$selected_show.text(text);

		// 回调函数名
		var callback = $t.attr('data-callback');

		// 启用自定义回调函数
		if (callback && callback != 'undefined') {
			try {
				if (typeof(eval(callback)) == "function") {
					window[callback]($t, value, text, $selected_show);
				}
			} catch (e) {
				alert('下拉选择框组件的回调函数“' + callback + '”未定义或执行错误');
				return false;
			}
		}

		return true;
	});

});