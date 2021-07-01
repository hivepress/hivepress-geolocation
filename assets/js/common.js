hivepress.initGeolocation = function() {
	(function($) {
		'use strict';

		$(document).ready(function() {

			// Location
			hivepress.getComponent('location').each(function() {
				var container = $(this),
					field = container.find('input[type=text]'),
					button = container.find('a'),
					settings = {
						details: field.closest('form'),
						detailsAttribute: 'data-coordinate',
					};

				if (container.data('countries')) {
					settings['componentRestrictions'] = {
						'country': container.data('countries'),
					};
				}

				if (container.data('types')) {
					settings['types'] = container.data('types');
				}

				field.geocomplete(settings);

				field.on('input', function() {
					if (!field.val()) {
						container.closest('form').find('input[data-coordinate]').val('');
					}
				});

				if (navigator.geolocation) {
					button.on('click', function(e) {
						navigator.geolocation.getCurrentPosition(function(position) {
							field.geocomplete('find', position.coords.latitude + ' ' + position.coords.longitude);
						});

						e.preventDefault();
					});
				} else {
					button.hide();
				}
			});

			// Map
			hivepress.getComponent('map').each(function() {
				var container = $(this),
					prevWindow = false,
					maxZoom = container.data('max-zoom'),
					markers = [],
					bounds = new google.maps.LatLngBounds(),
					map = new google.maps.Map(container.get(0), {
						zoom: 3,
						minZoom: 2,
						maxZoom: maxZoom,
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
					}),
					oms = new OverlappingMarkerSpiderfier(map, {
						markersWontMove: true,
						markersWontHide: true,
						basicFormatEvents: true,
					});

				container.height(container.width());

				$.each(container.data('markers'), function(index, data) {
					var nextWindow = new google.maps.InfoWindow({
							content: data.content,
						}),
						marker = new google.maps.Marker({
							title: data.title,
							position: {
								lat: data.latitude,
								lng: data.longitude,
							},
						});

					marker.addListener('spider_click', function() {
						if (prevWindow) {
							prevWindow.close();
						}

						prevWindow = nextWindow;
						nextWindow.open(map, marker);
					});

					markers.push(marker);
					oms.addMarker(marker);

					bounds.extend(marker.getPosition());
				});

				map.fitBounds(bounds);

				var clusterer = new MarkerClusterer(map, markers, {
					imagePath: hivepressGeolocationData.assetURL + '/images/markerclustererplus/m',
					maxZoom: maxZoom - 1,
				});
			});
		});
	})(jQuery);
}
