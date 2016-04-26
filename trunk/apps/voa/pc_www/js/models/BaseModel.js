define([ 
  'Underscore', 
  'Backbone',
], function(_, Backbone){
	
	var M = Backbone.Model.extend({
	
		parse: function(resp, xhr){
			return resp.result;
		},
		
		updateAttr: function(attr, value, options){
			var model = this;
			var req = {};
			req.url = this.url() + '/' + attr + '/' + value;
			req.data = {};
			req.dataType = 'json';
			req.complete = function(xhr, status){
				try{
					var ret = xhr.responseJSON;
					if (ret.errcode == 0){ //success
						
						//local
						try{
							var json = model.toJSON();
							var mdata = json;
							var keys = attr.split('.');
							var firstLv = keys[0];
							var k;
							while(keys.length>1){
								k = keys.shift();
								mdata = mdata[k];
							}
							mdata[keys[0]] = value;
							model.set(firstLv, json[firstLv]);
						}catch(ex){
							console.log('[BaseModel]', ex);
						}
						
						//callback
						if (options && 'success' in options)
							options.success.call(null, ret);
					}else{ //error
						if (options && 'error' in options)
							options.error.call(null, ret);
					}
				}catch(ex){
					console.log('[BaseModel]', ex);
				}
			};
			
			var theMethod = 'update'; //PUT
			if (window._isOldIE){
				theMethod = 'read'; //IE6~9”√GET∑¢
			}

			return (this.sync||Backbone.sync).call(this, theMethod, this, req);
		}
		
	});
	
	return M;
});
