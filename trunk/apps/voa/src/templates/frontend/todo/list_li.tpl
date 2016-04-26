<h1>待办事项</h1>
<ul class="mod_common_list">
	{foreach $list->incomplete as $row}
	<li>
		<a class="m_link {if $row['_stared'] gt 0}top{/if}" href="/todo/edit/{$row['td_id']}">
			<label><input data-id="{$row['td_id']}" type="checkbox" onclick="toggleComplete(this);" />完成</label>
			<div><h1>{$row['_subject']}</h1>{if $row['_calltime'] gt 0}{if $row['td_calltime'] > 0}<i></i>{/if}{/if}</div>
			<div>{if $row['td_exptime'] > 0}<time>{$row['_exptime']}</time>{/if}</div>
			<input type="hidden" class="_timestamp" value="{$row['td_created']}">
		</a>
	</li>
	{/foreach}
</ul>

<h1>已完成</h1>
<ul class="mod_common_list">
	{foreach $list->complete as $row}
	<li>
		<a class="m_link {if $row['_stared'] gt 0}top{/if}" href="/todo/edit/{$row['td_id']}">
			<label><input data-id="{$row['td_id']}" type="checkbox" onclick="toggleComplete(this);" checked/>完成</label>
			<div><h1>{$row['_subject']}</h1>{if $row['_calltime'] gt 0}{if $row['td_calltime'] > 0}<i></i>{/if}{/if}</div>
			<div>{if $row['td_exptime'] > 0}<time>{$row['_exptime']}</time>{/if}</div>
			<input type="hidden" class="_timestamp" value="{$row['td_created']}">
		</a>
	</li>
	{/foreach}
	<li>
		<a class="mod_ajax_more" href="javascript:void(0)">点击加载更多...</a>
	</li>
</ul>

<script type="text/moatmpl" id="ajaxTmpl">
{literal}
	{##items#,#
	<li>
		<a class="m_link _field.stared_" href="/todo/edit/_field.data-id_">
			<label><input data-id="_field.data-id_" type="checkbox" onclick="toggleComplete(this)" checked />完成</label>
			<div><h1>_field.subject_</h1><i data-hidden-when-lost="_field.clock_"></i></div>
			<div data-hidden-when-lost="_field.expTime_"><time>_field.expTime_</time></div>
			<input type="hidden" class="_timestamp" value="_field.created_">
		</a>
	</li>
	##}
{/literal}
</script>

<script>
require(['template'], function() {
	var ajaxLock = false;

	//加载更多
	var t = new MOA.mvc.Template;
	$onload(function() {
		$one('.mod_ajax_more').addEventListener('click', function(e) {
			if (ajaxLock) return;
			ajaxLock = true;

			MLoading.show('正在读取...');

			// 获取离底部最近的一个列表节点
			var closestNode = $all('ul:nth-of-type(2) ._timestamp');
			var nodeList = Array.prototype.slice.call(closestNode, -1);
			// 获取当前时间戳
			var timeStamp = nodeList[0].value;

			$ajax('/todo', 'GET', {
				'ac': 'more',
				'limit': 4,
				'datetime': timeStamp
			},
			function(ajaxResult) {
				var $lst = $one('ul:nth-of-type(2)');
				var html = t.parse($one('#ajaxTmpl').innerHTML, ajaxResult);
				$append($lst, html);
				$lst.appendChild($one('.mod_ajax_more').parentNode);

				MLoading.hide();
				ajaxLock = false;
			},
			true);
		});
	});

	// 切换完成状态
	function _parseAjax($checkbox) {

		if (ajaxLock) return;
		ajaxLock = true;

		MLoading.show('正在更新...');

		$ajax('/todo?ac=complete', 'GET', {
			'id': $checkbox.dataset.id,
			'checked': $checkbox.checked
		},
		function(ajaxResult) {
			if (ajaxResult.response === "success") {
				window.location.reload();
			} else {
				alert('something happend');
			}

			MLoading.hide();
			ajaxLock = false;
		},
		true);
	}

	// 暴露给全局吧，省好多事件处理垃圾代码
	window.toggleComplete = _parseAjax;
})
</script>
