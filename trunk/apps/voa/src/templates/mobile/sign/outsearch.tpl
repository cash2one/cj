{include file='mobile/header.tpl' css_file='app_sign.css'}
<div class="ui-tab">
<div class="ui-btn-group-tiled ui-padding-bottom-0 ui-padding-top-0 sale-ui-title" >
	
	{if $permission == 1}
		<p class="ui-btn-lg ui-btn-primary clearfix btn-width ui-selector _sel" style="background:#FFFFFF;color:#4C4C49;">
			<span class="choose">{$current}</span><select name="update" id="update" >
				{foreach $day as $_key => $_val}
				<option  value="{$_key}" {if $current == $_key}{/if}>{$_val}</option>

				{/foreach}
			</select>
			<i id="arrow1" class="label-tag sale-label-tag label-tag-down"></i>
		</p>
	{else}
		<p class="ui-btn-lg ui-btn-primary clearfix btn-width ui-selector _sel" style="background:#FFFFFF;color:#4C4C49;width:100%;">
			<span class="choose">{$current}</span><select name="update" id="update" >
				{foreach $day as $_key => $_val}
				<option  value="{$_key}" {if $current == $_key}{/if}>{$_val}</option>

				{/foreach}
			</select>
			<i id="arrow1" class="label-tag sale-label-tag label-tag-down"></i>
		</p>
	{/if}

	{if $permission == 1}
		<div class="sale-title-border">
			<div class="sale-title-border">
			</div>
		</div>

		<p id="m_selector" class="ui-btn-lg ui-btn-primary clearfix btn-width" style="background:#FFFFFF;">
			<a  class="ui-icon-add" style="width:100%;height:100%;color:#4C4C49;">选人
				<i class="label-tag sale-label-tag label-tag-down"></i></a>
		</p>
	{/if}
</div>
<div class="ui-select-content ui-form" style="display: none;">
	<div class="ui-form sale-ui-nowrap">
		<!-- <div class="ui-form-datetime">
			<input name="udate" value="2015-07-31 " class="_input_datetime_value" type="hidden" ><input type="date" class="ui-form-item-date _input_datetime" value="2015-07-31" id="udate">
		</div> -->
		{cyoa_input_datetime
		attr_value=' '
		title="选择日期"
		attr_name="udate"
		}
		<input type="hidden" id="cm_uid" name="cm_uid" value="" />
	</div>
	<div class="ui-btn-group-tiled ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
		<button type="button" id="cancel" name="cancel" class="ui-btn-lg">取消</button>
		<button  id="sure" name="sure" class="ui-btn-lg ui-btn-primary">确定</button>
	</div>
</div>
<div class="o_t_t">
	<div class="o_title">
		地理位置列表
	</div>
</div>
<div id="list_active">
</div>
{literal}
	<script id="list_tpl" type="text/template">
		<% if(_.isEmpty(list)){ %>
		<section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i></i>
			<p>暂无数据</p>
		</section>
		<% }else{ %>
		<% _.each(list, function(item) { %>
		<ul class="ul_show" style="background:#FFFFFF;line-height:55px;border-bottom:1px solid #DBD8D3;" >
			<li class="li_bor">
				<div class="clearfix o_lid" ><div class="o_lil"><%=item._sl_signtime%> <%=item.sl_address%></div><div class="o_lir"><img src='/misc/images/arrow_down.png' class="i_dow">


				</div></div>
			</li>
			<li class="img_show" style="display:none;" >

				<div class="o_imgd">
					<!-- 上传图片 -->


					<div class="ui-form-item ui-form-item-show upload" id="image_view_id" data-id="image_view_id" data-name="at_ids" data-thumbsize="45" data-bigsize="0" data-onlymodule="0">
						<div class="upload-box clearfix _view_image">
							<% if(!_.isEmpty(item.attachs)){ %>
							<% _.each(item.attachs, function(ite) { %>
											<span >
												<span class="o_imgs">
													
													<div class="ui-badge-wrap">
														<img src="<%=ite.thumb%>" data-big="<%=ite.url%>?_num=<%=ite.num%>" alt="" border="0" class="_view_preview" data-id="image_view_id" data-aid="<%=ite.at_id%>" style="max-height:45px;max-width:45px;margin-top:1px" />
													</div>
													
												</span>
											</span>


							<%})%>
							<%}%>

							<input type="hidden" id="image_view_id_input" name="at_ids" value="" />
						</div>
					</div>


				</div>
			</li>
		</ul>
		<%});%>
		<%}%>
	</script>
{/literal}
{literal}
	<script type="text/javascript">

		var s = null;
		require(["zepto", "underscore", "showlist", "submit", "addrbook", "frozen", "jweixin", "showimg"], function($, _, showlist, submit, addrbook, fz, wx, showimg) {


			var sl = new showlist();
			list(sl, '', '', true);


			$('#update').change(function () {
				var udate = this.value;

				$(".choose").text(udate);
				var cm_uid = $("#cm_uid").val();
				list(sl, udate, cm_uid, false);
			});
			var ab = new addrbook();
			ab.show({
				"dist": $("#addrbook"),
				"src": $("#m_selector"), // 触发对象,
				"ac" : "byuser",
				"tabs": {
					"user": {
						"name": "选择用户",
						"input": $("#cm_uid")
					}
				},
				"cb": function() {
					// status_id = $("#select_status").val();
					var udate = $("._input_datetime_value").val();
					if(udate == undefined){
						udate = ' ';
					}
					var cm_uid = $("#cm_uid").val();
					list(sl, udate, cm_uid, false);
				}
			});
			if (s == null) {
				s = new showimg();
			}
		});

		//获取数据列表
		function list(sl, udate, cm_uid, is_first) {
			var ajax = {'url': '/api/sign/get/outsearch?m_uid='+cm_uid+'&udate='+udate};
			if (is_first) {
				sl.show_ajax(ajax, {
					"dist": $('#list_active'),
					"tpl": $("#list_tpl"),
					"datakey": "list",
					"cb": function(dom) {
						//绑定图片点击触发动作
						$('._view_image img').on('click',function (event) {
							s.show($(this), '._view_image');
							event.stopPropagation();
							return false;
						});

						$('.ul_show').on('click',function(){
							if($(this).find('.img_show').css('display') == 'none'){
								$(this).find('.img_show').css('display','block');
								$(this).find('.i_dow').attr('src','/misc/images/arrow_right.png');

							}else{
								$(this).find('.img_show').css('display','none');
								$(this).find('.i_dow').attr('src','/misc/images/arrow_down.png');
							}


						})

					}
				});
			} else {
				sl.reinit(ajax);
			}
			$("#list_active").on("click", ".li_href", function(e) {
				window.location.href = $(this).data("href");
			});
		}
	</script>
{/literal}
{*
<script type="text/javascript">
	function wxjsapi_config(owx) {
		if (typeof(owx) != 'undefined') {
			var wx = owx;
		}
		{cyoa_jsapi list=['previewImage'] debug=0}
	}
	if (typeof(wx) == 'undefined' || !window.wx) {
		require(["jweixin"], function (wx) {
			wxjsapi_config(wx);
		});
	} else {
		wxjsapi_config(wx);
	}



</script>
*}



{include file='mobile/footer.tpl' SHOWIMG=1}
