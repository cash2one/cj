{include file='frontend/header.tpl'}
<body id="wbg_db_launch">

	<form action="{$form_action}" method="post" id="todo_{$ac}">

	{if $ac === 'new'}
		<label>完成<input name="completed" type="checkbox" value="1"/></label>
	{/if}

		<h1>待办内容</h1>
		<ul class="mod_common_list">
			<li class="cont">
				<textarea name="subject" id="subject" required>{if $ac === 'edit'}{$todo['_subject']}{/if}</textarea>
			</li>
			<li class="top">
				<label>置顶<input name="stared" type="checkbox" value="1" {if $todo['_stared'] === 1}checked{/if}/></label>
			</li>
		</ul>

		<ul class="mod_common_list">
			<li class="time1">
				<label>截止时间</label>
				<input name="exptime" value="{if $todo['td_exptime'] > 0}{$todo['_exptime']}{/if}" type="hidden" id="exptime-value"/>
				<span class="fake ph" id="exptime">请选择</span>
			</li>
			<li class="time2">
				<label>提醒时间</label>
				<input name="calltime" value="{if $todo['td_calltime'] > 0}{$todo['_calltime']}{/if}" type="hidden" id="calltime-value"/>
				<span class="fake ph" id="calltime">请选择</span>
			</li>
		</ul>

		<input type="hidden" name="formhash" value="{$formhash}" />

	{if $ac === 'new'}
		<div class="numbtns double">
			<input type="reset" value="取消" id="btn_go_back" />
			<input type="submit" value="保存" />
		</div>
	{else}
		<div class="numbtns triple">
			<input type="hidden" id="td_id" value="{$todo['td_id']}" />
			<input type="reset" value="取消" id="btn_go_back" />
			<a href="#" class="mod_button2" onclick="deleteTodo()">删除</a>
			<input type="submit" value="保存" />
		</div>
	{/if}

	</form>

<script>
var _formname = 'todo_{$ac}';
{if 'new' == $ac}MStorageForm.init(_formname);{/if}
{literal}
$onload(function() {
	$one('form').onsubmit = function(e) {
		var td_subject  = $one('#subject');

		e.preventDefault();

		if (!td_subject.value || !$trim(td_subject.value).length) {
			MDialog.notice('请填写待办事项内容!');
			return false;
		}

		if (true == ajax_form_lock) {
			return false;
		}

		ajax_form_lock = true;
		MLoading.show('稍等片刻...');
		MAjaxForm.submit(_formname, function(result) {
			MLoading.hide();
		});

		return false;
	}
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

<script>
var deleteTodo = function () {
	var ajaxLock = false;

	(function(){
		if (ajaxLock) return;
		ajaxLock = true;

		MLoading.show('正在更新...');
		$ajax(
			'/todo?ac=delete', 'GET',
			{
				'id': $one('#td_id').value
			},
			function(ajaxResult){

				if (ajaxResult.response === "success") {
					window.location.href = '/todo';
				} else {
					alert('something happend');
				}

				MLoading.hide();
				ajaxLock = false;
			},
			true
		);
	})()
}
</script>

<script>
require(['dialog', 'business', 'timeslider', 'formvalidate'], function(){
	var ajaxLock = false;

	$onload(function(){
		//提交
		$one('form').addEventListener('submit', function(e){
			e.preventDefault();

			var tool = MOA.form.FormValidate;

			if (!tool.requiredValid( $one('.cont textarea') )){
				MDialog.notice('请填写待办内容');
				return false;
			}

			return true;
		});

		//时间设置
		var daysRange1 = (isLeapYear() ? 367: 366),
			timeConfig1 =
			{
				rangeDays: daysRange1, //时间范围的业务逻辑为: 今天至一年后
				noticeMin: '请选择晚于当前的时间!',
				noticeMax: '请选择'+ daysRange1 +'天以内的时间!',
				startDay: {if $todo['td_exptime']}new Date({$todo['td_exptime'] * 1000}){else}new Date{/if},
				callback: function(date){}
			};
		parseIOS6styleTimeChooser(
			$one('#exptime'),
			timeConfig1
		);

		var daysRange2 = (isLeapYear() ? 367: 366),
			timeConfig2 =
			{
				rangeDays: daysRange2, //时间范围的业务逻辑为: 今天至一年后
				noticeMin: '请选择晚于当前的时间!',
				noticeMax: '请选择'+ daysRange2 +'天以内的时间!',
				startDay: {if $todo['td_calltime']}new Date({$todo['td_calltime'] * 1000}){else}new Date{/if},
				callback: function(date){}
			};
		parseIOS6styleTimeChooser(
			$one('#calltime'),
			timeConfig2
		);

	});

});
</script>
{include file='frontend/footer.tpl'}
