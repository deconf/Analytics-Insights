/*
 * Responsive charts
 */
jQuery(window).resize(
		function() {
			if (typeof ga_dash_drawmainchart == "function"
					&& typeof gadash_mainchart !== 'undefined'
					&& !jQuery.isNumeric(gadash_mainchart)) {
				ga_dash_drawmainchart(gadash_mainchart);
			}
			if (typeof ga_dash_drawlocations == "function"
					&& typeof gadash_locations !== 'undefined'
					&& !jQuery.isNumeric(gadash_locations)) {
				ga_dash_drawmaplocations(gadash_locations);
				ga_dash_drawlocations(gadash_locations);
			}
			if (typeof ga_dash_drawtrafficchannels == "function"
					&& typeof gadash_trafficchannels !== 'undefined'
					&& !jQuery.isNumeric(gadash_trafficchannels)) {
				ga_dash_drawtrafficchannels(gadash_trafficchannels);
			}
			if (typeof ga_dash_drawprs == "function"
					&& typeof gadash_prs !== 'undefined'
					&& !jQuery.isNumeric(gadash_prs)) {
				ga_dash_drawprs(gadash_prs);
			}
			if (typeof ga_dash_drawtrafficmediums == "function"
					&& typeof gadash_trafficmediums !== 'undefined'
					&& !jQuery.isNumeric(gadash_trafficmediums)) {
				ga_dash_drawtrafficmediums(gadash_trafficmediums);
			}
			if (typeof ga_dash_drawtraffictype == "function"
					&& typeof gadash_traffictype !== 'undefined'
					&& !jQuery.isNumeric(gadash_traffictype)) {
				ga_dash_drawtraffictype(gadash_traffictype);
			}
			if (typeof ga_dash_drawsocialnetworks == "function"
					&& typeof gadash_socialnetworks !== 'undefined'
					&& !jQuery.isNumeric(gadash_socialnetworks)) {
				ga_dash_drawsocialnetworks(gadash_socialnetworks);
			}
			if (typeof ga_dash_drawtrafficorganic == "function"
					&& typeof gadash_trafficorganic !== 'undefined'
					&& !jQuery.isNumeric(gadash_trafficorganic)) {
				ga_dash_drawtrafficorganic(gadash_trafficorganic);
			}
		});
