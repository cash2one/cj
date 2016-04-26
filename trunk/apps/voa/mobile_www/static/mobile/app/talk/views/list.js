define(["data/talk", "utils/render", "text!templates/list.html","text!templates/list_data.html", 'jquery', "iscrollview"
        , "css!styles/common.css","css!styles/list.css"], function(talk, render, tpl, son_tpl, $){
	
	
    function view() {
    	
    }
    
	var fresh_time = 3;		//刷新时间
   
    
	view.prototype.page = null;
    view.prototype.render = function(args) {
    	var self = this;
        $('title').html('联系人列表');
        self.render = new render();
    	self.render.template = tpl;
    	//this.r.only_return_element = true;
    	self.page = self.render.apply();
    	
        self.refresh();
        //清除定时器后再开启,以免重复
        if(window.timer1) clearInterval(timer1);
    	if(window.timer2) clearInterval(timer2);
    	timer1 = setInterval(function () {
    		self.refresh();
    	}, fresh_time * 1000);
    };
    
    view.prototype.refresh = function (is_first){
    	var self = this;
    	// 数据业务处理处
        talk.get_list({uid:userinfo.m_uid}, function (ret) {
    		
            var html = "";

            $.each(ret, function (i, item){
            	if(!item) return;
        		html += self.render.parse_template(son_tpl, {item: item});
        	});

        	self.page.find('#listDiv').html(html);
        		
			if($('#listDiv li').length > 0) {
        		$('.empty').remove();
        	}
        });
    };
    return view;
});
