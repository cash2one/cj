;(function (factory)
{
    if (typeof define === 'function' && define.amd)
    {
        define(['jquery', 'Underscore', 'js/config/routes.js',
			'js/libs/jquery-cookie.js',	
			'/misc/scripts/md5.js',	
			'js/config/init.js',	
			'js/config/leftMenu.js',
			'js/config/getAddressbookList.js',
			'js/config/getAddressbookById.js',
			'js/config/getAnnouncementList.js',
			'js/config/getAnnouncementById.js',
			'js/config/getCheckInMenu.js',
			'js/config/getCheckInDaily.js',
			'js/config/getSettingsMenu.js',
			'js/config/getSettingsProfile.js',
			'js/config/settingsPassword.js',
			], factory);
    }
    else if (typeof exports === 'object')
    {
        factory(require('jquery'));
    }
    else
    {
        factory(jQuery);
    }
}
(function ($, _, routes)
{
	console.log('ajax init......');
	var acts = _.filter(arguments, function(arg) { 
		if (arg) { 
			return arg.id != null
		} else {
			return false;
		}
	});
	var cachedata = {};
	$.md5 = function (str) {
		return hex_md5(str);
	};

	$.cache = {set: function (key, value) {
		console.log('set data==================key='+key);
		cachedata[key] = value;	
	}, get: function (key) {
		var item = _.find(cachedata, function (item, k) {
			return  k == key;
		});
		console.log('get data+++++++++++++++key='+key);
		console.log(item);
		return item;
	}};

    var originalAjaxMethod = $.ajax;
    // Define overriding method.
    $.ajax = function(data) {
		promise = $.Deferred();
        // reset the timer
		var args = arguments[0];
		var url = args.url, route, ret;
		found = _.find(routes.list, function (namedRoute) {
			route = namedRoute.url;
			if (!_.isRegExp(namedRoute.url)) {
				route = routeToRegExp(namedRoute.url);
			}
			return route.test(url);
		});
		console.log("ajax#########################################");
		console.log(found);
		if (found) { 
			var act = _.find(acts, function (act) {return act.id == found.name});
			console.log(act);
			if (act) {
				//console.log(this.extractParametersNames(url));
				if (args.type.toLowerCase() == "get") {
					//act.get(promise, originalAjaxMethod, args);
					setTimeout(function () { act.get(promise, originalAjaxMethod,  args, $, _)}, 1);
				} else if (args.type.toLowerCase() == "post") {
				//	console.log(args.type);
        		//	return originalAjaxMethod.apply( this, arguments);
					setTimeout(function () {ret = act.post(promise, originalAjaxMethod,  args, $, _)}, 1);
				} else {
					console.log("unkown type: \n---------------------------\n" + args.type);
				}
			} else {
				console.log(args.url+ 'dont not set!');
        		return originalAjaxMethod.apply( this, arguments);
			}
			//console.log(ret);
		}
        // Execute the original method.        
        //return originalAjaxMethod.apply( this, arguments);
		return promise.done( function (data) {
			console.log('promise+++++++++++++++++++++++++++');
			console.log(args);
			console.log(data);
			if (args.success) {
				args.success(args.responseJSON);
			} else if (args.complete) {
				args.complete({responseJSON: args.responseJSON});
			}
		});
    }

	var optionalParam = /\((.*?)\)/g;
	var namedParam = /(\(\?)?:\w+/g;
	var escapeRegExp = /[\-{}\[\]+?.,\\\^$|#\s]/g;
	var routeToRegExp = function(route) {
		route = route
		.replace(escapeRegExp, '\\$&')
		.replace(optionalParam, '(?:$1)?')
		.replace(namedParam, function(match, optional) {
			return optional ? match : '([^\/]+)';
		});
		return new RegExp('^' + route + '$');
	};
  
    return $;
}));
