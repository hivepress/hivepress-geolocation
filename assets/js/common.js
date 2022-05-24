var map = '';

hivepress.initGeolocation = function() {
	(function($) {
		'use strict';

		$(document).ready(function() {

			// Location
			hivepress.getComponent('location').each(function() {
				var container = $(this),
					form = container.closest('form'),
					field = container.find('input[type=text]'),
					latitudeField = form.find('input[data-coordinate=lat]'),
					longitudeField = form.find('input[data-coordinate=lng]'),
					regionField = form.find('input[data-region]'),
					button = container.find('a'),
					settings = {},
					locationFormat = container.attr('data-format'),
					locationFormatTokens = {};

				if (typeof mapboxData !== 'undefined') {
					settings = {
						accessToken: mapboxData.apiKey,
						language: hivepressCoreData.language,
					};

					// Set countries
					if (container.data('countries')) {
						settings['countries'] = container.data('countries').join(',');
					}

					// Set types
					if (container.data('types')) {
						settings['types'] = container.data('types').join(',');
					}

					// Create Geocoder
					var geocoder = new MapboxGeocoder(settings);

					geocoder.addTo(container.get(0));

					// Replace field
					var mapboxContainer = container.children('.mapboxgl-ctrl'),
						fieldAttributes = field.prop('attributes');

					field.remove();
					field = mapboxContainer.find('input[type=text]');

					$.each(fieldAttributes, function() {
						field.attr(this.name, this.value);
					});

					mapboxContainer.detach().prependTo(container);

					// Set location
					geocoder.on('result', function(result) {
						var types = [
							'place',
							'district',
							'region',
							'country',
						];

						// Set region
						if (regionField.length) {
							if (result.result.place_type.filter(value => types.includes(value)).length) {
								regionField.val(result.result.id);
							} else {
								regionField.val('');
							}
						}

						if (typeof locationFormat !== typeof undefined && locationFormat !== false) {

							locationFormat = container.attr('data-format');

							locationFormatTokens = {
								address: {
									value: '',
									token: '%place_address%',
								},
								place: {
									value: '',
									token: '%city%',
								},
								district: {
									value: '',
									token: '%county%',
								},
								region: {
									value: '',
									token: '%state%',
								},
								country: {
									value: '',
									token: '%country%',
								},
							};

							// Get location parts values.
							result.result.place_type.forEach(function(item) {
								if ('address' === item) {
									locationFormatTokens.address.value = result.result.text;

									if (typeof result.result.address !== 'undefined') {
										locationFormatTokens.address.value += ' ' + result.result.address;
									}
								} else if ('poi' === item) {
									locationFormatTokens.address.value = result.result.properties.address;
								} else {
									if (locationFormatTokens[item] !== undefined) {
										locationFormatTokens[item]['value'] = result.result.text;
									}
								}
							});

							// Get location parts values.
							result.result.context.forEach(function(item) {
								if (locationFormatTokens[item.id.split('.')[0]] !== undefined) {
									locationFormatTokens[item.id.split('.')[0]]['value'] = item.text;
								}
							});

							$.each(locationFormatTokens, function(item) {

								// Change location display format.
								locationFormat = locationFormat.replace(locationFormatTokens[item]['token'], locationFormatTokens[item]['value']);
							});

							// Set location field value.
							field.val(locationFormat);
						}

						// Set coordinates
						longitudeField.val(result.result.geometry.coordinates[0]);
						latitudeField.val(result.result.geometry.coordinates[1]);
					});
				} else {
					settings = {
						details: form,
						detailsAttribute: 'data-coordinate',
					};

					// Set countries
					if (container.data('countries')) {
						settings['componentRestrictions'] = {
							'country': container.data('countries'),
						};
					}

					// Set types
					if (container.data('types')) {
						settings['types'] = container.data('types');
					}

					// Initialize Geocomplete
					field.geocomplete(settings);

					// Set location
					field.bind('geocode:result', function(event, result) {
						var parts = [],
							types = [
								'locality',
								'administrative_area_level_2',
								'administrative_area_level_1',
								'country',
							];

						// Set region
						if (regionField.length) {
							if (result.address_components[0].types.filter(value => types.includes(value)).length) {
								regionField.val(result.place_id);
							} else {
								regionField.val('');
							}
						}

						if (typeof locationFormat !== typeof undefined && locationFormat !== false) {
							types.push('route', 'street_number');

							if (typeof locationFormat !== typeof undefined && locationFormat !== false) {
								locationFormat = container.attr('data-format');

								locationFormatTokens = {
									route: {
										value: '',
										token: '%place_address%',
									},
									locality: {
										value: '',
										token: '%city%',
									},
									administrative_area_level_2: {
										value: '',
										token: '%county%',
									},
									administrative_area_level_1: {
										value: '',
										token: '%state%',
									},
									country: {
										value: '',
										token: '%country%',
									},
								};
							}

							$.each(result.address_components, function(index, component) {
								if (component.types.filter(value => types.includes(value)).length) {

									// Get location parts values.
									component.types.forEach(function(item) {
										if ('street_number' === item && !container.data('scatter')) {
											locationFormatTokens.route.value = ' ' + component.long_name;
										} else if ('route' === item) {
											locationFormatTokens.route.value = component.long_name + locationFormatTokens.route.value;
										} else {
											if (locationFormatTokens[item] !== undefined) {
												locationFormatTokens[item]['value'] = component.long_name;
											}
										}
									});
								}
							});

							$.each(locationFormatTokens, function(item) {
								// Change location display format.
								locationFormat = locationFormat.replace(locationFormatTokens[item]['token'], locationFormatTokens[item]['value']);
							});

							// Set location field value.
							field.val(locationFormat);
						} else {

							// Set address
							if (container.data('scatter')) {
								types.push('route');

								$.each(result.address_components, function(index, component) {
									if (component.types.filter(value => types.includes(value)).length) {
										parts.push(component.long_name);
									}
								});

								field.val(parts.join(', '));
							}
						}
					});
				}

				// Clear location
				field.on('input', function() {
					if (!field.val()) {
						form.find('input[data-coordinate]').val('');
					}
				});

				// Detect location
				if (navigator.geolocation) {
					button.on('click', function(e) {
						navigator.geolocation.getCurrentPosition(function(position) {
							if (typeof mapboxData !== 'undefined') {
								geocoder.options.reverseGeocode = true;
								geocoder.options.limit = 1;

								geocoder.query(position.coords.latitude + ',' + position.coords.longitude);

								geocoder.options.reverseGeocode = false;
								geocoder.options.limit = 5;
							} else {
								field.geocomplete('find', position.coords.latitude + ' ' + position.coords.longitude);
							}
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
					maxZoom = container.data('max-zoom');

				// Set height
				if (container.is('[data-height]')) {
					height = container.data('height');
				}

				container.height(height);

				if (typeof mapboxData !== 'undefined') {

					// Set API key
					mapboxgl.accessToken = mapboxData.apiKey;

					// Create map
					var bounds = new mapboxgl.LngLatBounds();
					map = new mapboxgl.Map({
						container: container.get(0),
						style: 'mapbox://styles/mapbox/streets-v11',
						center: [0, 0],
						zoom: 1,
					});

					map.addControl(new mapboxgl.NavigationControl());
					map.addControl(new mapboxgl.FullscreenControl());

					// Set language
					map.addControl(new MapboxLanguage());

					// Get markers object.
					var points = [];

					// Add markers
					$.each(container.data('markers'), function(index, data) {
						bounds.extend([data.longitude, data.latitude]);

						points.push({
							'type': 'Feature',
							'geometry': {
								'type': 'Point',
								'coordinates': [data.longitude, data.latitude],
							},
							'properties': {
								'content': data.content,
							}
						});
					});

					// Fit bounds
					map.fitBounds(bounds, {
						maxZoom: maxZoom - 1,
						duration: 0,
					});

					map.on('load', () => {
						map.addSource('locations', {
							type: 'geojson',
							data: {
								"type": "FeatureCollection",
								"features": points,
							},
							cluster: true,
							clusterMaxZoom: 12,
							clusterRadius: 50
						});

						map.addLayer({
							id: 'clusters',
							type: 'circle',
							source: 'locations',
							filter: ['has', 'point_count'],
							paint: {
								'circle-color': [
									'step',
									['get', 'point_count'],
									'#51bbd6',
									100,
									'#f1f075',
									750,
									'#f28cb1'
								],
								'circle-radius': [
									'step',
									['get', 'point_count'],
									20,
									100,
									30,
									750,
									40
								]
							}
						});

						map.addLayer({
							id: 'cluster-count',
							type: 'symbol',
							source: 'locations',
							filter: ['has', 'point_count'],
							layout: {
								'text-field': '{point_count_abbreviated}',
								'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
								'text-size': 12
							}
						});

						if (container.attr('data-scatter')) {
							map.setMaxZoom(13);
							map.addLayer({
								id: 'unclustered-point',
								type: 'circle',
								source: 'locations',
								maxzoom: 16,
								filter: ['!', ['has', 'point_count']],
								paint: {
									'circle-color': '#11b4da',
									'circle-radius': 100,
									'circle-opacity': 0.5,
								}
							});
						} else {
							map.loadImage(
								mapboxData.markerImage,
								(error, image) => {
									map.addImage('custom-marker', image);
									map.addLayer({
										id: 'unclustered-point',
										type: 'symbol',
										source: 'locations',
										filter: ['!', ['has', 'point_count']],
										layout: {
											'icon-image': 'custom-marker',
										}
									});
								}
							);
						}

						// When click on cluster.
						map.on('click', 'clusters', (e) => {
							const features = map.queryRenderedFeatures(e.point, {
								layers: ['clusters']
							});
							const clusterId = features[0].properties.cluster_id;
							map.getSource('locations').getClusterExpansionZoom(
								clusterId,
								(err, zoom) => {
									if (err) return;

									map.easeTo({
										center: features[0].geometry.coordinates,
										zoom: zoom
									});
								}
							);
						});

						// When click on marker.
						map.on('click', 'unclustered-point', (e) => {
							const coordinates = e.features[0].geometry.coordinates.slice();
							const content = e.features[0].properties.content;

							while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
								coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
							}

							new mapboxgl.Popup()
								.setLngLat(coordinates)
								.setHTML(content)
								.addTo(map);
						});

						map.on('mouseenter', 'clusters', () => {
							map.getCanvas().style.cursor = 'pointer';
						});
						map.on('mouseleave', 'clusters', () => {
							map.getCanvas().style.cursor = '';
						});
					});
				} else {
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
					});
					var prevWindow = false,
						markers = [],
						bounds = new google.maps.LatLngBounds(),
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

					// Add markers
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

					// Fit bounds
					map.fitBounds(bounds);

					var observer = new MutationObserver(function(mutations) {
						map.fitBounds(bounds);
					});

					observer.observe(container.get(0), {
						attributes: true,
					});

					// Cluster markers
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
				}
			});
		});
	})(jQuery);
}

// Mapbox
if (typeof mapboxData !== 'undefined') {
	hivepress.initGeolocation();
}

// Resize Mapbox map.
(function($) {
	'use strict';
	$(document).ready(function() {

		// Toggle
		hivepress.getComponent('toggle').each(function() {
			var button = $(this);

			button.on('click', function(e) {
				if (typeof mapboxData !== 'undefined' && button.attr('data-toggle') === 'map' && button.attr('data-state') === 'active') {
					map.resize();
				}
			});
		});
	});
})(jQuery);
