{include file='mobile/header.tpl' css_file='app_news.css' navtitle='未读人员'}
{cyoa_user_show
	userids=$userids
	styleid=1
	title=''
}
<div class="unread-again" style="position:fixed; width:100%;bottom:0">
	<button type="button" class="ui-btn-lg ui-btn-primary" id="send" >再次推送</button>
</div>
<!--弹窗-->
<div class="ui-dialog">
	<div class="ui-dialog-cnt">
		<div class="ui-dialog-bd">
			<div>
				<div>
					<h4>未读提醒</h4>
					<h4>确定再次发送提醒？</h4>
				</div>
			</div>
		</div>
		<div class="ui-dialog-ft ui-btn-group">
			<button type="button" data-role="button"  id="cancel">取消</button>
			<button type="button" data-role="button"  id="note_sure">确认</button>
			
		</div>
	</div>
</div>
<script>
	var neid = {$smarty.get.ne_id};
</script>
{literal}
<style>
body{
	background-color: #fff;
}
.ui-form-contacts .ui-badge-wrap {
  width: 100%;
  height: 50px;
  border-bottom: 1px solid #ccc ;
  margin: 8px 11px 8px 0;
}
.ui-avatar-s {
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 12px;
}
.ui-form-contacts .ui-badge-wrap .name {
  overflow: hidden;
  word-break: normal;
  white-space: normal;
  word-wrap: break-word;
  color: #666;
  line-height: 40px;
  height: 40px;
  font-size: 12px;
  text-align: left;
}
.sale-back-main {
    text-align: center;
    vertical-align: middle;
    margin-left:45px;
    padding:10px;
    font-size: 18px;
    width: 100%;
    border-left: solid 1px #d8d8d8;
}
.sale-back-main button{
    margin-left: -70px;
    vertical-align: middle;
}
.unread-again {
	padding: 20px 0;
	border-top:1px solid #dfdfdf;
	background-color:#f4f4f4;
}
#send {
	width:90%;
	margin: 0 auto;
	border-radius: 8px;
}
@media screen and (-webkit-min-device-pixel-ratio: 2)
	.ui-btn-primary:before{
		border-radius: 8px;
	}
</style>
<script type="text/javascript">
require(["zepto", "underscore", "frozen"], function($, _, fz) {
	$("#send").tap(function(){
		var $this = $(this);
		var el = null;
		var dia = $(".ui-dialog").dialog("show");
		dia.on('dialog:action', function (e) {
			if (e.index == 1) {
				 $.ajax({
			        	type:'POST',
			        	url:'/api/news/post/sendmsg',
			        	dataType:'json',
			        	data:"ne_id="+neid,
			        	cache:false,
			        	timeout:10000,
			        	beforeSend: function () {
							el = $.loading({content: '正在发送...'})
						},
			        	success:function(s){
			        		el.loading('hide');
			        		if(s.errcode==0){
			        			$.tips({content:'消息已发送！',stayTime:2000,type:"warn"});
			        		}else{
			        			$.tips({content:s.errmsg,stayTime:2000,type:"warn"});
			        		}
			        	},
			        	error:function(){
			        		el.loading('hide');
			        		$.tips({content:'服务器繁忙，请稍后再试！',stayTime:2000,type:"warn"});
			        	}
			    });
			}
		});
	});
})
</script>
{/literal}
{include file='mobile/footer.tpl'}