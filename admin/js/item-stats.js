// GADWP stats container
jQuery(document).ready(
		function() {
			jQuery('a[id^="gadwp-"]').click(
					function(e) {
						var item_id = this.id.split('-')[1];
						var data = {
							action : 'gadash_get_itemstats',
							security : gadash_item_data.security,
							id : item_id
						}
						/*
						 * jQuery.post(gadash_item_data.ajaxurl, data,
						 * function(response) { console.log(response); });
						 */
						jQuery('#' + this.id + '-container').gadashPageDialog(
								gadash_item_data);

					});

			// on window resize
			jQuery(window).resize(function() {
				fluidDialog();
			});

			// dialog width larger than viewport
			jQuery(document).on("dialogopen", ".ui-dialog",
					function(event, ui) {
						fluidDialog();
					});

			function fluidDialog() {
				var $visible = jQuery(".ui-dialog:visible");
				// on each visible dialog
				$visible.each(function() {
					var $this = jQuery(this);
					var dialog = $this.find(".ui-dialog-content").data(
							"ui-dialog");
					// on each fluid dialog
					if (dialog.options.fluid) {
						var wWidth = jQuery(window).width();
						// window width vs dialog width
						if (wWidth < (parseInt(dialog.options.maxWidth) + 50)) {
							// don't fill the entire screen
							$this.css("max-width", "90%");
						} else {
							// maxWidth bug fix
							$this.css("max-width", dialog.options.maxWidth
									+ "px");
						}
						// change dialog position
						dialog.option("position", dialog.options.position);
					}
				});

			}
		});

jQuery.fn
		.extend({
			gadashPageDialog : function(gadash_item_data) {

				var selected = this;

				var template = {

					data : '<div class="gadwp-container"><div id="gadwp-progressbar"></div><select id="gadwp-sel-date"></select> <select id="gadwp-sel-report"></select><div id="gadwp-reports"></div></div>',

					addOptions : function(id, list) {

						var output = [];
						jQuery.each(list, function(key, value) {
							output.push('<option value="' + key + '">' + value
									+ '</option>');
						});
						jQuery(id).html(output.join(''));

					},

					init : function() {
						selected.append(this.data);
						this.addOptions('#gadwp-sel-date',
								gadash_item_data.dateList);
						this.addOptions('#gadwp-sel-report',
								gadash_item_data.reportList);
					}
				}
				
				var reports = {
						
				}

				template.init();

				return this.dialog({
					width : 'auto',
					maxWidth : 800,
					height : 'auto',
					modal : true,
					fluid : true,
					dialogClass : 'gadwp-style',
					resizable : false,
					title : ''
				});

			}
		});
