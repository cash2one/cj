define(["jquery"], function ($){
	function formvalidate () {
    }
	formvalidate.prototype = {
		error_type: {empty: 1, rule: 2},
		error: 0,
		check: function(inputs, _show_error, _show_success) {
		    var error = false;
		    for (var i = 0; i < inputs.length; i++) {
		            var input = inputs[i];
		            //if ($(input).is('[required]') && )
		            var rule = $(input).attr('reg_exp');
		            if (rule) {
		            	// 如果值为空，而且又是必须填的就返回空错
		            	if ($(input).val() == '' && $(input).is('[required]')) {
		            		_show_error(input, this.error_type.empty, "值不能为空");
		            		error = true;
		            		break;
		            	} else {
		            		rule = new RegExp(rule);
			                if (!rule.test($(input).val())) {
			                    //$(input).addClass('error');
			                    //$(input).focus();
			                    _show_error(input);
			                    error = true;
			                    break;
			                } else {
			                	_show_success(input);
			                }
		            	}
		                
		            } else {
		            	if ($(input).is('[required]')) {
		            		if ( $(input).attr('type') == 'checkbox') {
			                    if (!$(input).is(':checked')) {
			                        //$(input).focus();
			                        _show_error(input);
			                        error = true;
			                        break;
			                    }
			                } else if ($(input).val() == '') {
			                    //$(input).addClass('error');
			                    //$(input).focus();
			                    _show_error(input, this.error_type.empty, "值不能为空");
			                    error = true;
			                    break;
			                } else if($(input).attr('type') == 'password') {
			                	error = this._validate_password(input, _show_error);
			                	if (!error) {
			                		_show_success(input);
			                	} else {
			                		break;
			                	}
			                } else {
			                	_show_success(input);
			                }
		            	} else {
		            		_show_success(input);
		            	}
		               
		          }
		    };
		    this.error = error;
		    return error;
		},
		_validate_password: function (input, _show_error) {
			var error = false;
			if ($(input) != $('input[type=password]:first')) {
                if ($('input[type=password]:first').val() != $(input).val()) {
                    //$(input).focus();
                    _show_error(input, '两个密码不一致，请重新确认!');
                    error = true;
                }
                
            } else {
                 var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g"); 
                 var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g"); 
                 var enoughRegex = new RegExp("(?=.{6,}).*", "g"); 
                 //密码小于六位的时候，密码强度太低不给通过 
                 if (false == enoughRegex.test($(input).val())) { 
                    error = true;
                    //$(input).focus();
                    _show_error(input, '密码长度不要少于六位');
                 } else if (strongRegex.test($(input).val())) {
                 } else if (mediumRegex.test($(input).val())) { 
                 } else {
                    //$(input).focus();
                    _show_error(input, '密码要包含字母、数字、特殊字符等。');
                    error = true;
                 }
                 
            }
			return error;
		}
	};
    return formvalidate;
});