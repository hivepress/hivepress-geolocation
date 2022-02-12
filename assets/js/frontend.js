(function($) {
   'use strict';

   $(document).ready(function() {

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
