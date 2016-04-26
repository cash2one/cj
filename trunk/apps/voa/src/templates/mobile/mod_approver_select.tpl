<input name="{$iptname}" id="{$iptname}" value="" type="hidden" /><!--uid会逗号分割依次放入该hidden的value中-->
<div id="{$iptname}_div" class="mod_members to">
	<ul class="box">
		{if $accepter}
		<li data-id="{$accepter['m_uid']}" id="{$accepter['m_uid']}" class="newjoin"><a class="rm" href="javascript:void(0)"></a><img src="{$cinstance->avatar($accepter['m_uid'])}" />{$accepter['m_username']}</li>
		{/if}
		<li><a id="{$iptname}_add"  class="ui-icon-add ui-icon" href="javascript:void(0)"></a></li>
	</ul>
</div>
<script>
{literal}
require(['addressbook', 'dialog', 'business'], function(AddrbookComponent){
	{/literal}
	var iptname = '{$iptname}';
	{literal}
	$onload(function() {
		/** 新增用户按钮事件 */
		function _onAddMember(e){
			onAddMemberToAndCc(e, AddrbookComponent, '/member/list?type=' + AddrbookComponent.TYPE_ADDRESSBOOK_SINGLESELECT, '', AddrbookComponent.TYPE_ADDRESSBOOK_SINGLESELECT);
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
