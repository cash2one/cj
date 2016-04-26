{include file='mobile/header.tpl' navtitle="发起会议1/3"}
<form action="?step=2" method="POST" autocomplate="off">
<div class="ui-form">
	{cyoa_select
		title='地点'
		attr_name='addr'
		attr_options=$adds
		label_class='ui-icon add'
		div_class="ui-form-item ui-form-item-order ui-form-item-link ui-border-b"
	}
	
	{cyoa_select
		title='日期'
		attr_name='date'
		attr_options=$dates
		label_class='ui-icon date'
		div_class="ui-form-item ui-form-item-order ui-form-item-link ui-border-b"
		data_callback='change_time'
	}
	{cyoa_select
		title='时间'
		attr_name='time'
		attr_options=$times
		label_class='ui-icon time'
		div_class="ui-form-item ui-form-item-order ui-form-item-link ui-border-b"
	}
	
	{cyoa_select
		title='时长'
		attr_name='length'
		attr_options=$lengths
		label_class='ui-icon duration'
		attr_value='1'
	}
	
</div>
<div class="ui-btn-wrap">
	<button id="next" class="ui-btn-lg ui-btn-primary" type="button">快速预订</button>
</div>
</form>

{include file='mobile/footer.tpl'}
<script>
//修改日期时,修改时间select的选项
function change_time()
{
	var today = $('select[name=date]').find('option').eq(0).text();
	var date = $('select[name=date]');
	var time = $('select[name=time]');
	var is_today = date.val() == today;	//判断是否当天
	time.find('option').show().removeAttr('disabled');
	var v;
	var current = getHI();
	if(is_today) {
		time.find('option').each(function (i, e){
			if(e.value < current) {
				$(e).hide().attr('disabled', true);
			}else{
				v = e.value;
				return false;
			}
		});
		if(time.val() < getHI()) {
			time.val(v);
		}
		//同步值
		time.prev('p').text(time.val())
	}else{
		v = time.find('option').eq(0).val();
	}
}
require(["zepto"], function($) {
	
	change_time();
	
	//提交到下一步
	var pass = false;
	$('#next').click(function (){
		var today = $('select[name=date]').find('option').eq(0).text();
		var is_today = $('select[name=date]').val() == today;	//判断是否当天
		if(is_today) {
			if($('select[name=time]').val() < getHI()) {
				$.tips({
			        content:'不能早于当前时间'
			    });
				return false;
			}
		}
		if(!$('select[name=addr]').val()) {
			$.tips({
		        content:'没有会议室,请先在后台创建会议室'
		    });
			return false;
		}
		if(pass) {
			return true;
		}
		$.post('?step=check', $('form').serialize(), function (json){
			if(!json.state) {
				$.tips({
			        content:json.info
			    });
			}else{
				pass = true;
				$('form').submit();
			}
		}, 'json');
		return false;
	});
});
//获取当前"时:分"
function getHI()
{
	var date = new Date();
	var h = date.getHours();
	if(h < 10) h = '0' + h;
	var m = date.getMinutes();
	if(m < 10) m = '0' + m;
	return h + ':' + m;
}
</script>