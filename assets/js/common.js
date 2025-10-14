(function ($) {
	'use strict';

	hivepress.initGeolocation = function (container) {

		// Location
		container.find(hivepress.getSelector('location')).each(function () {
			var container = $(this),
				form = container.closest('form'),
				field = container.find('input[type=text]'),
				latitudeField = form.find('input[data-coordinate=lat]'),
				longitudeField = form.find('input[data-coordinate=lng]'),
				regionField = form.find('input[data-region]'),
				regionTypes = [],
				button = container.find('a'),
				settings = {};

			if (field.data('region-types')) {
				regionTypes = field.data('region-types');
			}

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

				$.each(fieldAttributes, function () {
					field.attr(this.name, this.value);
				});

				mapboxContainer.detach().prependTo(container);

				// Set location
				geocoder.on('result', function (result) {
					var types = regionTypes;

					// Set region
					if (regionField.length) {
						if (result.result.place_type.filter(value => types.includes(value)).length) {
							regionField.val(result.result.id);
						} else {
							regionField.val('');
						}
					}

					// Set coordinates
					longitudeField.val(result.result.geometry.coordinates[0]);
					latitudeField.val(result.result.geometry.coordinates[1]);

					// Set address
					if (field.data('address')) {
						if (!field.val()) {
							field.val(field.data('address'));
						}

						field.removeData('address');
					}
				});
			} else if ($.fn.geocomplete) {
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
				field.bind('geocode:result', function (event, result) {
					var parts = [],
						types = regionTypes;

					// Set region
					if (regionField.length) {
						if (result.address_components[0].types.filter(value => types.includes(value)).length) {
							regionField.val(result.place_id);
						} else {
							regionField.val('');
						}
					}

					// Set address
					if (field.data('address')) {
						if (!field.val()) {
							field.val(field.data('address'));
						}

						field.removeData('address');
					}

					if (container.data('scatter')) {
						types.push('route');

						$.each(result.address_components, function (index, component) {
							if (component.types.filter(value => types.includes(value)).length) {
								parts.push(component.long_name);
							}
						});

						field.val(parts.join(', '));
					}
				});
			} else {
				settings = {
					language: hivepressCoreData.language,
					sessionToken: false,
				};

				// Set countries
				if (container.data('countries')) {
					settings['includedRegionCodes'] = container.data('countries');
				}

				// Set types
				if (container.data('types')) {
					settings['includedPrimaryTypes'] = container.data('types');
				}

				// Create Geocoder
				var geocoder = new google.maps.Geocoder();

				// Initialize Autocomplete
				field.autocomplete({
					source: async function (request, response) {
						var results = [];

						if (!settings['sessionToken']) {
							settings['sessionToken'] = new google.maps.places.AutocompleteSessionToken();
						}

						const { suggestions } = await google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions($.extend(settings, {
							input: request.term,
						}));

						for (let suggestion of suggestions) {
							results.push({
								label: suggestion.placePrediction.text.toString(),
								value: suggestion.placePrediction,
							});
						}

						response(results);
					},
					select: async function (e, ui) {
						e.preventDefault();

						const place = ui.item.value.toPlace();

						await place.fetchFields({ fields: ['location', 'addressComponents'] });

						settings['sessionToken'] = false;

						field.val(ui.item.label);
						latitudeField.val(place.location.lat);
						longitudeField.val(place.location.lng);

						var parts = [],
							types = regionTypes;

						// Set region
						if (regionField.length) {
							if (place.addressComponents[0].types.filter(value => types.includes(value)).length) {
								regionField.val(place.id);
							} else {
								regionField.val('');
							}
						}

						// Set address
						if (field.data('address')) {
							if (!field.val()) {
								field.val(field.data('address'));
							}

							field.removeData('address');
						}

						if (container.data('scatter')) {
							types.push('route');

							$.each(place.addressComponents, function (index, component) {
								if (component.types.filter(value => types.includes(value)).length) {
									parts.push(component.longText);
								}
							});

							field.val(parts.join(', '));
						}
					},
					open: function () {
						var widget = $(this).autocomplete('widget');

						widget.addClass('pac-container pac-logo hdpi').removeClass('ui-menu ui-widget ui-widget-content ui-autocomplete ui-front');
						widget.find('li').children('span').removeClass('ui-menu-item-wrapper ui-state-active');

						widget.width(field.outerWidth());
					},
				});

				field.data('ui-autocomplete')._renderItem = function (ul, item) {
					return $('<li>').addClass('pac-item').append('<span class="pac-item-query">' + item.label + '</span>').appendTo(ul);
				};
			}

			// Clear location
			field.on('input', function () {
				if (field.val().length <= 1) {
					form.find('input[data-coordinate]').val('');

					if (regionField.length) {
						regionField.val('');
					}
				}
			});

			field.on('focusout', function () {
				if (field.val() && (!latitudeField.val() || !longitudeField.val())) {
					field.data('address', field.val());

					field.val('');
				}
			});

			// Detect location
			if (navigator.geolocation) {
				button.on('click', function (e) {
					navigator.geolocation.getCurrentPosition(function (position) {
						if (typeof mapboxData !== 'undefined') {
							geocoder.options.reverseGeocode = true;
							geocoder.options.limit = 1;

							geocoder.query(position.coords.latitude + ',' + position.coords.longitude);

							geocoder.options.reverseGeocode = false;
							geocoder.options.limit = 5;
						} else if ($.fn.geocomplete) {
							field.geocomplete('find', position.coords.latitude + ' ' + position.coords.longitude);
						} else {
							geocoder.geocode({ location: { lat: position.coords.latitude, lng: position.coords.longitude } }, (results, status) => {
								if (status === 'OK' && results.length) {
									field.val(results[0].formatted_address).focus().autocomplete('search');
								}
							});
						}
					});

					e.preventDefault();
				});
			} else {
				button.hide();
			}
		});

		// Map
		container.find(hivepress.getSelector('map')).each(function () {
			var container = $(this),
				height = container.width(),
				maxZoom = container.data('max-zoom'),
				markerIcon = container.data('marker');

			// Set height
			if (container.is('[data-height]')) {
				height = container.data('height');
			}

			container.height(height);

			if (typeof mapboxData !== 'undefined') {

				// Set API key
				mapboxgl.accessToken = mapboxData.apiKey;

				// Create map
				var bounds = new mapboxgl.LngLatBounds(),
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

				// Add markers
				$.each(container.data('markers'), function (index, data) {
					bounds.extend([data.longitude, data.latitude]);

					var marker = new mapboxgl.Marker()
						.setLngLat([data.longitude, data.latitude])
						.setPopup(new mapboxgl.Popup().setHTML(data.content))
						.addTo(map);
				});

				// Fit bounds
				map.fitBounds(bounds, {
					maxZoom: maxZoom - 1,
					padding: 50,
					duration: 0,
				});

				var observer = new ResizeObserver(function () {
					map.resize();

					map.fitBounds(bounds, {
						maxZoom: maxZoom - 1,
						padding: 50,
						duration: 0,
					});
				}).observe(container.get(0));
			} else {
				var prevWindow = false,
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

				// Add markers
				$.each(container.data('markers'), function (index, data) {
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

					if (markerIcon) {
						markerSettings['icon'] = {
							url: markerIcon,
							scaledSize: new google.maps.Size(50, 50),
						};
					}

					if (container.data('scatter')) {
						markerSettings['icon'] = iconSettings;
					}

					var marker = new google.maps.Marker(markerSettings);

					marker.addListener('spider_click', function () {
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

				var observer = new ResizeObserver(function () {
					map.fitBounds(bounds);
				}).observe(container.get(0));

				// Cluster markers
				var clusterer = new MarkerClusterer(map, markers, {
					imagePath: hivepressGeolocationData.assetURL + '/images/markerclustererplus/m',
					maxZoom: maxZoom - 1,
				});

				if (container.data('scatter')) {
					map.addListener('zoom_changed', function () {
						iconSettings['scale'] = Math.pow(1.3125, map.getZoom());

						$.each(markers, function (index, marker) {
							markers[index].setIcon(iconSettings);
						});
					});
				}
			}
		});
	}

	$(document).on('hivepress:init', function (event, container) {
		hivepress.initGeolocation(container);
	});
})(jQuery);
