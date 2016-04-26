<div style="margin-left: 100px;">
	<div id="record_menu">
		<button type="button" id="recording" res="true">开始录音</button>
		<button type="button" id="play_record" res="true" disabled>开始播放</button>
		<button type="button" id="upload_record" disabled>上传</button>
		<button type="button" id="delete_record" disabled>删除</button>
	</div>
	<div id="show_record" style="display: none">
		<img src="/static/images/record.png" />
	</div>
	<input type="hidden" name="record_serverId" value="" id="record_serverId"/>
</div>


{literal}
<script type="text/javascript">
$(function(){

	var localId = null;   //录音本地存储ID
	
	 //开始录音、停止录音
	$('#recording').bind('click',function(){
		self = $(this);
		var res = self.attr('res');
		if (res == 'true') {  					 //开始录音
			wx.startRecord();
			self.html('停止录音').attr('res',false).siblings().attr('disabled',false);
			
			//监听录音自动停止
			wx.onVoiceRecordEnd({
				// 录音时间超过一分钟没有停止的时候会执行 complete 回调
			    complete: function (res) {
			        localId = res.localId;
			        $('#recording').html('开始录音').attr('res',true).attr('disabled',true).siblings().attr('disabled',false);
			    }
			});
		} else {								//停止录音
			wx.stopRecord({
			    success: function (res) {
			        localId = res.localId;
			        self.html('开始录音').attr('res',true).siblings().attr('disabled',true);
			    }
			});
			 
		}
	});
		
	//开始播放、停止播放
	$('#play_record').bind('click',function(){
		self = $(this);
		var res = self.attr('res');
		if (res == 'true') {  					 //开始播放
			wx.playVoice();
			self.html('停止播放').attr('res',false).siblings().attr('disabled',false);
			
			//监听语音播放完毕
			wx.onVoicePlayEnd({
			    serverId: '', // 需要下载的音频的服务器端ID，由uploadVoice接口获得
			    success: function (res) {
			        localId = res.localId;
			        $('#recording').html('开始录音').attr('res',true).attr('disabled',true).siblings().attr('disabled',false);
			    }
			});
		} else {								//停止播放
			wx.stopVoice({
			    localId: localId
			}); 
			self.html('开始播放').attr('res',true).siblings().attr('disabled',true);
		}
	});
	

	
	//上传语音
	$('#upload_record').bind('click',function(){
		wx.uploadVoice({
		    localId: localId, // 需要上传的音频的本地ID，由stopRecord接口获得
		    isShowProgressTips: 1	// 默认为1，显示进度提示
		        success: function (res) {
		        var serverId = res.serverId; // 返回音频的服务器端ID
		        $('#record_serverId').val(serverId);
		        $('#record_menu').hide();
		        $('#show_record').show();
		    }
		});
	});
	
	//删除语音
	$('#delete_record').bind('click',function(){
		 localId = null;
		 $('#recording').html('开始录音').attr('res',true).attr('disabled',false);
		 $('#play_record').html('开始播放').attr('res',true).attr('disabled',true);
		 $('#upload_record').attr('disabled',true);
	});
	
});
</script>
{/literal}
