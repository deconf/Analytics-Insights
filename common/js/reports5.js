/*-
 * Author: Alin Marcu 
 * Author URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

"use strict";

if ( aiwpItemData.mapsApiKey ) {
	google.charts.load( 'current', {
		'mapsApiKey' : aiwpItemData.mapsApiKey,
		'packages' : [ 'corechart', 'table', 'orgchart', 'geochart', 'controls' ]
	} );
} else {
	google.charts.load( 'current', {
		'packages' : [ 'corechart', 'table', 'orgchart', 'geochart', 'controls' ]
	} );
}

google.charts.setOnLoadCallback( AIWPReportLoad );

// Get the numeric ID
aiwpItemData.getID = function ( item ) {
	if ( aiwpItemData.scope == 'admin-item' ) {
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
aiwpItemData.getSelector = function ( scope ) {
	if ( scope == 'admin-item' ) {
		return 'a[id^="aiwp-"]';
	} else {
		return 'li[id^="wp-admin-bar-aiwp"] a';
	}
}

aiwpItemData.responsiveDialog = function () {
	var dialog, wWidth, visible;

	visible = jQuery( ".ui-dialog:visible" );

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
	aiwpItemReport : function ( itemId ) {
		var postData, tools, template, reports, refresh, init, swmetric, slug = "-" + itemId;

		tools = {
			setCookie : function ( name, value ) {
				var expires, dateItem = new Date();

				if ( aiwpItemData.scope == 'admin-widgets' ) {
					name = "aiwp_wg_" + name;
				} else {
					name = "aiwp_ir_" + name;
				}
				dateItem.setTime( dateItem.getTime() + ( 24 * 60 * 60 * 1000 * 365 ) );
				expires = "expires=" + dateItem.toUTCString();
				document.cookie = name + "=" + value + "; " + expires + "; path=/";
			},
			getCookie : function ( name ) {
				var cookie, cookiesArray, div, i = 0;

				if ( aiwpItemData.scope == 'admin-widgets' ) {
					name = "aiwp_wg_" + name + "=";
				} else {
					name = "aiwp_ir_" + name + "=";
				}
				cookiesArray = document.cookie.split( ';' );
				for ( i = 0; i < cookiesArray.length; i++ ) {
					cookie = cookiesArray[ i ];
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

		template = {

			addOptions : function ( id, list ) {
				var defaultMetric, defaultDimension, defaultView, output = [];

				if ( !tools.getCookie( 'default_metric' ) || !tools.getCookie( 'default_dimension' ) || !tools.getCookie( 'default_swmetric' ) ) {
					defaultMetric = 'sessions';
					defaultDimension = '30daysAgo';
					if ( aiwpItemData.scope == 'front-item' || aiwpItemData.scope == 'admin-item' ) {
						swmetric = 'pageviews';
					} else {
						swmetric = 'sessions';
					}
					tools.setCookie( 'default_metric', defaultMetric );
					tools.setCookie( 'default_dimension', defaultDimension );
					tools.setCookie( 'default_swmetric', swmetric );
				} else {
					defaultMetric = tools.getCookie( 'default_metric' );
					defaultDimension = tools.getCookie( 'default_dimension' );
					defaultView = tools.getCookie( 'default_view' );
					swmetric = tools.getCookie( 'default_swmetric' );
				}

				if ( list == false ) {
					if ( aiwpItemData.scope == 'front-item' || aiwpItemData.scope == 'admin-item' ) {
						output = ''; // Remove Sessions metric selection on item reports
					} else {
						output = '<span id="aiwp-swmetric-sessions" title="' + aiwpItemData.i18n[ 5 ] + '" class="dashicons dashicons-clock" style="font-size:22px;padding:4px;"></span>';
					}

					output += '<span id="aiwp-swmetric-users" title="' + aiwpItemData.i18n[ 6 ] + '" class="dashicons dashicons-admin-users" style="font-size:22px;padding:4px;"></span>';
					output += '<span id="aiwp-swmetric-pageviews" title="' + aiwpItemData.i18n[ 7 ] + '" class="dashicons dashicons-admin-page" style="font-size:22px;padding:4px;"></span>';

					jQuery( id ).html( output );

					jQuery( '#aiwp-swmetric-' + swmetric ).css( "color", "#008ec2" );
				} else {
					jQuery.each( list, function ( key, value ) {
						if ( key == defaultMetric || key == defaultDimension || key == defaultView ) {
							output.push( '<option value="' + key + '" selected="selected">' + value + '</option>' );
						} else {
							output.push( '<option value="' + key + '">' + value + '</option>' );
						}
					} );
					jQuery( id ).html( output.join( '' ) );
				}
			},

			init : function () {
				var tpl;

				if ( !jQuery( '#aiwp-window' + slug ).length ) {
					return;
				}

				if ( jQuery( '#aiwp-window' + slug ).html().length ) { // add main template once
					return;
				}

				tpl = '<div id="aiwp-container' + slug + '">';
				if ( aiwpItemData.viewList != false ) {
					tpl += '<select id="aiwp-sel-view' + slug + '"></select>';
				}
				tpl += '<select id="aiwp-sel-period' + slug + '"></select> ';
				tpl += '<select id="aiwp-sel-report' + slug + '"></select>';
				tpl += '<div id="aiwp-sel-metric' + slug + '" style="float:right;display:none;">';
				tpl += '</div>';
				tpl += '<div id="aiwp-progressbar' + slug + '"></div>';
				tpl += '<div id="aiwp-status' + slug + '"></div>';
				tpl += '<div id="aiwp-reports' + slug + '"></div>';
				tpl += '<div style="text-align:right;width:100%;font-size:0.8em;clear:both;margin-right:5px;margin-top:10px;">';
				tpl += aiwpItemData.i18n[ 14 ];
				tpl += ' <a href="https://deconf.com/analytics-insights-for-wordpress/?utm_source=aiwp_report&utm_medium=link&utm_content=back_report&utm_campaign=aiwp" rel="nofollow" style="text-decoration:none;font-size:1em;">Analytics Insights</a>&nbsp;';
				tpl += '</div>';
				tpl += '</div>',

				jQuery( '#aiwp-window' + slug ).append( tpl );
				
				jQuery( "#aiwp-reports" + slug ).css( {
						"background-color" : "#FFFFFF",
						"height" : "auto",
						"margin-top" : "0",
						"padding-top" : "0",
						"padding-bottom" : "0",
						"color" : "#3c434a",
						"text-align" : "inherit"
				} );				

				template.addOptions( '#aiwp-sel-view' + slug, aiwpItemData.viewList );
				template.addOptions( '#aiwp-sel-period' + slug, aiwpItemData.dateList );
				template.addOptions( '#aiwp-sel-report' + slug, aiwpItemData.reportList );
				template.addOptions( '#aiwp-sel-metric' + slug, false );
			}
		}

		reports = {
			oldViewPort : 0,
			orgChartTableChartData : '',
			tableChartData : '',
			orgChartPieChartsData : '',
			geoChartTableChartData : '',
			areaChartBottomStatsData : '',
			realtime : '',
			rtRuns : null,
			i18n : null,

			getTitle : function ( scope ) {
				if ( scope == 'admin-item' ) {
					return jQuery( '#aiwp' + slug ).attr( "title" );
				} else {
					return document.getElementsByTagName( "title" )[ 0 ].innerHTML;
				}
			},

			alertMessage : function ( msg ) {
				jQuery( "#aiwp-status" + slug ).css( {
					"margin-top" : "3px",
					"padding-left" : "5px",
					"height" : "auto",
					"color" : "#000",
					"border-left" : "5px solid red"
				} );
				jQuery( "#aiwp-status" + slug ).html( msg );
			},

			areaChartBottomStats : function ( response ) {
			
				var tpl;
			
				tpl = '<div id="aiwp-areachartbottomstats' + slug + '">';
				tpl += '<div id="aiwp-areachart' + slug + '"></div>';
				tpl += '<div id="aiwp-bottomstats' + slug + '">';
				tpl += '<div class="inside">';
				tpl += '<div class="small-box"><h3>' + aiwpItemData.i18n[ 5 ] + '</h3><p id="gdsessions' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box"><h3>' + aiwpItemData.i18n[ 6 ] + '</h3><p id="gdusers' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box"><h3>' + aiwpItemData.i18n[ 7 ] + '</h3><p id="gdpageviews' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box"><h3>' + aiwpItemData.i18n[ 8 ] + '</h3><p id="gdbouncerate' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box"><h3>' + aiwpItemData.i18n[ 9 ] + '</h3><p id="gdorganicsearch' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box"><h3>' + aiwpItemData.i18n[ 10 ] + '</h3><p id="gdpagespervisit' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box"><h3>' + aiwpItemData.i18n[ 26 ] + '</h3><p id="gdpagetime' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box"><h3>' + aiwpItemData.i18n[ 27 ] + '</h3><p id="gdpageload' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box"><h3>' + aiwpItemData.i18n[ 28 ] + '</h3><p id="gdsessionduration' + slug + '">&nbsp;</p></div>';
				tpl += '</div>';
				tpl += '</div>';
				tpl += '</div>';

				if ( !jQuery( '#aiwp-areachartbottomstats' + slug ).length ) {
					jQuery( '#aiwp-reports' + slug ).html( tpl );
				}

				jQuery( '#aiwp-areachart' + slug ).removeAttr("style");
				
				jQuery( '#aiwp-bottomstats' + slug ).removeAttr("style");
				
				jQuery( '#aiwp-sel-metric' + slug ).hide();				
				
				reports.areaChartBottomStatsData = response;
				
				if ( jQuery.isArray( response ) ) {
					if ( !jQuery.isNumeric( response[ 0 ] ) ) {
						if ( jQuery.isArray( response[ 0 ] ) ) {
							jQuery( '#aiwp-reports' + slug ).show();
							if ( postData.query == 'visitBounceRate,bottomstats' ) {
								reports.drawAreaChart( response[ 0 ], true );
							} else {
								reports.drawAreaChart( response[ 0 ], false );
							}
						} else {
							reports.throwDebug( response[ 0 ] );
						}
					} else {
						jQuery( '#aiwp-reports' + slug ).show();
						reports.throwError( '#aiwp-areachart' + slug, response[ 0 ], "125px" );
					}
					if ( !jQuery.isNumeric( response[ 1 ] ) ) {
						if ( jQuery.isArray( response[ 1 ] ) ) {
							jQuery( '#aiwp-reports' + slug ).show();
							reports.drawBottomStats( response[ 1 ] );
						} else {
							reports.throwDebug( response[ 1 ] );
						}
					} else {
						jQuery( '#aiwp-reports' + slug ).show();
						reports.throwError( '#aiwp-bottomstats' + slug, response[ 1 ], "40px" );
					}
				} else {
					reports.throwDebug( response );
				}
				NProgress.done();

			},

			orgChartPieCharts : function ( response ) {
				
				var tpl;
				var i = 0;
				
				tpl = '<div id="aiwp-orgchartpiecharts' + slug + '">';
				tpl += '<div id="aiwp-orgchart' + slug + '"></div>';
				tpl += '<div class="aiwp-floatwraper">';
				tpl += '<div id="aiwp-piechart-1' + slug + '" class="halfsize floatleft"></div>';
				tpl += '<div id="aiwp-piechart-2' + slug + '" class="halfsize floatright"></div>';
				tpl += '</div>';
				tpl += '<div class="aiwp-floatwraper">';
				tpl += '<div id="aiwp-piechart-3' + slug + '" class="halfsize floatleft"></div>';
				tpl += '<div id="aiwp-piechart-4' + slug + '" class="halfsize floatright"></div>';
				tpl += '</div>';
				tpl += '</div>';
				
				jQuery( '#aiwp-piechart-1' + slug ).removeAttr("style");
				jQuery( '#aiwp-piechart-2' + slug ).removeAttr("style");
				jQuery( '#aiwp-piechart-3' + slug ).removeAttr("style");
				jQuery( '#aiwp-piechart-4' + slug ).removeAttr("style");

				if ( !jQuery( '#aiwp-orgchartpiecharts' + slug ).length ) {
					jQuery( '#aiwp-reports' + slug ).html( tpl );
				}
				
				jQuery( '#aiwp-sel-metric' + slug ).show();
								
				reports.orgChartPieChartsData = response;
				if ( jQuery.isArray( response ) ) {
					if ( !jQuery.isNumeric( response[ 0 ] ) ) {
						if ( jQuery.isArray( response[ 0 ] ) ) {
							jQuery( '#aiwp-reports' + slug ).show();
							reports.drawOrgChart( response[ 0 ] );
						} else {
							reports.throwDebug( response[ 0 ] );
						}
					} else {
						jQuery( '#aiwp-reports' + slug ).show();
						reports.throwError( '#aiwp-orgchart' + slug, response[ 0 ], "125px" );
					}

					for ( i = 1; i < response.length; i++ ) {
						if ( !jQuery.isNumeric( response[ i ] ) ) {
							if ( jQuery.isArray( response[ i ] ) ) {
								jQuery( '#aiwp-reports' + slug ).show();
								reports.drawPieChart( 'piechart-' + i, response[ i ], reports.i18n[ i ] );
							} else {
								reports.throwDebug( response[ i ] );
							}
						} else {
							jQuery( '#aiwp-reports' + slug ).show();
							reports.throwError( '#aiwp-piechart-' + i + slug, response[ i ], "80px" );
						}
					}
				} else {
					reports.throwDebug( response );
				}
				NProgress.done();
			},

			geoChartTableChart : function ( response ) {
				
				var tpl;
				
				tpl = '<div id="aiwp-geocharttablechart' + slug + '">';
				tpl += '<div id="aiwp-geochart' + slug + '"></div>';
				tpl += '<div id="aiwp-dashboard' + slug + '">';
				tpl += '<div id="aiwp-control' + slug + '"></div>';
				tpl += '<div id="aiwp-tablechart' + slug + '"></div>';
				tpl += '</div>';
				tpl += '</div>';
			
				if ( !jQuery( '#aiwp-geocharttablechart' + slug ).length ) {
					jQuery( '#aiwp-reports' + slug ).html( tpl );
				}
				
				jQuery( '#aiwp-geochart' + slug ).removeAttr("style");
				
				jQuery( '#aiwp-tablechart' + slug ).removeAttr("style");
								
				jQuery( '#aiwp-sel-metric' + slug ).show();	
			
				reports.geoChartTableChartData = response;
				if ( jQuery.isArray( response ) ) {
					if ( !jQuery.isNumeric( response[ 0 ] ) ) {
						if ( jQuery.isArray( response[ 0 ] ) ) {
							jQuery( '#aiwp-reports' + slug ).show();
							reports.drawGeoChart( response[ 0 ] );
							reports.drawTableChart( response[ 0 ] );
						} else {
							reports.throwDebug( response[ 0 ] );
						}
					} else {
						jQuery( '#aiwp-reports' + slug ).show();
						reports.throwError( '#aiwp-geochart' + slug, response[ 0 ], "125px" );
						reports.throwError( '#aiwp-tablechart' + slug, response[ 0 ], "125px" );
					}
				} else {
					reports.throwDebug( response );
				}
				NProgress.done();
			},

			orgChartTableChart : function ( response ) {
				
				var tpl;
				
				tpl = '<div id="aiwp-orgcharttablechart' + slug + '">';
				tpl += '<div id="aiwp-orgchart' + slug + '"></div>';
				tpl += '<div id="aiwp-dashboard' + slug + '">';
				tpl += '<div id="aiwp-control' + slug + '"></div>';
				tpl += '<div id="aiwp-tablechart' + slug + '"></div>';
				tpl += '</div>';
				tpl += '</div>';

				if ( !jQuery( '#aiwp-orgcharttablechart' + slug ).length ) {
					jQuery( '#aiwp-reports' + slug ).html( tpl );
				}
				
				jQuery( '#aiwp-orgchart' + slug ).removeAttr("style");
				
				jQuery( '#aiwp-tablechart' + slug ).removeAttr("style");
				
				jQuery( '#aiwp-sel-metric' + slug ).show();		
			
				reports.orgChartTableChartData = response
				if ( jQuery.isArray( response ) ) {
					if ( !jQuery.isNumeric( response[ 0 ] ) ) {
						if ( jQuery.isArray( response[ 0 ] ) ) {
							jQuery( '#aiwp-reports' + slug ).show();
							reports.drawOrgChart( response[ 0 ] );
						} else {
							reports.throwDebug( response[ 0 ] );
						}
					} else {
						jQuery( '#aiwp-reports' + slug ).show();
						reports.throwError( '#aiwp-orgchart' + slug, response[ 0 ], "125px" );
					}

					if ( !jQuery.isNumeric( response[ 1 ] ) ) {
						if ( jQuery.isArray( response[ 1 ] ) ) {
							reports.drawTableChart( response[ 1 ] );
						} else {
							reports.throwDebug( response[ 1 ] );
						}
					} else {
						reports.throwError( '#aiwp-tablechart' + slug, response[ 1 ], "125px" );
					}
				} else {
					reports.throwDebug( response );
				}
				NProgress.done();
			},

			tableChart : function ( response ) {

				var tpl;
			
				tpl = '<div id="aiwp-404tablechart' + slug + '"><br>';
				tpl += '<div id="aiwp-dashboard' + slug + '">';
				tpl += '<div id="aiwp-control' + slug + '"></div>';
				tpl += '<div id="aiwp-tablechart' + slug + '"></div>';
				tpl += '</div>';

				if ( !jQuery( '#aiwp-404tablechart' + slug ).length ) {
					jQuery( '#aiwp-reports' + slug ).html( tpl );
				}
				
				jQuery( '#aiwp-404tablechart' + slug ).removeAttr("style");
				
				jQuery( '#aiwp-tablechart' + slug ).removeAttr("style");
				
				jQuery( '#aiwp-sel-metric' + slug ).show();			
			
				reports.tableChartData = response
				if ( jQuery.isArray( response ) ) {
					if ( !jQuery.isNumeric( response[ 0 ] ) ) {
						if ( jQuery.isArray( response[ 0 ] ) ) {
							jQuery( '#aiwp-reports' + slug ).show();
							reports.drawTableChart( response[ 0 ] );
						} else {
							reports.throwDebug( response[ 0 ] );
						}
					} else {
						jQuery( '#aiwp-reports' + slug ).show();
						reports.throwError( '#aiwp-tablechart' + slug, response[ 0 ], "125px" );
					}
				} else {
					reports.throwDebug( response );
				}
				NProgress.done();
			},

			drawTableChart : function ( data ) {
				var chartData, options, chart, dashboard, control, wrapper;

				chartData = google.visualization.arrayToDataTable( data );
				options = {
					page : 'enable',
					pageSize : 10,
					width : '100%',
					allowHtml : true,
					sortColumn : 1,
					sortAscending : false					
				};

				dashboard = new google.visualization.Dashboard(document.getElementById( 'aiwp-dashboard' + slug ));
				
			    control = new google.visualization.ControlWrapper({
			        controlType: 'StringFilter',
			        containerId: 'aiwp-control' + slug,
			        options: {
			            filterColumnIndex: 0, 
			            matchType : 'any',
			            ui : { label : '', cssClass : 'aiwp-dashboard-control' },
			        }
			    });
			    
			    google.visualization.events.addListener(control, 'ready', function () {
			        jQuery('.aiwp-dashboard-control input').prop('placeholder', aiwpItemData.i18n[ 30 ]);
			    });
				
			    wrapper = new google.visualization.ChartWrapper({
			    	  'chartType' : 'Table',
			    	  'containerId' : 'aiwp-tablechart' + slug,
			    	  'options' : options,
		    	});
			    
			    dashboard.bind(control, wrapper);
			    
			    dashboard.draw( chartData );
			    
			    // outputs selection
			    google.visualization.events.addListener(wrapper, 'select', function() {
			    	console.log(wrapper.getDataTable().getValue(wrapper.getChart().getSelection()[0].row, 0));
			    });				

			},

			drawOrgChart : function ( data ) {
				var chartData, options, chart;

				chartData = google.visualization.arrayToDataTable( data );
				options = {
					allowCollapse : true,
					allowHtml : true,
					height : '100%'
				};
				chart = new google.visualization.OrgChart( document.getElementById( 'aiwp-orgchart' + slug ) );

				chart.draw( chartData, options );
			},

			drawPieChart : function ( id, data, title ) {
				var chartData, options, chart;

				chartData = google.visualization.arrayToDataTable( data );
				options = {
					is3D : false,
					tooltipText : 'percentage',
					legend : 'none',
					chartArea : {
						width : '99%',
						height : '80%'
					},
					title : title,
					pieSliceText : 'value',
					colors : aiwpItemData.colorVariations
				};
				chart = new google.visualization.PieChart( document.getElementById( 'aiwp-' + id + slug ) );

				chart.draw( chartData, options );
			},

			drawGeoChart : function ( data ) {
				var chartData, options, chart;

				chartData = google.visualization.arrayToDataTable( data );
				options = {
					chartArea : {
						width : '99%',
						height : '90%'
					},
					colors : [ aiwpItemData.colorVariations[ 5 ], aiwpItemData.colorVariations[ 4 ] ]
				}
				if ( aiwpItemData.region ) {
					options.region = aiwpItemData.region;
					options.displayMode = 'markers';
					options.datalessRegionColor = 'EFEFEF';
				}
				chart = new google.visualization.GeoChart( document.getElementById( 'aiwp-geochart' + slug ) );

				chart.draw( chartData, options );
			},

			drawAreaChart : function ( data, format ) {
				var chartData, options, chart, formatter;

				jQuery( '#aiwp-sel-metric' + slug ).hide();	
				
				chartData = google.visualization.arrayToDataTable( data );

				if ( format ) {
					formatter = new google.visualization.NumberFormat( {
						suffix : '%',
						fractionDigits : 2
					} );

					formatter.format( chartData, 1 );
				}

				options = {
					legend : {
						position : 'none'
					},
					pointSize : 3,
					colors : [ aiwpItemData.colorVariations[ 0 ], aiwpItemData.colorVariations[ 4 ] ],
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
				chart = new google.visualization.AreaChart( document.getElementById( 'aiwp-areachart' + slug ) );

				chart.draw( chartData, options );
			},

			drawBottomStats : function ( data ) {
				jQuery( "#gdsessions" + slug ).html( data[ 0 ] );
				jQuery( "#gdusers" + slug ).html( data[ 1 ] );
				jQuery( "#gdpageviews" + slug ).html( data[ 2 ] );
				jQuery( "#gdbouncerate" + slug ).html( data[ 3 ] );
				jQuery( "#gdorganicsearch" + slug ).html( data[ 4 ] );
				jQuery( "#gdpagespervisit" + slug ).html( data[ 5 ] );
				jQuery( "#gdpagetime" + slug ).html( data[ 6 ] );
				jQuery( "#gdpageload" + slug ).html( data[ 7 ] );
				jQuery( "#gdsessionduration" + slug ).html( data[ 8 ] );
			},

			rtOnlyUniqueValues : function ( value, index, self ) {
				return self.indexOf( value ) === index;
			},

			rtCountSessions : function ( rtData, searchValue, index ) {
				var count = 0, i = 0;

				for ( i = 0; i < rtData[ "rows" ].length; i++ ) {
					if ( jQuery.inArray( searchValue, rtData[ "rows" ][ i ] ) > -1 ) {
						count += parseInt( rtData[ "rows" ][ i ][ index ] );
					}
				}
				return count;
			},

			rtGenerateTooltip : function ( rtData ) {
				var count = 0, table = "", i = 0;

				for ( i = 0; i < rtData.length; i++ ) {
					count += parseInt( rtData[ i ].count );
					table += "<tr><td class='aiwp-pgdetailsl'>" + rtData[ i ].value + "</td><td class='aiwp-pgdetailsr'>" + rtData[ i ].count + "</td></tr>";
				}
				;
				if ( count ) {
					return ( "<table>" + table + "</table>" );
				} else {
					return ( "" );
				}
			},

			rtPageDetails : function ( rtData, searchValue ) {
				var sant, pageTitle, pgStatsTable, i = 0, j = 0, sum = 0, newsum = 0, countrfr = 0, countkwd = 0, countdrt = 0, countscl = 0, countcpg = 0, tablerfr = "", tablekwd = "", tablescl = "", tablecpg = "", tabledrt = "";

				rtData = rtData[ "rows" ];

				for ( i = 0; i < rtData.length; i++ ) {

					if ( rtData[ i ][ 0 ] == searchValue ) {
						pageTitle = rtData[ i ][ 5 ];

						switch ( rtData[ i ][ 3 ] ) {

							case "REFERRAL":
								countrfr += parseInt( rtData[ i ][ 6 ] );
								tablerfr += "<tr><td class='aiwp-pgdetailsl'>" + rtData[ i ][ 1 ] + "</td><td class='aiwp-pgdetailsr'>" + rtData[ i ][ 6 ] + "</td></tr>";
								break;
							case "ORGANIC":
								countkwd += parseInt( rtData[ i ][ 6 ] );
								tablekwd += "<tr><td class='aiwp-pgdetailsl'>" + rtData[ i ][ 2 ] + "</td><td class='aiwp-pgdetailsr'>" + rtData[ i ][ 6 ] + "</td></tr>";
								break;
							case "SOCIAL":
								countscl += parseInt( rtData[ i ][ 6 ] );
								tablescl += "<tr><td class='aiwp-pgdetailsl'>" + rtData[ i ][ 1 ] + "</td><td class='aiwp-pgdetailsr'>" + rtData[ i ][ 6 ] + "</td></tr>";
								break;
							case "CUSTOM":
								countcpg += parseInt( rtData[ i ][ 6 ] );
								tablecpg += "<tr><td class='aiwp-pgdetailsl'>" + rtData[ i ][ 1 ] + "</td><td class='aiwp-pgdetailsr'>" + rtData[ i ][ 6 ] + "</td></tr>";
								break;
							case "DIRECT":
								countdrt += parseInt( rtData[ i ][ 6 ] );
								break;
						}
					}
				}

				if ( countrfr ) {
					tablerfr = "<table><tr><td>" + reports.i18n[ 0 ] + "(" + countrfr + ")</td></tr>" + tablerfr + "</table><br />";
				}
				if ( countkwd ) {
					tablekwd = "<table><tr><td>" + reports.i18n[ 1 ] + "(" + countkwd + ")</td></tr>" + tablekwd + "</table><br />";
				}
				if ( countscl ) {
					tablescl = "<table><tr><td>" + reports.i18n[ 2 ] + "(" + countscl + ")</td></tr>" + tablescl + "</table><br />";
				}
				if ( countcpg ) {
					tablecpg = "<table><tr><td>" + reports.i18n[ 3 ] + "(" + countcpg + ")</td></tr>" + tablecpg + "</table><br />";
				}
				if ( countdrt ) {
					tabledrt = "<table><tr><td>" + reports.i18n[ 4 ] + "(" + countdrt + ")</td></tr></table><br />";
				}
				return ( "<p><center><strong>" + pageTitle + "</strong></center></p>" + tablerfr + tablekwd + tablescl + tablecpg + tabledrt );
			},

			rtRefresh : function () {
				if ( reports.render.focusFlag ) {
					postData.from = false;
					postData.to = false;
					postData.query = 'realtime';
					jQuery.post( aiwpItemData.ajaxurl, postData, function ( response ) {
						if ( jQuery.isArray( response ) ) {
							jQuery( '#aiwp-reports' + slug ).show();
							reports.realtime = response[ 0 ];
							if ( aiwpItemData.reportingType == '1' ){
								reports.drawRealtimeGA4( reports.realtime );
							} else {
								reports.drawRealtime( reports.realtime );
							}
						} else {
							reports.throwDebug( response );
						}

						NProgress.done();

					} );
				}
			},

			drawRealtime : function ( rtData ) {
	
				var rtInfoRight, uPagePath, uReferrals, uKeywords, uSocial, uCustom, i = 0, pagepath = [], referrals = [], keywords = [], social = [], visittype = [], custom = [], uPagePathStats = [], pgStatsTable = "", uReferrals = [], uKeywords = [], uSocial = [], uCustom = [], uVisitType = [ "REFERRAL", "ORGANIC", "SOCIAL", "CUSTOM" ], uVisitorType = [ "DIRECT", "NEW" ];
				
				jQuery( function () {
					jQuery( '#aiwp-widget *' ).tooltip( {
						tooltipClass : "aiwp"
					} );
				} );

				rtData = rtData[ 0 ];

				if ( jQuery.isNumeric( rtData ) || typeof rtData === "undefined" ) {
					rtData = [];
					rtData[ "totalsForAllResults" ] = []
					rtData[ "totalsForAllResults" ][ "rt:activeUsers" ] = "0";
					rtData[ "rows" ] = [];
				}

				if ( rtData[ "totalsForAllResults" ][ "rt:activeUsers" ] !== document.getElementById( "aiwp-online" ).innerHTML ) {
					jQuery( "#aiwp-online" ).fadeOut( "slow" );
					jQuery( "#aiwp-online" ).fadeOut( 500 );
					jQuery( "#aiwp-online" ).fadeOut( "slow", function () {
						if ( ( parseInt( rtData[ "totalsForAllResults" ][ "rt:activeUsers" ] ) ) < ( parseInt( document.getElementById( "aiwp-online" ).innerHTML ) ) ) {
							jQuery( "#aiwp-online" ).css( {
								'background-color' : '#FFE8E8'
							} );
						} else {
							jQuery( "#aiwp-online" ).css( {
								'background-color' : '#E0FFEC'
							} );
						}
						document.getElementById( "aiwp-online" ).innerHTML = rtData[ "totalsForAllResults" ][ "rt:activeUsers" ];
					} );
					jQuery( "#aiwp-online" ).fadeIn( "slow" );
					jQuery( "#aiwp-online" ).fadeIn( 500 );
					jQuery( "#aiwp-online" ).fadeIn( "slow", function () {
						jQuery( "#aiwp-online" ).css( {
							'background-color' : '#FFFFFF'
						} );
					} );
				}

				if ( rtData[ "totalsForAllResults" ][ "rt:activeUsers" ] == 0 ) {
					rtData[ "rows" ] = [];
				}

				for ( i = 0; i < rtData[ "rows" ].length; i++ ) {
					pagepath.push( rtData[ "rows" ][ i ][ 0 ] );
					if ( rtData[ "rows" ][ i ][ 3 ] == "REFERRAL" ) {
						referrals.push( rtData[ "rows" ][ i ][ 1 ] );
					}
					if ( rtData[ "rows" ][ i ][ 3 ] == "ORGANIC" ) {
						keywords.push( rtData[ "rows" ][ i ][ 2 ] );
					}
					if ( rtData[ "rows" ][ i ][ 3 ] == "SOCIAL" ) {
						social.push( rtData[ "rows" ][ i ][ 1 ] );
					}
					if ( rtData[ "rows" ][ i ][ 3 ] == "CUSTOM" ) {
						custom.push( rtData[ "rows" ][ i ][ 1 ] );
					}
					visittype.push( rtData[ "rows" ][ i ][ 3 ] );
				}

				uPagePath = pagepath.filter( reports.rtOnlyUniqueValues );
				for ( i = 0; i < uPagePath.length; i++ ) {
					uPagePathStats[ i ] = {
						"pagepath" : uPagePath[ i ],
						"count" : reports.rtCountSessions( rtData, uPagePath[ i ], 6 )
					}
				}
				uPagePathStats.sort( function ( a, b ) {
					return b.count - a.count
				} );

				pgStatsTable = "";
				for ( i = 0; i < uPagePathStats.length; i++ ) {
					if ( i < aiwpItemData.rtLimitPages ) {
						pgStatsTable += '<div class="aiwp-pline"><div class="aiwp-pleft"><a href="#" data-aiwp="' + reports.rtPageDetails( rtData, uPagePathStats[ i ].pagepath ) + '">' + uPagePathStats[ i ].pagepath.substring( 0, 70 ) + '</a></div><div class="aiwp-pright">' + uPagePathStats[ i ].count + '</div></div>';
					}
				}
				document.getElementById( "aiwp-pages" ).innerHTML = '<br /><div class="aiwp-pg">' + pgStatsTable + '</div>';

				uReferrals = referrals.filter( reports.rtOnlyUniqueValues );
				for ( i = 0; i < uReferrals.length; i++ ) {
					uReferrals[ i ] = {
						"value" : uReferrals[ i ],
						"count" : reports.rtCountSessions( rtData, uReferrals[ i ], 6 )
					};
				}
				uReferrals.sort( function ( a, b ) {
					return b.count - a.count
				} );

				uKeywords = keywords.filter( reports.rtOnlyUniqueValues );
				for ( i = 0; i < uKeywords.length; i++ ) {
					uKeywords[ i ] = {
						"value" : uKeywords[ i ],
						"count" : reports.rtCountSessions( rtData, uKeywords[ i ], 6 )
					};
				}
				uKeywords.sort( function ( a, b ) {
					return b.count - a.count
				} );

				uSocial = social.filter( reports.rtOnlyUniqueValues );
				for ( i = 0; i < uSocial.length; i++ ) {
					uSocial[ i ] = {
						"value" : uSocial[ i ],
						"count" : reports.rtCountSessions( rtData, uSocial[ i ], 6 )
					};
				}
				uSocial.sort( function ( a, b ) {
					return b.count - a.count
				} );

				uCustom = custom.filter( reports.rtOnlyUniqueValues );
				for ( i = 0; i < uCustom.length; i++ ) {
					uCustom[ i ] = {
						"value" : uCustom[ i ],
						"count" : reports.rtCountSessions( rtData, uCustom[ i ], 6 )
					};
				}
				uCustom.sort( function ( a, b ) {
					return b.count - a.count
				} );

				rtInfoRight = '<div class="aiwp-bigtext"><a href="#" data-aiwp="' + reports.rtGenerateTooltip( uReferrals ) + '"><div class="aiwp-bleft">' + reports.i18n[ 0 ] + '</a></div><div class="aiwp-bright">' + reports.rtCountSessions( rtData, uVisitType[ 0 ], 6 ) + '</div></div>';
				rtInfoRight += '<div class="aiwp-bigtext"><a href="#" data-aiwp="' + reports.rtGenerateTooltip( uKeywords ) + '"><div class="aiwp-bleft">' + reports.i18n[ 1 ] + '</a></div><div class="aiwp-bright">' + reports.rtCountSessions( rtData, uVisitType[ 1 ], 6 ) + '</div></div>';
				rtInfoRight += '<div class="aiwp-bigtext"><a href="#" data-aiwp="' + reports.rtGenerateTooltip( uSocial ) + '"><div class="aiwp-bleft">' + reports.i18n[ 2 ] + '</a></div><div class="aiwp-bright">' + reports.rtCountSessions( rtData, uVisitType[ 2 ], 6 ) + '</div></div>';
				rtInfoRight += '<div class="aiwp-bigtext"><a href="#" data-aiwp="' + reports.rtGenerateTooltip( uCustom ) + '"><div class="aiwp-bleft">' + reports.i18n[ 3 ] + '</a></div><div class="aiwp-bright">' + reports.rtCountSessions( rtData, uVisitType[ 3 ], 6 ) + '</div></div>';

				rtInfoRight += '<div class="aiwp-bigtext"><div class="aiwp-bleft">' + reports.i18n[ 4 ] + '</div><div class="aiwp-bright">' + reports.rtCountSessions( rtData, uVisitorType[ 0 ], 6 ) + '</div></div>';
				rtInfoRight += '<div class="aiwp-bigtext"><div class="aiwp-bleft">' + reports.i18n[ 5 ] + '</div><div class="aiwp-bright">' + reports.rtCountSessions( rtData, uVisitorType[ 1 ], 6 ) + '</div></div>';

				document.getElementById( "aiwp-tdo-right" ).innerHTML = rtInfoRight;
			},
			
			drawRealtimeGA4 : function ( rtData ) {
				
				var rtInfoRight, pgStatsTable, i, uScreenName, desktopCount = 0, mobileCount = 0, tabletCount = 0, screenName = [], uScreenNameStats = [];
				
				jQuery( function () {
					jQuery( '#aiwp-widget *' ).tooltip( {
						tooltipClass : "aiwp"
					} );
				} );

				if ( jQuery.isNumeric( rtData ) || typeof rtData === "undefined" ) {
					rtData = [];
					rtData[ "totals" ] = "0";
					rtData[ "rows" ] = [];
				}

				if ( parseInt( rtData[ "totals" ] ) !== parseInt( document.getElementById( "aiwp-online-ga4" ).innerHTML ) ) {
					jQuery( "#aiwp-online-ga4" ).fadeOut( "slow" );
					jQuery( "#aiwp-online-ga4" ).fadeOut( 500 );
					jQuery( "#aiwp-online-ga4" ).fadeOut( "slow", function () {
						if ( ( parseInt( rtData[ "totals" ] ) ) < ( parseInt( document.getElementById( "aiwp-online-ga4" ).innerHTML ) ) ) {
							jQuery( "#aiwp-online-ga4" ).css( {
								'background-color' : '#FFE8E8'
							} );
						} else {
							jQuery( "#aiwp-online-ga4" ).css( {
								'background-color' : '#E0FFEC'
							} );
						}
						document.getElementById( "aiwp-online-ga4" ).innerHTML = rtData[ "totals" ];
					} );
					jQuery( "#aiwp-online-ga4" ).fadeIn( "slow" );
					jQuery( "#aiwp-online-ga4" ).fadeIn( 500 );
					jQuery( "#aiwp-online-ga4" ).fadeIn( "slow", function () {
						jQuery( "#aiwp-online-ga4" ).css( {
							'background-color' : '#FFFFFF'
						} );
					} );
				}

				if ( rtData[ "totals" ] == 0 ) {
					rtData[ "rows" ] = [];
				}

				for ( i = 0; i < rtData[ "rows" ].length; i++ ) {
				
					screenName.push( rtData[ "rows" ][ i ][ 1 ] );
					
					if ( rtData[ "rows" ][ i ][ 0 ] == "desktop" ) {
						desktopCount = desktopCount + parseInt( rtData[ "rows" ][ i ][ 2 ] );
					}
					if ( rtData[ "rows" ][ i ][ 0 ] == "mobile" ) {
						mobileCount = mobileCount + parseInt( rtData[ "rows" ][ i ][ 2 ] );
					}
					if ( rtData[ "rows" ][ i ][ 0 ] == "tablet" ) {
						tabletCount = tabletCount + parseInt( rtData[ "rows" ][ i ][ 2 ] );
					}
				}

				uScreenName = screenName.filter( reports.rtOnlyUniqueValues );
				for ( i = 0; i < uScreenName.length; i++ ) {
					uScreenNameStats[ i ] = {
						"screenName" : uScreenName[ i ],
						"count" : reports.rtCountSessions( rtData, uScreenName[ i ], 2 )
					}
				}
				uScreenNameStats.sort( function ( a, b ) {
					return b.count - a.count
				} );

				pgStatsTable = "";
				for ( i = 0; i < uScreenNameStats.length; i++ ) {
					if ( i < aiwpItemData.rtLimitPages ) {
						pgStatsTable += '<div class="aiwp-pline"><div class="aiwp-pleft">' + uScreenNameStats[ i ].screenName.substring( 0, 70 ) + '</div><div class="aiwp-pright">' + uScreenNameStats[ i ].count + '</div></div>';
					}
				}
				document.getElementById( "aiwp-pages" ).innerHTML = '<br /><div class="aiwp-pg">' + pgStatsTable + '</div>';
				
				rtInfoRight = '<div class="aiwp-bigtext-ga4"><div class="aiwp-bleft-ga4"><span class="dashicons dashicons-desktop"></span> ' + reports.i18n[ 12 ] + '</div><div class="aiwp-bright-ga4">' + desktopCount + '</div></div>';
				rtInfoRight += '<div class="aiwp-bigtext-ga4"><div class="aiwp-bleft-ga4"><span class="dashicons dashicons-smartphone"></span> ' + reports.i18n[ 13 ] + '</div><div class="aiwp-bright-ga4">' + mobileCount + '</div></div>';
				rtInfoRight += '<div class="aiwp-bigtext-ga4"><div class="aiwp-bleft-ga4"><span class="dashicons dashicons-tablet"></span> ' + reports.i18n[ 14 ] + '</div><div class="aiwp-bright-ga4">' + tabletCount + '</div></div>';

				document.getElementById( "aiwp-tdo-right-ga4" ).innerHTML = rtInfoRight;
			},

			throwDebug : function ( response ) {
				jQuery( "#aiwp-status" + slug ).css( {
					"margin-top" : "3px",
					"padding-left" : "5px",
					"height" : "auto",
					"color" : "#000",
					"border-left" : "5px solid red"
				} );
				if ( response == '-24' ) {
					jQuery( "#aiwp-status" + slug ).html( aiwpItemData.i18n[ 15 ] );
				} else {
					jQuery( "#aiwp-reports" + slug ).css( {
						"background-color" : "#F7F7F7",
						"height" : "auto",
						"margin-top" : "10px",
						"padding-top" : "50px",
						"padding-bottom" : "50px",
						"color" : "#000",
						"text-align" : "center"
					} );
					jQuery( "#aiwp-reports" + slug ).html( response );
					jQuery( "#aiwp-reports" + slug ).show();
					jQuery( "#aiwp-status" + slug ).html( aiwpItemData.i18n[ 11 ] );
					console.log( "\n********************* AIWP Log ********************* \n\n" + response );
					if ( response ) {
						postData = {
							action : 'aiwp_set_error',
							response : response,
							aiwp_security_set_error : aiwpItemData.security,
						}
						jQuery.post( aiwpItemData.ajaxurl, postData );
					}
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
					jQuery( target ).html( aiwpItemData.i18n[ 12 ] );
				} else {
					jQuery( target ).html( aiwpItemData.i18n[ 13 ] + ' (' + response + ')' );
				}
			},

			render : function ( view, period, query ) {
				var projectId, from, to, tpl, focusFlag;

				if ( period == 'realtime' ) {
					jQuery( '#aiwp-sel-report' + slug ).hide();
				} else {
					jQuery( '#aiwp-sel-report' + slug ).show();
					clearInterval( reports.rtRuns );
				}

				jQuery( '#aiwp-status' + slug ).html( '' );
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

				tools.setCookie( 'default_metric', query );
				tools.setCookie( 'default_dimension', period );

				if ( typeof view !== 'undefined' ) {
					tools.setCookie( 'default_view', view );
					projectId = view;
				} else {
					projectId = false;
				}

				if ( aiwpItemData.scope == 'admin-item' ) {
					postData = {
						action : 'aiwp_backend_item_reports',
						aiwp_security_backend_item_reports : aiwpItemData.security,
						from : from,
						to : to,
						filter : itemId
					}
				} else if ( aiwpItemData.scope == 'front-item' ) {
					postData = {
						action : 'aiwp_frontend_item_reports',
						aiwp_security_frontend_item_reports : aiwpItemData.security,
						from : from,
						to : to,
						filter : aiwpItemData.filter
					}
				} else {
					postData = {
						action : 'aiwp_backend_item_reports',
						aiwp_security_backend_item_reports : aiwpItemData.security,
						projectId : projectId,
						from : from,
						to : to
					}
				}
				if ( period == 'realtime' ) {

					reports.i18n = aiwpItemData.i18n.slice( 20, 36 );

					reports.render.focusFlag = 1;
					
					jQuery( '#aiwp-sel-metric' + slug ).hide();

					jQuery( window ).bind( "focus", function ( event ) {
						reports.render.focusFlag = 1;
					} ).bind( "blur", function ( event ) {
						reports.render.focusFlag = 0;
					} );
					
					if ( aiwpItemData.reportingType == '1' ) {

						tpl = '<div id="aiwp-realtime' + slug + '">';
						tpl += '<div class="aiwp-rt-box">';
						tpl += '<div class="aiwp-rt-title">' + reports.i18n[ 15 ] + '</div>';
						tpl += '<div class="aiwp-tdo-left-ga4">';
						tpl += '<div class="aiwp-online-ga4" id="aiwp-online-ga4">0</div>';
						tpl += '</div>';
						tpl += '<div class="aiwp-tdo-right-ga4" id="aiwp-tdo-right-ga4">';
						tpl += '<div class="aiwp-bigtext-ga4">';
						tpl += '<div class="aiwp-bleft-ga4"><span class="dashicons dashicons-desktop"></span> ' + reports.i18n[ 12 ] + '</div>';
						tpl += '<div class="aiwp-bright-ga4">0</div>';
						tpl += '</div>';
						tpl += '<div class="aiwp-bigtext-ga4">';
						tpl += '<div class="aiwp-bleft-ga4"><span class="dashicons dashicons-smartphone"></span> ' + reports.i18n[ 13 ] + '</div>';
						tpl += '<div class="aiwp-bright-ga4">0</div>';
						tpl += '</div>';
						tpl += '<div class="aiwp-bigtext-ga4">';
						tpl += '<div class="aiwp-bleft-ga4"><span class="dashicons dashicons-tablet"></span> ' + reports.i18n[ 14 ] + '</div>';
						tpl += '<div class="aiwp-bright-ga4">0</div>';
						tpl += '</div>';
						tpl += '</div>';
						tpl += '</div>';
						tpl += '<div>';
						tpl += '<div id="aiwp-pages" class="aiwp-pages">&nbsp;</div>';
						tpl += '</div>';
						tpl += '</div>';					
					
					} else {

						tpl = '<div id="aiwp-realtime' + slug + '">';
						tpl += '<div class="aiwp-rt-box">';
						tpl += '<div class="aiwp-tdo-left">';
						tpl += '<div class="aiwp-online" id="aiwp-online">0</div>';
						tpl += '</div>';
						tpl += '<div class="aiwp-tdo-right" id="aiwp-tdo-right">';
						tpl += '<div class="aiwp-bigtext">';
						tpl += '<div class="aiwp-bleft">' + reports.i18n[ 0 ] + '</div>';
						tpl += '<div class="aiwp-bright">0</div>';
						tpl += '</div>';
						tpl += '<div class="aiwp-bigtext">';
						tpl += '<div class="aiwp-bleft">' + reports.i18n[ 1 ] + '</div>';
						tpl += '<div class="aiwp-bright">0</div>';
						tpl += '</div>';
						tpl += '<div class="aiwp-bigtext">';
						tpl += '<div class="aiwp-bleft">' + reports.i18n[ 2 ] + '</div>';
						tpl += '<div class="aiwp-bright">0</div>';
						tpl += '</div>';
						tpl += '<div class="aiwp-bigtext">';
						tpl += '<div class="aiwp-bleft">' + reports.i18n[ 3 ] + '</div>';
						tpl += '<div class="aiwp-bright">0</div>';
						tpl += '</div>';
						tpl += '<div class="aiwp-bigtext">';
						tpl += '<div class="aiwp-bleft">' + reports.i18n[ 4 ] + '</div>';
						tpl += '<div class="aiwp-bright">0</div>';
						tpl += '</div>';
						tpl += '<div class="aiwp-bigtext">';
						tpl += '<div class="aiwp-bleft">' + reports.i18n[ 5 ] + '</div>';
						tpl += '<div class="aiwp-bright">0</div>';
						tpl += '</div>';
						tpl += '</div>';
						tpl += '</div>';
						tpl += '<div>';
						tpl += '<div id="aiwp-pages" class="aiwp-pages">&nbsp;</div>';
						tpl += '</div>';
						tpl += '</div>';

					}
						
					jQuery( '#aiwp-reports' + slug ).html( tpl );

					reports.rtRefresh( reports.render.focusFlag );

					reports.rtRuns = setInterval( reports.rtRefresh, 5000 );

				} else {
					if ( jQuery.inArray( query, [ 'referrers', 'contentpages', 'searches' ] ) > -1 ) {

						postData.query = 'channelGrouping,' + query;
						postData.metric = swmetric;

						jQuery.post( aiwpItemData.ajaxurl, postData, function ( response ) {
							reports.orgChartTableChart( response );
						} );
					} else if ( query == '404errors' ) {

						postData.query = query;
						postData.metric = swmetric;

						jQuery.post( aiwpItemData.ajaxurl, postData, function ( response ) {
							reports.tableChart( response );
						} );
					} else if ( query == 'trafficdetails' || query == 'technologydetails' ) {

						if ( query == 'trafficdetails' ) {
							postData.query = 'channelGrouping,medium,visitorType,source,socialNetwork';
							reports.i18n = aiwpItemData.i18n.slice( 0, 5 );
						} else {
							reports.i18n = aiwpItemData.i18n.slice( 15, 20 );
							postData.query = 'deviceCategory,browser,operatingSystem,screenResolution,mobileDeviceBranding';
						}
						postData.metric = swmetric;

						jQuery.post( aiwpItemData.ajaxurl, postData, function ( response ) {
							reports.orgChartPieCharts( response )
						} );

					} else if ( query == 'locations' ) {

						postData.query = query;
						postData.metric = swmetric;

						jQuery.post( aiwpItemData.ajaxurl, postData, function ( response ) {
							reports.geoChartTableChart( response );
						} );

					} else {

						postData.query = query + ',bottomstats';

						jQuery.post( aiwpItemData.ajaxurl, postData, function ( response ) {
							reports.areaChartBottomStats( response );
						} );

					}

				}

			},

			refresh : function () {
				if ( jQuery( '#aiwp-areachartbottomstats' + slug ).length > 0 && jQuery.isArray( reports.areaChartBottomStatsData ) ) {
					reports.areaChartBottomStats( reports.areaChartBottomStatsData );
				}
				if ( jQuery( '#aiwp-orgchartpiecharts' + slug ).length > 0 && jQuery.isArray( reports.orgChartPieChartsData ) ) {
					reports.orgChartPieCharts( reports.orgChartPieChartsData );
				}
				if ( jQuery( '#aiwp-geocharttablechart' + slug ).length > 0 && jQuery.isArray( reports.geoChartTableChartData ) ) {
					reports.geoChartTableChart( reports.geoChartTableChartData );
				}
				if ( jQuery( '#aiwp-orgcharttablechart' + slug ).length > 0 && jQuery.isArray( reports.orgChartTableChartData ) ) {
					reports.orgChartTableChart( reports.orgChartTableChartData );
				}
				if ( jQuery( '#aiwp-404tablechart' + slug ).length > 0 && jQuery.isArray( reports.tableChartData ) ) {
					reports.tableChart( reports.tableChartData );
				}
			},

			init : function () {

				try {
					NProgress.configure( {
						parent : "#aiwp-progressbar" + slug,
						showSpinner : false
					} );
					NProgress.start();
				} catch ( e ) {
					reports.alertMessage( aiwpItemData.i18n[ 0 ] );
				}

				reports.render( jQuery( '#aiwp-sel-view' + slug ).val(), jQuery( '#aiwp-sel-period' + slug ).val(), jQuery( '#aiwp-sel-report' + slug ).val() );

				jQuery( window ).resize( function () {
					var diff = jQuery( window ).width() - reports.oldViewPort;
					if ( ( diff < -5 ) || ( diff > 5 ) ) {
						reports.oldViewPort = jQuery( window ).width();
						reports.refresh(); // refresh only on over 5px viewport width changes
					}
				} );
			}
		}

		template.init();

		reports.init();

		jQuery( '#aiwp-sel-view' + slug ).change( function () {
			reports.init();
		} );

		jQuery( '#aiwp-sel-period' + slug ).change( function () {
			reports.init();
		} );

		jQuery( '#aiwp-sel-report' + slug ).change( function () {
			reports.init();
		} );

		jQuery( '[id^=aiwp-swmetric-]' ).click( function () {
			swmetric = this.id.replace( 'aiwp-swmetric-', '' );
			tools.setCookie( 'default_swmetric', swmetric );
			jQuery( '#aiwp-swmetric-sessions' ).css( "color", "#444" );
			jQuery( '#aiwp-swmetric-users' ).css( "color", "#444" );
			jQuery( '#aiwp-swmetric-pageviews' ).css( "color", "#444" );
			jQuery( '#' + this.id ).css( "color", "#008ec2" );

			//jQuery( '#aiwp-reports' + slug ).html( '' );
			reports.init();
		} );

		if ( aiwpItemData.scope == 'admin-widgets' ) {
			return;
		} else {
			return this.dialog( {
				width : 'auto',
				maxWidth : 510,
				height : 'auto',
				modal : true,
				fluid : true,
				dialogClass : 'aiwp wp-dialog',
				resizable : false,
				title : reports.getTitle( aiwpItemData.scope ),
				position : {
					my : "top",
					at : "top+100",
					of : window
				}
			} );
		}
	}
} );

function AIWPReportLoad () {
	if ( aiwpItemData.scope == 'admin-widgets' ) {
		jQuery( '#aiwp-window-1' ).aiwpItemReport( 1 );
	} else {
		jQuery( aiwpItemData.getSelector( aiwpItemData.scope ) ).click( function () {
			if ( !jQuery( "#aiwp-window-" + aiwpItemData.getID( this ) ).length > 0 ) {
				jQuery( "body" ).append( '<div id="aiwp-window-' + aiwpItemData.getID( this ) + '"></div>' );
			}
			jQuery( '#aiwp-window-' + aiwpItemData.getID( this ) ).aiwpItemReport( aiwpItemData.getID( this ) );
		} );
	}

	// on window resize
	jQuery( window ).resize( function () {
		aiwpItemData.responsiveDialog();
	} );

	// dialog width larger than viewport
	jQuery( document ).on( "dialogopen", ".ui-dialog", function ( event, ui ) {
		aiwpItemData.responsiveDialog();
	} );
}