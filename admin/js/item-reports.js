"use strict";

google.load("visualization", "1", {
  packages : ["corechart", "table", "orgchart", "geochart"]
});

jQuery(document).ready(

function() {
  jQuery('a[id^="gadwp-"]').click(function(e) {

    var item_id = getID(this);
    var slug = "-" + item_id;

    if (!jQuery("#gadwp-window" + slug).length > 0) {
      jQuery("body").append('<div id="gadwp-window' + slug + '"></div>');
    }
    jQuery('#gadwp-window' + slug).gadwpItemReport(slug, item_id);
  });

  function getID(item) {
    if (typeof item.id == "undefined") {
      return 0
    }
    if (item.id.split('-')[1] == "undefined") {
      return 0;
    } else {
      return item.id.split('-')[1];
    }
  }

  // on window resize
  jQuery(window).resize(function() {
    fluidDialog();
  });

  // dialog width larger than viewport
  jQuery(document).on("dialogopen", ".ui-dialog", function(event, ui) {
    fluidDialog();
  });

  function fluidDialog() {
    var visible = jQuery(".ui-dialog:visible");
    // on each visible dialog
    visible.each(function() {
      var $this = jQuery(this);
      var dialog = $this.find(".ui-dialog-content").data("ui-dialog");
      // on each fluid dialog
      if (dialog.options.fluid) {
        var wWidth = jQuery(window).width();
        // window width vs dialog width
        if (wWidth < (parseInt(dialog.options.maxWidth) + 50)) {
          // don't fill the entire screen
          $this.css("max-width", "90%");
        } else {
          // maxWidth bug fix
          $this.css("max-width", dialog.options.maxWidth + "px");
        }
        // change dialog position
        dialog.option("position", dialog.options.position);
      }
    });

  }
});

jQuery.fn.extend({
  gadwpItemReport : function(slug, item_id) {

    var template = {

      data : '<div id="gadwp-container' + slug + '"><select id="gadwp-sel-period' + slug + '"></select> <select id="gadwp-sel-report' + slug + '"></select><div id="gadwp-progressbar' + slug + '"></div><div id="gadwp-status' + slug + '"></div><div id="gadwp-reports' + slug + '"></div><div style="text-align:right;width:100%;font-size:0.8em;clear:both;margin-right:5px;margin-top:10px;">' + gadwp_item_data.i18n[14] + ' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_report&utm_medium=link&utm_content=back_report&utm_campaign=gadwp" rel="nofollow" style="text-decoration:none;font-size:1em;">GADWP</a>&nbsp;</div></div>',

      addOptions : function(id, list) {

        var output = [];
        jQuery.each(list, function(key, value) {
          if (key == '30daysAgo' || key == 'sessions') {
            output.push('<option value="' + key + '" selected="selected">' + value + '</option>');
          } else {
            output.push('<option value="' + key + '">' + value + '</option>');
          }
        });
        jQuery(id).html(output.join(''));
      },

      init : function() {
        if (jQuery('#gadwp-window' + slug).html().length) { // add main template once
          return;
        }

        jQuery('#gadwp-window' + slug).append(this.data);

        this.addOptions('#gadwp-sel-period' + slug, gadwp_item_data.dateList);
        this.addOptions('#gadwp-sel-report' + slug, gadwp_item_data.reportList);

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

      alertMessage : function(msg) {
        jQuery("#gadwp-status" + slug).css({
          "margin-top" : "3px",
          "padding-left" : "5px",
          "height" : "auto",
          "color" : "#000",
          "border-left" : "5px solid red"
        });
        jQuery("#gadwp-status" + slug).html(msg);
      },

      drawprs : function(gadwp_prs) {
        var data = google.visualization.arrayToDataTable(gadwp_prs);
        var options = {
          page : 'enable',
          pageSize : 10,
          width : '100%',
          allowHtml: true
        };

        var chart = new google.visualization.Table(document.getElementById('gadwp-prs' + slug));
        chart.draw(data, options);
      },

      drawtrafficchannels : function(gadwp_trafficchannels) {
        var data = google.visualization.arrayToDataTable(gadwp_trafficchannels);
        var options = {
          allowCollapse : true,
          allowHtml : true
        };

        var chart = new google.visualization.OrgChart(document.getElementById('gadwp-trafficchannels' + slug));
        chart.draw(data, options);
      },

      drawtrafficmediums : function(gadwp_trafficmediums) {
        var data = google.visualization.arrayToDataTable(gadwp_trafficmediums);
        var options = {
          is3D : false,
          tooltipText : 'percentage',
          legend : 'none',
          chartArea : {
            width : '99%',
            height : '80%'
          },
          title : gadwp_item_data.i18n[1],
          colors : gadwp_item_data.colorVariations
        };

        var chart = new google.visualization.PieChart(document.getElementById('gadwp-trafficmediums' + slug));
        chart.draw(data, options);
      },

      drawtraffictype : function(gadwp_traffictype) {
        var data = google.visualization.arrayToDataTable(gadwp_traffictype);
        var options = {
          is3D : false,
          tooltipText : 'percentage',
          legend : 'none',
          chartArea : {
            width : '99%',
            height : '80%'
          },
          title : gadwp_item_data.i18n[2],
          colors : gadwp_item_data.colorVariations
        };

        var chart = new google.visualization.PieChart(document.getElementById('gadwp-traffictype' + slug));
        chart.draw(data, options);
      },

      drawsocialnetworks : function(gadwp_socialnetworks) {
        var data = google.visualization.arrayToDataTable(gadwp_socialnetworks);
        var options = {
          is3D : false,
          tooltipText : 'percentage',
          legend : 'none',
          chartArea : {
            width : '99%',
            height : '80%'
          },
          title : gadwp_item_data.i18n[3],
          colors : gadwp_item_data.colorVariations
        };

        var chart = new google.visualization.PieChart(document.getElementById('gadwp-socialnetworks' + slug));
        chart.draw(data, options);
      },

      drawtrafficorganic : function(gadwp_trafficorganic) {
        var data = google.visualization.arrayToDataTable(gadwp_trafficorganic);
        var options = {
          is3D : false,
          tooltipText : 'percentage',
          legend : 'none',
          chartArea : {
            width : '99%',
            height : '80%'
          },
          title : gadwp_item_data.i18n[4],
          colors : gadwp_item_data.colorVariations
        };

        var chart = new google.visualization.PieChart(document.getElementById('gadwp-trafficorganic' + slug));
        chart.draw(data, options);
      },

      drawlocations : function(gadwp_locations) {
        var data = google.visualization.arrayToDataTable(gadwp_locations);
        var options = {
          page : 'enable',
          pageSize : 10,
          width : '100%'
        };

        var chart = new google.visualization.Table(document.getElementById('gadwp-locations' + slug));
        chart.draw(data, options);
      },

      drawmaplocations : function(gadwp_locations) {

        var data = google.visualization.arrayToDataTable(gadwp_locations);

        var options = {
          chartArea : {
            width : '99%',
            height : '90%'
          },
          colors : [gadwp_item_data.colorVariations[5], gadwp_item_data.colorVariations[4]],
        }

        if (gadwp_item_data.region) {
          options.region = gadwp_item_data.region;
          options.displayMode = 'markers';
          options.datalessRegionColor = 'EFEFEF';
        }

        var chart = new google.visualization.GeoChart(document.getElementById('gadwp-map' + slug));
        chart.draw(data, options);
      },

      drawmainchart : function(gadwp_mainchart) {

        var data = google.visualization.arrayToDataTable(gadwp_mainchart);

        var options = {
          legend : {
            position : 'none'
          },
          pointSize : 3,
          colors : [gadwp_item_data.colorVariations[0], gadwp_item_data.colorVariations[4]],
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
        var chart = new google.visualization.AreaChart(document.getElementById('gadwp-mainchart' + slug));
        chart.draw(data, options);
      },

      drawbottomstats : function(gadwp_bottomstats) {
        jQuery("#gdsessions" + slug).text(gadwp_bottomstats[0]);
        jQuery("#gdusers" + slug).text(gadwp_bottomstats[1]);
        jQuery("#gdpageviews" + slug).text(gadwp_bottomstats[2]);
        jQuery("#gdbouncerate" + slug).text(parseFloat(gadwp_bottomstats[3]).toFixed(2) + "%");
        jQuery("#gdorganicsearch" + slug).text(gadwp_bottomstats[4]);
        jQuery("#gdpagespervisit" + slug).text(parseFloat(gadwp_bottomstats[5]).toFixed(2));
      },

      checknpcounter : function(max) {
        try {
          if (this.npcounter == max) {
            NProgress.done();
          } else {
            this.npcounter++;
            NProgress.set((1 / (max + 1)) * this.npcounter);
          }
        } catch (e) {
        }
      },

      throwDebug : function(response) {
        jQuery("#gadwp-status" + slug).css({
          "margin-top" : "3px",
          "padding-left" : "5px",
          "height" : "auto",
          "color" : "#000",
          "border-left" : "5px solid red"
        });
        jQuery("#gadwp-status" + slug).html(gadwp_item_data.i18n[11]);
        console.log("\n********************* GADWP Log ********************* \n\n" + response);
      },

      throwError : function(target, response, n, p) {
        jQuery(target).css({
          "background-color" : "#F7F7F7",
          "height" : "auto",
          "padding-top" : p,
          "padding-bottom" : p,
          "color" : "#000",
          "text-align" : "center"
        });
        if (response == -21) {
          jQuery(target).html(gadwp_item_data.i18n[12] + ' (' + response + ')');
        } else {
          jQuery(target).html(gadwp_item_data.i18n[13] + ' (' + response + ')');
        }
        this.checknpcounter(n);
      },

      render : function(period, query) {
        var from, to;
        jQuery('#gadwp-status' + slug).html('');
        switch (period) {
          case 'today' :
            from = 'today';
            to = 'today';
            break;
          case 'yesterday' :
            from = 'yesterday';
            to = 'yesterday';
            break;
          case '7daysAgo' :
            from = '7daysAgo';
            to = 'yesterday';
            break;
          case '14daysAgo' :
            from = '14daysAgo';
            to = 'yesterday';
            break;
          case '90daysAgo' :
            from = '90daysAgo';
            to = 'yesterday';
            break;
          default :
            from = '30daysAgo';
            to = 'yesterday';
            break;
        }

        var data = {
          action : 'gadwp_get_ItemReports',
          gadwp_security_item_reports : gadwp_item_data.security,
          from : from,
          to : to,
          filter : item_id
        }

        if (jQuery.inArray(query, ['referrers', 'contentpages', 'searches']) > -1) {
          jQuery('#gadwp-reports' + slug).html('<div id="gadwp-trafficchannels' + slug + '"></div>')
          jQuery('#gadwp-reports' + slug).append('<div id="gadwp-prs' + slug + '"></div>');
          data.query = 'trafficchannels';
          jQuery.post(gadwp_item_data.ajaxurl, data, function(response) {
            if (!jQuery.isNumeric(response)) {
              if (jQuery.isArray(response)) {
                reports.trafficchannels = response;
                google.setOnLoadCallback(reports.drawtrafficchannels(reports.trafficchannels));
                reports.checknpcounter(1);
              } else {
                reports.throwDebug(response);
                reports.checknpcounter(1);
              }
            } else {
              reports.throwError('#gadwp-trafficchannels' + slug, response, 1, "125px");
            }
          });
          data.query = query;
          jQuery.post(gadwp_item_data.ajaxurl, data, function(response) {
            if (!jQuery.isNumeric(response)) {
              if (jQuery.isArray(response)) {
                reports.prs = response;
                google.setOnLoadCallback(reports.drawprs(reports.prs));
                reports.checknpcounter(1);
              } else {
                reports.throwDebug(response);
                reports.checknpcounter(1);
              }
            } else {
              reports.throwError('#gadwp-prs' + slug, response, 1, "125px");
            }
          });
        } else if (query == 'trafficdetails') {
          jQuery('#gadwp-reports' + slug).html('<div id="gadwp-trafficchannels' + slug + '"></div>')
          jQuery('#gadwp-reports' + slug).append('<div class="gadwp-floatwraper"><div id="gadwp-trafficmediums' + slug + '"></div><div id="gadwp-traffictype' + slug + '"></div></div>');
          jQuery('#gadwp-reports' + slug).append('<div class="gadwp-floatwraper"><div id="gadwp-trafficorganic' + slug + '"></div><div id="gadwp-socialnetworks' + slug + '"></div></div>');
          data.query = 'trafficchannels';
          jQuery.post(gadwp_item_data.ajaxurl, data, function(response) {
            if (!jQuery.isNumeric(response)) {
              if (jQuery.isArray(response)) {
                reports.trafficchannels = response;
                google.setOnLoadCallback(reports.drawtrafficchannels(reports.trafficchannels));
                reports.checknpcounter(4);
              } else {
                reports.throwDebug(response);
                reports.checknpcounter(4);                
              }
            } else {
              reports.throwError('#gadwp-trafficchannels' + slug, response, 4, "125px");
            }
          });
          data.query = 'medium';
          jQuery.post(gadwp_item_data.ajaxurl, data, function(response) {
            if (!jQuery.isNumeric(response)) {
              if (jQuery.isArray(response)) {
                reports.trafficmediums = response;
                google.setOnLoadCallback(reports.drawtrafficmediums(reports.trafficmediums));
                reports.checknpcounter(4);
              } else {
                reports.throwDebug(response);
                reports.checknpcounter(4);
              }
            } else {
              reports.throwError('#gadwp-trafficmediums' + slug, response, 4, "80px");
            }
          });
          data.query = 'visitorType';
          jQuery.post(gadwp_item_data.ajaxurl, data, function(response) {
            if (!jQuery.isNumeric(response)) {
              if (jQuery.isArray(response)) {
                reports.traffictype = response;
                google.setOnLoadCallback(reports.drawtraffictype(reports.traffictype));
                reports.checknpcounter(4);
              } else {
                reports.throwDebug(response);
                reports.checknpcounter(4);
              }
            } else {
              reports.throwError('#gadwp-traffictype' + slug, response, 4, "80px");
            }
          });
          data.query = 'source';
          jQuery.post(gadwp_item_data.ajaxurl, data, function(response) {
            if (!jQuery.isNumeric(response)) {
              if (jQuery.isArray(response)) {
                reports.trafficorganic = response;
                google.setOnLoadCallback(reports.drawtrafficorganic(reports.trafficorganic));
                reports.checknpcounter(4);
              } else {
                reports.throwDebug(response);
                reports.checknpcounter(4);
              }
            } else {
              reports.throwError('#gadwp-trafficorganic' + slug, response, 4, "80px");
            }
          });
          data.query = 'socialNetwork';
          jQuery.post(gadwp_item_data.ajaxurl, data, function(response) {
            if (!jQuery.isNumeric(response)) {
              if (jQuery.isArray(response)) {
                reports.socialnetworks = response;
                google.setOnLoadCallback(reports.drawsocialnetworks(reports.socialnetworks));
                reports.checknpcounter(4);
              } else {
                reports.throwDebug(response);
                reports.checknpcounter(4);
              }
            } else {
              reports.throwError('#gadwp-socialnetworks' + slug, response, 4, "80px");
            }
          });
        } else if (query == 'locations') {
          jQuery('#gadwp-reports' + slug).html('<div id="gadwp-map' + slug + '"></div>')
          jQuery('#gadwp-reports' + slug).append('<div id="gadwp-locations' + slug + '"></div>');
          data.query = query;
          jQuery.post(gadwp_item_data.ajaxurl, data, function(response) {
            if (!jQuery.isNumeric(response)) {
              if (jQuery.isArray(response)) {
                reports.locations = response;
                google.setOnLoadCallback(reports.drawmaplocations(reports.locations));
                reports.checknpcounter(1);
                google.setOnLoadCallback(reports.drawlocations(reports.locations));
                reports.checknpcounter(1);
              } else {
                reports.throwDebug(response);
                reports.checknpcounter(1);
              }
            } else {
              reports.throwError('#gadwp-map' + slug, response, 1, "125px");
              reports.throwError('#gadwp-locations' + slug, response, 1, "125px");
            }
          });
        } else {
          jQuery('#gadwp-reports' + slug).html('<div id="gadwp-mainchart' + slug + '"></div>')
          jQuery('#gadwp-reports' + slug).append('<div id="gadwp-bottomstats' + slug + '" class="gadwp-wrapper"><div class="inside"><div class="small-box"><h3>' + gadwp_item_data.i18n[5] + '</h3><p id="gdsessions' + slug + '">&nbsp;</p></div><div class="small-box"><h3>' + gadwp_item_data.i18n[6] + '</h3><p id="gdusers' + slug + '">&nbsp;</p></div><div class="small-box"><h3>' + gadwp_item_data.i18n[7] + '</h3><p id="gdpageviews' + slug + '">&nbsp;</p></div><div class="small-box"><h3>' + gadwp_item_data.i18n[8] + '</h3><p id="gdbouncerate' + slug + '">&nbsp;</p></div><div class="small-box"><h3>' + gadwp_item_data.i18n[9] + '</h3><p id="gdorganicsearch' + slug + '">&nbsp;</p></div><div class="small-box"><h3>' + gadwp_item_data.i18n[10] + '</h3><p id="gdpagespervisit' + slug + '">&nbsp;</p></div></div></div>');

          data.query = query;
          jQuery.post(gadwp_item_data.ajaxurl, data, function(response) {
            if (!jQuery.isNumeric(response)) {
              if (jQuery.isArray(response)) {
                reports.mainchart = response;
                google.setOnLoadCallback(reports.drawmainchart(reports.mainchart));
                reports.checknpcounter(1);
              } else {
                reports.throwDebug(response);
                reports.checknpcounter(1);
              }
            } else {
              reports.throwError('#gadwp-mainchart' + slug, response, 1, "125px");
            }
          });

          data.query = 'bottomstats';
          jQuery.post(gadwp_item_data.ajaxurl, data, function(response) {

            if (!jQuery.isNumeric(response)) {
              if (jQuery.isArray(response)) {
                reports.bottomstats = response;
                google.setOnLoadCallback(reports.drawbottomstats(reports.bottomstats));
                reports.checknpcounter(1);
              } else {
                reports.throwDebug(response);
                reports.checknpcounter(1);
              }
            } else {
              reports.throwError('#gadwp-bottomstats' + slug, response, response, 1, "40px");
            }
          });

        }

      },

      refresh : function() {
        if (jQuery('#gadwp-bottomstats' + slug).length > 0){
          this.drawbottomstats(this.bottomstats);
        }
        if (jQuery('#gadwp-mainchart' + slug).length > 0 && jQuery.isArray(this.mainchart)){
          this.drawmainchart(this.mainchart);
        }
        if (jQuery('#gadwp-map' + slug).length > 0 && jQuery.isArray(this.locations)){
          this.drawmaplocations(this.locations);
        }
        if (jQuery('#gadwp-locations' + slug).length > 0 && jQuery.isArray(this.locations)){
          this.drawlocations(this.locations);
        }
        if (jQuery('#gadwp-socialnetworks' + slug).length > 0 && jQuery.isArray(this.socialnetworks)){
          this.drawsocialnetworks(this.socialnetworks);
        }
        if (jQuery('#gadwp-trafficorganic' + slug).length > 0 && jQuery.isArray(this.trafficorganic)){        
          this.drawtrafficorganic(this.trafficorganic);
        }
        if (jQuery('#gadwp-traffictype' + slug).length > 0 && jQuery.isArray(this.traffictype)){        
          this.drawtraffictype(this.traffictype);
        }
        if (jQuery('#gadwp-trafficmediums' + slug).length > 0 && jQuery.isArray(this.trafficmediums)){        
          this.drawtrafficmediums(this.trafficmediums);
        }
        if (jQuery('#gadwp-trafficchannels' + slug).length > 0 && jQuery.isArray(this.trafficchannels)){        
          this.drawtrafficchannels(this.trafficchannels);
        }
        if (jQuery('#gadwp-prs' + slug).length > 0 && jQuery.isArray(this.prs)){
          this.drawprs(this.prs);
        }  
      },

      init : function() {

        if (jQuery("#gadwp-reports" + slug).html().length) { // only when report is empty
          return;
        }

        try {
          NProgress.configure({
            parent : "#gadwp-progressbar" + slug
          });
          NProgress.configure({
            showSpinner : false
          });
          NProgress.start();
        } catch (e) {
          this.alertMessage(gadwp_item_data.i18n[0]);
        }

        this.render(jQuery('#gadwp-sel-period' + slug).val(), jQuery('#gadwp-sel-report' + slug).val());

        jQuery(window).resize(function() {
          reports.refresh();
        });
      }
    }

    template.init();

    reports.init();

    jQuery('#gadwp-sel-period' + slug).change(function() {
      jQuery('#gadwp-reports' + slug).html('');
      reports.init();
    });

    jQuery('#gadwp-sel-report' + slug).change(function() {
      jQuery('#gadwp-reports' + slug).html('');
      reports.init();
    });

    return this.dialog({
      width : 'auto',
      maxWidth : 510,
      height : 'auto',
      modal : true,
      fluid : true,
      dialogClass : 'wp-dialog',
      resizable : false,
      title : jQuery('#gadwp'+slug).attr( "title" ),
      position : {
        my : "top",
        at : "top+100",
        of : window
      }
    });

  }
});