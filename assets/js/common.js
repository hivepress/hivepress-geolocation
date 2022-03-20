(function($) {
    'use strict';

    $(document).ready(function() {

        var map = null;

        // Map
        hivepress.getComponent('map').each(function() {
            var container = $(this),
                maxZoom = container.data('max-zoom');

            var mapKey = mapboxData.apiKey;

            if ('mapbox' === mapboxData.provider && mapKey) {

                mapboxgl.accessToken = mapKey;
                map = new mapboxgl.Map({
                    container: container.get(0),
                    style: 'mapbox://styles/mapbox/streets-v11',
                    center: [0, 0],
                    zoom: 1
                });

                var bounds = new mapboxgl.LngLatBounds();

                $.each(container.data('markers'), function(index, data) {
                    bounds.extend([data.longitude, data.latitude]);

                    new mapboxgl.Marker()
                        .setLngLat([data.longitude, data.latitude])
                        .setPopup(new mapboxgl.Popup().setHTML(data.content))
                        .addTo(map);
                });

                map.fitBounds(bounds, {
                    maxZoom: maxZoom - 1,
                });
            } else {
                var height = container.width(),
                    prevWindow = false,
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
            }
        });

        // Location
        hivepress.getComponent('location').each(function() {
            var container = $(this),
                field = container.find('input[type=text]'),
                currentForm = field.closest('form'),
                mapKey = mapboxData.apiKey;

            if ('mapbox' === mapboxData.provider && mapKey) {
                // Check token exist.
                if (!mapboxgl.accessToken) {
                    mapboxgl.accessToken = mapKey;
                }

                // Add the control to the map.
                const geocoder = new MapboxGeocoder({
                    accessToken: mapboxgl.accessToken,
                    mapboxgl: mapboxgl,
                    language: hivepressCoreData.language,
                    types: 'country, region, district, place, locality, address',
                    worldview: mapboxData.region,
                });

                geocoder.on('result', function(result) {
                    var placeName = result.result.text,
                        parts = {
                            'place': '',
                            'district': '',
                            'region': '',
                            'country': '',
                        },
                        region = '',
                        addressSearch = false;

                    container.closest('form').find('input[data-coordinate="lng"]').val(result.result.geometry.coordinates[0]);
                    container.closest('form').find('input[data-coordinate="lat"]').val(result.result.geometry.coordinates[1]);

                    $.each(result.result.place_type, function(index, component) {
                        parts[component] = placeName;

                        if ('address' === placeName) {
                            addressSearch = true;
                            return false;
                        }
                    });

                    if (!addressSearch) {
                        $.each(result.result.context, function(index, component) {
                            parts[component.id.split('.')[0]] = component.text;
                        });

                        $.each(parts, function(key, value) {
                            if (value) {
                                region += '|' + value;
                            }
                        });

                        region = region.substring(1);
                    }

                    $('input[data-regions]').val(region);
                });
                $(container).prepend(geocoder.onAdd(map));

                var fieldSettings = {
                        class: field.attr('class'),
                        placeholder: field.attr('placeholder'),
                        maxlength: field.attr('maxlength'),
                        name: field.attr('name'),
                        required: field.attr('required')
                    },
                    fieldValue = field.val();

                field.add('.mapboxgl-ctrl-geocoder--icon-search, .mapboxgl-ctrl-geocoder--button').remove();
                container.find('.mapboxgl-ctrl-geocoder--input')
                    .attr(fieldSettings)
                    .val(fieldValue);

            } else {
                var button = container.find('a'),
                    settings = {
                        details: currentForm,
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

                field.geocomplete(settings).bind("geocode:result", function(event, result) {
                    var parts = [],
                        types = [
                            'locality',
                            'administrative_area_level_1',
                            'administrative_area_level_2',
                            'country',
                        ];

                    $.each(result.address_components, function(index, component) {

                        if (component.types.indexOf('route') >= 0) {
                            parts = [];
                            return false;
                        }

                        if (component.types.filter(function(type) {
                                return types.indexOf(type) !== -1;
                            }).length) {
                            parts.push(component.long_name);
                        }
                    });

                    $('input[data-regions]').val(parts.join('|'));
                });

                if (container.data('scatter')) {
                    field.bind('geocode:result', function(event, result) {
                        var parts = [],
                            types = [
                                'route',
                                'locality',
                                'administrative_area_level_1',
                                'administrative_area_level_2',
                                'country',
                            ];

                        $.each(result.address_components, function(index, component) {
                            if (component.types.filter(function(type) {
                                    return types.indexOf(type) !== -1;
                                }).length) {
                                parts.push(component.long_name);
                            }
                        });

                        field.val(parts.join(', '));
                    });
                }

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
            }
        });
    });
})(jQuery);