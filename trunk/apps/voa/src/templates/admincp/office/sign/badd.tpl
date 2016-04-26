{include file="$tpl_dir_base/header.tpl"}
<style type="text/css">
	*{
		margin:0px;
		padding:0px;
	}
	body, button, input, select, textarea {
		font: 12px/16px Verdana, Helvetica, Arial, sans-serif;
	}
	p{
		width:603px;
		padding-top:3px;
		margin-top:10px;
		overflow:hidden;
	}
</style>

{literal}
<script type="text/javascript">
	var range_on = "{/literal}{$list['range_on']}", sign_on = "{$list['sign_on']}", sign_off = "{$list['sign_off']}{literal}";
	$(function(){
		if (0 == range_on) $('#range_on').hide();
		else  $('#range_on').show();

		if (0 == sign_on) $('#sign_on').hide();
		else $('#sign_on').show();

		if (0 == sign_off) $('#sign_off').hide();
		else $('#sign_off').show();

		if (1 == sign_on && $("input[name='remind_on']").val() == '') {
			$("input[name='remind_on']").val("上班时间快到了，快来签个到吧!");
		}

		if (1 == sign_off && $("input[name='remind_off']").val() == '') {
			$("input[name='remind_off']").val('下班了，快去签退吧!');
		}


		$('#r_o_1').on('click', function(){
			 $('#range_on').show();
		});

		$('#r_o_0').on('click', function(){
			$('#range_on').hide();
			/*$("input[name='latitude']").val();
			$("input[name='longitude']").val();
			$("input[name='address']").val();*/
		});

		$('#s_o_1').on('click', function(){
			$('#sign_on').show();
			if ($("input[name='remind_on']").val() == '') {
				$("input[name='remind_on']").val("上班时间快到了，快来签个到吧!");
			}
		});

		$('#s_o_0').on('click', function(){
			 $('#sign_on').hide();
		});

		$('#s_off_1').on('click', function(){
			$('#sign_off').show();
			if ($("input[name='remind_off']").val() == '') {
				$("input[name='remind_off']").val("下班了，快去签退吧!");	
			}

		});

		$('#s_off_0').on('click', function(){
			 $('#sign_off').hide();
		});


	});
</script>

{/literal}
{*腾讯地图js*}
<script charset="utf-8" src="http://map.qq.com/api/js?v=2.exp&key=IR2BZ-NFD3U-NMGVC-4CMIK-3Y3DS-QGFOC"></script>
{literal}
<script>
var searchService, citylocation, map, markers = [];
var latitude_now = "{/literal}{$list['latitude']}", longitude_now = "{$list['longitude']}{literal}";
var latitude, longitude, toggle = 0;

var mark_initialize = function() {
	if ('' != latitude || '' != longitude) {
		var center = new qq.maps.LatLng(latitude,longitude);
		map = new qq.maps.Map(document.getElementById('map_canvas'), {
			center: center,
			zoom: 13
		});
	
		var marker = new qq.maps.Marker({
		    map:map,
		    position: center
		});
		// 注入标记,防止污染
		markers.push(marker);
	}
};

if (latitude_now == '' && longitude_now == '') {
	latitude = 39.1215;
	longitude = 116.4435;
} else {
	toggle = 1;
	latitude = latitude_now;
	longitude = longitude_now;
}

$(function() {
	var init = function() {
		var center = new qq.maps.LatLng(latitude, longitude);
		map = new qq.maps.Map(document.getElementById('map_canvas'), {
			center: center,
			zoom: 13
		});

		var latlngBounds = new qq.maps.LatLngBounds();
		searchService = new qq.maps.SearchService({
			complete : function(results) {
				var pois = results.detail.pois;
				for (var i = 0, l = pois.length; i < l; i ++) {
					var poi = pois[i];
					latlngBounds.extend(poi.latLng);
					var marker = new qq.maps.Marker({
						map: map,
						position: poi.latLng
					});
					marker.setTitle(i + 1);
					markers.push(marker);
				}

				map.fitBounds(latlngBounds);
			}
		});
		citylocation = new qq.maps.CityService({
			complete : function(result) {
				var tes = "{/literal}{$list['longitude']}{literal}";
				if (tes == '' || tes == 0) {
					map.setCenter(result.detail.latLng);
				}
			}
		});
		citylocation.searchLocalCity();

		// ++++++++++++++++++add by ppker,date 09-02 用于反地址解析+++++++++++++++++++++++
		/** 定义 Geocoder 地址解析类 info 类 */
		var info = new qq.maps.InfoWindow({map: map});
		geocoder = new qq.maps.Geocoder({
			complete: function (result) {
				// 赋值
				var region_data = result.detail.addressComponents.province; // 第一区域
				var keyword_data = result.detail.addressComponents.city; // 第二区域
				var address_data = result.detail.addressComponents.district; // 第三区域
				// 如果下拉框选择的值和 点击获取的值不一致 那么更新第二区域选择框内容
				if ($('#region').val() != region_data) {
					geocode_first_change_second(region_data);
				}
				// 清除上次的标记
				clearOverlays(markers);

				map.setCenter(result.detail.location);
				var marker = new qq.maps.Marker({
					map: map,
					position: result.detail.location
				});
				// 注入标记,防止污染
				markers.push(marker);

				// 激活 信息框
				{
					info.open();
					info.setContent('<div style="width:280px;height:100px;">' +
							result.detail.address + '</div>');
					info.setPosition(result.detail.location);
				}
				// 赋值需要上传的地址
				$('#address').val(result.detail.address);
				// 如果下拉框选择的值和 点击获取的值不一致 那么更新第三区域选择框内容
				if ($('#region').val() != region_data) {
					geocode_second_change(region_data, keyword_data);
				}
				// 搜索下拉框赋值
				$('#region').val(region_data);
				$('#keyword').val(keyword_data);
				$('#qu').val(address_data);
				// 如果 具体的门牌号没有,那就用公路的, 没有公路的 就用 乡村的
				if (result.detail.addressComponents.streetNumber != '') {
					$('#jie_dao').val(result.detail.addressComponents.streetNumber);
				} else if (result.detail.addressComponents.street != '') {
					$('#jie_dao').val(result.detail.addressComponents.street);
				} else {
					$('#jie_dao').val(result.detail.addressComponents.town + result.detail.addressComponents.village);
				}
			}
		});

		// 封装解析函数 便于调用
		var jixi = function (lat, lng) {
			var latLng = new qq.maps.LatLng(lat, lng);
			// 调用地址解析类
			geocoder.getAddress(latLng);
		}

		// 编辑时显示默认数据
		if (toggle == 1) {
			var marker = new qq.maps.Marker({
				position: center,
				map: map
			});
			markers.push(marker);
			// 激活信息框
			jixi(latitude_now, longitude_now);
		}

		qq.maps.event.addListener(map, 'click', function(event) {
			clearOverlays(markers);
			// 添加标记
			var marker = new qq.maps.Marker({
				position: event.latLng,
				map: map
			});
			// 注入清除
			markers.push(marker);
			$('#latitude').val(event.latLng.getLat());
			$('#longitude').val( event.latLng.getLng());
			// 调用解析方法
			jixi(event.latLng.getLat(), event.latLng.getLng());
		});
	}
	// end +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	init();
});
{/literal}

//清除地图上的marker
function clearOverlays(overlays) {
	var overlay;
	while (overlay = overlays.pop()) {
		overlay.setMap(null);
	}
}
// 点击搜索
function searchKeyword() {
	var keyword = document.getElementById("keyword").value;
	var region = document.getElementById("region").value;
	var qu = document.getElementById("qu").value;
	var jie_dao = document.getElementById("jie_dao").value;
	clearOverlays(markers);
	searchService.setLocation(region);
	//searchService.search(keyword);
	if (jie_dao != '') {
		searchService.search(jie_dao);
	} else {
		searchService.search(qu);
	}
}
/**
 * 根据第一区域选择项更新第二区域内容
 * @param data
 */
function geocode_first_change_second(data) {
	var change_region = data; // 第一区域的值
	var obj_change = region[change_region]; // 第二区域的对象
	var keyword = ''; // 存放第二区域 下拉框的 html
	for (var y in obj_change) {
		keyword += '<option value="' + y + '">' + y + '</option>';
	}
	$('#keyword').html(keyword);
}
/**
 * 根据第二区域选择项更新第二区域内容
 * @param data
 * @param second_data
 */
function geocode_second_change(data, second_data) {
	var first_region = data; // 第一区域的值
	var second_region = second_data; // 第二区域的值
	var obj_change = region[first_region][second_region]; // 第三区域的对象
	var keyword = ''; // 存放第三区域 下拉框的 html
	for (var z in obj_change) {
		keyword += '<option value="' + obj_change[z] + '">' + obj_change[z] + '</option>';
	}
	$('#qu').html(keyword);
}
</script>
{*end*}

{*地图js 数据*}
<script charset="utf-8" src="{$JSDIR}region.js"></script>
<script type="text/javascript">
//	初始化地图搜索
	$(function () {
		// 初始化 最高级别的 区域
		for(var x in region) {
			$('#region').append('<option value="' + x + '">' + x + '</option>');
		}
		// 根据IP地址初始化地址
		$.ajax({
			'type': 'GET',
			'url': 'http://apis.map.qq.com/ws/location/v1/ip',
			'dataType': 'jsonp',
			'data': {
				output: 'jsonp',
				key: 'UY5BZ-5WHAW-KXORU-R6UUI-Q4WW7-Y4FUX'
			},
			success: function(data){
				var province = data.result.ad_info.province;
				var city = data.result.ad_info.city;
				// 选中最高区域的选择项
				$('#region').val(province);
				// 更新第二区域
				first_change_second();
				// 选中第二区域选择项
				$('#keyword').val(city);
				// 更新第三区域西安则向
				second_change();
			},
			error: function(){
				alert('读取地理位置错误发生错误');
			}
		});
		// 当最高级别的区域框 发生变化时 更新 第二区域
		$('#region').on('change', function() {
			first_change_second();
		});
		// 当第二区域放生变化 时
		$('#keyword').on('change', function() {
			second_change();
		});
		// 当第一区域发生变化时 第三区域也变化
		$('#region').on('change', function() {
			first_change_third();
		});
	});
// 绑定详细地址搜索输入框的 回车 确定搜索
	$(function () {
		$('#jie_dao').keydown(function(event){
			switch(event.keyCode) {
				case 13:
					searchKeyword();
					return false;
			}
		});
	});
	// 当最高级别的区域框 发生变化时 更新 第二区域
	function first_change_second() {
		var change_region = $('#region').val(); // 第一区域的值
		var obj_change = region[change_region]; // 第二区域的对象
		var keyword = ''; // 存放第二区域 下拉框的 html
		for(var y in obj_change) {
			keyword += '<option value="' + y + '">' + y + '</option>';
		}
		$('#keyword').html(keyword);
	}
	// 当第二区域放生变化 时
	function second_change() {
		var first_region = $('#region').val(); // 第一区域的值
		var second_region = $('#keyword').val(); // 第二区域的值
		var obj_change = region[first_region][second_region]; // 第三区域的对象
		var keyword = ''; // 存放第三区域 下拉框的 html
		for(var z in obj_change) {
			keyword += '<option value="' + obj_change[z] + '">' + obj_change[z] + '</option>';
		}
		$('#qu').html(keyword);
	}
	// 当第一区域发生变化时 第三区域也变化
	function first_change_third() {
		var first_region = $('#region').val(); // 第一区域的值
		var second_region = $('#keyword').val(); // 第二区域的值
		var obj_change = region[first_region][second_region]; // 第三区域的对象
		var keyword = ''; // 存放第三区域 下拉框的 html
		for(var z in obj_change) {
			keyword += '<option value="' + obj_change[z] + '">' + obj_change[z] + '</option>';
		}
		$('#qu').html(keyword);
	}
</script>
{*end*}

<div class="panel panel-default">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" id="badd" method="post" action="{$formActionUrl}" data-ng-app="ng.poler.plugins.pc">
			{if !empty($list)}
				<input type="hidden" name="sbid" value="{$list['sbid']}">
			{/if}

			<span id="cd_id_choose" style="display: none;"></span>

			<input type="hidden" name="formhash" value="{$formhash}" />
			<dl class="dl-horizontal font12 vcy-dl-list">
				<dt>班次<span style="color:red;">*</span></dt>
				<dd><input type="text" name="name" class="form-control" required="required" value="{$list['name']}" maxlength="10"></dd>
				<dd></dd>
				<dt>时间段<span style="color:red;">*</span></dt>
				<dd><select name="work_begin" id="work_begin" class="form-control">
						{foreach $str_arr as $val }
							{$val}
						{/foreach}

					</select></dd>
				<dd>至</dd>
				<dt></dt>
				<dd><select name="work_end" id="work_end" class="form-control">
						{foreach $str_arr2 as $val }
							{$val}
						{/foreach}
					</select></dd>
				<dd>工作时段格式为24小时制，如：18:30</dd>

				<dt>日期<span style="color:red;">*</span></dt>
				<dd>
					{if !empty($list)}
					<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="1" {if in_array('周一',$list['work_days'])}checked{/if}>周一</label>

					<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="2" {if in_array('周二',$list['work_days'])}checked{/if}>周二</label>
					<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="3" {if in_array('周三',$list['work_days'])}checked{/if}>周三</label>
					<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="4" {if in_array('周四',$list['work_days'])}checked{/if}>周四</label>
					<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="5" {if in_array('周五',$list['work_days'])}checked{/if}>周五</label>
					<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="6" {if in_array('周六',$list['work_days'])}checked{/if}>周六</label>
					<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="0" {if in_array('周日',$list['work_days'])}checked{/if}>周日<label>
							{else if}
							<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="1" checked>周一</label>
							<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="2" checked>周二</label>
							<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="3" checked>周三</label>
							<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="4" checked>周四</label>
							<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="5" checked>周五</label>
							<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="6" >周六</label>
							<label style="padding:5px 10px;"><input type="checkbox" name="work_days[]" class="text-info" value="0" >周日<label>
									{/if}
				</dd>
				<dt>启用时间<span style="color:red;">*</span></dt>
				<dd>
					<script>
						init.push(function () {
							var options2 = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range').datepicker(options2);
						});
					</script>
					<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
						<input type="text" class="input-sm form-control" id="id_ac_time_before" name="start_begin" placeholder="开始日期" {if !empty($list['start_begin'])} value="{$list['start_begin']}"{else if}value="{$default_date}" {/if} required="required">


					</div>&nbsp;

					<label><input type="checkbox" {if $list['start_end'] == ' '}checked{/if} id="is_check"  value="1" >不设结束日期</label></dd>

				<div class="check" style="display:none;">
					<dd></dd>
					<dt >结束时间：</dt>
					<dd >
						<script>
							init.push(function () {
								var options2 = {
									todayBtn: "linked",
									orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
								}
								$('#bs-datepicker-range2').datepicker(options2);
							});
						</script>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range2">
							<input type="text" class="input-sm form-control" id="id_ac_time_after" name="start_end" placeholder="结束时间" value="{if $list['start_end'] != null}{$list['start_end']}{/if}" >


						</div>

					</dd>
				</div>
				<dd></dd>
				<dt>部门<span style="color:red;">*</span></dt>
				<dd><div class="row">


						<div id="deps_container" class="col-sm-10">

							<!-- angular 选人组件 begin -->
							<div class="angularjs-area " data-ng-controller="ChooseShimCtrl">
								<a class="btn btn-defaul" data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选择部门</a>
							</div>
							<!-- angular 选人组件 end -->

							<pre id="dep_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

							{*{include*}
							{*file="$tpl_dir_base/common_selector_member.tpl"*}
							{*input_type='checkbox'*}
							{*input_name_department='department[]'*}
							{*selector_box_id='deps_container'*}
							{*allow_member=false*}
							{*allow_department=true*}
							{*default_data = $default_departments*}
							{*}*}
						</div>
					</div></dd>
				<dd></dd>
				
				<dt>范围开关：</dt>
				<dd>
					<label><input type="radio" name="range_on" id="r_o_1" value="1" {if $list['range_on'] ==1 }checked{/if} />开启范围</label>&nbsp;&nbsp;&nbsp;
					<label><input type="radio" name="range_on" id="r_o_0" value="0" {if $list['range_on'] ==0 }checked{/if} />关闭范围</label>
				</dd>			
			
				<div id="range_on">	
					<dt>考勤地点：</dt>
					<dd>
						<div class="row">
							<div class="col-md-3">
								<select id="region" class="form-control"></select>
							</div>
							<div class="col-md-3">
								<select id="keyword" class="form-control"></select>
							</div>
							<div class="col-md-3">
								<select id="qu" class="form-control"></select>
							</div>
						</div>
						<div class="row" style="padding: 10px 0 10px 0;">
							<div class="col-md-3">
								<input type="text" id="jie_dao" value="" class="input-sm form-control" />
							</div>
							<div class="col-md-3">
								<input type="button" value="搜索" onclick="searchKeyword()" class = "btn btn-success">
							</div>
						</div>

						<div style="width:603px;height:300px" id="map_canvas"></div>
						<p>搜索之后请点击地图获取准确位置</p>
					</dd>
					<input type="hidden" name="latitude" value="{$list['latitude']}" id="latitude" />
					<input type="hidden" name = "longitude" value="{$list['longitude']}" id="longitude" />
					<input type="hidden" name = "address" value="{$list['address']}" id="address" />
							
					
					<dt>考勤范围：</dt>
					<dd><input type="number" class="form-control" name="address_range" id="address_range"  value="{$list['address_range']}"></dd>
					<dd hidden id="address_range_error" style="color: red;"></dd>
					<dd>单位：米 建议范围500-1000</dd>
				</div>	

				<dd></dd>

				<dt>考勤次数<span style="color:red;">*</span></dt>
				<dd>
					{if $list['sb_set'] < 3}
						<label><input type="checkbox" name="sb_set[]" value="1" {if $list['sb_set'] ==1 }checked{/if}>上班签到</label> &nbsp;<label><input type="checkbox" name="sb_set[]" value="2" {if $list['sb_set'] ==2 }checked{/if}>下班签退</label>
					{else if}
						<label><input type="checkbox" name="sb_set[]" value="1" checked>上班签到</label> &nbsp;<label>
						<input type="checkbox" name="sb_set[]" value="2" checked>下班签退</label>
					{/if}
				</dd>
				<dd>可复选，手机端显示为单次签到/签退或既签到也签退。</dd>
				<dd></dd>

				<dt>签到提醒开关</dt>
					<dd>
						<label><input type="radio" name="sign_on" id="s_o_1" value="1" {if $list['sign_on'] ==1 }checked{/if} />开启签到提醒</label>&nbsp;&nbsp;&nbsp;
						<label><input type="radio" name="sign_on" id="s_o_0" value="0" {if $list['sign_on'] ==0 }checked{/if} />关闭签到提醒</label>
					</dd>

				<div id="sign_on">
					<dt>签到提醒：</dt>
					<dd><input type="text" class="form-control" name="remind_on"  {if empty($list['remind_on'])} value ="" {else if} value="{$list['remind_on']}"{/if}  maxlength="15"></dd>
					<dd>上班前五分钟提醒</dd>
				</div>

				<dd></dd>


				<dt>签退提醒开关</dt>
					<dd>
						<label><input type="radio" name="sign_off" id="s_off_1" value="1" {if $list['sign_off'] ==1 }checked{/if} />开启签退提醒</label>&nbsp;&nbsp;&nbsp;
						<label><input type="radio" name="sign_off" id="s_off_0" value="0" {if $list['sign_off'] ==0 }checked{/if} />关闭签退提醒</label>
					</dd>

				<div id="sign_off">
					<dt>签退提醒：</dt>
					<dd><input type="text" class="form-control" name="remind_off" value="{$list['remind_off']}" maxlength="15"></dd>
					<dd>若下班五分钟内未签退,发起签退提醒</dd>
				</div>
				<dd></dd>
				<dt>晚多久签退算加班:</dt>
				<dd><input type="number" class="form-control" name="late_range" id="late_range" value="{$list['late_range']}"></dd>
				<dd>单位:分钟</dd>
				<dd></dd>
				<dt>晚多久算迟到:</dt>
				<dd><input type="number" class="form-control" name="come_late_range" id="come_late_range" value="{$list['come_late_range']}"></dd>
				<dd>单位:分钟</dd>
				<dd></dd>
				<dt>早退时间范围:</dt>
				<dd><input type="number" class="form-control" name="leave_early_range" id="leave_early_range" value="{$list['leave_early_range']}"></dd>
				<dd>单位:分钟</dd>
				<dd></dd>

				<dd>
					<button type="submit" class="btn btn-primary">提交</button>

					<span class="space"></span>
					{*<a href="javascript:history.go(-1);" role="button" class="btn btn-default">取消</a>*}
					<button class="btn btn-default" id ="history">取消</button>
					<div class="clearfix"><br></div>
				</dd>
			</dl>
		</form>

	</div>
</div>
<script type="text/javascript">

	/* 选人组件 */
	var dep_arr = [];
	var cd_id_choose = '';

	/* 选人组件默认值 */
	// 部门默认值
	dep_arr = {$default_departments};
	if (dep_arr.length != 0) {
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < dep_arr.length; i ++) {
			cd_id_choose += '<input name="department[]" value="' + dep_arr[i]['id'] + '" type="hidden">';
			select_dep_name += dep_arr[i]['name'] + ' ';
		}
		$('#cd_id_choose').html(cd_id_choose);

		// 展示
		if (select_dep_name != '') {
			$('#dep_deafult_data').html(select_dep_name).show();
		} else {
			$('#dep_deafult_data').hide();
		}
	}
	// 选择部门回调
	function selectedDepartmentCallBack(data){
		dep_arr = data;

		// 页面埋入 选择的值
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < data.length; i ++) {
			cd_id_choose += '<input name="department[]" value="' + data[i]['id'] + '" type="hidden">';
			select_dep_name += data[i]['name'] + ' ';
		}
		$('#cd_id_choose').html(cd_id_choose);

		// 展示
		if (select_dep_name != '') {
			$('#dep_deafult_data').html(select_dep_name).show();
		} else {
			$('#dep_deafult_data').hide();
		}
	}

	$(function(){
		// 当编辑时 修改 H1标题
		var is_edit = "{$is_edit}";
		if (is_edit == 1) {
			$('#sub-navbar h1').html('<i class="fa fa-edit page-header-icon"></i>  编辑班次');
		}

{literal}
		//开启/截止时间开关
		var check_check = $('#is_check');
		check_check.on('click', function() {

			if(!check_check.is(':checked')){
				$('.check').show();
			}else{
				$('.check').hide();
				$('#id_ac_time_after').val('');
			}

		});
		$('#address_range').blur(function(){
			var range = 500;
			if($(this).val() < range){
				$(this).val(range);
				$('#address_range_error').html('最小距离为:' + range + '米').show();
			}
		});
		$('#late_range').blur(function(){
			if($(this).val() < 0){
				$(this).val(0);
			}
		});
		$('#come_late_range').blur(function(){
			if($(this).val() < 0){
				$(this).val(0);
			}
		});
		$('#leave_early_range').blur(function(){
			if($(this).val() < 0){
				$(this).val(0);
			}
		});
		var b = "{/literal}{$list['start_end']}{literal}";
		if(b != ' '){
			$('.check').show();
		}
		$('#badd').submit(function(){
			if($('#work_begin').val() > $('#work_end').val()){
				alert('工作结束时间不得早于开始时间');
				return false;
			}
			if($('#address_range').val() < 500){
				$('#address_range').val(500);
			}
			if($('#late_range').val() < 0){
				$('#late_range').val(0);
			}
			if($('#come_late_range').val() < 0){
				$('#come_late_range').val(0);
			}
			if($('#leave_early_range').val() < 0){
				$('#leave_early_range').val(0);
			}
			return true;
		});
		$('#history').on('click', function(){
			if (confirm("确定取消吗？取消后您已填数据不可恢复！")) {
				window.history.back(-1);
				return false;
			} else {
				return false;
			}
		});

	});
</script>
{/literal}
{include file="$tpl_dir_base/footer.tpl"}