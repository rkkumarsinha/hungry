;
jQuery.widget("ui.routemap",{
	options:{
		source_latitude:undefined,
		source_longitude:undefined,
		target_latitude:undefined,
		target_longitude:undefined,
		zoom:5
	},
	_create: function(){
		var self = this;	
		this.getCurrentLatLong();
	},

	getCurrentLatLong:function(){
		var self = this;
		// $.univ().successMessage("Detecting Your Location");
		if ( navigator.geolocation ) {
        	function success(pos) {
				self.options.source_latitude =  pos.coords.latitude;
				self.options.source_longitude =  pos.coords.longitude;
				// self.options.source_latitude =  24.590001299999997;
				// self.options.source_longitude =  73.7139802;
				self.createMap();
        	}
        	function fail(error) {
        		console.log("failde from routemap");
            	// alert('failed');
        	}
        	function error(error){

        	}
        	navigator.geolocation.getCurrentPosition(success, fail, {maximumAge: 5000, enableHighAccuracy:true, timeout: 600});
        }
	},

	createMap:function(){
		var self = this;

		$(self.element).gmap3({
			getroute:{
			    options:{
			        origin:[self.options.source_latitude,self.options.source_longitude],
			        destination:[self.options.target_latitude,self.options.target_longitude],
			        travelMode: google.maps.DirectionsTravelMode.DRIVING

			    },
			    callback: function(results){
			      if (!results) return;
			      var html = "";
			      html += "<div>Distance: "+results.routes[0].legs[0].distance.text+"</div>";
			      html += "<div>Duration: "+results.routes[0].legs[0].duration.text+"</div>";

			      // for (var i = 0; i < results.routes.length; i++) {
			      // 	// html+= results.routes[i].legs.distance.text+"<br/>";
			      // 	// html+= results.routes[i].legs.duration.text+"<br/>";
			      // 	console.log(results.routes[i].legs[0]);
			      // 	// $(results.routes[i]).each(function(index){
			      // 	// 	console.log(index);
			      // 	// });
			      // };

			      $('#hungrymaproute-distance').html(html);
			      $(self.element).gmap3({
			        map:{
			          options:{
			            zoom: self.options.zoom,
			            center: [self.options.target_latitude,self.options.target_longitude]
			          }
			        },
			        directionsrenderer:{
			          options:{
			            directions:results
			          } 
			        },
			        action:'setCenter', args:[ new google.maps.LatLng(self.options.target_latitude, self.options.target_longitude) ]
			      });
			    }
			  }
		});
	}
});
