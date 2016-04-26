<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="right-col">
				<div class="panel tl-body">
					<div class="train-items">
						<span class="train-item__title">*报名信息</span>
						<ul class="train-item-ul">
							{foreach $list as $val}
								<li class="train-item-ul__li" data-value="{$val['sid']}">
									<a class="train-item-ul__cur train-item-ul__link js_train_item_link" data-id="{$val['sid']}" data-required="{$val['is_required']}"  data-type="{$val['fieldtype']}" href="javascript:void(0);">{$val['fieldname']}</a>
									{if $val['sid'] >= 4}
										<a href="javascript:void(0);" data-hash="{$formhash}"  data-value="{$val['sid']}" class="js_train_item_del train-item__del">X</a>
									{/if}
								</li>							
							{/foreach}
						</ul>
					</div>
					<div class="train-add">
						<a href="javascript:void(0);" id="js_train_setting_btn" class="train-item-ul__link train-add__btn ">+</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/template" id="js_train_item_tpl">
	<li class="train-item-ul__li" data-value="filed_id">
		<a class="train-item-ul__cur train-item-ul__link" href="javascript:void(0);" >fieldname</a>
		<a href="javascript:void(0);" data-value="filed_id" data-hash="{$formhash}"  class="js_train_item_del train-item__del">X</a>
	</li>
</script>
<script type="text/template" id="js_train_setting_edit">
<form id="js_train_setting_form">
	<ul class="train-dialog__ul">
		<li class="train-dialog__ul__li">
			<label>选项名称:</label>
			<input type="text" name="fieldname" maxlength="6">
		</li>
		<li class="train-dialog__ul__li">
			<label>是否必填:</label>
			<input type="checkbox" name="is_required" value="required">
		</li>
		<li class="train-dialog__ul__li">
			<label>填写类型:</label>
			<span class="train-dialog__span"><input name="fieldtype" class="train-dialog__input" value="number"  type="radio">数字</span>
			<span class="train-dialog__span"><input name="fieldtype" class="train-dialog__input" value="text" type="radio">文本</span>	
		</li>
		<li>
			<input type="hidden" name="sid" value=""/>
		</li>		
	</ul>
</form>
</script>
<script type="text/template" id="js_train_setting_dialog">
<form id="js_train_setting_form">
	<ul class="train-dialog__ul">
		<li class="train-dialog__ul__li">
			<label>选项名称:</label>
			<input type="text" name="fieldname" maxlength="6">
		</li>
		<li class="train-dialog__ul__li">
			<label>是否必填:</label>
			<input type="checkbox" name="is_required" value="required">
		</li>
		<li class="train-dialog__ul__li">
			<label>填写类型:</label>
			<span class="train-dialog__span"><input name="fieldtype" class="train-dialog__input" value="number"  type="radio">数字</span>
			<span class="train-dialog__span"><input name="fieldtype" class="train-dialog__input" value="text" type="radio">文本</span>	
		</li>
		<li class="train-dialog__ul__li">	
			<a class="train-dialog__btn" id="js_train_close" href="javascript:void(0)">取消</a>
			<a class="train-dialog__btn" data-hash="{$formhash}" id="js_train_add" href="javascript:void(0)">添加</a>
		</li>
	</ul>
</form>
</script>
<script type="text/javascript">
	$.train.setting_add();
    $.train.setting_edit();
</script>