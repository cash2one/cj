/**
 * 选择员工
 * $Author$
 * $Id$
 * @returns {MemberSelect}
 */

function MemberSelect() {
	var _q = MOA.query.one;
	var _ajax_lock = false;
	var __method__ = this;
	this._multi = false;
	this._ajurl = '/member/list';
	this._ajmethod = 'post';
	this._ajparam = {};
	this._id_panel; /** 用户选择容器 id */
	this._id_view_stack; /** view stack */
	this._id_uids; /** 用户 uid hidden id */
	this._id_ul_container; /** 用户展示 ul */
	this._callback = null; /** 选择后的回调 */
	/** 初始化 */
	this.init = function(sid, config, ename) {
		if ('undefined' == typeof(ename)) {
			ename = 'click';
		}
		
		if ('undefined' != typeof(config)) {
			for (var k in config) {
				__method__['_' + k] = config[k];
			}
		}
		
		_q('#' + sid).addEventListener(ename, function(e) {
			__method__._show(e);
		});
		
		/** 新增删除事件 */
		$each($all('a.rm', $one('#' + __method__._id_ul_container)), function(lia) {
			var val = $data(lia.parentNode, 'id');
			if ('undefined' == typeof(val) || 0 == val) {
				return;
			}
			
			lia.addEventListener('click', __method__._remove);
		});
	};
	this._show = function(e) {
		if (_ajax_lock) return;
		_ajax_lock = true;
		MLoading.show('稍等片刻...');
		$ajax(__method__._ajurl, __method__._ajmethod, __method__._ajparam,
			function(ajaxResult) { /** [ajax] callback */
				var ipt_uids = $one('#' + __method__._id_uids),
					container = $one('#' + __method__._id_ul_container),
					addLi = $one('li:last-of-type', container),
					pageReturn = function() {
						MMember.close(function() {
							_ajax_lock = false;
						});
					},
					func_deal = function(arr) {
						$each($all('li', container), function(li) {
							var val = $data(li, 'id');
							if ('undefined' == typeof(val) || 0 == val) {
								return;
							}

							container.removeChild(li);
						});
						if (0 == arr.length) {
							return;
						}
						
						$each(arr, function(user) {
							/** 添加新图标 */
							$append(container, MMember.getBoxItemHTML(user, true));
							var newitem = $one('li:last-of-type', container);
							$addCls(newitem, 'newjoin');
							$data(newitem, 'id', user.uid);
							$one('a.rm', newitem).addEventListener('click', __method__._remove);
							/** 调整位置 */
							container.appendChild(addLi);
							/** 更新表单域中的input:hidden值 */
							__method__.update_uids();
						});
					};
				/** 填充并显示好友列表面板 */
				var ids = $trim(ipt_uids.value).length ? ipt_uids.value.split(',') : [];
				MMember.open(ajaxResult, ids, __method__._multi, 
					function() { /** 好友面板打开后 */
						MLoading.hide();
					},
					function(dataArr) { /** 选择好友后 */
						if (__method__._callback) {
							__method__._callback(dataArr);
						} else {
							func_deal(dataArr);
						}
						
						pageReturn();
					},
					false
				); /** end of MMember.open */
			},
			true /** [ajax] use json */
		);
	};
	this._remove = function(e) {
		var item = e.currentTarget.parentNode;
		item.parentNode.removeChild(item);
		/** 更新表单域中的input:hidden值 */
		__method__.update_uids();
	};
	this.update_uids = function(e) {
		var ipt_uids = $one('#' + __method__._id_uids),
			lis = $all('li', $one('#' + __method__._id_ul_container)),
			arr = [];
		$each(lis, function(li) {
			arr.push($data(li, 'id'));
		});
		arr.pop();
		ipt_uids.value = arr.join(',');
		ipt_uids = null;
		lis = null;
	};
}