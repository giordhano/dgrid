define(["dojo/_base/lang", "dojo/_base/xhr", "dojo/_base/Deferred", "dojo/json", "dojo/store/util/QueryResults"],
function(lang, xhr, Deferred, json, QueryResults){
	var QueryStore = function(options){
		options && lang.mixin(this, options);
		if (!this.dataUrl) { throw new Error("no dataUrl specified!"); }
	};
	QueryStore.prototype = {
		get: function(id){
			return xhr.get({
				url: this.dataUrl,
				handleAs: "json",
				content: {
					id: id
				}
			}).promise;
		},

		getIdentity: function(object){
			return object.id;
		},
		
		query: function(query, options){
			var content = {};
			options = options || {};
			if(options.start){ content.start = options.start; }
			if(options.count){ content.count = options.count; }
			if(options.sort){
				content.sort =
					(options.sort[0].descending ? "-" : "") + options.sort[0].attribute;
			}
			var total = new Deferred(),
				r = QueryResults(xhr.get({
					url: this.dataUrl,
					handleAs: "json",
					content: content
				}).then(function(data){
					total.resolve(data.total);
					return data.items;
				}));
			// attach total promise
			r.total = total.promise;
			return r;
		},
		
		add: function(object, options){
			return this.put(object, options);
		},
		
		put: function(object, options){
			return xhr.post({
				url: this.dataUrl,
				content: { object: json.stringify(object) }
			}).promise;
		},
		
		remove: function(id){
			return xhr.post({
				url: this.dataUrl,
				content: { id: id }
			}).promise;
		}
	};
	return QueryStore;
});
