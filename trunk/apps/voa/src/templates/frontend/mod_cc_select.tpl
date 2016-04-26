<input name="{$iptname}" id="{$iptname}" value="" type="hidden" />
<div id="{$iptname}_div" class="mod_members cc div_cc_members"{if $display_none} style="display:none;"{/if}>
	<ul class="box" id="cc_ul_list">
		{foreach $ccusers as $v}
		<li id="{$v['m_uid']}" data-id="{$v['m_uid']}" class="newjoin">
			<a class="rm" href="javascript:void(0)">删除</a>
			<img src="{$cinstance->avatar($v['m_uid'])}" />{$v['m_username']}
		</li>
		{/foreach}
		<li><a id="{$iptname}_add" class="add" href="javascript:void(0)">添加</a></li>
	</ul>
</div>
<script>
{literal}
require(['addressbook', 'dialog', 'business'], function(AddrbookComponent){
	{/literal}
	var iptname = '{$iptname}';
	{literal}
	$onload(function() {
		function _onAddMember(e) {
			onAddMemberToAndCc(e, AddrbookComponent, '/member/list?type=' + AddrbookComponent.TYPE_ADDRESSBOOK_MULTISELECT, '', AddrbookComponent.TYPE_ADDRESSBOOK_MULTISELECT);
		}
		
		function _remove(e) {
			var item = e.currentTarget.parentNode;
			item.parentNode.removeChild(item);
			/** 更新表单域中的input:hidden值 */
			updateMembersHiddenIpts($one('#' + iptname + '_div'));
		};
		
		updateMembersHiddenIpts($one('#' + iptname + '_div'));
		$one('#' + iptname + '_add').addEventListener('click', _onAddMember);
		
		/** 新增删除事件 */
		$each($all('a.rm', $one('#' + iptname + '_div')), function(lia) {
			var val = $data(lia.parentNode, 'id');
			if ('undefined' == typeof(val) || 0 == val) {
				return;
			}
			
			lia.addEventListener('click', _remove);
		});
	});
});
{/literal}
</script>
