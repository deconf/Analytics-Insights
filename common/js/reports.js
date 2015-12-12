"use strict";

jQuery( function () {
	google.load( "visualization", "1", {
		packages : [ "corechart", "table", "orgchart", "geochart" ],
		'language' : gadwp_item_data.language,
		'callback' : GADWPLoad
	} );
} );

// Get the numeric ID
gadwp_item_data.getID = function ( item ) {
	if ( gadwp_item_data.scope == 'admin-item' ) {
		if ( typeof item.id == "undefined" ) {
			return 0
		}
		if ( item.id.split( '-' )[ 1 ] == "undefined" ) {
			return 0;
		} else {
			return item.id.split( '-' )[ 1 ];
		}
	} else {
		if ( typeof item.id == "undefined" ) {
			return 1;
		}
		if ( item.id.split( '-' )[ 4 ] == "undefined" ) {
			return 1;
		} else {
			return item.id.split( '-' )[ 4 ];
		}
	}
}

// Get the selector
gadwp_item_data.getSelector = function ( scope ) {
	if ( scope == 'admin-item' ) {
		return 'a[id^="gadwp-"]';
	} else {
		return 'li[id^="wp-admin-bar-gadwp"]';
	}
}

gadwp_item_data.responsiveDialog = function () {
	var dialog;
	var wWidth;
	var visible = jQuery( ".ui-dialog:visible" );

	// on each visible dialog
	visible.each( function () {
		dialog = jQuery( this ).find( ".ui-dialog-content" ).data( "ui-dialog" );
		// on each fluid dialog
		if ( dialog.options.fluid ) {
			wWidth = jQuery( window ).width();
			// window width vs dialog width
			if ( wWidth < ( parseInt( dialog.options.maxWidth ) + 50 ) ) {
				// don't fill the entire screen
				jQuery( this ).css( "max-width", "90%" );
			} else {
				// maxWidth bug fix
				jQuery( this ).css( "max-width", dialog.options.maxWidth + "px" );
			}
			// change dialog position
			dialog.option( "position", dialog.options.position );
		}
	} );
}

jQuery.fn.extend( {
	gadwpItemReport : function ( item_id ) {
		var post_data;
		var slug = "-" + item_id;
		var dialog_title;
		var tools = {
			set_cookie : function ( name, value ) {
				var expires;
				var date_item = new Date();

				if ( gadwp_item_data.scope == 'admin-widgets' ) {
					name = "gadwp_wg_" + name;
				} else {
					name = "gadwp_ir_" + name;
				}
				date_item.setTime( date_item.getTime() + ( 24 * 60 * 60 * 1000 * 7 ) );
				expires = "expires=" + date_item.toUTCString();
				document.cookie = name + "=" + value + "; " + expires + "; path=/";
			},
			get_cookie : function ( name ) {
				var i = 0;
				var cookie;
				var cookies_array;
				var div;

				if ( gadwp_item_data.scope == 'admin-widgets' ) {
					name = "gadwp_wg_" + name + "=";
				} else {
					name = "gadwp_ir_" + name + "=";
				}
				cookies_array = document.cookie.split( ';' );
				for ( i = 0; i < cookies_array.length; i++ ) {
					cookie = cookies_array[ i ];
					while ( cookie.charAt( 0 ) == ' ' )
						cookie = cookie.substring( 1 );
					if ( cookie.indexOf( name ) == 0 )
						return cookie.substring( name.length, cookie.length );
				}
				return false;
			},
			escape : function ( str ) {
				div = document.createElement( 'div' );
				div.appendChild( document.createTextNode( str ) );
				return div.innerHTML;
			}
		}

		var template = {

			addOptions : function ( id, list ) {
				var default_metric;
				var default_dimension;
				var default_view;
				var output = [];

				if ( list == false ) {
					return;
				}

				if ( !tools.get_cookie( 'default_metric' ) || !tools.get_cookie( 'default_dimension' ) ) {
					if ( gadwp_item_data.scope == 'admin-widgets' ) {
						default_metric = 'sessions';
					} else {
						default_metric = 'uniquePageviews';
					}
					default_dimension = '30daysAgo';
				} else {
					default_metric = tools.get_cookie( 'default_metric' );
					default_dimension = tools.get_cookie( 'default_dimension' );
					default_view = tools.get_cookie( 'default_view' );
				}

				jQuery.each( list, function ( key, value ) {
					if ( key == default_metric || key == default_dimension || key == default_view ) {
						output.push( '<option value="' + key + '" selected="selected">' + value + '</option>' );
					} else {
						output.push( '<option value="' + key + '">' + value + '</option>' );
					}
				} );
				jQuery( id ).html( output.join( '' ) );
			},

			init : function () {
				var tpl;

				if ( !jQuery( '#gadwp-window' + slug ).length ) {
					return;
				}

				if ( jQuery( '#gadwp-window' + slug ).html().length ) { // add main template once
					return;
				}

				tpl = '<div id="gadwp-container' + slug + '">';
				if ( gadwp_item_data.viewList != false ) {
					tpl += '<select id="gadwp-sel-view' + slug + '"></select>';
				}
				tpl += '<select id="gadwp-sel-period' + slug + '"></select> ';
				tpl += '<select id="gadwp-sel-report' + slug + '"></select>';
				tpl += '<div id="gadwp-progressbar' + slug + '"></div>';
				tpl += '<div id="gadwp-status' + slug + '"></div>';
				tpl += '<div id="gadwp-reports' + slug + '"></div>';
				tpl += '<div style="text-align:right;width:100%;font-size:0.8em;clear:both;margin-right:5px;margin-top:10px;">';
				tpl += gadwp_item_data.i18n[ 14 ];
				tpl += ' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_report&utm_medium=link&utm_content=back_report&utm_campaign=gadwp" rel="nofollow" style="text-decoration:none;font-size:1em;">GADWP</a>&nbsp;';
				tpl += '</div>';
				tpl += '</div>',

				jQuery( '#gadwp-window' + slug ).append( tpl );

				template.addOptions( '#gadwp-sel-view' + slug, gadwp_item_data.viewList );
				template.addOptions( '#gadwp-sel-period' + slug, gadwp_item_data.dateList );
				template.addOptions( '#gadwp-sel-report' + slug, gadwp_item_data.reportList );

			}
		}

		var reports = {
			npcounter : 0,
			prs : '',
			trafficchannels : '',
			trafficmediums : '',
			traffictype : '',
			trafficorganic : '',
			socialnetworks : '',
			locations : '',
			mainchart : '',
			bottomstats : '',
			realtime : '',
			realtime_running : null,

			getTitle : function ( scope ) {
				if ( scope == 'admin-item' ) {
					return jQuery( '#gadwp' + slug ).attr( "title" );
				} else {
					return document.getElementsByTagName( "title" )[ 0 ].innerHTML;
				}
			},

			alertMessage : function ( msg ) {
				jQuery( "#gadwp-status" + slug ).css( {
					"margin-top" : "3px",
					"padding-left" : "5px",
					"height" : "auto",
					"color" : "#000",
					"border-left" : "5px solid red"
				} );
				jQuery( "#gadwp-status" + slug ).html( msg );
			},

			drawprs : function ( gadwp_prs ) {
				var chart_data = google.visualization.arrayToDataTable( gadwp_prs );
				var options = {
					page : 'enable',
					pageSize : 10,
					width : '100%',
					allowHtml : true
				};
				var chart = new google.visualization.Table( document.getElementById( 'gadwp-prs' + slug ) );

				chart.draw( chart_data, options );
			},

			drawtrafficchannels : function ( gadwp_trafficchannels ) {
				var chart_data = google.visualization.arrayToDataTable( gadwp_trafficchannels );
				var options = {
					allowCollapse : true,
					allowHtml : true,
					height : '100%'
				};
				var chart = new google.visualization.OrgChart( document.getElementById( 'gadwp-trafficchannels' + slug ) );

				chart.draw( chart_data, options );
			},

			drawtrafficmediums : function ( gadwp_trafficmediums ) {
				var chart_data = google.visualization.arrayToDataTable( gadwp_trafficmediums );
				var options = {
					is3D : false,
					tooltipText : 'percentage',
					legend : 'none',
					chartArea : {
						width : '99%',
						height : '80%'
					},
					title : gadwp_item_data.i18n[ 1 ],
					colors : gadwp_item_data.colorVariations
				};
				var chart = new google.visualization.PieChart( document.getElementById( 'gadwp-trafficmediums' + slug ) );

				chart.draw( chart_data, options );
			},

			drawtraffictype : function ( gadwp_traffictype ) {
				var chart_data = google.visualization.arrayToDataTable( gadwp_traffictype );
				var options = {
					is3D : false,
					tooltipText : 'percentage',
					legend : 'none',
					chartArea : {
						width : '99%',
						height : '80%'
					},
					title : gadwp_item_data.i18n[ 2 ],
					colors : gadwp_item_data.colorVariations
				};
				var chart = new google.visualization.PieChart( document.getElementById( 'gadwp-traffictype' + slug ) );

				chart.draw( chart_data, options );
			},

			drawsocialnetworks : function ( gadwp_socialnetworks ) {
				var chart_data = google.visualization.arrayToDataTable( gadwp_socialnetworks );
				var options = {
					is3D : false,
					tooltipText : 'percentage',
					legend : 'none',
					chartArea : {
						width : '99%',
						height : '80%'
					},
					title : gadwp_item_data.i18n[ 3 ],
					colors : gadwp_item_data.colorVariations
				};
				var chart = new google.visualization.PieChart( document.getElementById( 'gadwp-socialnetworks' + slug ) );

				chart.draw( chart_data, options );
			},

			drawtrafficorganic : function ( gadwp_trafficorganic ) {
				var chart_data = google.visualization.arrayToDataTable( gadwp_trafficorganic );
				var options = {
					is3D : false,
					tooltipText : 'percentage',
					legend : 'none',
					chartArea : {
						width : '99%',
						height : '80%'
					},
					title : gadwp_item_data.i18n[ 4 ],
					colors : gadwp_item_data.colorVariations
				};
				var chart = new google.visualization.PieChart( document.getElementById( 'gadwp-trafficorganic' + slug ) );

				chart.draw( chart_data, options );
			},

			drawlocations : function ( gadwp_locations ) {
				var chart_data = google.visualization.arrayToDataTable( gadwp_locations );
				var options = {
					page : 'enable',
					pageSize : 10,
					width : '100%'
				};
				var chart = new google.visualization.Table( document.getElementById( 'gadwp-locations' + slug ) );

				chart.draw( chart_data, options );
			},

			drawmaplocations : function ( gadwp_locations ) {
				var chart_data = google.visualization.arrayToDataTable( gadwp_locations );
				var options = {
					chartArea : {
						width : '99%',
						height : '90%'
					},
					colors : [ gadwp_item_data.colorVariations[ 5 ], gadwp_item_data.colorVariations[ 4 ] ]
				}
				if ( gadwp_item_data.region ) {
					options.region = gadwp_item_data.region;
					options.displayMode = 'markers';
					options.datalessRegionColor = 'EFEFEF';
				}
				var chart = new google.visualization.GeoChart( document.getElementById( 'gadwp-map' + slug ) );

				chart.draw( chart_data, options );
			},

			drawmainchart : function ( gadwp_mainchart, format ) {
				var chart_data = google.visualization.arrayToDataTable( gadwp_mainchart );
				var formatter;

				if ( format ) {
					formatter = new google.visualization.NumberFormat( {
						suffix : '%',
						fractionDigits : 2
					} );

					formatter.format( chart_data, 1 );
				}

				var options = {
					legend : {
						position : 'none'
					},
					pointSize : 3,
					colors : [ gadwp_item_data.colorVariations[ 0 ], gadwp_item_data.colorVariations[ 4 ] ],
					chartArea : {
						width : '99%',
						height : '90%'
					},
					vAxis : {
						textPosition : "in",
						minValue : 0
					},
					hAxis : {
						textPosition : 'none'
					}
				};
				var chart = new google.visualization.AreaChart( document.getElementById( 'gadwp-mainchart' + slug ) );

				chart.draw( chart_data, options );
			},

			drawbottomstats : function ( gadwp_bottomstats ) {
				jQuery( "#gdsessions" + slug ).text( gadwp_bottomstats[ 0 ] );
				jQuery( "#gdusers" + slug ).text( gadwp_bottomstats[ 1 ] );
				jQuery( "#gdpageviews" + slug ).text( gadwp_bottomstats[ 2 ] );
				jQuery( "#gdbouncerate" + slug ).text( gadwp_bottomstats[ 3 ] + "%" );
				jQuery( "#gdorganicsearch" + slug ).text( gadwp_bottomstats[ 4 ] );
				jQuery( "#gdpagespervisit" + slug ).text( gadwp_bottomstats[ 5 ] );
			},

			rt_onlyUniqueValues : function ( value, index, self ) {
				return self.indexOf( value ) === index;
			},

			rt_countsessions : function ( gadwp_realtime, searchvalue ) {
				var count = 0;
				var i = 0;

				for ( i = 0; i < gadwp_realtime[ "rows" ].length; i = i + 1 ) {
					if ( jQuery.inArray( searchvalue, gadwp_realtime[ "rows" ][ i ] ) > -1 ) {
						count += parseInt( gadwp_realtime[ "rows" ][ i ][ 6 ] );
					}
				}
				return count;
			},

			rt_generatetooltip : function ( gadwp_realtime ) {
				var count = 0;
				var table = "";
				var i = 0;

				for ( i = 0; i < gadwp_realtime.length; i = i + 1 ) {
					count += parseInt( gadwp_realtime[ i ].count );
					table += "<tr><td class='gadwp-pgdetailsl'>" + gadwp_realtime[ i ].value + "</td><td class='gadwp-pgdetailsr'>" + gadwp_realtime[ i ].count + "</td></tr>";
				}
				;
				if ( count ) {
					return ( "<table>" + table + "</table>" );
				} else {
					return ( "" );
				}
			},

			rt_pagedetails : function ( gadwp_realtime, searchvalue ) {
				var sant;
				var i = 0;
				var j = 0;
				var sum = 0;
				var newsum = 0;
				var newgadwp_realtime = [];
				var countrfr = 0;
				var countkwd = 0;
				var countdrt = 0;
				var countscl = 0;
				var countcpg = 0;
				var tablerfr = "";
				var tablekwd = "";
				var tablescl = "";
				var tablecpg = "";
				var tabledrt = "";
				var pagetitle;
				var pgstatstable;

				for ( i = 0; i < gadwp_realtime[ "rows" ].length; i = i + 1 ) {
					sant = 1;
					for ( j = 0; j < newgadwp_realtime.length; j = j + 1 ) {
						jQuery.each( gadwp_realtime[ "rows" ][ i ], function () {
							sum += parseFloat( this ) || 0;
						} );
						jQuery.each( newgadwp_realtime[ j ], function () {
							newsum += parseFloat( this ) || 0;
						} );
						if ( sum == newsum ) {
							newgadwp_realtime[ j ][ 6 ] = parseInt( newgadwp_realtime[ j ][ 6 ] ) + parseInt( gadwp_realtime[ "rows" ][ i ][ 6 ] );
							sant = 0;
						}
					}
					if ( sant ) {
						newgadwp_realtime.push( gadwp_realtime[ "rows" ][ i ].slice() );
					}
				}

				for ( i = 0; i < newgadwp_realtime.length; i = i + 1 ) {
					if ( newgadwp_realtime[ i ][ 0 ] == searchvalue ) {
						pagetitle = newgadwp_realtime[ i ][ 5 ];

						switch ( newgadwp_realtime[ i ][ 3 ] ) {

							case "REFERRAL":
								countrfr += parseInt( newgadwp_realtime[ i ][ 6 ] );
								tablerfr += "<tr><td class='gadwp-pgdetailsl'>" + newgadwp_realtime[ i ][ 1 ] + "</td><td class='gadwp-pgdetailsr'>" + newgadwp_realtime[ i ][ 6 ] + "</td></tr>";
								break;
							case "ORGANIC":
								countkwd += parseInt( newgadwp_realtime[ i ][ 6 ] );
								tablekwd += "<tr><td class='gadwp-pgdetailsl'>" + newgadwp_realtime[ i ][ 2 ] + "</td><td class='gadwp-pgdetailsr'>" + newgadwp_realtime[ i ][ 6 ] + "</td></tr>";
								break;
							case "SOCIAL":
								countscl += parseInt( newgadwp_realtime[ i ][ 6 ] );
								tablescl += "<tr><td class='gadwp-pgdetailsl'>" + newgadwp_realtime[ i ][ 1 ] + "</td><td class='gadwp-pgdetailsr'>" + newgadwp_realtime[ i ][ 6 ] + "</td></tr>";
								break;
							case "CUSTOM":
								countcpg += parseInt( newgadwp_realtime[ i ][ 6 ] );
								tablecpg += "<tr><td class='gadwp-pgdetailsl'>" + newgadwp_realtime[ i ][ 1 ] + "</td><td class='gadwp-pgdetailsr'>" + newgadwp_realtime[ i ][ 6 ] + "</td></tr>";
								break;
							case "DIRECT":
								countdrt += parseInt( newgadwp_realtime[ i ][ 6 ] );
								break;
						}
					}
				}

				if ( countrfr ) {
					tablerfr = "<table><tr><td>" + gadwp_item_data.i18n_realtime[ 0 ] + "(" + countrfr + ")</td></tr>" + tablerfr + "</table><br />";
				}
				if ( countkwd ) {
					tablekwd = "<table><tr><td>" + gadwp_item_data.i18n_realtime[ 1 ] + "(" + countkwd + ")</td></tr>" + tablekwd + "</table><br />";
				}
				if ( countscl ) {
					tablescl = "<table><tr><td>" + gadwp_item_data.i18n_realtime[ 2 ] + "(" + countscl + ")</td></tr>" + tablescl + "</table><br />";
				}
				if ( countcpg ) {
					tablecpg = "<table><tr><td>" + gadwp_item_data.i18n_realtime[ 3 ] + "(" + countcpg + ")</td></tr>" + tablecpg + "</table><br />";
				}
				if ( countdrt ) {
					tabledrt = "<table><tr><td>" + gadwp_item_data.i18n_realtime[ 4 ] + "(" + countdrt + ")</td></tr></table><br />";
				}
				return ( "<p><center><strong>" + pagetitle + "</strong></center></p>" + tablerfr + tablekwd + tablescl + tablecpg + tabledrt );
			},

			rt_refresh : function ( ) {
				if ( reports.render.focusFlag ) {
					post_data.from = false;
					post_data.to = false;
					post_data.query = 'realtime';
					jQuery.post( gadwp_item_data.ajaxurl, post_data, function ( response ) {
						if ( jQuery.isArray( response ) ) {
							jQuery( '#gadwp-reports' + slug ).show();
							reports.realtime = response[ 0 ];
							reports.drawrealtime( reports.realtime );
						} else {
							reports.throwDebug( response );
						}

						NProgress.done();

					} );
				}
			},

			drawrealtime : function ( gadwp_realtime ) {
				var i = 0;
				var pagepath = [];
				var referrals = [];
				var keywords = [];
				var social = [];
				var visittype = [];
				var custom = [];
				var upagepathstats = [];
				var upagepath;
				var pgstatstable = "";
				var ureferralsstats = [];
				var ureferrals;
				var ukeywordsstats = [];
				var ukeywords;
				var usocialstats = [];
				var usocial;
				var ucustomstats = [];
				var ucustom;
				var uvisittype = [ "REFERRAL", "ORGANIC", "SOCIAL", "CUSTOM" ];
				var uvisitortype = [ "DIRECT", "NEW" ];

				jQuery( function () {
					jQuery( '#gadwp-widget *' ).tooltip( {
						tooltipClass : "gadwp"
					} );
				} );

				gadwp_realtime = gadwp_realtime[ 0 ];

				if ( jQuery.isNumeric( gadwp_realtime ) || typeof gadwp_realtime === "undefined" ) {
					gadwp_realtime = [];
					gadwp_realtime[ "totalsForAllResults" ] = []
					gadwp_realtime[ "totalsForAllResults" ][ "rt:activeUsers" ] = "0";
					gadwp_realtime[ "rows" ] = [];
				}

				if ( gadwp_realtime[ "totalsForAllResults" ][ "rt:activeUsers" ] !== document.getElementById( "gadwp-online" ).innerHTML ) {
					jQuery( "#gadwp-online" ).fadeOut( "slow" );
					jQuery( "#gadwp-online" ).fadeOut( 500 );
					jQuery( "#gadwp-online" ).fadeOut( "slow", function () {
						if ( ( parseInt( gadwp_realtime[ "totalsForAllResults" ][ "rt:activeUsers" ] ) ) < ( parseInt( document.getElementById( "gadwp-online" ).innerHTML ) ) ) {
							jQuery( "#gadwp-online" ).css( {
								'background-color' : '#FFE8E8'
							} );
						} else {
							jQuery( "#gadwp-online" ).css( {
								'background-color' : '#E0FFEC'
							} );
						}
						document.getElementById( "gadwp-online" ).innerHTML = gadwp_realtime[ "totalsForAllResults" ][ "rt:activeUsers" ];
					} );
					jQuery( "#gadwp-online" ).fadeIn( "slow" );
					jQuery( "#gadwp-online" ).fadeIn( 500 );
					jQuery( "#gadwp-online" ).fadeIn( "slow", function () {
						jQuery( "#gadwp-online" ).css( {
							'background-color' : '#FFFFFF'
						} );
					} );
				}

				if ( gadwp_realtime[ "totalsForAllResults" ][ "rt:activeUsers" ] == 0 ) {
					gadwp_realtime[ "rows" ] = [];
				}

				for ( i = 0; i < gadwp_realtime[ "rows" ].length; i = i + 1 ) {
					pagepath.push( gadwp_realtime[ "rows" ][ i ][ 0 ] );
					if ( gadwp_realtime[ "rows" ][ i ][ 3 ] == "REFERRAL" ) {
						referrals.push( gadwp_realtime[ "rows" ][ i ][ 1 ] );
					}
					if ( gadwp_realtime[ "rows" ][ i ][ 3 ] == "ORGANIC" ) {
						keywords.push( gadwp_realtime[ "rows" ][ i ][ 2 ] );
					}
					if ( gadwp_realtime[ "rows" ][ i ][ 3 ] == "SOCIAL" ) {
						social.push( gadwp_realtime[ "rows" ][ i ][ 1 ] );
					}
					if ( gadwp_realtime[ "rows" ][ i ][ 3 ] == "CUSTOM" ) {
						custom.push( gadwp_realtime[ "rows" ][ i ][ 1 ] );
					}
					visittype.push( gadwp_realtime[ "rows" ][ i ][ 3 ] );
				}

				upagepath = pagepath.filter( reports.rt_onlyUniqueValues );
				for ( i = 0; i < upagepath.length; i = i + 1 ) {
					upagepathstats[ i ] = {
						"pagepath" : upagepath[ i ],
						"count" : reports.rt_countsessions( gadwp_realtime, upagepath[ i ] )
					}
				}
				upagepathstats.sort( function ( a, b ) {
					return b.count - a.count
				} );

				pgstatstable = "";
				for ( i = 0; i < upagepathstats.length; i = i + 1 ) {
					if ( i < gadwp_item_data.realtime_maxpages ) {
						pgstatstable += '<div class="gadwp-pline"><div class="gadwp-pleft"><a href="#" data-gadwp="' + reports.rt_pagedetails( gadwp_realtime, upagepathstats[ i ].pagepath ) + '">' + upagepathstats[ i ].pagepath.substring( 0, 70 ) + '</a></div><div class="gadwp-pright">' + upagepathstats[ i ].count + '</div></div>';
					}
				}
				document.getElementById( "gadwp-pages" ).innerHTML = '<br /><div class="gadwp-pg">' + pgstatstable + '</div>';

				ureferrals = referrals.filter( reports.rt_onlyUniqueValues );
				for ( i = 0; i < ureferrals.length; i = i + 1 ) {
					ureferralsstats[ i ] = {
						"value" : ureferrals[ i ],
						"count" : reports.rt_countsessions( gadwp_realtime, ureferrals[ i ] )
					};
				}
				ureferralsstats.sort( function ( a, b ) {
					return b.count - a.count
				} );

				ukeywords = keywords.filter( reports.rt_onlyUniqueValues );
				for ( i = 0; i < ukeywords.length; i = i + 1 ) {
					ukeywordsstats[ i ] = {
						"value" : ukeywords[ i ],
						"count" : reports.rt_countsessions( gadwp_realtime, ukeywords[ i ] )
					};
				}
				ukeywordsstats.sort( function ( a, b ) {
					return b.count - a.count
				} );

				usocial = social.filter( reports.rt_onlyUniqueValues );
				for ( i = 0; i < usocial.length; i = i + 1 ) {
					usocialstats[ i ] = {
						"value" : usocial[ i ],
						"count" : reports.rt_countsessions( gadwp_realtime, usocial[ i ] )
					};
				}
				usocialstats.sort( function ( a, b ) {
					return b.count - a.count
				} );

				ucustom = custom.filter( reports.rt_onlyUniqueValues );
				for ( i = 0; i < ucustom.length; i = i + 1 ) {
					ucustomstats[ i ] = {
						"value" : ucustom[ i ],
						"count" : reports.rt_countsessions( gadwp_realtime, ucustom[ i ] )
					};
				}
				ucustomstats.sort( function ( a, b ) {
					return b.count - a.count
				} );

				document.getElementById( "gadwp-tdo-right" ).innerHTML = '<div class="gadwp-bigtext"><a href="#" data-gadwp="' + reports.rt_generatetooltip( ureferralsstats ) + '"><div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 0 ] + '</a></div><div class="gadwp-bright">' + reports.rt_countsessions( gadwp_realtime, uvisittype[ 0 ] ) + '</div></div>';
				document.getElementById( "gadwp-tdo-right" ).innerHTML += '<div class="gadwp-bigtext"><a href="#" data-gadwp="' + reports.rt_generatetooltip( ukeywordsstats ) + '"><div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 1 ] + '</a></div><div class="gadwp-bright">' + reports.rt_countsessions( gadwp_realtime, uvisittype[ 1 ] ) + '</div></div>';
				document.getElementById( "gadwp-tdo-right" ).innerHTML += '<div class="gadwp-bigtext"><a href="#" data-gadwp="' + reports.rt_generatetooltip( usocialstats ) + '"><div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 2 ] + '</a></div><div class="gadwp-bright">' + reports.rt_countsessions( gadwp_realtime, uvisittype[ 2 ] ) + '</div></div>';
				document.getElementById( "gadwp-tdo-right" ).innerHTML += '<div class="gadwp-bigtext"><a href="#" data-gadwp="' + reports.rt_generatetooltip( ucustomstats ) + '"><div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 3 ] + '</a></div><div class="gadwp-bright">' + reports.rt_countsessions( gadwp_realtime, uvisittype[ 3 ] ) + '</div></div>';

				document.getElementById( "gadwp-tdo-right" ).innerHTML += '<div class="gadwp-bigtext"><div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 4 ] + '</div><div class="gadwp-bright">' + reports.rt_countsessions( gadwp_realtime, uvisitortype[ 0 ] ) + '</div></div>';
				document.getElementById( "gadwp-tdo-right" ).innerHTML += '<div class="gadwp-bigtext"><div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 5 ] + '</div><div class="gadwp-bright">' + reports.rt_countsessions( gadwp_realtime, uvisitortype[ 1 ] ) + '</div></div>';
			},

			throwDebug : function ( response ) {
				jQuery( "#gadwp-status" + slug ).css( {
					"margin-top" : "3px",
					"padding-left" : "5px",
					"height" : "auto",
					"color" : "#000",
					"border-left" : "5px solid red"
				} );
				if ( response == '-24' ) {
					jQuery( "#gadwp-status" + slug ).html( gadwp_item_data.i18n[ 15 ] );
				} else {
					jQuery( "#gadwp-status" + slug ).html( gadwp_item_data.i18n[ 11 ] );
					console.log( "\n********************* GADWP Log ********************* \n\n" + response );
				}
			},

			throwError : function ( target, response, p ) {
				jQuery( target ).css( {
					"background-color" : "#F7F7F7",
					"height" : "auto",
					"padding-top" : p,
					"padding-bottom" : p,
					"color" : "#000",
					"text-align" : "center"
				} );
				if ( response == -21 ) {
					jQuery( target ).html( gadwp_item_data.i18n[ 12 ] + ' (' + response + ')' );
				} else {
					jQuery( target ).html( gadwp_item_data.i18n[ 13 ] + ' (' + response + ')' );
				}
			},

			render : function ( view, period, query ) {
				var projectId;
				var from;
				var to;
				var tpl;
				var focusFlag;

				if ( period == 'realtime' ) {
					jQuery( '#gadwp-sel-report' + slug ).hide();
				} else {
					jQuery( '#gadwp-sel-report' + slug ).show();
					clearInterval( reports.realtime_running );
				}

				jQuery( '#gadwp-status' + slug ).html( '' );
				switch ( period ) {
					case 'today':
						from = 'today';
						to = 'today';
						break;
					case 'yesterday':
						from = 'yesterday';
						to = 'yesterday';
						break;
					case '7daysAgo':
						from = '7daysAgo';
						to = 'yesterday';
						break;
					case '14daysAgo':
						from = '14daysAgo';
						to = 'yesterday';
						break;
					case '90daysAgo':
						from = '90daysAgo';
						to = 'yesterday';
						break;
					case '365daysAgo':
						from = '365daysAgo';
						to = 'yesterday';
						break;
					case '1095daysAgo':
						from = '1095daysAgo';
						to = 'yesterday';
						break;
					default:
						from = '30daysAgo';
						to = 'yesterday';
						break;
				}

				tools.set_cookie( 'default_metric', query );
				tools.set_cookie( 'default_dimension', period );

				if ( typeof view !== 'undefined' ) {
					tools.set_cookie( 'default_view', view );
					projectId = view;
				} else {
					projectId = false;
				}

				if ( gadwp_item_data.scope == 'admin-item' ) {
					post_data = {
						action : 'gadwp_backend_item_reports',
						gadwp_security_backend_item_reports : gadwp_item_data.security,
						from : from,
						to : to,
						filter : item_id
					}
				} else if ( gadwp_item_data.scope == 'front-item' ) {
					post_data = {
						action : 'gadwp_frontend_item_reports',
						gadwp_security_frontend_item_reports : gadwp_item_data.security,
						from : from,
						to : to,
						filter : gadwp_item_data.filter
					}
				} else {
					post_data = {
						action : 'gadwp_backend_item_reports',
						gadwp_security_backend_item_reports : gadwp_item_data.security,
						projectId : projectId,
						from : from,
						to : to
					}
				}
				if ( period == 'realtime' ) {
					
					reports.render.focusFlag = 1;

					jQuery( window ).bind( "focus", function ( event ) {
						reports.render.focusFlag = 1;
					} ).bind( "blur", function ( event ) {
						reports.render.focusFlag = 0;
					} );

					tpl = '<div id="gadwp-realtime' + slug + '">';
					tpl += '<div class="gadwp-rt-box">';
					tpl += '<div class="gadwp-tdo-left">';
					tpl += '<div class="gadwp-online" id="gadwp-online">0</div>';
					tpl += '</div>';
					tpl += '<div class="gadwp-tdo-right" id="gadwp-tdo-right">';
					tpl += '<div class="gadwp-bigtext">';
					tpl += '<div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 0 ] + '</div>';
					tpl += '<div class="gadwp-bright">0</div>';
					tpl += '</div>';
					tpl += '<div class="gadwp-bigtext">';
					tpl += '<div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 1 ] + '</div>';
					tpl += '<div class="gadwp-bright">0</div>';
					tpl += '</div>';
					tpl += '<div class="gadwp-bigtext">';
					tpl += '<div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 2 ] + '</div>';
					tpl += '<div class="gadwp-bright">0</div>';
					tpl += '</div>';
					tpl += '<div class="gadwp-bigtext">';
					tpl += '<div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 3 ] + '</div>';
					tpl += '<div class="gadwp-bright">0</div>';
					tpl += '</div>';
					tpl += '<div class="gadwp-bigtext">';
					tpl += '<div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 4 ] + '</div>';
					tpl += '<div class="gadwp-bright">0</div>';
					tpl += '</div>';
					tpl += '<div class="gadwp-bigtext">';
					tpl += '<div class="gadwp-bleft">' + gadwp_item_data.i18n_realtime[ 5 ] + '</div>';
					tpl += '<div class="gadwp-bright">0</div>';
					tpl += '</div>';
					tpl += '</div>';
					tpl += '</div>';
					tpl += '<div>';
					tpl += '<div id="gadwp-pages" class="gadwp-pages">&nbsp;</div>';
					tpl += '</div>';
					tpl += '</div>';

					jQuery( '#gadwp-reports' + slug ).html( tpl );
					
					reports.rt_refresh( reports.render.focusFlag );

					reports.realtime_running = setInterval( reports.rt_refresh, 55000 );

				} else {
					if ( jQuery.inArray( query, [ 'referrers', 'contentpages', 'searches' ] ) > -1 ) {

						tpl = '<div id="gadwp-trafficchannels' + slug + '"></div>';
						tpl += '<div id="gadwp-prs' + slug + '"></div>';

						jQuery( '#gadwp-reports' + slug ).html( tpl );
						jQuery( '#gadwp-reports' + slug ).hide();

						post_data.query = 'trafficchannels,' + query;

						jQuery.post( gadwp_item_data.ajaxurl, post_data, function ( response ) {
							if ( jQuery.isArray( response ) ) {
								if ( !jQuery.isNumeric( response[ 0 ] ) ) {
									if ( jQuery.isArray( response[ 0 ] ) ) {
										jQuery( '#gadwp-reports' + slug ).show();
										reports.trafficchannels = response[ 0 ];
										reports.drawtrafficchannels( reports.trafficchannels );
									} else {
										reports.throwDebug( response[ 0 ] );
									}
								} else {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.throwError( '#gadwp-trafficchannels' + slug, response[ 0 ], "125px" );
								}

								if ( !jQuery.isNumeric( response[ 1 ] ) ) {
									if ( jQuery.isArray( response[ 1 ] ) ) {
										reports.prs = response[ 1 ];
										reports.drawprs( reports.prs );
									} else {
										reports.throwDebug( response[ 1 ] );
									}
								} else {
									reports.throwError( '#gadwp-prs' + slug, response[ 1 ], "125px" );
								}
							} else {
								reports.throwDebug( response );
							}
							NProgress.done();
						} );

					} else if ( query == 'trafficdetails' ) {

						tpl = '<div id="gadwp-trafficchannels' + slug + '"></div>';
						tpl += '<div class="gadwp-floatwraper">';
						tpl += '<div id="gadwp-trafficmediums' + slug + '"></div>';
						tpl += '<div id="gadwp-traffictype' + slug + '"></div>';
						tpl += '</div>';
						tpl += '<div class="gadwp-floatwraper">';
						tpl += '<div id="gadwp-trafficorganic' + slug + '"></div>';
						tpl += '<div id="gadwp-socialnetworks' + slug + '"></div>';
						tpl += '</div>';

						jQuery( '#gadwp-reports' + slug ).html( tpl );
						jQuery( '#gadwp-reports' + slug ).hide();

						post_data.query = 'trafficchannels,medium,visitorType,source,socialNetwork';

						jQuery.post( gadwp_item_data.ajaxurl, post_data, function ( response ) {
							if ( jQuery.isArray( response ) ) {
								if ( !jQuery.isNumeric( response[ 0 ] ) ) {
									if ( jQuery.isArray( response[ 0 ] ) ) {
										jQuery( '#gadwp-reports' + slug ).show();
										reports.trafficchannels = response[ 0 ];
										reports.drawtrafficchannels( reports.trafficchannels );
									} else {
										reports.throwDebug( response[ 0 ] );
									}
								} else {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.throwError( '#gadwp-trafficchannels' + slug, response[ 0 ], "125px" );
								}

								if ( !jQuery.isNumeric( response[ 1 ] ) ) {
									if ( jQuery.isArray( response[ 1 ] ) ) {
										jQuery( '#gadwp-reports' + slug ).show();
										reports.trafficmediums = response[ 1 ];
										reports.drawtrafficmediums( reports.trafficmediums );
									} else {
										reports.throwDebug( response[ 1 ] );
									}
								} else {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.throwError( '#gadwp-trafficmediums' + slug, response[ 1 ], "80px" );
								}

								if ( !jQuery.isNumeric( response[ 2 ] ) ) {
									if ( jQuery.isArray( response[ 2 ] ) ) {
										jQuery( '#gadwp-reports' + slug ).show();
										reports.traffictype = response[ 2 ];
										reports.drawtraffictype( reports.traffictype );
									} else {
										reports.throwDebug( response[ 2 ] );
									}
								} else {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.throwError( '#gadwp-traffictype' + slug, response[ 2 ], "80px" );
								}

								if ( !jQuery.isNumeric( response[ 3 ] ) ) {
									if ( jQuery.isArray( response[ 3 ] ) ) {
										jQuery( '#gadwp-reports' + slug ).show();
										reports.trafficorganic = response[ 3 ];
										reports.drawtrafficorganic( reports.trafficorganic );
									} else {
										reports.throwDebug( response[ 3 ] );
									}
								} else {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.throwError( '#gadwp-trafficorganic' + slug, response[ 3 ], "80px" );
								}

								if ( !jQuery.isNumeric( response[ 4 ] ) ) {
									if ( jQuery.isArray( response[ 4 ] ) ) {
										jQuery( '#gadwp-reports' + slug ).show();
										reports.socialnetworks = response[ 4 ];
										reports.drawsocialnetworks( reports.socialnetworks );
									} else {
										reports.throwDebug( response[ 4 ] );
									}
								} else {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.throwError( '#gadwp-socialnetworks' + slug, response[ 4 ], "80px" );
								}
							} else {
								reports.throwDebug( response );
							}
							NProgress.done();
						} );

					} else if ( query == 'locations' ) {

						tpl = '<div id="gadwp-map' + slug + '"></div>';
						tpl += '<div id="gadwp-locations' + slug + '"></div>';

						jQuery( '#gadwp-reports' + slug ).html( tpl );
						jQuery( '#gadwp-reports' + slug ).hide();

						post_data.query = query;

						jQuery.post( gadwp_item_data.ajaxurl, post_data, function ( response ) {
							if ( jQuery.isArray( response ) ) {
								if ( !jQuery.isNumeric( response[ 0 ] ) ) {
									if ( jQuery.isArray( response[ 0 ] ) ) {
										jQuery( '#gadwp-reports' + slug ).show();
										reports.locations = response[ 0 ];
										reports.drawmaplocations( reports.locations );
										reports.drawlocations( reports.locations );
									} else {
										reports.throwDebug( response[ 0 ] );
									}
								} else {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.throwError( '#gadwp-map' + slug, response[ 0 ], "125px" );
									reports.throwError( '#gadwp-locations' + slug, response[ 0 ], "125px" );
								}
							} else {
								reports.throwDebug( response );
							}
							NProgress.done();
						} );

					} else {

						tpl = '<div id="gadwp-mainchart' + slug + '"></div>';
						tpl += '<div id="gadwp-bottomstats' + slug + '" class="gadwp-wrapper">';
						tpl += '<div class="inside">';
						tpl += '<div class="small-box"><h3>' + gadwp_item_data.i18n[ 5 ] + '</h3><p id="gdsessions' + slug + '">&nbsp;</p></div>';
						tpl += '<div class="small-box"><h3>' + gadwp_item_data.i18n[ 6 ] + '</h3><p id="gdusers' + slug + '">&nbsp;</p></div>';
						tpl += '<div class="small-box"><h3>' + gadwp_item_data.i18n[ 7 ] + '</h3><p id="gdpageviews' + slug + '">&nbsp;</p></div>';
						tpl += '<div class="small-box"><h3>' + gadwp_item_data.i18n[ 8 ] + '</h3><p id="gdbouncerate' + slug + '">&nbsp;</p></div>';
						tpl += '<div class="small-box"><h3>' + gadwp_item_data.i18n[ 9 ] + '</h3><p id="gdorganicsearch' + slug + '">&nbsp;</p></div>';
						tpl += '<div class="small-box"><h3>' + gadwp_item_data.i18n[ 10 ] + '</h3><p id="gdpagespervisit' + slug + '">&nbsp;</p></div>';
						tpl += '</div>';
						tpl += '</div>';

						jQuery( '#gadwp-reports' + slug ).html( tpl );
						jQuery( '#gadwp-reports' + slug ).hide();

						post_data.query = query + ',bottomstats';

						jQuery.post( gadwp_item_data.ajaxurl, post_data, function ( response ) {
							if ( jQuery.isArray( response ) ) {
								if ( !jQuery.isNumeric( response[ 0 ] ) ) {
									if ( jQuery.isArray( response[ 0 ] ) ) {
										jQuery( '#gadwp-reports' + slug ).show();
										reports.mainchart = response[ 0 ];
										if ( query == 'visitBounceRate' ) {
											reports.drawmainchart( reports.mainchart, true );
										} else {
											reports.drawmainchart( reports.mainchart, false );
										}
									} else {
										reports.throwDebug( response[ 0 ] );
									}
								} else {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.throwError( '#gadwp-mainchart' + slug, response[ 0 ], "125px" );
								}
								if ( !jQuery.isNumeric( response[ 1 ] ) ) {
									if ( jQuery.isArray( response[ 1 ] ) ) {
										jQuery( '#gadwp-reports' + slug ).show();
										reports.bottomstats = response[ 1 ];
										reports.drawbottomstats( reports.bottomstats );
									} else {
										reports.throwDebug( response[ 1 ] );
									}
								} else {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.throwError( '#gadwp-bottomstats' + slug, response[ 1 ], "40px" );
								}
							} else {
								reports.throwDebug( response );
							}
							NProgress.done();
						} );

					}

				}

			},

			refresh : function () {
				if ( jQuery( '#gadwp-bottomstats' + slug ).length > 0 ) {
					reports.drawbottomstats( reports.bottomstats );
				}
				if ( jQuery( '#gadwp-mainchart' + slug ).length > 0 && jQuery.isArray( reports.mainchart ) ) {
					reports.drawmainchart( reports.mainchart );
				}
				if ( jQuery( '#gadwp-map' + slug ).length > 0 && jQuery.isArray( reports.locations ) ) {
					reports.drawmaplocations( reports.locations );
				}
				if ( jQuery( '#gadwp-locations' + slug ).length > 0 && jQuery.isArray( reports.locations ) ) {
					reports.drawlocations( reports.locations );
				}
				if ( jQuery( '#gadwp-socialnetworks' + slug ).length > 0 && jQuery.isArray( reports.socialnetworks ) ) {
					reports.drawsocialnetworks( reports.socialnetworks );
				}
				if ( jQuery( '#gadwp-trafficorganic' + slug ).length > 0 && jQuery.isArray( reports.trafficorganic ) ) {
					reports.drawtrafficorganic( reports.trafficorganic );
				}
				if ( jQuery( '#gadwp-traffictype' + slug ).length > 0 && jQuery.isArray( reports.traffictype ) ) {
					reports.drawtraffictype( reports.traffictype );
				}
				if ( jQuery( '#gadwp-trafficmediums' + slug ).length > 0 && jQuery.isArray( reports.trafficmediums ) ) {
					reports.drawtrafficmediums( reports.trafficmediums );
				}
				if ( jQuery( '#gadwp-trafficchannels' + slug ).length > 0 && jQuery.isArray( reports.trafficchannels ) ) {
					reports.drawtrafficchannels( reports.trafficchannels );
				}
				if ( jQuery( '#gadwp-prs' + slug ).length > 0 && jQuery.isArray( reports.prs ) ) {
					reports.drawprs( reports.prs );
				}
			},

			init : function () {

				if ( !jQuery( "#gadwp-reports" + slug ).length ) {
					return;
				}

				if ( jQuery( "#gadwp-reports" + slug ).html().length ) { // only when report is empty
					return;
				}

				try {
					NProgress.configure( {
						parent : "#gadwp-progressbar" + slug,
						showSpinner : false
					} );
					NProgress.start();
				} catch ( e ) {
					reports.alertMessage( gadwp_item_data.i18n[ 0 ] );
				}

				reports.render( jQuery( '#gadwp-sel-view' + slug ).val(), jQuery( '#gadwp-sel-period' + slug ).val(), jQuery( '#gadwp-sel-report' + slug ).val() );

				jQuery( window ).resize( function () {
					reports.refresh();
				} );
			}
		}

		template.init();

		reports.init();

		jQuery( '#gadwp-sel-view' + slug ).change( function () {
			jQuery( '#gadwp-reports' + slug ).html( '' );
			reports.init();
		} );

		jQuery( '#gadwp-sel-period' + slug ).change( function () {
			jQuery( '#gadwp-reports' + slug ).html( '' );
			reports.init();
		} );

		jQuery( '#gadwp-sel-report' + slug ).change( function () {
			jQuery( '#gadwp-reports' + slug ).html( '' );
			reports.init();
		} );

		if ( gadwp_item_data.scope == 'admin-widgets' ) {
			return;
		} else {
			return this.dialog( {
				width : 'auto',
				maxWidth : 510,
				height : 'auto',
				modal : true,
				fluid : true,
				dialogClass : 'gadwp wp-dialog',
				resizable : false,
				title : reports.getTitle( gadwp_item_data.scope ),
				position : {
					my : "top",
					at : "top+100",
					of : window
				}
			} );
		}
	}
} );

function GADWPLoad () {
	if ( gadwp_item_data.scope == 'admin-widgets' ) {
		jQuery( '#gadwp-window-1' ).gadwpItemReport( 1 );
	} else {
		jQuery( gadwp_item_data.getSelector( gadwp_item_data.scope ) ).click( function () {
			if ( !jQuery( "#gadwp-window-" + gadwp_item_data.getID( this ) ).length > 0 ) {
				jQuery( "body" ).append( '<div id="gadwp-window-' + gadwp_item_data.getID( this ) + '"></div>' );
			}
			jQuery( '#gadwp-window-' + gadwp_item_data.getID( this ) ).gadwpItemReport( gadwp_item_data.getID( this ) );
		} );
	}

	// on window resize
	jQuery( window ).resize( function () {
		gadwp_item_data.responsiveDialog();
	} );

	// dialog width larger than viewport
	jQuery( document ).on( "dialogopen", ".ui-dialog", function ( event, ui ) {
		gadwp_item_data.responsiveDialog();
	} );
}
