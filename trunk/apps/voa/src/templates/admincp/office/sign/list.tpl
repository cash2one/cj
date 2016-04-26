{include file="$tpl_dir_base/header.tpl"}
<link href="{$CSSDIR}bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$JSDIR}bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="{$JSDIR}bootstrap-datetimepicker.zh-CN.js"></script>
<ul class="nav nav-tabs">
	<li {if $searchBy['sr_type']==0}class="active"{/if}><a href="{$link_all}">全部</a></li>
	<li {if $searchBy['sr_type']==1}class="active"{/if}><a href="{$link_work_on}">上班</a></li>
	<li {if $searchBy['sr_type']==2}class="active"{/if}><a href="{$link_work_off}">下班</a></li>
</ul>
<style type="text/css">
    .loading{
        width:184px;
        height:56px;
        position: absolute;
        top:87%;
        left:37%;
        line-height:56px;
        color:#fff;
        padding-left:56px;
        font-size:15px;
        background: #000 url({$IMGDIR}loading2.gif) no-repeat 10px 50%;
        opacity: 0.7;
        z-index:9999;
        -moz-border-radius:20px;
        -webkit-border-radius:20px;
        border-radius:20px;
        filter:progid:DXImageTransform.Microsoft.Alpha(opacity=70);
    }
</style>
    <div class="panel panel-default font12">
		<div class="panel-heading"><strong>搜索考勤记录</strong></div>
		<div class="panel-body">
		<form id="id-form-search" class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}" data-ng-app="ng.poler.plugins.pc">

			<span id="cd_id_choose" style="display: none;"></span>

            <input type="hidden" name="searchUrl" id="searchUrl" value="{$searchActionUrl}" />
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">	

				<div class="form-group" style="margin-left:-18px; width:100%;">
                    <label class="vcy-label-none col-md-3" for="id_m_username" style="display:block;width: 50px;top: 7px; margin: 0;padding: 0 0 0 10px;">部门：</label>
                    <div class="col-md-7" style="width:auto; padding:0;margin-right: 10px;">

                        <!-- angular 选人组件 begin -->
                        <div class="angularjs-area " data-ng-controller="ChooseShimCtrl">
                            <a class="btn btn-defaul pull-left"
							   data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')" style="background: #fff">
								<i class="fa fa-plus" style="color:#46b8da"></i> 部门
							</a>
							<pre id="dep_deafult_data" style="margin-left: 5px;"></pre>
                        </div>
                        <!-- angular 选人组件 end -->
						<style type="text/css">
							#dep_deafult_data{
							margin-top: 10px;font-size: 12px;letter-spacing: 1px;background-color: #f4b04f;display:none;border-radius: 5px;padding: 2px 5px;color: #ffffff;float: left;border: 1px solid #f4b04f;margin: 5px 5px 0 0;
							}
							#dep_deafult_data:after{
								content: "X";
								cursor: pointer;
							}
						</style>

                    </div>
					<!-- <input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="签到人用户名" value="{$searchBy['m_username']|escape}" maxlength="54" /> -->

			{*		<label class="vcy-label-none" for="id_sr_type">签到类型：</label>
					<select id="id_sr_type" name="sr_type" class="form-control font12" data-width="auto">
						<option value="">不限</option>
						{foreach $signType as $_k => $_n}
							<option value="{$_k}"{if $searchBy['sr_type']==$_k} selected="selected"{/if}>{$_n}</option>
						{/foreach}
					</select>*}
					<label class="vcy-label-none" for="id_sr_type">考勤状态：</label>
					<select id="id_sr_status" name="sr_sign" class="form-control font12" data-width="auto">
						<option value="">不限</option>
						{foreach $signStatus as $_k => $_n}
							{if $_k != $signStatusSet['remove']}
								<option value="{$_k}"{if $searchBy['sr_sign']==$_k} selected="selected"{/if}>{$_n}</option>
							{/if}
						{/foreach}
					</select>
					<span class="space"></span>
					<script>
						init.push(function () {
							var options2 = {
								language: 'zh-CN',
								format: 'yyyy-mm-dd',
								startView: 2,
								minView: 2,
								autoclose: true
							};
							$('#id_signtime_min').datetimepicker(options2);
							$('#id_signtime_max').datetimepicker(options2);
						});
					</script>
					<label class="vcy-label-none" for="id_signtime_min">考勤日期：</label>
					<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
						<input type="text" class="input-sm form-control" style="cursor:default;" readonly id="id_signtime_min" name="signtime_min"   placeholder="开始日期" {if empty($searchBy['signtime_min'])}value="{$begin_d}" {else if} value="{$searchBy['signtime_min']|escape}" {/if} />
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" style="cursor:default;" readonly id="id_signtime_max"  name="signtime_max" placeholder="结束日期"  {if empty($searchBy['signtime_max'])}value="{$end_d}"{else if}value="{$searchBy['signtime_max']|escape}"{/if} />
					</div>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_sr_type">姓名：</label>
					<input type="text" class="form-control form-small" id="m_username" name="m_username" value="{$searchBy['m_username']}" style="width:120px;" maxlength="54" />

					<input type="hidden" value="{$sr_type}" name="sr_type" />

					<span class="space"></span>
					<button type="button" id='mySubmit' class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<button  type="button" id="id-download" class="btn btn-warning form-small form-small-btn margin-left-12"><i class="fa fa-cloud-download"></i> 导出</button>

                    <div id="loading" style="display: none" class="loading">导出中,请稍后...</div>
                </div>
			</div>
		</form>
	</div>
	</div>
	<div class="table-light">
		<div class="table-header">
			<div class="table-caption font12">
				记录列表
			</div>
		</div>
		<table class="table table-striped table-hover font12 table-bordered">
			<colgroup>
				<col class="t-col-12" />
				<col class="t-col-15" />
				<col class="t-col-11" />
				<col class="t-col-11" />
				<col class="t-col-15" />

				<col />
				<col class="t-col-12" />
			</colgroup>
			<thead>
			<tr>
				<th>姓名</th>
				<th>所属部门</th>
				<th>考勤类型</th>
				<th>考勤状态</th>
				<th>考勤时间</th>

				<th>地理位置</th>
				<th>详情</th>
			</tr>
			</thead>
			{if $total > 0}
				<tfoot>
				<tr>
					<td colspan="7" class="text-right vcy-page">{$multi}</td>
				</tr>
				</tfoot>
			{/if}
			<tbody>
			{foreach $list as $_id=>$_data}
				<tr>
					<td>{$_data['m_username']}</td>
					<td>{$_data['cd_name']}</td>
					<td>{$_data['_type']}</td>
					<td>{$_data['_sign']}</td>
					<td>{$_data['_signtime']|escape}</td>

					<td class="text-left">{$_data['sr_address']}</td>
					<td>
						{$base->linkShow($detailUrlBase, $_id, '详情', 'fa-eye', '')}
					</td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="7" class="warning">{if $issearch}未搜索到指定条件的签到记录{else}暂无任何签到记录{/if}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

<script type="text/javascript">

	/* 选人组件 */
	var dep_arr = [];
	var cd_id_choose = '';

	/* 选人组件默认值 */
	// 部门默认值
	dep_arr = {$searchBy['dep_default']};
	if (dep_arr.length != 0) {
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < dep_arr.length; i ++) {
			cd_id_choose += '<input name="cd_id[]" value="' + dep_arr[i]['id'] + '" type="hidden">';
			select_dep_name += dep_arr[i]['cd_name'] + ' ';
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
		if (data.length > 1) {
			alert('只能选一个部门');

			return false;
		}
		dep_arr = data;

		// 页面埋入 选择的值
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < data.length; i ++) {
			cd_id_choose += '<input name="cd_id[]" value="' + data[i]['id'] + '" type="hidden">';
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

		$(document).on("click", "#dep_deafult_data", function(){
			dep_arr = [];
			$('#cd_id_choose').html('');
			$('#id-form-search').submit();
		 });

    $(function(){

        //+---------------------------------------------------
        //| 比较日期差 dtEnd 格式为日期型或者有效日期格式字符串
        //+---------------------------------------------------
       var dateDiff = function(strInterval, dtStart, dtEnd) {
            if (typeof dtEnd == 'string' )//如果是字符串转换为日期型
            {
                dtEnd = StringToDate(dtEnd);
            }
           if (typeof dtStart == 'string' )//如果是字符串转换为日期型
           {
               dtStart = StringToDate(dtStart);
           }
            switch (strInterval) {
                case 's' :return parseInt((dtEnd - dtStart) / 1000);
                case 'n' :return parseInt((dtEnd - dtStart) / 60000);
                case 'h' :return parseInt((dtEnd - dtStart) / 3600000);
                case 'd' :return parseInt((dtEnd - dtStart) / 86400000);
                case 'd+' :return parseInt((dtEnd - dtStart) / 86400000) + 1;
                case 'w' :return parseInt((dtEnd - dtStart) / (86400000 * 7));
                case 'm' :return (dtEnd.getMonth()+1)+((dtEnd.getFullYear()-dtStart.getFullYear())*12) - (dtStart.getMonth()+1);
                case 'y' :return dtEnd.getFullYear() - dtStart.getFullYear();
            }
        }


        $("#mySubmit").click(function(){
            // 日期验证
            var min = $('#id_signtime_min').val(),max = $('#id_signtime_max').val();
            if(min == ''){
                alert('开始日期不能为空！');
                return;
            }else if(max == ''){
                alert('结束日期不能为空！');
                return;
            }

            if(parseInt(StringToDate(min) / 86400000) > parseInt(StringToDate(max) / 86400000)){
                alert('考勤查询开始日期不能大于结束日期，请重新选择！');
                return;
            }


            if(dateDiff('d+', min, max) > 31){
                alert('考勤查询日期范围最大不能超出31天，请重新选择！');
                return;
            }

            var url = $("#searchUrl").val();

            $("#id-form-search").attr("action", url).submit();

        });

        //+---------------------------------------------------
        //| 字符串转成日期类型
        //| 格式 MM/dd/YYYY MM-dd-YYYY YYYY/MM/dd YYYY-MM-dd
        //+---------------------------------------------------
        function StringToDate(DateStr)
        {

            var converted = Date.parse(DateStr);
            var myDate = new Date(converted);
            if (isNaN(myDate))
            {
                var arys = DateStr.split('-');
                myDate = new Date(arys[0],--arys[1],arys[2]);
            }
            return myDate;
        }


        $('#id-download').click(function(){
			// 日期验证
			var min = $('#id_signtime_min').val(),max = $('#id_signtime_max').val();
			if(min == ''){
				alert('开始日期不能为空！');
				return;
			}else if(max == ''){
				alert('结束日期不能为空！');
				return;
			}

            if(parseInt(StringToDate(min) / 86400000) > parseInt(StringToDate(max) / 86400000)){
                alert('考勤查询开始日期不能大于结束日期，请重新选择！');
                return;
            }

            if(dateDiff('d+', min, max) > 31){
                alert('考勤查询日期范围最大不能超出31天，请重新选择！');
                return;
            }

            $('#loading').show();

            var url = "/Sign/Apicp/SignCp/exportRecord";

			if (jQuery('#__dump__').length == 0) {
				jQuery('body').append('<iframe id="__dump__" name="__dump__" src="about:blank" style="width:0;height:0;padding:0;margin:0;border:none;"></iframe>');
			}
			jQuery('#id-form-search').append('<input type="hidden" id="id-dump-input" name="is_dump" value="1" />').attr('target', '__dump__').attr("action", url).submit();

			$('#id-download').prop('disabled', true);
            setTimeout(function timeout () {
                $('#loading').hide();
            }, 2000);

			setTimeout(function timeout () {
				$('#id-download').prop('disabled', false).html('<i class="fa fa-cloud-download"></i> 导出');
			}, 3000);

            $('#id-form-search').removeAttr('target');
            $('#id-dump-input').remove();
		});

	});
</script>

{include file="$tpl_dir_base/footer.tpl"}