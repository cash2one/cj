/**
 * H5 文本域计算剩余字数
 * Create By Yanwenzhong
 * $Author$
 * $Id$
 */

require(["zepto", "frozen"], function($, fz) {

	/**
	 * 每次键盘弹起，计算字数
	 */
	$('#cyoa-body').on('keyup change', 'textarea', function () {
		var $this = $(this);
		var maxlength = $this.attr('maxlength');
		if (maxlength == 'undefined' || maxlength <= 0 || maxlength == null) {
			return;
		}
		var val = $this.val();
		// 一个换行符，系统maxlength属性默认为两个字符，所以要再减去一个
		var re = new RegExp("\n", "g");
		var arr = val.match(re);
		var plus = 0;
		if (arr != null) {
			plus = arr.length;
		}
		// 已输入的字数
		var total = parseInt(val.length) - parseInt(plus);
		if ($this.data('amount') != 'undefined' && $this.data('amount') == 1) {
			// 显示输入总数
			$this.parent('._textarea').next('._remaining').find('strong').text(total);
		} else {
			// 显示剩余字数
			var r = parseInt(maxlength) - total;
			$this.parent('._textarea').next('._remaining').find('strong').text(r);
		}
	});
});
