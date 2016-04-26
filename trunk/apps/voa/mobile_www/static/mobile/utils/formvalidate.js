define(["jquery"], function($) {

	// 构造方法
	function Formvalidate() {
		// do something
    }

	Formvalidate.prototype = {
		error_type: {empty: 1, rule: 2},
		error: 0,
		check: function(inputs, _show_error, _show_success) {
		    var isok = true;
		    for (var i = 0; i < inputs.length; i ++) {
				var input = inputs[i];
				var rule = $(input).attr('reg_exp');
				// 如果有正则
				if (rule) {
					// 如果值为空，而且又是必须填的就返回空错
					if ($(input).val() == '' && $(input).is('[required]')) {
						_show_error(input, this.error_type.empty, "值不能为空");
						isok = false;
						break;
					}

					rule = new RegExp(rule);
					if (!rule.test($(input).val())) {
						_show_error(input);
						isok = false;
						break;
					}

					// 验证成功
					_show_success(input);
					continue;
				}

				// 如果是非必填
				if (!$(input).is('[required]')) {
					_show_success(input);
					continue;
				}

				// 如果是复选框
				if ('checkbox' == $(input).attr('type')) {
					if (!$(input).is(':checked')) {
						_show_error(input);
						isok = false;
						break;
					}
				} else if ('' == $(input).val()) {
					_show_error(input, this.error_type.empty, "值不能为空");
					isok = false;
					break;
				} else if ('password' == $(input).attr('type')) {
					isok = this._validate_password(input, _show_error);
					if (!isok) {
						break;
					}

					_show_success(input);
				} else {
					_show_success(input);
				}
		    }

		    this.error = !isok;
		    return isok;
		},
		/**
		 * 验证密码是否相等
		 * @param {element} input 输入框
		 * @param {function} _show_error 错误提示方法
		 * @returns {boolean}
		 * @private
		 */
		_validate_password: function (input, _show_error) {
			var pwd = $(input).val();
			var first_pw = $('input[type=password]:first');
			if ($(input) != first_pw) {
                if (first_pw.val() != pwd) {
                    _show_error(input, '两个密码不一致，请重新确认!');
                    return false;
                }
            } else {
                 var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
                 var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
                 var enoughRegex = new RegExp("(?=.{6,}).*", "g");
                 //密码小于六位的时候，密码强度太低不给通过
                 if (false == enoughRegex.test(pwd)) {
                    _show_error(input, '密码长度不要少于六位');
					 return false;
                 } else if (strongRegex.test(pwd)) {
					 // do nothing.
                 } else if (mediumRegex.test(pwd)) {
					 // do nothing.
                 } else {
                    _show_error(input, '密码要包含字母、数字、特殊字符等。');
					 return false;
                 }

            }

			return true;
		}
	};

    return Formvalidate;
});