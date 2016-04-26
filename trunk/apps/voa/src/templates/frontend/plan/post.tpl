{include file='frontend/header.tpl'}
<body id="wbg_rc_launch">

	<div id="viewstack">
		<section>
		   <form action="{$form_action}" method="post" id="plan_{$ac}">

				<ul class="mod_common_list mod_startend_time time"
					data-range-start="{$range_begin}"
					data-range-end="{$range_finish}"
					{if $selected_begin}data-start-selected="{$selected_begin}"{/if}
					{if $selected_finish}data-end-selected="{$selected_finish}"{/if}
					data-range-length="60"
					data-range-step="0"
					data-range-start-tailstr="00:00"
					data-range-end-tailstr="24:00"
				>
					<li class="begin">
						<label>开始时间：</label>
						<span class="fake init">请选择</span>
						<select id="begin_at" name="begin_at"></select>
					</li>
					<li class="end">
						<label>结束时间：</label>
						<span class="fake init">请选择</span>
						<select id="finish_at" name="finish_at"></select>
					</li>
				</ul>

				<script>
				{literal}
				require(['dialog', 'business'], function(){
					$onload(function(){
						parseStartEndDateSelects('ul.time');
					});
				});
				{/literal}
				</script>

				<h1>日程详情</h1>
				<ul class="mod_common_list desc">
					<li class="tags">
						<p>
						{foreach $types as $key => $value}
							<label>
								<input name="type" type="radio" value="{$key}" {if $plan['pl_type'] eq $key}checked{/if}/>
								<span>{$value}</span>
							</label>
						{/foreach}
						</p>
					</li>
					<li>
						<label>备注:</label><textarea name="subject" id="subject" placeholder="请填写" >{if $plan}{$plan['pl_subject']}{/if}</textarea>
					</li>
				</ul>

				<ul class="mod_common_list desc">
					<li>
						<label>地点:</label><input type="text" name="address" id="address" placeholder="请填写" value="{if $plan}{$plan['pl_address']}{/if}" />
					</li>
					<li class="time">
						<label>提醒时间:</label>
						{include file='frontend/mod_ymdhi_select.tpl' iptname='alarm_at' iptvalue=$plan['_js_alarm_at'] startts=$plan['pl_alarm_at']}
					</li>
				</ul>

				<h1>共享:</h1>
				<div class="mod_common_list_style">
					{include file='frontend/mod_cc_select.tpl' iptname='carboncopyuids' ccusers=$ccusers}
				</div>

				<div class="numbtns double">
					{if $ac !== "new"}<input type="hidden" id="pl_id" value="{$plan['pl_id']}" />{/if}
					<input type="hidden" name="formhash" value="{$formhash}">
					<input type="reset" value="取消" id="btn_go_back" />
					<input type="submit" value="保存" />
				</div>


		   </form>
		</section>
		<menu class="mod_members_panel">
		</menu>
	</div>

	<script>
	var _formname = 'plan_{$ac}';
	{if 'new' == $ac}MStorageForm.init(_formname);{/if}
	{literal}
	$onload(function() {
		//提交
		$one('form').addEventListener('submit', function(e){
			e.preventDefault();

			var pl_subject = $one('#subject');
			var pl_begin_at = $one('#begin_at');
			var pl_finish_at = $one('#finish_at');
			var pl_alarm_at = $one('#alarm_at');

			if (!pl_subject.value || !$trim(pl_subject.value).length) {
				MDialog.notice('请填写日程内容!');
				return false;
			}

			if (!pl_begin_at.value || !$trim(pl_begin_at.value).length) {
				MDialog.notice('请填写日程截止时间!');
				return false;
			}

			if (!pl_finish_at.value || !$trim(pl_finish_at.value).length) {
				MDialog.notice('请填写日程截止时间!');
				return false;
			}

			if (!pl_alarm_at.value || !$trim(pl_alarm_at.value).length) {
				MDialog.notice('请填写日程提醒时间!');
				return false;
			}

			if (true == ajax_form_lock) {
				e.preventDefault();
				return false;
			}

			ajax_form_lock = true;
			MLoading.show('稍等片刻...');
			MAjaxForm.submit(_formname, function(result) {
				MLoading.hide();
			});

			return false;
		});
	});

	function errorhandle_post(url, msg, js) {
		ajax_form_lock = false;
		MDialog.notice(msg);
	}

	function succeedhandle_post(url, msg, js) {
		MStorageForm.clear();
		MDialog.notice(msg);
		setTimeout(function() {
			window.location.href = url;
		}, 500);
	}
	{/literal}
	</script>
	{include file='frontend/footer.tpl'}
