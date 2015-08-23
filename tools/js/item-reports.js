"use strict";

google.load( "visualization", "1", {
	packages : [ "corechart", "table", "orgchart", "geochart" ],
	'language' : gadwp_item_data.language
} );

//Get the numeric ID
gadwp_item_data.getID	= function ( item ) {
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

//Get the selector
gadwp_item_data.getSelector	= function ( scope ) {
	if ( scope == 'admin-item' ) {
		return 'a[id^="gadwp-"]';
	} else {
		return 'li[id^="wp-admin-bar-gadwp"]';
	}	
}

gadwp_item_data.responsiveDialog = function () {
	var visible = jQuery( ".ui-dialog:visible" );
	// on each visible dialog
	visible.each( function () {
		var $this = jQuery( this );
		var dialog = $this.find( ".ui-dialog-content" ).data( "ui-dialog" );
		// on each fluid dialog
		if ( dialog.options.fluid ) {
			var wWidth = jQuery( window ).width();
			// window width vs dialog width
			if ( wWidth < ( parseInt( dialog.options.maxWidth ) + 50 ) ) {
				// don't fill the entire screen
				$this.css( "max-width", "90%" );
			} else {
				// maxWidth bug fix
				$this.css( "max-width", dialog.options.maxWidth + "px" );
			}
			// change dialog position
			dialog.option( "position", dialog.options.position );
		}
	} );
}

jQuery( document ).ready( function () {
	jQuery( gadwp_item_data.getSelector( gadwp_item_data.scope ) ).click( function ( e ) {
		if ( !jQuery( "#gadwp-window-" + gadwp_item_data.getID( this ) ).length > 0 ) {
			jQuery( "body" ).append( '<div id="gadwp-window-' + gadwp_item_data.getID( this ) + '"></div>' );
		}
		jQuery( '#gadwp-window-' + gadwp_item_data.getID( this ) ).gadwpItemReport( gadwp_item_data.getID( this ) );
	} );

	// on window resize
	jQuery( window ).resize( function () {
		gadwp_item_data.responsiveDialog();
	} );

	// dialog width larger than viewport
	jQuery( document ).on( "dialogopen", ".ui-dialog", function ( event, ui ) {
		gadwp_item_data.responsiveDialog();
	} );
} );

jQuery.fn.extend( {
	gadwpItemReport : function ( item_id ) {
		var slug = "-" + item_id;
		var dialog_title;

		var tools = {
			set_cookie : function ( name, value ) {
				var date_item = new Date();
				date_item.setTime( date_item.getTime() + ( 24 * 60 * 60 * 1000 * 7 ) );
				var expires = "expires=" + date_item.toUTCString();
				document.cookie = "gadwp_ir_" + name + "=" + value + "; " + expires + "; path=/";
			},
			get_cookie : function ( name ) {
				var name = "gadwp_ir_" + name + "=";
				var cookies_array = document.cookie.split( ';' );
				for ( var i = 0; i < cookies_array.length; i++ ) {
					var cookie = cookies_array[ i ];
					while ( cookie.charAt( 0 ) == ' ' )
						cookie = cookie.substring( 1 );
					if ( cookie.indexOf( name ) == 0 )
						return cookie.substring( name.length, cookie.length );
				}
				return false;
			},
			escape : function ( str ) {
				var div = document.createElement( 'div' );
				div.appendChild( document.createTextNode( str ) );
				return div.innerHTML;
			},
		}

		var template = {

			data : '<div id="gadwp-container' + slug + '"><select id="gadwp-sel-period' + slug + '"></select> <select id="gadwp-sel-report' + slug + '"></select><div id="gadwp-progressbar' + slug + '"></div><div id="gadwp-status' + slug + '"></div><div id="gadwp-reports' + slug + '"></div><div style="text-align:right;width:100%;font-size:0.8em;clear:both;margin-right:5px;margin-top:10px;">' + gadwp_item_data.i18n[ 14 ] + ' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_report&utm_medium=link&utm_content=back_report&utm_campaign=gadwp" rel="nofollow" style="text-decoration:none;font-size:1em;">GADWP</a>&nbsp;</div></div>',

			addOptions : function ( id, list ) {
				var default_metric, default_dimension;
				var output = [];

				jQuery.each( list, function ( key, value ) {
					if ( !tools.get_cookie( 'default_metric' ) || !tools.get_cookie( 'default_dimension' ) ) {
						default_metric = 'uniquePageviews';
						default_dimension = '30daysAgo';
					} else {
						default_metric = tools.get_cookie( 'default_metric' );
						default_dimension = tools.get_cookie( 'default_dimension' );
					}

					if ( key == default_metric || key == default_dimension ) {
						output.push( '<option value="' + key + '" selected="selected">' + value + '</option>' );
					} else {
						output.push( '<option value="' + key + '">' + value + '</option>' );
					}
				} );
				jQuery( id ).html( output.join( '' ) );
			},

			init : function () {
				if ( jQuery( '#gadwp-window' + slug ).html().length ) { // add main template once
					return;
				}

				jQuery( '#gadwp-window' + slug ).append( this.data );

				this.addOptions( '#gadwp-sel-period' + slug, gadwp_item_data.dateList );
				this.addOptions( '#gadwp-sel-report' + slug, gadwp_item_data.reportList );

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
				var data = google.visualization.arrayToDataTable( gadwp_prs );
				var options = {
					page : 'enable',
					pageSize : 10,
					width : '100%',
					allowHtml : true
				};

				var chart = new google.visualization.Table( document.getElementById( 'gadwp-prs' + slug ) );
				chart.draw( data, options );
			},

			drawtrafficchannels : function ( gadwp_trafficchannels ) {
				var data = google.visualization.arrayToDataTable( gadwp_trafficchannels );
				var options = {
					allowCollapse : true,
					allowHtml : true,
					height : '100%'
				};

				var chart = new google.visualization.OrgChart( document.getElementById( 'gadwp-trafficchannels' + slug ) );
				chart.draw( data, options );
			},

			drawtrafficmediums : function ( gadwp_trafficmediums ) {
				var data = google.visualization.arrayToDataTable( gadwp_trafficmediums );
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
				chart.draw( data, options );
			},

			drawtraffictype : function ( gadwp_traffictype ) {
				var data = google.visualization.arrayToDataTable( gadwp_traffictype );
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
				chart.draw( data, options );
			},

			drawsocialnetworks : function ( gadwp_socialnetworks ) {
				var data = google.visualization.arrayToDataTable( gadwp_socialnetworks );
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
				chart.draw( data, options );
			},

			drawtrafficorganic : function ( gadwp_trafficorganic ) {
				var data = google.visualization.arrayToDataTable( gadwp_trafficorganic );
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
				chart.draw( data, options );
			},

			drawlocations : function ( gadwp_locations ) {
				var data = google.visualization.arrayToDataTable( gadwp_locations );
				var options = {
					page : 'enable',
					pageSize : 10,
					width : '100%'
				};

				var chart = new google.visualization.Table( document.getElementById( 'gadwp-locations' + slug ) );
				chart.draw( data, options );
			},

			drawmaplocations : function ( gadwp_locations ) {

				var data = google.visualization.arrayToDataTable( gadwp_locations );

				var options = {
					chartArea : {
						width : '99%',
						height : '90%'
					},
					colors : [ gadwp_item_data.colorVariations[ 5 ], gadwp_item_data.colorVariations[ 4 ] ],
				}

				if ( gadwp_item_data.region ) {
					options.region = gadwp_item_data.region;
					options.displayMode = 'markers';
					options.datalessRegionColor = 'EFEFEF';
				}

				var chart = new google.visualization.GeoChart( document.getElementById( 'gadwp-map' + slug ) );
				chart.draw( data, options );
			},

			drawmainchart : function ( gadwp_mainchart, format ) {

				var data = google.visualization.arrayToDataTable( gadwp_mainchart );

				if ( format ) {
					var formatter = new google.visualization.NumberFormat( {
						suffix : '%',
						fractionDigits : 2
					} );

					formatter.format( data, 1 );
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
				chart.draw( data, options );
			},

			drawbottomstats : function ( gadwp_bottomstats ) {
				jQuery( "#gdsessions" + slug ).text( gadwp_bottomstats[ 0 ] );
				jQuery( "#gdusers" + slug ).text( gadwp_bottomstats[ 1 ] );
				jQuery( "#gdpageviews" + slug ).text( gadwp_bottomstats[ 2 ] );
				jQuery( "#gdbouncerate" + slug ).text( gadwp_bottomstats[ 3 ] + "%" );
				jQuery( "#gdorganicsearch" + slug ).text( gadwp_bottomstats[ 4 ] );
				jQuery( "#gdpagespervisit" + slug ).text( gadwp_bottomstats[ 5 ] );
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

			render : function ( period, query ) {
				var from, to;

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

				if ( gadwp_item_data.scope == 'admin-item' ) {
					var data = {
						action : 'gadwp_backend_item_reports',
						gadwp_security_backend_item_reports : gadwp_item_data.security,
						from : from,
						to : to,
						filter : item_id,
					}
				} else {
					var data = {
						action : 'gadwp_frontend_item_reports',
						gadwp_security_frontend_item_reports : gadwp_item_data.security,
						from : from,
						to : to,
						filter : gadwp_item_data.filter,
					}
				}

				if ( jQuery.inArray( query, [ 'referrers', 'contentpages', 'searches' ] ) > -1 ) {

					jQuery( '#gadwp-reports' + slug ).html( '<div id="gadwp-trafficchannels' + slug + '"></div>' );
					jQuery( '#gadwp-reports' + slug ).append( '<div id="gadwp-prs' + slug + '"></div>' );
					jQuery( '#gadwp-reports' + slug ).hide();

					data.query = 'trafficchannels,' + query;

					jQuery.post( gadwp_item_data.ajaxurl, data, function ( response ) {
						if ( jQuery.isArray( response ) ) {
							if ( !jQuery.isNumeric( response[ 0 ] ) ) {
								if ( jQuery.isArray( response[ 0 ] ) ) {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.trafficchannels = response[ 0 ];
									google.setOnLoadCallback( reports.drawtrafficchannels( reports.trafficchannels ) );
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
									google.setOnLoadCallback( reports.drawprs( reports.prs ) );
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

					jQuery( '#gadwp-reports' + slug ).html( '<div id="gadwp-trafficchannels' + slug + '"></div>' );
					jQuery( '#gadwp-reports' + slug ).append( '<div class="gadwp-floatwraper"><div id="gadwp-trafficmediums' + slug + '"></div><div id="gadwp-traffictype' + slug + '"></div></div>' );
					jQuery( '#gadwp-reports' + slug ).append( '<div class="gadwp-floatwraper"><div id="gadwp-trafficorganic' + slug + '"></div><div id="gadwp-socialnetworks' + slug + '"></div></div>' );
					jQuery( '#gadwp-reports' + slug ).hide();

					data.query = 'trafficchannels,medium,visitorType,source,socialNetwork';

					jQuery.post( gadwp_item_data.ajaxurl, data, function ( response ) {
						if ( jQuery.isArray( response ) ) {
							if ( !jQuery.isNumeric( response[ 0 ] ) ) {
								if ( jQuery.isArray( response[ 0 ] ) ) {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.trafficchannels = response[ 0 ];
									google.setOnLoadCallback( reports.drawtrafficchannels( reports.trafficchannels ) );
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
									google.setOnLoadCallback( reports.drawtrafficmediums( reports.trafficmediums ) );
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
									google.setOnLoadCallback( reports.drawtraffictype( reports.traffictype ) );
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
									google.setOnLoadCallback( reports.drawtrafficorganic( reports.trafficorganic ) );
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
									google.setOnLoadCallback( reports.drawsocialnetworks( reports.socialnetworks ) );
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

					jQuery( '#gadwp-reports' + slug ).html( '<div id="gadwp-map' + slug + '"></div>' )
					jQuery( '#gadwp-reports' + slug ).append( '<div id="gadwp-locations' + slug + '"></div>' );
					jQuery( '#gadwp-reports' + slug ).hide();

					data.query = query;

					jQuery.post( gadwp_item_data.ajaxurl, data, function ( response ) {
						if ( jQuery.isArray( response ) ) {
							if ( !jQuery.isNumeric( response[ 0 ] ) ) {
								if ( jQuery.isArray( response[ 0 ] ) ) {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.locations = response[ 0 ];
									google.setOnLoadCallback( reports.drawmaplocations( reports.locations ) );
									google.setOnLoadCallback( reports.drawlocations( reports.locations ) );
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

					jQuery( '#gadwp-reports' + slug ).html( '<div id="gadwp-mainchart' + slug + '"></div>' )
					jQuery( '#gadwp-reports' + slug ).append( '<div id="gadwp-bottomstats' + slug + '" class="gadwp-wrapper"><div class="inside"><div class="small-box"><h3>' + gadwp_item_data.i18n[ 5 ] + '</h3><p id="gdsessions' + slug + '">&nbsp;</p></div><div class="small-box"><h3>' + gadwp_item_data.i18n[ 6 ] + '</h3><p id="gdusers' + slug + '">&nbsp;</p></div><div class="small-box"><h3>' + gadwp_item_data.i18n[ 7 ] + '</h3><p id="gdpageviews' + slug + '">&nbsp;</p></div><div class="small-box"><h3>' + gadwp_item_data.i18n[ 8 ] + '</h3><p id="gdbouncerate' + slug + '">&nbsp;</p></div><div class="small-box"><h3>' + gadwp_item_data.i18n[ 9 ] + '</h3><p id="gdorganicsearch' + slug + '">&nbsp;</p></div><div class="small-box"><h3>' + gadwp_item_data.i18n[ 10 ] + '</h3><p id="gdpagespervisit' + slug + '">&nbsp;</p></div></div></div>' );
					jQuery( '#gadwp-reports' + slug ).hide();

					data.query = query + ',bottomstats';

					jQuery.post( gadwp_item_data.ajaxurl, data, function ( response ) {
						if ( jQuery.isArray( response ) ) {
							if ( !jQuery.isNumeric( response[ 0 ] ) ) {
								if ( jQuery.isArray( response[ 0 ] ) ) {
									jQuery( '#gadwp-reports' + slug ).show();
									reports.mainchart = response[ 0 ];
									if ( query == 'visitBounceRate' ) {
										google.setOnLoadCallback( reports.drawmainchart( reports.mainchart, true ) );
									} else {
										google.setOnLoadCallback( reports.drawmainchart( reports.mainchart, false ) );
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
									google.setOnLoadCallback( reports.drawbottomstats( reports.bottomstats ) );
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

			},

			refresh : function () {
				if ( jQuery( '#gadwp-bottomstats' + slug ).length > 0 ) {
					this.drawbottomstats( this.bottomstats );
				}
				if ( jQuery( '#gadwp-mainchart' + slug ).length > 0 && jQuery.isArray( this.mainchart ) ) {
					this.drawmainchart( this.mainchart );
				}
				if ( jQuery( '#gadwp-map' + slug ).length > 0 && jQuery.isArray( this.locations ) ) {
					this.drawmaplocations( this.locations );
				}
				if ( jQuery( '#gadwp-locations' + slug ).length > 0 && jQuery.isArray( this.locations ) ) {
					this.drawlocations( this.locations );
				}
				if ( jQuery( '#gadwp-socialnetworks' + slug ).length > 0 && jQuery.isArray( this.socialnetworks ) ) {
					this.drawsocialnetworks( this.socialnetworks );
				}
				if ( jQuery( '#gadwp-trafficorganic' + slug ).length > 0 && jQuery.isArray( this.trafficorganic ) ) {
					this.drawtrafficorganic( this.trafficorganic );
				}
				if ( jQuery( '#gadwp-traffictype' + slug ).length > 0 && jQuery.isArray( this.traffictype ) ) {
					this.drawtraffictype( this.traffictype );
				}
				if ( jQuery( '#gadwp-trafficmediums' + slug ).length > 0 && jQuery.isArray( this.trafficmediums ) ) {
					this.drawtrafficmediums( this.trafficmediums );
				}
				if ( jQuery( '#gadwp-trafficchannels' + slug ).length > 0 && jQuery.isArray( this.trafficchannels ) ) {
					this.drawtrafficchannels( this.trafficchannels );
				}
				if ( jQuery( '#gadwp-prs' + slug ).length > 0 && jQuery.isArray( this.prs ) ) {
					this.drawprs( this.prs );
				}
			},

			init : function () {

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
					this.alertMessage( gadwp_item_data.i18n[ 0 ] );
				}

				this.render( jQuery( '#gadwp-sel-period' + slug ).val(), jQuery( '#gadwp-sel-report' + slug ).val() );

				jQuery( window ).resize( function () {
					reports.refresh();
				} );
			}
		}

		template.init();

		reports.init();

		jQuery( '#gadwp-sel-period' + slug ).change( function () {
			jQuery( '#gadwp-reports' + slug ).html( '' );
			reports.init();
		} );

		jQuery( '#gadwp-sel-report' + slug ).change( function () {
			jQuery( '#gadwp-reports' + slug ).html( '' );
			reports.init();
		} );

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
} );