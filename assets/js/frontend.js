hivepress.initMap = function() {
  (function($) {
    'use strict';

    // Geocomplete
    hivepress.getObject('geocomplete').each(function() {
      var field = $(this);

      field.geocomplete({
        details: field.closest('form'),
        detailsAttribute: 'data-type',
      });
    });

    // Geolocate
    hivepress.getObject('geolocate').each(function() {
      var button = $(this);

      if (navigator.geolocation) {
        button.on('click', function(e) {
          navigator.geolocation.getCurrentPosition(function(position) {
            button.closest('form').find(hivepress.getSelector('geocomplete')).geocomplete('find', position.coords.latitude + ' ' + position.coords.longitude);
          });

          e.preventDefault();
        });
      } else {
        button.hide();
      }
    });

    // Map
    hivepress.getObject('map').each(function() {
      var container = $(this),
        prevWindow = false,
        bounds = new google.maps.LatLngBounds(),
        map = new google.maps.Map(container.get(0), {
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

      $(document).ready(function() {
        container.height(container.width());
      });

      $.each(container.data('json'), function(index, data) {
        var nextWindow = new google.maps.InfoWindow({
            content: data.content,
          }),
          marker = new google.maps.Marker({
            map: map,
            title: data.title,
            position: {
              lat: data.latitude,
              lng: data.longitude,
            },
          });

        marker.addListener('click', function() {
          if (prevWindow) {
            prevWindow.close();
          }

          prevWindow = nextWindow;
          nextWindow.open(map, marker);
        });

        bounds.extend(marker.getPosition());
      });

      map.fitBounds(bounds);
    });
  })(jQuery);
}