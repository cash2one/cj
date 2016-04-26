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
<script src="http://api.map.baidu.com/api?v=2.0&ak=ZgwqnK2tl1y2k6oRI8DCyZ2kjmOcsz22" type="text/javascript">

</script>





{*<script charset="utf-8" src="http://map.qq.com/api/js?v=2.exp&libraries=convertor&key=UY5BZ-5WHAW-KXORU-R6UUI-Q4WW7-Y4FUX"></script>*}




<div class="panel panel-default">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
			<table class="table" >
				<colgroup>
					<col class="t-col-12" />
					<col class="t-col-11" />
					<col class="t-col-5" />
					<col class="t-col-5" />
					<col class="t-col-5" />
					<col class="t-col-40" />
					<col class="t-col-5"/>

				</colgroup>
				<thead>
				<tr>
					<td>上报人:</td>
					<td class="text-right">{$data['m_username']}</td>
					<td></td>
					<td></td>
					<td></td>
					<td rowspan="5">

						<div style="width:603px;height:300px" id="map_canvas"></div>
						</dd>

						<dd></dd></td>


				</tr>
				<tr>
					<td>所属部门:</td>
					<td colspan = 2>{$data['cd_name']}</td>
					<td></td>
					<td></td>




				</tr>
				<tr>
					<td>上报时间:</td>
					<td colspan = "2">{$data['sl_signtime']}</td>

					<td></td>
					<td></td>



				</tr>

				<tr>
					<td>上报位置:</td>
					<td colspan = 2>{$data['sl_address']}</td>
					<td></td>
					<td></td>




				</tr>
				<tr>
					<td>上传图片:</td>
					<td colspan="3" class="text-left">
						{foreach $data['attachs'] as $_img}
							<a href="{$_img['url']}" target="_blank"><img src="{$_img['url']}" alt="" width=50 height= 50></a>
						{/foreach}
					</td>

					<td></td>



				</tr>
				<tr>
					<td>备注:</td>
					<td colspan = 2>{$data['sl_note']}</td>
					<td></td>
					<td></td>




				</tr>
				</thead>

				<tbody>


				</tbody>
			</table>



		</form>

	</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}

<script type="text/javascript">


    //GPS坐标
    var x = {$data['sl_longitude']};
    var y =  {$data['sl_latitude']};
    var address = "地址：{$data['sl_address']}";

    var points = [new BMap.Point(x,y)];
    var marker = new BMap.Marker(points);  // 创建标注
    var opts = {
        width : 200,     // 信息窗口宽度
        height: 120,     // 信息窗口高度
        title : "外出考勤" , // 信息窗口标题
        enableMessage:false//设置允许信息窗发送短息
        //message:""
    };
    var infoWindow = new BMap.InfoWindow(address, opts);// 创建信息窗口对象

    //地图初始化
    var mapOption = {
        enableMapClick : false
    };
    var bm = new BMap.Map("map_canvas",mapOption);//构造底图时，关闭底图可点功能
    bm.centerAndZoom(new BMap.Point(x,y), 15);

    bm.addControl(new BMap.NavigationControl());
    bm.enableScrollWheelZoom(true);
    bm.removeEventListener("click", false);//注销地图点击事件
    bm.disableDoubleClickZoom();


    //坐标转换完之后的回调函数
    translateCallback = function (data){
        if(data.status === 0) {
            for (var i = 0; i < data.points.length; i++) {
                bm.addOverlay(new BMap.Marker(data.points[i]));
                bm.setCenter(data.points[i]);
                bm.addOverlay(marker);// 将标注添加到地图中
                bm.openInfoWindow(infoWindow,data.points[i]); //开启信息窗口
            }
        }
    };

    setTimeout(function(){
        var convertor = new BMap.Convertor();
        convertor.translate(points, 1, 5, translateCallback);
    }, 1000);




</script>
