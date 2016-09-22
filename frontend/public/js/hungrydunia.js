;
jQuery.widget("ui.hungrydunia",{
	options:{
		user_latitude:undefined,
		user_longitude:undefined,
		restaurant_latitude:undefined,
		restaurant_longitude:undefined
	},
	_create: function(){
		var self = this;
		this.getCurrentLatLong();
	},

	getCurrentLatLong:function(){
		var self = this;
		if ( navigator.geolocation ) {
        	function success(pos) {
				self.options.user_latitude =  pos.coords.latitude;
				self.options.user_longitude =  pos.coords.longitude;
				$('#lat').val(self.options.user_latitude);//.trigger('change');
        	}

        	function fail(error) {
            	alert('failed');
        	}
        	
        	function error(error){

        	}

        	// navigator.geolocation.getCurrentPosition(success, fail, {maximumAge: 500000, enableHighAccuracy:true, timeout: 6000});
        	navigator.geolocation.watchPosition(success, error, {enableHighAccuracy: false,timeout: 5000,maximumAge: 0});
        }

        window.setTimeout(function(){
        	$('#lat').val(90).trigger('change');
        },700);


	},

	calculateDistance:function(){

		var self = this;
		current_latlong = new google.maps.LatLng(self.options.user_latitude,self.options.user_longitude);
		destination = {lat:26.9000, lng:75.8000};
		origin = {lat:self.options.user_latitude,lng:self.options.user_longitude};
		$(self.element).gmap3({
		  getdistance:{
		    options:{ 
				origins:origin,
      			destinations:destination,
		      	travelMode: google.maps.TravelMode.DRIVING
		    },
		    callback: function(results, status){

		      var html = "";
		      if (results){
		        for (var i = 0; i < results.rows.length; i++){
		          var elements = results.rows[i].elements;
		          for(var j=0; j<elements.length; j++){
		            switch(elements[j].status){
		              case "OK":
		                html += elements[j].distance.text + " (" + elements[j].duration.text + ")<br />";
		                break;
		              case "NOT_FOUND":
		                html += "The origin and/or destination of this pairing could not be geocoded<br />";
		                break;
		              case "ZERO_RESULTS":
		                html += "No route could be found between the origin and destination.<br />";
		                break;
		            }
		          }
		        } 
		      } else {
		        html = "error";
		      }
		      // console.log(current_latlong);
		      // console.log(html);
		      // $('#distance').attr('html',html );
		    }
		  }
		});
	}
});
