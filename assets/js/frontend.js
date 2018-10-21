function initMap() {
	(function($) {
	  'use strict';

		/**
		 * Gets prefixed selector.
		 */
		function getSelector(name) {
			return '.hp-js-' + name;
		}

		/**
		 * Gets custom object.
		 */
		function getObject(name) {
			return $(getSelector(name));
		}

		getObject('geocomplete').each(function() {
			var field=$(this);

			field.geocomplete({
				details: field.closest('form'),
				detailsAttribute: 'data-type',
			});
		});

		getObject('geolocate').each(function() {
			var button=$(this);

			if(navigator.geolocation) {
				button.on('click', function(e) {
					navigator.geolocation.getCurrentPosition(function(position) {
						button.closest('form').find('.hp-js-geocomplete').geocomplete('find', position.coords.latitude+' '+position.coords.longitude);
					});

					e.preventDefault();
				});
			} else {
				button.hide();
			}
		});

		// Google map
		getObject('map').each(function() {
			var prev_infowindow=false,
				bounds=new google.maps.LatLngBounds(),
				map=new google.maps.Map($(this).get(0), {
					zoom: 3,
					minZoom: 3,
					maxZoom: 15,
					mapTypeControl: false,
					streetViewControl: false,
					center: {
						lat: 0,
						lng: 0,
					},
					styles: [{
						featureType: 'poi',
						stylers: [{
							visibility: 'off',
						}],
					}],
				});

			$(this).css('height', '333px');

			$.each($(this).data('json'), function(index, data) {
				var next_infowindow=new google.maps.InfoWindow({
						content: data.content,
					}),
					marker=new google.maps.Marker({
						map: map,
						title: data.title,
						position: {
							lat: data.latitude,
							lng: data.longitude,
						},
					});

				marker.addListener('click', function() {
					if(prev_infowindow) {
						prev_infowindow.close();
					}

					prev_infowindow=next_infowindow;
					next_infowindow.open(map, marker);
				});

				bounds.extend(marker.getPosition());
			});

			map.fitBounds(bounds);
		});
	})(jQuery);
}