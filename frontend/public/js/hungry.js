$.each({
	hungryrating:function(restaurant_id,comment_id,disable){

		$(".hungryrating > input").rating({disabled:disable})
			.on("rating.clear", function(event) {
				$.univ().successMessage('your rating is reset');
    		}).on("rating.change", function(event, value, caption) {
        		// alert("You rated: " + value + " = " + $(caption).text());
	        	var self = this;
	        	$.ajax({
				  url: "?page=hungryreview",
				  type:"POST",
				  datatype:"json",
				  data: {
				  	hungry_rating:value,
				  	hungry_restaurant:restaurant_id,
				  	comment_id:comment_id
				  }
				}).done(function(ret) {
					// $(".hungryrating > input").rating("refresh", {disabled:true, showClear:false});
					if(ret=='true'){
        				$(self).rating("refresh", {disabled:true, showClear:false});
						alert('Thank you for your rating');
					}
				});
    	});
	},
	hungryInputRating:function(){
		$("input.hungryinputrating").rating({})
			.on("rating.clear", function(event) {
				$.univ().successMessage('your rating is reset');
    		}).on("rating.change", function(event, value, caption) {
        		// alert("You rated: " + value + " = " + $(caption).text());
        		$(this).val(value);
        	});
	},

	hungryRatingShow:function(){
		$("input.hungryratingshow").rating({disabled:true});
	},
	getDistance:function(lat1,lon1,lat2,lon2){
		var R = 6371; // km (change this constant to get miles)

        var dLat = (lat2-lat1) * Math.PI / 180;
        var dLon = (lon2-lon1) * Math.PI / 180;
        var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180 ) * Math.cos(lat2 * Math.PI / 180 ) *
        Math.sin(dLon/2) * Math.sin(dLon/2);
      	var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
     	var d = R * c;

      	if (d>1) return (Math.round(d)/1000+"km");
      	else if (d<=1) return Math.round(d*1000)+"m";

      	return d;
	},

	hungryLocationPicker:function(map_display,location_lat,location_long,latitude_field,longitude_field,location_picker_field,radius_field){
		$('#'+map_display).locationpicker({
			location:{
				latitude: location_lat,
				longitude:location_long
			},
			radius:300,
			inputBinding: {
			    latitudeInput: $('#'+latitude_field),
			    longitudeInput: $('#'+longitude_field),
			    radiusInput: $('#'+radius_field),
			    locationNameInput: $('#'+location_picker_field)
			},
			enableAutocomplete: true,
		});
	},

	selectCity: function(field_id,city_id,city_list,base_url){
		if(city_id <= 0){
			$.univ().errorMessage('please select city');
			return;
		}

		var city_name = "";
		$.each(city_list, function(index, city) {
			if(city.id == city_id){
				city_name = city.name;
				return false;
			}
		});

		window.location.href = base_url+city_name;
	}

},$.univ._import);