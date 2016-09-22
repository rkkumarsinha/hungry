;
jQuery.widget("ui.searchmap",{
	options:{
		restaurants:undefined
	},
	_create: function(){
		var self = this;
		alert('called');
		console.log(self.options.restaurants);
	}
});
