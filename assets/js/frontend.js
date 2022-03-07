(function($) {
   'use strict';

   $(document).ready(function() {

	   // Mapbox map.
	   hivepress.getComponent('map').each(function() {
		   var container = $(this);
		   var mapKey = container.attr('data-map-key');
		   if('mapbox' === container.attr('data-provider') && mapKey){

			   L.mapbox.accessToken = mapKey;
			   var map = L.mapbox.map(container.get(0))
			   .setView([0,0], 1)
			   .addLayer(L.mapbox.styleLayer('mapbox://styles/mapbox/streets-v11'));

			   $.each(container.data('markers'), function(index, data) {

				   L.marker([data.latitude, data.longitude], {
					   icon: L.mapbox.marker.icon({
						   'marker-color': '#ff1307'
					   })
				   })
				   .bindPopup('<p>' + data.content + '</p>')
				   .addTo(map);
			   });
		   }
	   });

	   // Radius slider
	   hivepress.getComponent('radius-slider').each(function() {
		   var field = $(this),
			   slider = null;

		   field.wrap('<div class="hp-field--number-range hp-field--radius" />');

		   slider = $('<div />').insertAfter(field).slider({
			   min: Number(field.attr('min')),
			   max: Number(field.attr('max')),
			   value: Number(field.val()),
			   slide: function(e, ui) {
				   field.val(ui.value);
			   },
		   });

		   slider.wrap('<div />');
	   });
   });
})(jQuery);
