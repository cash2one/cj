{include file='cyadmin/header.tpl'}
<style type="text/css">
	div.copy{
		margin-left:-83px;
	}
	.copy .help-block{
		float:left;
		margin-left:285px;
	}
	.copy i.glyphicon-ok, .copy i.glyphicon-remove{
		left:566px;
	}	

</style>

<!-- <link rel="stylesheet" type="text/css" href="{$static_url}js/bootstr_valodator/css/bootstrapValidator.min.css">
<script type="text/javascript" src="{$static_url}js/bootstr_valodator/js/bootstrapValidator.min.js"></script> -->

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">应用设置</h3>
	</div>
	<div class="panel-body">
		<form class="form-horizontal" method="post" action="/enterprise/appset/" id="form_validator">
		   	<div class="form-group">
		   		<label for="inputdate1" class="col-sm-3 control-label"><span style="float:right;">试用期限：</span></label>
		   	    <div class="col-sm-4">
		   	    	<input name="trydate" class="form-control" id="inputdate1" placeholder="试用期限(天)" value="{$request['trydate']['value']}" required="required" />
		   	    </div>
		   	</div>
			
			<div class="form-group">
				<label for="inputdate2" class="col-sm-3 control-label"><span style="float:right;">期限设置：(试用期-即将到期)</span></label>
			    <div class="col-sm-4">
			    	<input name="syq_jjdq_set" value="{$request['syq_jjdq_set']['value']}" class="form-control" id="datestate2" placeholder="(天)" />
			    </div>
			</div>

			<div class="form-group">
				<label for="inputdate3" class="col-sm-3 control-label"><span style="float:right;">(已付费-即将到期)</span></label>
			    <div class="col-sm-4">
			    	<input name="yff_jjdq_set" value="{$request['yff_jjdq_set']['value']}" class="form-control" id="datestate4" placeholder="(天)" />
			    </div>
			</div>

			<div class="form-group">
				<label for="inputdate3" class="col-sm-3 control-label"><span style="float:right;">消息提醒：</span></label>
			    <div class="col-sm-4">
			    	<button type="button" class="btn btn-success" id="xzxx">新增消息</button>
			    </div>
			</div>

			<!-- <div class="form-group" style="width:70%;margin-left:250px;text-align:center;size:18px;">
				<pre class="line-height:10px;"><span class="label label-primary" sytle="size:18px;line-height:40px;">若要重新初始化各个状态请依次从下往上删除，当然最上面的一条是无法删除的！</span></pre>
			</div> -->
			
			{if empty($notice) != true}
				
				{foreach $notice['notice_state'] as $k=>$val}
					<div class="form-group copy" style="margin-left:-45px;">
						<label for="inputdate3" class="col-sm-2 control-label"><span style="float:right;"></span></label>
					    <div class="col-sm-9">
							<!--此处是套件的选项-->
							<select class="form-control pull-left tao" name="notice[notice_mod][]" style="width:113px;margin-right:10px;">
								<option value="0">请选择</option>
								{foreach $mod_array as $kk1=>$vv1}
									<option value={$kk1} {if $kk1 == $notice['notice_mod'][$k]}selected{/if}>{$vv1}</option>
								{/foreach}
							</select>
					    	<select class="form-control pull-left trydate1" name="notice[notice_state][]" style="width:150px;">

					    		{foreach $notice_state as $k1=>$v1}
					    			<option value={$k1} {if $val ==$k1 }selected{/if}>{$v1}</option>
					    		{/foreach}
					    		
					    	</select>
					    	<span class="pull-left" style="line-height:34px;">&nbsp;前&nbsp;</span>
							<input name="notice[agodate][]" value="{$notice['agodate'][$k]}" class="form-control pull-left" id="agodate" placeholder="(天)" style="width:300px;" required data-bv-notempty-message="这里不能为空" min="0" data-bv-greaterthan-inclusive="true" data-bv-greaterthan-message="数字不能小于0" />
							<input type="hidden" name="notice[meid][]" value="{$notice['meid'][$k]}" class="form-control pull-left qxz" style="width:100px;" id="qxz{$k}" />
							<strong class="label label-primary mb_title pull-left" style="margin-top:8px;margin-left:20px;width:118px;">{$news_title[$notice['meid'][$k]]['title']}</strong>
							<button type="button" class="btn btn-success pull-left qxz" style="margin-left:20px;" id="qxz{$k}">请选择</button>
							<button type="button" class="close pull-left closer" aria-label="Close" style="margin-left:20px;margin-top:7px;"><span aria-hidden="true">&times;</span></button>
					    </div>
					</div>
				{/foreach}
			{else}
				
			{/if}			
			
			<div class="form-group" id="hr">
				<hr style="width:80%;margin-top:15px;" />
			</div>
			
			<div class="form-group" style="position:relative;left:120px;">
				<button type="submit" class="btn btn-primary" id="save1">保存</button>
				<button type="button" class="btn btn-default">返回</button>
			</div>	
				
		</form>

		<!-- Modal -->
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel">选择消息模板</h4>
		      </div>
		      <div class="modal-body">
				<div class="mb">
					<form class="mb-search">
						<div class="input-group" style="width:80%;margin-left:40px;">
							<input class="form-control" type="text" name="search" />
							<span class="input-group-addon" id="basic-addon2">搜一搜</span>
						</div>
					</form>
					
					<ul class="nav" style="margin-left:40px;margin-top:15px;" sign="">
						<table class="table table-hover">
						</table>
						{foreach $data1 as $k=>$val}
						<li><input type="radio" name="meiid[]" value="{$val['meid']}" /><span style="margin-left:12px;">{$val['title']|escape}</span></li>
						{/foreach}
					</ul>
					<div class="text-center fy">{$multi}</div>
				</div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
		        <button type="button" class="btn btn-primary mb_save">保存</button>
		      </div>
		    </div>
		  </div>
		</div>


	</div>
</div>
{literal}
<script type="text/javascript">
	$(function(){
		//全局模板变量
		var $mb = null;
		// 几个付费状态的文字变动
		var cc = $("select.trydate1 option:selected[value=0]");
		cc.parent().next().html("&nbsp;后&nbsp;");
		$("select.trydate1 option:selected[value=3]").parent().next().html("&nbsp;后&nbsp;");


		var fy_url = '/enterprise/news/list',
		    ser_url = '/enterprise/appset';
		$('#xzxx').on("click",function(){
			// 首先获取当前的.qxz id 的数字
			var count = $('button.qxz');
			var div = $("<div class=\"form-group copy\" style=\"margin-left:-45px;\"><label for=\"inputdate3\" class=\"col-sm-2 control-label\"><span style=\"float:right;\"></span></label><div class=\"col-sm-9\">{/literal}{$mod_str}{literal}<select class=\"form-control pull-left trydate1\" name=\"notice[notice_state][]\" style=\"width:150px;\"><option value=0 >试用期</option><option value=1 >试用期-即将到期</option><option value=2 >试用期-已到期</option><option value=3 >已付费</option><option value=4 >已付费-即将到期</option><option value=5 >已付费-已到期</option></select><span class=\"pull-left\" style=\"line-height:34px;\">&nbsp;后&nbsp;</span><input name=\"notice[agodate][]\" value=\"\" class=\"form-control pull-left\" id=\"agodate\" placeholder=\"(天)\" style=\"width:300px;\" /><input type=\"hidden\" name=\"notice[meid][]\" value=\"\" class=\"form-control pull-left qxz\" style=\"width:100px;\" id=\"qxz0\" /><strong class=\"label label-primary mb_title pull-left\" style=\"margin-top:8px;margin-left:20px;width:118px;\"></strong><button type=\"button\" class=\"btn btn-success pull-left qxz\" style=\"margin-left:20px;\" id=\"qxz0\">请选择</button><button type=\"button\" class=\"close pull-left closer\" aria-label=\"Close\" style=\"margin-left:20px;margin-top:7px;\"><span aria-hidden=\"true\">&times;</span></button></div></div>");
			//var div = $(".copy").eq(0).clone();

			$('#hr').before(div);
			// 后面添加标记清除
			// div.find(".qxz").after('<button type="button" class="close closer" aria-label="Close"><span aria-hidden="true">&times;</span></button>');	
		});

		$("body").on('click','button[id^=qxz]',function(){
			$mb = $(this); //赋值给全局 其他地方调用
			var s_id = $(this).attr('id');

			$("#myModal").find("ul").eq(0).attr("sign",s_id);
			// 获取隐藏的meid
			var hide_id = $(this).prev().prev().val();
			
			// 初始化模态框的数据 避免因查询造成的数据污染
			var data = { page:1 };
			mb_list(data);	

			if(hide_id){
				//alert(hide_id);
				$("#myModal ul li").find("input[value="+hide_id+"]").attr('checked',true);
			}
			$("#myModal").modal('show');
		});

		$(".mb_save").on("click",function(){
			var mb_val = $("#myModal ul input:radio:checked").val();
			var mb_title = $("#myModal ul input:radio:checked").next().text(); // 获取title
			if(!mb_val) {
				alert("请选择消息模板");
			}



			$mb.prev().prev("input").val(mb_val);
			$mb.prev().text(mb_title);
			$mb = null; // 回收垃圾
			$("#myModal").modal('hide');

			/*var s_id = $("#myModal").find("ul").eq(0).attr("sign");
			//console.log($("#"+s_id).val());
			$("input#"+s_id).val(mb_val);
			$("input#"+s_id).next().text(mb_title);
			$("#myModal").modal('hide');*/
		});

		// 模板消息 选中的优化
		$('.modal-dialog .mb').on('click','ul.nav li',function(){
			$(this).children('input').attr('checked',true);
		});

		// 初始化的 select的禁用设置
		 $('div.copy').each(function(i,val){
		 	var tao_num = $(this).find('select.tao').children('option:selected').val();
		 	if(tao_num == 0){
		 		$(this).find('select.trydate1').find('option[value=3]').attr('disabled','disabled');
		 		$(this).find('select.trydate1').find('option[value=4]').attr('disabled','disabled');
		 		$(this).find('select.trydate1').find('option[value=5]').attr('disabled','disabled');
		 	}else if(tao_num == 1 || tao_num == 5){
		 		$(this).find('select.trydate1').find('option[value=0]').attr('disabled','disabled');
		 		$(this).find('select.trydate1').find('option[value=1]').attr('disabled','disabled');
		 		$(this).find('select.trydate1').find('option[value=2]').attr('disabled','disabled');
		 	}
		 });


		// select 的相互禁用
//		$('body').on('change','.copy select.tao',function(){
//			var num_id = $(this).children('option:selected').val();
//			if(num_id == 0){
//				$(this).next().find("option:selected").attr("selected",false);
//				$(this).next().find("option[value=0]").attr("selected",true);
//
//				$(this).next().find("option[value=3]").attr("disabled","disabled");
//				$(this).next().find("option[value=4]").attr("disabled","disabled");
//				$(this).next().find("option[value=5]").attr("disabled","disabled");
//
//				$(this).next().find("option[value=0]").attr("disabled",false);
//				$(this).next().find("option[value=1]").attr("disabled",false);
//				$(this).next().find("option[value=2]").attr("disabled",false);
//
//			}
//
//			if(num_id == 1 || num_id == 5){
//				$(this).next().find("option:selected").attr("selected",false);
//				$(this).next().find("option[value=3]").attr("selected",true);
//
//				$(this).next().find("option[value=0]").attr("disabled","disabled");
//				$(this).next().find("option[value=1]").attr("disabled","disabled");
//				$(this).next().find("option[value=2]").attr("disabled","disabled");
//
//				$(this).next().find("option[value=3]").attr("disabled",false);
//				$(this).next().find("option[value=4]").attr("disabled",false);
//				$(this).next().find("option[value=5]").attr("disabled",false);
//			}
//
//		});




		// select 选框的变化 更换对应的请选择按钮所隐藏的id
		$('body').on('change','.copy select.trydate1',function(){
			var num_id = $(this).children('option:selected').val(); // 实时num_id
			$(this).parent().children("button.qxz").attr('id','qxz'+num_id);
			// 隐藏input 用于存放模板id
			$(this).parent().children("input.qxz").attr('id','qxz'+num_id);

			// 前几天 后几天 字体的变化
			if(num_id == 0 || num_id == 3){
				$(this).next().html('&nbsp;后&nbsp;');
			}else{
				$(this).next().html('&nbsp;前&nbsp;');
			}
			// 关于选择的时候出现相同的 期限设置，要进行提示
			/*var array_select = [];
			var s_select = $('select.trydate1');
			s_select.each(function(i,val){
				var s_val = $(this).find('option:selected').val();
				array_select.push(s_val);
			});
			//console.log(array_select); // ["0", "1", "2", "3", "2", "5"]
			// 进行判断数组内容重复
			// 先排序 不管什么情况 数组只要有相同的值 排序后他俩肯定是相邻的
			var sort_array = array_select.sort();
			for(var i = 0; i < array_select.length; i++){
				if(sort_array[i] == sort_array[i+1]){
					alert('您当前选择的期限有重复，请认真选择！');
					return;
				}
			}*/

			
		});


		// 清除
		$('body').on('click','.closer',function(){
			// 第一条不可以删除
			/*if(!$(this).parent().parent().prev('.copy').length){
				return;
			}*/
			/*if($(this).prev().attr('id') == 'qxz0'){
				return;
			}*/
			if($('.copy').length > 0){
				//alert($('.copy').length);
				var c = $(this).parent().parent().remove();
			}else{
				return;
			}
			
		});

		// 模态框分页
		$('.fy').on('click','a',function(e){
			e.preventDefault();
			var href = $(this).attr('href');

			if(href.indexOf('?')>=0){
				var n = href.indexOf('page');
				var data = {
					page: href.substring(n+5)
				}
				mb_list(data);
				// 默认数据的显示
				var sign = $('#myModal .mb ul').attr('sign');
				var mb_id = $("input#"+sign).val();
				if(mb_id){
					$("#myModal ul li").find("input[value="+mb_id+"]").attr('checked',true);
				}
			}
		});

		var mb_list = function(d){

			$.ajax(fy_url, {
				type : 'get',
				async: false, //此处需要同步
				data : { mo:1,page:d.page },
				dataType : 'json',
				success : function(d){
					var lis = '';
					$.each(d.list,function(i,n){
						lis+='<li><input type="radio" name="meiid[]" value="'+this.meid+'" /><span style="margin-left:12px;">'+this.title+'</span></li>';
					});
					$('.mb ul').html(lis);
					$('.fy').html($(d.page));
				}
			});
		};

		// 模态框搜索功能
		$("#basic-addon2").on('click',function(){
			var search = $("[name=search]").val();
			search = String(search);
			$.ajax(ser_url, {
				type : 'get',
				data : { search:search },
				dataType : 'json',
				success : function(d){
					if(false != d.list){
						var lis = '';
						$.each(d.list,function(i,n){
							lis+='<li><input type="radio" name="meiid[]" value="'+this.meid+'" /><span style="margin-left:12px;">'+this.title+'</span></li>';
						});
						$('.mb ul.nav').html(lis);
						$('.fy').html($(d.multi));
					}else{
						$('.mb ul.nav').html("<p>没有找到你要搜索的模板！</p>");
						$('.fy').empty();
					}
				}
			});

		});

		// 手工js写验证 太粗糙了 可用bootstrap匹配的插件进行规范验证
		$('#save1').click(function(e){
			//e.preventDefault();
			//var inputdate1 = $('#inputdate1').val();
			//if('' == inputdate1) alert('使用期限不可为空！');
			var meids = $(".qxz[name^=notice]");
			//alert(meids.length); 
			$.each(meids, function(i,n){
				if($(this).val() == ''){
					alert('消息模板必须选择完整!');
					e.preventDefault();
					return;
				}
			});

			// 验证设置的前多少天
			var agos = $("input[name^='notice[agodate]']");
			$.each(agos, function(i,n){
				if($(this).val() == ''){
					alert('设置天数不能为空，请填写数字！');
					e.preventDefault();
					return;
				}
			});

			return;
		});

		// bootstrap的表单验证

		$('#form_validator').bootstrapValidator({
		    message: '不是有效的数据',
		    feedbackIcons: {
		        valid: 'glyphicon glyphicon-ok',
		        invalid: 'glyphicon glyphicon-remove',
		        validating: 'glyphicon glyphicon-refresh'
		    },
		    fields: {
		        trydate: {
		            message: '试用期限填写的不是有效数据，不能小于0',
		            validators: {
		                notEmpty: {
		                    message: '试用期限必选，且不可为空'
		                },
		                between: {
		                            min: 0,
		                            max: 9999999999,
		                            message: '不能小于0'
		                }
		            }
		        },

		        syq_jjdq_set: {
		            message: '试用期-即将到期填写的不是有效数据，不能小于0',
		            validators: {
		                notEmpty: {
		                    message: '试用期-即将到期必选，且不可为空'
		                },
		                between: {
		                            min: 0,
		                            max: 9999999999,
		                            message: '不能小于0'
		                }
		            }
		        },

		        yff_jjdq_set: {
		            message: '已付费-即将到期填写的不是有效数据，不能小于0',
		            validators: {
		                notEmpty: {
		                    message: '已付费-即将到期必选，且不可为空'
		                },
		                between: {
		                            min: 0,
		                            max: 9999999999,
		                            message: '不能小于0'
		                }
		            }
		        },

		        // 验证 或者是因为有中括号[]的缘故了
		        
		    }
		});

	})
	
</script>
{/literal}
{include file='cyadmin/footer.tpl'}