/**
 * H5前端 - datetime
 * Created by Deepseath on 2015/3/22.
 */
require(["zepto"], function ($) {

	// 绑定事件的容器
	var $body = $('#cyoa-body');
	// 用于临时存放当前触发的控件对象
	var box;
	// 绑定输入控件，用于临时储存当前输入控件的HTML
	$body.on('click', 'input._input_datetime', function () {
		// 控件所在的容器
		box = $(this).parent();
		// 存储控件的HTML数据
		$body.data('_tmp_clone_', box.html());
	});

	// 绑定焦点移出输入控件触发动作（由于Android系统不支持input的change事件绑定，所以选择focusout）
	$body.on('focusout', 'input._input_datetime', function () {
		// 当前的输入对象
		var $this = $(this);
		// 当前的值
		var val = $this.val();
		// 当前输入的类型，date or time
		var type = $this.attr('type');
		// 初始化最新的值（日期 + 时间）
		var value = '';
		// 如果非空
		if (val != '') {
			// 如果是日期
			if (type == 'date') {
				// 组合日期+时间
				value = val + ' ' + $this.next('input[type=time]').val();
			} else {
				// 组合日期 + 时间
				value = $this.prev('input[type=date]').val() + ' ' + val;
			}
			// 赋值隐藏的表单控件
			$this.parent().find('input._input_datetime_value').val(value);
		} else {
			// 如果当前选择的值为空，此情况在IOS会出现，点击“清除”时
			// 将被清除了的控件重新填补
			box.html($body.data('_tmp_clone_'));
		}

	});
});
