{include file='mobile/header.tpl' navtitle='签到' css_file='app_sign.css'}

<div  class="i_dat">
	<h4>{abs($cur_m)}月{abs($cur_d)}日 {$weeknames[$cur_w]}</h4>
	<!-- 	<p>{rgmdate($timestamp, 'H:i')}</p> -->
</div>
<div  class="i_bot_l"></div>
<div id="common_on"></div>
{if empty($work_on) && in_array($sb_set, array(1,3))}
	<div id="show_on_t" style="" ><b>上班 </b>({$sign_set['work_begin_hi']})</div>
	<div id="show_on_c" >
		<div  class="i_s_hi">
			{$on_signtime_hi}
		</div>
		<div  class="i_s_bt">
			<button   class="_sign_btn _sign_on">签到</button>
		</div>
	</div>
{elseif $work_on && in_array($sb_set, array(1,3))}

	<div style="" class="det_sr" data-srid = {$detail_sr_id}>
		<div  class="i_s_bs"><b>上班</b> ({$sign_set['work_begin_hi']})</div>
		<div  class="_sign_reason">添加备注</div>
	</div>
	<div  class="i_on_hi">
		<div  class="i_on_time">
			{$on_signtime_hi}
		</div>
		<div  class="i_on_ad">
			{$work_on['sr_address']}
		</div>
	</div>
{/if}

{if empty($work_off) && in_array($sb_set, array(2,3))}
	<div id="sign-but" style="width:280px;margin:0 auto;margin-top:40px;{if $sb_set == 2 || !empty($work_on)}display:block{else if}display:none{/if}"><b>下班</b> ({$sign_set['work_end_hi']})</div>
	<div id="sign-b-off" style="width:280px;height:160px;margin:0 auto;background:#00A6FF;border-radius:5%;border:1px solid #FFFFFF;{if $sb_set == 2  || !empty($work_on)}display:block{else if}display:none{/if}">
		<div  class="i_s_hi">
			{$off_signtime_hi}
		</div>
		<div class="i_s_bt">
			<button   class="_sign_btn _sign_off">签退</button>
		</div>
	</div>
{elseif $work_off && in_array($sb_set, array(2,3))}
	<div  class="det_sr" id="off_show" data-srid = {$detail_sr_id}>
		<div class="i_s_bs"><b>下班</b> ({$sign_set['work_end_hi']})</div>
		{if $sb_set == 2}
			<div class="_sign_reason">添加备注</div>
		{/if}
	</div>
	<div  class="i_on_hi" id="o_show">
		<div class="i_on_time">
			{$off_signtime_hi}
		</div>
		<div class="i_on_ad">
			{$work_off['sr_address']}
		</div>
	</div>


{/if}

<div id="commontpl"></div>
<input type="hidden" id="work_begin_hi" value="{$sign_set['work_begin_hi']}">
<input type="hidden" id="work_end_hi" value="{$sign_set['work_end_hi']}">
<input type="hidden" id="off_signtime_hi" value="{$off_signtime_hi}">
<input type="hidden" id="sb_set" value="{$sb_set}">
<input type="hidden" id="si_on" value="{$si_on}">
<input type="hidden" id="detail_sr_id" value="{$detail_sr_id}">
<input type="hidden" id= "sbid" value = "{$sbid}">
<input type="hidden" id="sign_type" value="{$sign_type}">
<input type="hidden" id= "select" value="{$select}">
<span id="sign-off" class="ui-list-right _sign" data-current="{$timestamp}" data-workoff="{$work_off_unix}">

		</span>

{foreach $sign_detail as $_detail}
	<div class="i_dl" id="de_li">
		<li class="d_li" id="detail_li">备注:{$_detail['sd_reason']}</li>
	</div>
	{foreachelse}
	<div class="i_dl"  id="de_li">
		<li class="d_li" id="detail_li"></li>
	</div>
{/foreach}

<div class="ui-dialog">
	<div class="ui-dialog-cnt">
		<header class="ui-dialog-hd ui-border-b">
			<h3>选择班次</h3>
			<i class="ui-dialog-close" data-role="button"></i>
		</header>
		<ul id="blist">
			{foreach $batchlist as $k => $v}
				<a href="/frontend/sign/index?batchid={$k}"><li style="line-height:30px;" name="batchlist" value="{$k}">{$v}</li></a>
			{/foreach}
		</ul>

	</div>
</div>
<script class="demo-script">

</script>
<!-- 签到备注弹窗模板 -->
<script type="text/template" id="sign-reason-tpl">
	<div class="ui-form-item-textarea sign-bio-wrapper">
		<textarea id="sign-reason-content" class="sign-bio"><%=content%></textarea>
	</div>
</script>

<!-- 签到、签退显示模板 -->
<script type="text/template" id="sign-tpl">
	<h4 class="ui-nowrap"><%=address%></h4>
	<p><%=time%></p>
</script>

<!-- 地理位置上报数据模板 -->
<script type="text/template" id="sign-location-log-tpl">
	<div class="ui-form-item ui-form-item-link ui-form-qd-address">
		<label><%=time%></label>
		<input type="text" value="<%=address%>" readonly="readonly" />
	</div>
</script>

<script type="text/javascript">
	require(["zepto", "frozen", "jweixin", "localtion", "app_sign"], function ($, fz, wx, get_location, sign) {
		// 载入接口
		var select = $('#select').val();
		if(select == 1){
			$(".ui-dialog").dialog("show");
		}

		{cyoa_jsapi list=['getLocation'] debug=0}

		$(function () {

			var gl = new get_location();
			var s = new sign();

			// 监听 签到/签退 按钮
			$('#cyoa-body').on('click', '._sign_btn', function () {

				var $t = $(this);
				//设置当前按钮不可用
				$t.attr('disabled', false);
				if ($t.hasClass('_sign_off')) {
					// 签退
					var $workoff = $('#sign-off');
					if ($workoff.data('current') < $workoff.data('workoff')) {
						var dia = $.dialog({
							"content": "当前未到下班时间是否确定“签退”？",
							"button": ["取消", "确认"]
						});
						dia.on("dialog:action", function (e) {
							if (e.index == 1) {
								// 点击“确定”
								gl.get(function (r) {
									s.sign(r);
								}, false);
							}
						});
					} else {
						gl.get(function (r) {
							s.sign(r);
						}, false);
					}
				} else {
					// 签到
					gl.get(function (r) {
						s.sign(r);
					}, false);
				}
			});

			$(document).on('click', '._sign_reason', function(){
				s.sign_reason($(this));
			})

			// 监听地理位置上报按钮
			$('._sign_location').click(function () {
				gl.get(function (r) {
					s.sign_location(r);
				}, true);
			});

			// 折叠地理位置上报列表
			$('#cyoa-body').on('click', '#location-log-first', function () {
				$(this).find('div').toggleClass('ui-form-qd-address');
				$('#location-log-list').toggle();
			});

		});

	});

</script>

<div id="sign-temp" style="display: none"></div>

{include file='mobile/footer.tpl'}