/**
 * 使用 localStorage 的方式临时保存 form 信息
 * $Author$
 * $Id$
 */

var MStorageForm = (function() {
	var _q = MOA.query.one;
	var _form = null;
	var _data = {};
	var _attr = 'storage';

	/** 获取当前 key */
	var _get_key = function(formid) {
		return '__form__' + formid;
	};

	/** 对 form 初始化 */
	var _init = function(formid, attr) {
		_form = _q('#' + formid);
		_data = MOA.storage.get(_get_key(formid));
		_data = null == _data ? {} : _data;
		_attr = 'undefined' == typeof(attr) ? 'storage' : attr;
		$each($all('input', _form), function(ipt) {
			if (null == ipt.getAttribute(_attr) || null == ipt.getAttribute('name') || 0 < ipt.value.length) {
				return;
			}

			ipt.addEventListener('input', _save);
			if ('undefined' != typeof(_data[ipt.getAttribute('name')])) {
				ipt.value = _data[ipt.getAttribute('name')];
			}
		});

		$each($all('textarea', _form), function(ta) {
			if (null == ta.getAttribute(_attr) || null == ta.getAttribute('name') || 0 < ta.value.length) {
				return;
			}

			ta.addEventListener('input', _save);
			if ('undefined' != typeof(_data[ta.getAttribute('name')])) {
				ta.value = _data[ta.getAttribute('name')];
			}
		});
	};

	/** 处理保存 */
	var _save = function(e) {
		var target = e.currentTarget;
		if (null == target.getAttribute('name')) {
			return;
		}

		_data[target.getAttribute('name')] = target.value;
		MOA.storage.set(_get_key(_form.id), _data);
	};

	/** 清理客户端缓存 */
	var _clear = function() {
		if (null == _form) {
			return;
		}

		MOA.storage.remove(_get_key(_form.id));
	};

	return {'init':_init, 'clear':_clear};
}());
