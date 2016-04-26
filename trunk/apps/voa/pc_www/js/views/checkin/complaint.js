define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'models/CheckinComplaint',
  'text!templates/checkin/complaint.html'
], function($, _, Backbone, appUtils, theModel, theTmpl){

  var CheckinCalendarView = Backbone.View.extend({
    
	tagName: 'div',
	id: 'checkin_complaint',
	
	template: _.template(theTmpl),
	model: null,
	tmplData: null,
	
	initialize: function(){
	},
	
	render: function(year, month){
		
		this.model = new theModel;
		this.model.on('change', this.onModelChange, this);
		this.model.on('invalid', this.onModelInvalid, this);
		
		var u = this.model.urlRoot + '/' + year + '/' + month;
		this.model.fetch({url: u });
		
		return this;
    },
	
	events: {
		"mousewheel .body": "showScrollbar",
		"mouseover .body": "showScrollbar",
		"mouseout .body": "hideScrollbar",
		"click .cancelBtn": "onCancel",
		"click .submitBtn": "onSubmit"
	},
	
	showScrollbar: appUtils.showScrollbar,
	hideScrollbar: appUtils.hideScrollbar,
	applyScrollbar: function(){
		var outHt = this.el.parentNode.clientHeight;
		var innerHt = outHt - this.$el.find('.header').height();
		appUtils.applyCustomScrollbar(
			this.$el,
			this.$el.find('.body'),
			outHt,
			innerHt
		);
	},
	
	onModelInvalid: function(model, error){
		switch (error){
			case 'empty_subject':
				appUtils.dialog.warning(this.tmplData.subject.validation);
				break;
			case 'empty_content':
				appUtils.dialog.warning(this.tmplData.content.validation);
				break;
		}
	},
	
	onModelChange: function(model, sync){
		
		//fill data
		var rst = appUtils.parseAjax(sync.xhr.responseJSON);
		if (!rst) return;
		var tdata = _.extend(rst, {});
		
		this.tmplData = tdata;
		
		//build dom
		this.$el.html( this.template(tdata) );
		window._appFacade.layout.$col3.html(this.el);		
		
		var taVal = tdata.content.value.replace(/\\r\\n/g, '\r\n');
		$('.content textarea', this.el).get(0).value = taVal;
		
		var tbl = $('table', this.el);
		var ths = $('th', tbl);
		ths.each(function(idx, th){
			$(th).width( tbl.width() - 3 - (ths.size()-1) );
		});
		
		//layout scrollbar
		this.applyScrollbar();
		
		this.delegateEvents();
	},
	
	onCancel: function(e){
		var h = 'checkin/calendar/' + this.model.get('year') + '/' + this.model.get('month');
		var cfm = this.tmplData.buttons.cancel.confirm;
		appUtils.dialog.confirm(
			cfm.message,
			cfm.no,
			cfm.yes,
			function(nofy){
				nofy.close();
			},
			function(nofy){
				window._appFacade.router.navigate(h, {trigger:true});
				nofy.close();
			}
		);
	},
	
	onSubmit: function(e){
		var m = this.model;
		var d = this.tmplData;
		var val_s = $('.subject input', this.el).val();
		var val_c = $('.content textarea', this.el).val();
		var cfm = d.buttons.submit.confirm;
		var h = 'checkin/calendar/' + this.model.get('year') + '/' + this.model.get('month');
		appUtils.dialog.confirm(
			cfm.message,
			cfm.no,
			cfm.yes,
			function(nofy){
				nofy.close();
			},
			function(nofy){
				m.save({
					'subject': _.extend(d.subject, {value: val_s}),
					'content': _.extend(d.content, {value: val_c})
				},
				{
					wait: false,
					validate: true,
					silent: true,
					success: function(model, response, options) {						
						var rst = appUtils.parseAjax(response);
						if (!rst) return;						
						appUtils.dialog.success(rst.notice);
						window._appFacade.router.navigate(h, {trigger:true});
					}
				});
				nofy.close();
			}
		);
	}
	
  });
  
  return CheckinCalendarView;
});
