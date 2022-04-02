hivepress.initGeolocation = function() {
	(function($) {
		'use strict';

		$(document).ready(function() {

			// Location
			hivepress.getComponent('location').each(function() {
				var container = $(this),
					form = container.closest('form'),
					field = container.find('input[type=text]'),
					regionField = form.find('input[name=_region]'),
					button = container.find('a'),
					settings = {
						details: form,
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

				field.bind('geocode:result', function(event, result) {
					var parts = [],
						types = [
							'locality',
							'administrative_area_level_2',
							'administrative_area_level_1',
							'country',
						];

					if (regionField.length) {
						if (result.address_components[0].types.filter(function(type) {
								return types.indexOf(type) !== -1;
							}).length) {
							regionField.val(result.place_id);
						} else {
							regionField.val('');
						}
					}

					if (container.data('scatter')) {
						types.push('route');

						$.each(result.address_components, function(index, component) {
							if (component.types.filter(function(type) {
									return types.indexOf(type) !== -1;
								}).length) {
								parts.push(component.long_name);
							}
						});

						field.val(parts.join(', '));
					}
				});

				field.on('input', function() {
					if (!field.val()) {
						form.find('input[data-coordinate]').val('');
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
					height = container.width(),
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
					}),
					iconSettings = {
						path: google.maps.SymbolPath.CIRCLE,
						fillColor: '#3a77ff',
						fillOpacity: 0.25,
						strokeColor: '#3a77ff',
						strokeWeight: 1,
						strokeOpacity: 0.75,
						scale: 10,
					};

				if (container.is('[data-height]')) {
					height = container.data('height');
				}

				container.height(height);

				$.each(container.data('markers'), function(index, data) {
					var nextWindow = new google.maps.InfoWindow({
							content: data.content,
						}),
						markerSettings = {
							title: data.title,
							position: {
								lat: data.latitude,
								lng: data.longitude,
							},
						};

					if (container.data('scatter')) {
						markerSettings['icon'] = iconSettings;
					}

					var marker = new google.maps.Marker(markerSettings);

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

				var observer = new MutationObserver(function(mutations) {
					map.fitBounds(bounds);
				});

				observer.observe(container.get(0), {
					attributes: true,
				});

				var clusterer = new MarkerClusterer(map, markers, {
					imagePath: hivepressGeolocationData.assetURL + '/images/markerclustererplus/m',
					maxZoom: maxZoom - 1,
				});

				if (container.data('scatter')) {
					map.addListener('zoom_changed', function() {
						iconSettings['scale'] = Math.pow(1.3125, map.getZoom());

						$.each(markers, function(index, marker) {
							markers[index].setIcon(iconSettings);
						});
					});
				}
			});
		});
	})(jQuery);
}
