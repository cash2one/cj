define([
  'jQuery',
  'Underscore',
  'Backbone',
  
  'utils/appUtils',
  
  'text!templates/checkin/calendar.html'
], function($, _, Backbone, appUtils, theTmpl){

  var CheckinCalendarView = Backbone.View.extend({
    
	tagName: 'div',
	id: 'checkin_calendar',
	
	template: _.template(theTmpl),
	
	tmplData: null,
	
	initialize: function(){
		this.currAct = window._appFacade.router.current();
	},
	
	render: function(year, month){
		
		var url = '/checkin/calendar';
		if (year && month){
			url += '/' + year + '/' + month;
		}
		appUtils.doSyncGet(url,  _.bind(this.onGet, this));
		
		return this;
    },
	
	events: {
		"mousewheel .body": "showScrollbar",
		"mouseover .body": "showScrollbar",
		"mouseout .body": "hideScrollbar",
		"change .shoulder .y select": "onYearsSelectChg",
		"click .searchBtn": "doSearch"
	},
	
	showScrollbar: appUtils.showScrollbar,
	hideScrollbar: appUtils.hideScrollbar,
	applyScrollbar: function(){
		var outHt = this.el.parentNode.clientHeight;
		var innerHt = outHt - this.$el.find('.header').height() - this.$el.find('.shoulder').height() - 3/*borders*/;
		appUtils.applyCustomScrollbar(
			this.$el,
			this.$el.find('.body'),
			outHt,
			innerHt
		);
	},
	
	onGet: function(data, ret){
		//fill data
		var rst = appUtils.parseAjax(ret);
		if (!rst) return;
		var tdata = _.extend(rst, {});
		
		this.tmplData = tdata;
		
		//build dom
		this.$el.html( this.template(tdata) );
		window._appFacade.layout.$col3.html(this.el);
		
		//月份选择
		var yInit = this.tmplData.year, 
			mInit = this.tmplData.month;
		$('#ysel_'+yInit).attr('selected', 'selected');
		this.onYearsSelectChg();
		$('#msel_'+mInit).attr('selected', 'selected');
		
		//概要
		var summUl = $('ul.summary', this.el),
			lis = $('.summary li', this.el),
			w1 = parseInt( summUl.width() / lis.size() );
		lis.each(function(idx, li){
			$(li).width(w1);
		});
		
		//日历
		var year = parseInt(yInit);
		var month = parseInt(mInit);
		var matrix = appUtils.time.getDatesOfMonth(year, month-1);
		var tbl = $('table tbody', this.el);
		while (matrix.length){
			var row = matrix.shift();
			var html = '<tr>';
			while(row.length){
				var col = row.shift();
				if (col == null){
					html += '<td></td>';
				}else{
					var d = appUtils.time.getFixedIOSDate(col);
					var ymd = [d.getFullYear(), d.getMonth()+1, d.getDate()].join('-');
					html += '<td id="day-'+ ymd +'">' + d.getDate() + '</td>';
				}
			}
			html += '</tr>';
			tbl.append(html);
		}
		
		//考勤
		var data1 = this.tmplData.calendar;
		var tds = $('td', tbl);
		for (var k in data1.workdays){
			var td = $('#day-'+k);
			if (td.length){
				td.addClass('workday');
				var v = data1.workdays[k];
				if (v in data1.style){
					td.append('<i>'+v+'</i>');
					td.addClass(data1.style[v]);
				}
			}
		}
		
		//layout scrollbar
		this.applyScrollbar();
		
		this.delegateEvents();
	},
	
	onYearsSelectChg: function(e){
		var $ySel = $('.shoulder .y select', this.el);
		var year = $ySel.val();
		var monthRange = this.tmplData.search.range[year];
		var $mSel = $('.shoulder .m select', this.el);
		$mSel.html('');
		for(var i=0,lng=monthRange.length; i<lng; i++){
			var m = monthRange[i];
			$mSel.append('<option id="msel_'+m+'" value="'+ m +'" '+ (i?"":"selected") +'>'+m+'</option>');
		}
	},
	
	doSearch: function(e){
		var $ySel = $('.shoulder .y select', this.el);
		var $mSel = $('.shoulder .m select', this.el);
		var hash = 'checkin/calendar/' + $ySel.val() + '/' + $mSel.val();
		window._appFacade.router.navigate(hash, {trigger: true});
	}
	
  });
  return CheckinCalendarView;
});
