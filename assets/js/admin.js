/**
 * WPfomify Admin Script
 * @since 1.0.0
 */
(function ($) {
	IBXFomoAdmin = {
		/**
		 * Holds default information of fields.
		 *
		 * @var _defaultFieldMeta
		 * @access private
		 */
		_defaultFieldMeta: {},

		/**
		 * Initialize the functions.
		 *
		 * @method _init
		 * @access private
		 */
		_init: function () {
			IBXFomoAdmin._initTabs();
			IBXFomoAdmin._initTooltip();
			IBXFomoAdmin._bindEvents();
			IBXFomoAdmin._initMergeTags();
			IBXFomoAdmin._initFields();
			IBXFomoAdmin._initCustomForms();
			IBXFomoAdmin._countdownFieldsToggle();
			IBXFomoAdmin._countdownToggle();
			IBXFomoAdmin._addPreviewButton();
		},

		/**
		 * Initialize metabox tabs.
		 *
		 * @method _initTabs
		 * @access private
		 */
		_initTabs: function () {
			if ($(".mbt-metabox-tab.active").next().length === 0) {
				$(".ibx-wpfomo-next-tab").hide();
				$(".ibx-wpfomo-save-config").css("display", "inline-block");
			}
			$('.mbt-metabox-tabs-content').addClass('loaded');
		},

		/**
		 * Initialize tooltip.
		 *
		 * @method _initTooltip
		 * @access private
		 */
		_initTooltip: function () {
			// append tooltip container to body.
			$('<div class="ibx-wpfomo-tooltip"></div>').appendTo("body");

			// add help icon to section title.
			$(".mbt-metabox-tabs-wrapper .mbt-metabox-section").each(function () {
				if ($(this).find(".mbt-metabox-section-description").length > 0) {
					$(this)
						.find(".mbt-metabox-section-title")
						.append('<span class="ibx-wpfomo-tooltip-btn">?</span>');
				}
			});
		},

		/**
		 * Bind events.
		 *
		 * @method _bindEvents
		 * @access private
		 */
		_bindEvents: function () {
			$("html").on("mouseover", IBXFomoAdmin._triggerTooltip);
			$("body").on("mbt-tab-change", IBXFomoAdmin._tabChange);
			$("body").on("change", "#ibx_wpfomo_type", IBXFomoAdmin._typeChange);
			$("body").on(
				"change",
				"#ibx_wpfomo_conversions_source",
				IBXFomoAdmin._conversionSourceChange
			);
			$("body").on(
				"change",
				"#ibx_wpfomo_reviews_source",
				IBXFomoAdmin._reviewsSourceChange
			);
			$("body").on(
				"change",
				"#ibx_wpfomo_notification_layout",
				IBXFomoAdmin._notificationLayoutChange
			);
			$("body").on(
				"keyup, keypress, keydown",
				".mbt-metabox-tabs-wrapper .ibx-wpfomo-template-field input",
				IBXFomoAdmin._templateFieldChange
			);
			$("body").on(
				"click",
				".ibx-wpfomo-template-toolbar span",
				IBXFomoAdmin._templateToolbarClicked
			);
			$("body").on(
				"input change",
				".ibx-wpfomo-template-toolbar .ibx-wpfomo-template--font-size input",
				IBXFomoAdmin._templateFontSizeChange
			);
			$("body").on(
				"click",
				".ibx-wpfomo-next-tab",
				IBXFomoAdmin._nextButtonClicked
			);
			$("body").on(
				"click",
				".ibx-wpfomo-save-config",
				IBXFomoAdmin._saveButtonClicked
			);
			$("body").on(
				"click",
				".ibx_wpfomo_custom_form_url_submit",
				IBXFomoAdmin._customFormUrlSubmit
			);
			$("body").on(
				"change",
				"#ibx_wpfomo_custom_form_select",
				IBXFomoAdmin._customFormChange
			);
			$("body").on(
				"change",
				"#ibx_wpfomo_enable_countdown",
				IBXFomoAdmin._countdownToggle
			);

			$("body").on(
				"change",
				"#ibx_wpfomo_countdown_style",
				IBXFomoAdmin._countdownStyleChange
			);
			$("body").on(
				"change",
				"#mbt-metabox-section-page_analytics-toggle, #mbt-metabox-section-conversion_analytics-toggle",
				IBXFomoAdmin._renderAnalyticsPreview
			);
			$("body").on(
				"click",
				".ibx-notification-preview-panel-close",
				IBXFomoAdmin._hidePreviewPanel
			);

			$(
				"#mbt-metabox-section-page_analytics-toggle, #mbt-metabox-section-conversion_analytics-toggle"
			).trigger("change");
		},

		/**
		 * Trigger tooltip on mouseover tooltip button.
		 *
		 * @param {Object} event
		 * @method _triggerTooltip
		 * @access private
		 */
		_triggerTooltip: function (e) {
			if (!$(e.target).hasClass("ibx-wpfomo-tooltip-btn")) {
				if (!$(e.target).hasClass("ibx-wpfomo-tooltip")) {
					if (!$(e.target).hasClass("mbt-metabox-section-title")) {
						$(".ibx-wpfomo-tooltip").fadeOut(200, function () {
							$(this).removeAttr("style");
							$(this).html("");
						});

						return;
					}
				}
			}

			if ($(e.target).hasClass("ibx-wpfomo-tooltip-btn")) {
				var sectionHelp = $(e.target)
					.parents(".mbt-metabox-section")
					.find(".mbt-metabox-section-description")
					.html();

				$(".ibx-wpfomo-tooltip").html(sectionHelp);
				$(".ibx-wpfomo-tooltip")
					.css({
						top: $(e.target).offset().top - 10 + "px",
						left: $(e.target).offset().left + 30 + "px"
					})
					.fadeIn(200);
			}
		},

		/**
		 * Insert merge tag into template field by clicking on it.
		 *
		 * @method _initMergeTags
		 * @access private
		 */
		_initMergeTags: function () {
			// get template field.
			var templateField = $(
				".mbt-metabox-tabs-wrapper .ibx-wpfomo-notification-template"
			)[0];

			$(".mbt-metabox-tabs-wrapper .ibx-wpfomo-notification-template").on(
				"focus",
				function () {
					templateField = this;
				}
			);

			// bind click event on merge tags.
			$(".mbt-metabox-tabs-wrapper .ibx-wpfomo-notification-template")
				.parents(".mbt-field-control-wrapper")
				.find(".ibx-wpfomo-template-vars")
				.on("click", ".ibx-wpfomo-merge-tag", function (e) {
					e.preventDefault();

					if ("undefined" !== templateField.setRangeText) {
						templateField.setRangeText($(this).text());
						$(templateField).trigger("change");
					}
				});
		},

		/**
		 * Trigger events on field change.
		 *
		 * @method _initFields
		 * @access private
		 */
		_initFields: function () {
			IBXFomoAdmin._defaultFieldMeta = {
				initial_delay: {
					label: $("#mbt-field-initial_delay")
						.find(".mbt-field-label label")
						.text(),
					help: $("#mbt-field-initial_delay")
						.find(".mbt-field-control-wrapper p.description")
						.html()
				},
				display_time: {
					label: $("#mbt-field-display_time")
						.find(".mbt-field-label label")
						.text(),
					help: $("#mbt-field-display_time")
						.find(".mbt-field-control-wrapper p.description")
						.html()
				}
			};

			$("#ibx_wpfomo_type").trigger("change");
			$("#ibx_wpfomo_fb_icon_source").trigger("change");
		},

		/**
		 * Trigger events on Type field change.
		 *
		 * @method _typeChange
		 * @access private
		 */
		_typeChange: function () {
			var type = $(this).val();
			var defaults = IBXFomoAdmin._defaultFieldMeta;

			IBXFomoAdmin._renderPreview(type);
			$("#ibx-notification-preview-panel-button").hide();
			if (type === "conversion") {
				$("#ibx-notification-preview-panel-button").show();
				$("#ibx_wpfomo_conversions_source").trigger("change");
				$("#ibx_wpfomo_woo_product_link").trigger("change");
				$("#ibx_wpfomo_edd_product_link").trigger("change");
				$("#ibx_wpfomo_ld_product_link").trigger("change");

				$("#mbt-field-initial_delay")
					.find(".mbt-field-label label")
					.text($("#mbt-field-initial_delay").data("label-notification"));
				$("#mbt-field-initial_delay")
					.find(".mbt-field-control-wrapper p.description")
					.html($("#mbt-field-initial_delay").data("help-notification"));
				$("#mbt-field-display_time")
					.find(".mbt-field-label label")
					.text($("#mbt-field-display_time").data("label-notification"));
				$("#mbt-field-display_time")
					.find(".mbt-field-control-wrapper p.description")
					.html($("#mbt-field-display_time").data("help-notification"));
			}

			if (type == "fomo_bar") {
				$("#mbt-field-initial_delay")
					.find(".mbt-field-label label")
					.text(defaults.initial_delay.label);
				$("#mbt-field-initial_delay")
					.find(".mbt-field-control-wrapper p.description")
					.html(defaults.initial_delay.help);
				$("#mbt-field-display_time")
					.find(".mbt-field-label label")
					.text(defaults.display_time.label);
				$("#mbt-field-display_time")
					.find(".mbt-field-control-wrapper p.description")
					.html(defaults.display_time.help);
			}
			if (type === "reviews") {
				$("#ibx-notification-preview-panel-button").show();
				$("#ibx_wpfomo_reviews_source").trigger("change");
			}

			$(document).trigger( 'ibx_wpfomo_type_change', [type] );
		},

		/**
		 * Render preview based on Type.
		 *
		 * @param {String} type Fomo type
		 * @method _renderPreview
		 * @access private
		 */
		_renderPreview: function (type) {
			if (type === "fomo_bar" || type === "floating_button") {
				// Hide notification preview.
				$(".ibx-notification-popup")
					.not(".ibx-notification-popup-analytics")
					.hide();

				$("#ibx_wpfomo_fb_icon_source").trigger("change");
				if (type === "floating_button") {
					// Show floating button preview.
					$(".ibx-wpfomo-floating-button-wrap").show();
				}
			} else {
				$(".ibx-notification-popup")
					.not(".ibx-notification-popup-analytics")
					.removeClass("ibx-notification-type-reviews")
					.removeClass("ibx-notification-type-conversion");
				$(".ibx-notification-popup")
					.not(".ibx-notification-popup-analytics")
					.show()
					.addClass("ibx-notification-type-" + type);
				$(".ibx-wpfomo-floating-button-wrap").hide();
			}
		},

		/**
		 * Render preview based on analytics.
		 *
		 * @method _renderAnalyticsPreview
		 * @access private
		 */
		_renderAnalyticsPreview: function (e) {
			var id = $(e.target).attr("id");
			if ($(e.target).is(":checked")) {
				if (id.includes("page_analytics")) {
					$(".ibx-notification-popup-analytics.preview-page-analytics").show();
				}
				if (id.includes("conversion_analytics")) {
					$(
						".ibx-notification-popup-analytics.preview-conversion-analytics"
					).show();
				}
			} else {
				if (id.includes("page_analytics")) {
					$(".ibx-notification-popup-analytics.preview-page-analytics").hide();
				}
				if (id.includes("conversion_analytics")) {
					$(
						".ibx-notification-popup-analytics.preview-conversion-analytics"
					).hide();
				}
			}
		},

		/**
		 * Trigger events on Conversion Source field change.
		 *
		 * @method _conversionSourceChange
		 * @access private
		 */
		_conversionSourceChange: function (e) {
			$("#ibx_wpfomo_woo_product_link").trigger("change");
			$("#ibx_wpfomo_edd_product_link").trigger("change");
			$("#ibx_wpfomo_ld_product_link").trigger("change");
			$(".mbt-metabox-section-content .mbt-field[data-type=template]")
				.filter(function () {
					return $(this).css("display") != "none";
				})
				.find("input")
				.trigger("change");
			if ($(e.target).val() !== "import_csv") {
				$("#mbt-metabox-section-csv_fields_section").hide();
			} else {
				$("#mbt-metabox-section-csv_fields_section").show();
			}
			if ($(e.target).val() !== "woocommerce") {
				$("#mbt-field-woo_custom_url").hide();
			}

			$(document).trigger( 'ibx_wpfomo_source_change', [$(this), 'conversion'] );
		},

		/**
		 * Trigger events on Reviews Source field change.
		 *
		 * @method _conversionSourceChange
		 * @access private
		 */
		_reviewsSourceChange: function () {
			$(".mbt-metabox-section-content .mbt-field[data-type=template]")
				.filter(function () {
					return $(this).css("display") != "none";
				})
				.find("input")
				.trigger("change");

			$(document).trigger( 'ibx_wpfomo_source_change', [$(this), 'reviews'] );
		},

		/**
		 * Update layout CSS class on template field.
		 *
		 * @method _notificationLayoutChange
		 * @access private
		 */
		_notificationLayoutChange: function () {
			$(".mbt-field .ibx-wpfomo-template-field")
				.removeClass("ibx-wpfomo-layout-first")
				.removeClass("ibx-wpfomo-layout-second")
				.addClass("ibx-wpfomo-layout-" + $(this).val());

			$(".ibx-notification-popup")
				.removeClass("ibx-notification-layout-first")
				.removeClass("ibx-notification-layout-second")
				.addClass("ibx-notification-layout-" + $(this).val());
		},

		_templateFieldChange: function (e) {
			var key = e.which || e.keyCode;

			if (key === 13) {
				e.preventDefault();
				if ($(e.target).data("index") < 2) {
					$(e.target)
						.parent()
						.next()
						.find("input")
						.focus();
				}
			}
		},

		_templateToolbarClicked: function (e) {
			var $btn = $(this),
				active = $btn.hasClass('active'),
				input = $btn.parent().parent().find('input.ibx-wpfomo-notification-template'),
				cssInput = $btn.parent().parent().find('input.ibx_wpfomo_notification_css');

			$btn.toggleClass('active');

			setTimeout(function() {
				if ( $btn.hasClass('active') ) {
					active = true;
				} else {
					active = false;
				}

				if ( $btn.hasClass('ibx-wpfomo-template--bold') ) {
					if ( active ) {
						input[0].style['font-weight'] = 'bold';
					} else {
						input[0].style.removeProperty('font-weight');
					}
				}
				if ( $btn.hasClass('ibx-wpfomo-template--italic') ) {
					if ( active ) {
						input[0].style['font-style'] = 'italic';
					} else {
						input[0].style.removeProperty('font-style');
					}
				}

				cssInput.val( input.attr('style') );

				input.trigger('wpfomo.style.change', [cssInput.val()]);
			}, 100);
		},

		_templateFontSizeChange: function() {
			var input = $(this).parents('.ibx-wpfomo-template--row').find('input.ibx-wpfomo-notification-template');
			var cssInput = $(this).parents('.ibx-wpfomo-template--row').find('input.ibx_wpfomo_notification_css');

			input[0].style['font-size'] = $(this).val() + 'px';
			cssInput.val( input.attr('style') );

			input.trigger('wpfomo.style.change', [cssInput.val()]);
		},

		/**
		 * Trigger events Next tab button clicked.
		 *
		 * @param {Object} event
		 * @method _nextButtonClicked
		 * @access private
		 */
		_nextButtonClicked: function (e) {
			e.preventDefault();

			if ($("li.mbt-metabox-tab.active").next().length > 0) {
				$("li.mbt-metabox-tab.active")
					.next()
					.find("a")
					.trigger("click");
			}
		},

		/**
		 * Trigger events on Save button clicked.
		 *
		 * @param {Object} event
		 * @method _saveButtonClicked
		 * @access private
		 */
		_saveButtonClicked: function (e) {
			e.preventDefault();

			$(this).addClass("disabled");
			$(this).text($(this).data("saving"));

			$("input#publish").trigger("click");
		},

		/**
		 * Show next or save button when tab has switched manually.
		 *
		 * @param {Object} event
		 * @param {String} target
		 * @method _tabChange
		 * @access private
		 */
		_tabChange: function (e, target) {
			if (
				$(target)
					.parent()
					.next().length > 0
			) {
				$(".ibx-wpfomo-next-tab").css("display", "inline-block");
				$(".ibx-wpfomo-save-config").hide();
			} else {
				$(".ibx-wpfomo-next-tab").hide();
				$(".ibx-wpfomo-save-config").css("display", "inline-block");
			}
		},

		/**
		 * Custom form url submit.
		 *
		 * @param {Object} event
		 * @method _customFormUrlSubmit
		 * @access private
		 */
		_customFormUrlSubmit: function (e) {
			var ibx_wpfomo_custom_form_url = $("#ibx_wpfomo_custom_form_url").val();
			var parsed_formdetails = [];
			if (ibx_wpfomo_custom_form_url != "") {
				$(".mbt-loader").show();
				$.ajax({
					type: "post",
					url: ajaxurl,
					data: {
						action: "ibx_wpfomo_parse_custom_form_from_url",
						ibx_wpfomo_custom_form_url: ibx_wpfomo_custom_form_url
					},
					success: function (res) {
						// convert string to json object.
						if (typeof res !== "undefined" && res !== "") {
							$("#ibx_wpfomo_custom_form_parsed").val(res);
						} else {
							return parsed_formdetails;
						}
						IBXFomoAdmin._loadCustomForms(res);
					}
				});
			}
			// return parsed_formdetails;
		},

		/**
		 * init custom form.
		 *
		 * @method _initCustomForms
		 * @access private
		 */
		_initCustomForms() {
			var res = $("#ibx_wpfomo_custom_form_parsed").val();
			if (typeof res !== "undefined" && res !== "") {
				IBXFomoAdmin._loadCustomForms(res);
			}
		},

		/**
		 * Load custom form.
		 *
		 * @param {String} parsed response
		 * @method _loadCustomForms
		 * @access private
		 */
		_loadCustomForms(res) {
			var obj_res = JSON.parse(res);
			var sel = ibx_wpfomo_settings.custom_form_select;
			var forms_output = [];
			var selected = "";
			Object.keys(obj_res).forEach(function (k) {
				forms_output.push(
					"<option " +
					selected +
					' form_src_post = "' +
					obj_res[k].form_src_post +
					'" form_name = "' +
					obj_res[k].name +
					'" form_id = "' +
					obj_res[k].id +
					'"  value="' +
					obj_res[k].form_unique_key +
					'">' +
					obj_res[k].form_unique_key +
					"</option>"
				);
			});

			$("#ibx_wpfomo_custom_form_select").html(forms_output.join(""));
			if (typeof sel !== "undefined" && sel !== "") {
				$('#ibx_wpfomo_custom_form_select option[value="' + sel + '"]').attr(
					"selected",
					"selected"
				);
			}
			$("#ibx_wpfomo_custom_form_select").trigger("change");
			$(".mbt-loader").hide();
		},

		/**
		 * Custom form selection change.
		 *
		 * @param {Object} event
		 * @method _customFormChange
		 * @access private
		 */
		_customFormChange(e) {
			var res = $("#ibx_wpfomo_custom_form_parsed").val();
			var selected_frm = $("#ibx_wpfomo_custom_form_select").val();
			var sel_name = ibx_wpfomo_settings.custom_form_name_select;
			var sel_email = ibx_wpfomo_settings.custom_form_email_select;
			var form_src_post = $("option:selected", this).attr("form_src_post");
			$("#ibx_wpfomo_custom_form_src_post").val(form_src_post);
			if (typeof res !== "undefined" && res !== "") {
				var obj_res = JSON.parse(res);
				var fields_output = [];
				fields_output.push('<option value="">Select</option>');
				Object.keys(obj_res).forEach(function (f) {
					if (selected_frm == obj_res[f].form_unique_key) {
						Object.keys(obj_res[f].inputs).forEach(function (inp) {
							var obj_field = obj_res[f].inputs[inp];
							fields_output.push(
								'<option field_type = "' +
								obj_field.type +
								'" field_id = "' +
								obj_field.id +
								'" field_name = "' +
								obj_field.name +
								'"  value="' +
								obj_field.name +
								'">' +
								obj_field.name +
								"</option>"
							);
						});
						return;
					}
				});
				$("#ibx_wpfomo_custom_form_name_select").html(fields_output.join(""));
				$("#ibx_wpfomo_custom_form_email_select").html(fields_output.join(""));

				if (typeof sel_name !== "undefined" && sel_name !== "") {
					$(
						'#ibx_wpfomo_custom_form_name_select option[value="' +
						sel_name +
						'"]'
					).attr("selected", "selected");
				}
				if (typeof sel_email !== "undefined" && sel_email !== "") {
					$(
						'#ibx_wpfomo_custom_form_email_select option[value="' +
						sel_email +
						'"]'
					).attr("selected", "selected");
				}
			}
		},

		_countdownToggle: function() {
			if ( $('#ibx_wpfomo_enable_countdown').is(':checked') ) {
				IBXFomoAdmin._countdownFieldsToggle();
			} else {
				$('#mbt-field-countdown_time').hide();
				$('#mbt-field-fixed_countdown_time').hide();
			}
		},

		_countdownStyleChange: function() {
			IBXFomoAdmin._countdownFieldsToggle();
		},

		_countdownFieldsToggle: function() {
			if ( 'fixed' === $('#ibx_wpfomo_countdown_style').val() ) {
				$('#mbt-field-countdown_time').hide();
				$('#mbt-field-fixed_countdown_time').show();
			} else {
				$('#mbt-field-countdown_time').show();
				$('#mbt-field-fixed_countdown_time').hide();
			}
		},

		_addPreviewButton: function () {
			var btn =
				'<div class="ibx-wpfomo-preview-btn">' +
				'<a href="#">' +
				'<span class="dashicons dashicons-visibility"></span>' +
				'<span class="dashicons dashicons-hidden"></span>' +
				'<span class="ibx-wpfomo-preview-text">Preview</span>' +
				"</a>" +
				"</div>";
			// $(btn).appendTo('.mbt-metabox-tabs-wrap-inner');
			// this._triggerPreview();
		},

		_triggerPreview: function () {
			$(".ibx-wpfomo-preview-btn a").on("click", function (e) {
				e.preventDefault();
				if (
					$(this)
						.parent()
						.hasClass("ibx-wpfomo-preview-open")
				) {
					$(".ibx-notification-preview-panel-close").trigger("click");
					$(this)
						.parent()
						.removeClass("ibx-wpfomo-preview-open");
				} else {
					$(".mbt-metabox-section-content .mbt-field[data-type=template]").each(
						function () {
							if (
								$(this)
									.css("display")
									.toLowerCase() != "none"
							) {
								$(this)
									.find(".mbt-input-field.ibx-wpfomo-notification-template")
									.trigger("change");
							}
						}
					);
					IBXFomoAdmin._showPreviewPanel();
					$(this)
						.parent()
						.addClass("ibx-wpfomo-preview-open");
				}
			});
		},

		_showPreviewPanel: function () {
			$(".ibx-notification-preview-panel").addClass("slide-panel");
		},

		_hidePreviewPanel: function () {
			$(".ibx-notification-preview-panel").removeClass("slide-panel");
			$(".ibx-wpfomo-preview-btn").removeClass("ibx-wpfomo-preview-open");
		}
	};

	/* Initialize IBXFomoAdmin */
	$(window).on('load', function () {
		setTimeout(function () {
			IBXFomoAdmin._init();
		}, 100);
	});

	$(document).ready(function () {
		/* Post Columns - Active/Inactive toggle */
		$(".wp-list-table .column-notification_status img")
			.off("click")
			.on("click", function (e) {
				e.stopPropagation();

				var $this = $(this),
					isActive =
						$(this)
							.attr("src")
							.indexOf("active1.png") >= 0,
					postID = $(this).data("post"),
					nonce = $(this).data("nonce");

				if (isActive) {
					$this.attr(
						"src",
						$this.attr("src").replace("active1.png", "active0.png")
					);
					$this.attr("title", "Inactive").attr("alt", "Inactive");
				} else {
					$this.attr(
						"src",
						$this.attr("src").replace("active0.png", "active1.png")
					);
					$this.attr("title", "Active").attr("alt", "Active");
				}

				$.ajax({
					type: "post",
					url: ajaxurl,
					data: {
						action: "ibx_wpfomo_toggle_status",
						post_id: postID,
						nonce: nonce,
						status: isActive ? "inactive" : "active"
					},
					success: function (res) {
						if (res !== "success") {
							isActive = $this.attr("src").indexOf("active1.png") >= 0;
							if (isActive) {
								$this.attr(
									"src",
									$this.attr("src").replace("active1.png", "active0.png")
								);
								$this.attr("title", "Inactive").attr("alt", "Inactive");
							} else {
								$this.attr(
									"src",
									$this.attr("src").replace("active0.png", "active1.png")
								);
								$this.attr("title", "Active").attr("alt", "Active");
							}
						}
					}
				});
			});

		/* Preview */
		IBXFomoPreview.init([
			// Text color
			{
				type: "color",
				field: "#ibx_wpfomo_text_color",
				selector:
					".ibx-notification-popup, .ibx-notification-popup .ibx-notification-popup-close",
				property: "color"
			},
			// Background color
			{
				type: "color",
				field: "#ibx_wpfomo_background_color",
				selector: ".ibx-notification-popup",
				property: "background"
			},
			// Link color
			{
				type: "color",
				field: "#ibx_wpfomo_link_color",
				selector: ".ibx-notification-popup .ibx-notification-popup-title",
				property: "color"
			},
			// Ratings color
			{
				type: "color",
				field: "#ibx_wpfomo_star_color",
				selector: ".ibx-notification-popup .ibx-notification-popup-rating span",
				property: "color"
			},
			// Round Corners
			{
				type: "number",
				field: "#ibx_wpfomo_round_corners",
				selector: ".ibx-notification-popup",
				property: "border-radius",
				unit: "px"
			},
			// Image Round Corners
			{
				type: "number",
				field: "#ibx_wpfomo_img_round_corners",
				selector: ".ibx-notification-popup .ibx-notification-popup-img img",
				property: "border-radius",
				unit: "px"
			},
			// Border width
			{
				type: "number",
				field: "#ibx_wpfomo_border",
				selector: ".ibx-notification-popup",
				property: "border-width",
				unit: "px"
			},
			// Border color
			{
				type: "color",
				field: "#ibx_wpfomo_border_color",
				selector: ".ibx-notification-popup",
				property: "border-color"
			},
			// Box Shadow
			{
				type: "box-shadow",
				field: {
					blur: "#ibx_wpfomo_shadow_blur",
					spread: "#ibx_wpfomo_shadow_spread",
					color: "#ibx_wpfomo_shadow_color",
					opacity: "#ibx_wpfomo_shadow_opacity"
				},
				selector: ".ibx-notification-popup",
				property: "box-shadow"
			},
			// Typography
			{
				type: "number",
				field: "#ibx_wpfomo_first_row_font_size",
				selector: ".ibx-notification-row-first",
				property: "font-size",
				unit: "px"
			},
			{
				type: "number",
				field: "#ibx_wpfomo_second_row_font_size",
				selector: ".ibx-notification-popup-title",
				property: "font-size",
				unit: "px"
			}
		]);


		$("#ibx-notification-preview-panel-button").click(function (e) {
			e.preventDefault();
			//trigger change events
			renderPreview();
			$(".ibx-notification-preview-panel").addClass("slide-panel");
		});

		$("#ibx_wpfomo_page_analytics_title").on("input", function () {
			var txt = "30 " + $(this).val();
			$("span[for=ibx_wpfomo_page_analytics_title]").html(txt);
		});

		$("#ibx_wpfomo_page_analytics_msg").on("input", function () {
			var txt = $(this).val();
			$("span[for=ibx_wpfomo_page_analytics_msg]").html(txt);
		});

		$("#ibx_wpfomo_conversion_analytics_title").on("input", function () {
			var txt = "18 " + $(this).val();
			$("span[for=ibx_wpfomo_conversion_analytics_title]").html(txt);
		});

		$("#ibx_wpfomo_conversion_analytics_msg").on("input", function () {
			var txt = $(this).val();
			$("span[for=ibx_wpfomo_conversion_analytics_msg]").html(txt);
		});


		$('.ibx-wpfomo-notification-template').on('wpfomo.style.change', function(e, style) {
			updatePreview( $(e.target), style );
		});

		function updatePreview(input, style) {
			var input = input,
				index = input.data('index');
				rowClass = '';

			if ( 0 === index ) {
				rowClass = 'ibx-notification-row-first';
			}
			if ( 1 === index ) {
				rowClass = 'ibx-notification-row-second';
			}
			if ( 2 === index ) {
				rowClass = 'ibx-notification-row-third';
			}

			if ( '' === rowClass ) {
				return;
			}

			// if ( $('#ibx-notification-preview-panel .' + rowClass).length > 0 ) {
			// 	$('#ibx-notification-preview-panel .' + rowClass).attr('style', style);
			// }
			if ( $('.' + rowClass).length > 0 ) {
				$('.' + rowClass).attr('style', style);
			}
		}

		var activeTemplate;

		$(document).on('ibx_wpfomo_source_change', function(e, input, type) {
			setTimeout(function() {
				var toggleData = input.data('toggle')[input.val()];
				if ( 'undefined' !== typeof toggleData && 'undefined' !== typeof toggleData.fields ) {
					var toggleFields = toggleData.fields;
					toggleFields.forEach(function(field) {
						if ( undefined !== field ) {
							if ( $('#mbt-field-' + field + '[data-type="template"]').length > 0 ) {
								activeTemplate = $('#mbt-field-' + field + '[data-type="template"] .ibx-wpfomo-notification-template');
								$('#mbt-field-' + field + '[data-type="template"] .ibx-wpfomo-notification-template').trigger('change');
							}
						}
					});
				} else {
					$('.mbt-field[data-type="template"]:visible').find('.ibx-wpfomo-notification-template').trigger('change');
				}

				if ( 'undefined' !== typeof activeTemplate ) {
					renderTextPreview();
					renderPreview();
				}
			}, 1200);
		});

		$('.ibx-wpfomo-notification-template').on('input blur change', function() {
			renderTextPreview();
			renderPreview();
		});

		function renderPreview() {
			// style preview.
			var inputs = activeTemplate;
			if ( 'undefined' === typeof inputs ) {
				$(".mbt-metabox-section-content .mbt-field[data-type=template]").each(
					function () {
						if ( $(this).is(':visible') ) {
							inputs = $(this).find(".mbt-input-field.ibx-wpfomo-notification-template");
						}
					}
				);
			}

			if ( 'undefined' !== typeof inputs ) {
				inputs.each(function() {
					updatePreview($(this), $(this).attr('style'));
				});
			}
		}

		function renderTextPreview() {
			var tagsData = {
				"{{name}}": "John D.",
				"{{title}}": "The Product",
				"{{time}}": "1 week ago",
				"{{city}}": "Altamont",
				"{{state}}": "British Columbia",
				"{{country}}": "Canada",
				"{{plan}}": "Enterprise Plan",
				"{{review_title}}": "Just Awesome!",
				"{{rating}}":
					'<span class="ibx-notification-popup-rating"><span>☆</span><span>☆</span><span>☆</span><span>☆</span><span>☆</span></span>'
			};

			var type = $('#ibx_wpfomo_type').val(),
				selector = '';

			if ( 'conversion' === type ) {
				selector = $('.ibx-notification-popup .type-conversion')
			} else if ( 'reviews' === type ) {
				selector = $('.ibx-notification-popup .type-reviews')
			} else {
				return;
			}

			var inputs = activeTemplate || $('.mbt-field[data-type="template"]:visible').find('.ibx-wpfomo-notification-template');
			var html = '';

			inputs.each(function() {
				var value = $(this).val(),
					index = $(this).data('index');
				
				if ( 0 === index ) {
					html += '<span class="ibx-notification-row-first">' + value + '</span>';
				}
				if ( 1 === index ) {
					html += '<span class="ibx-notification-row-second ibx-notification-popup-title">' + value + '</span>';
				}
				if ( 2 === index ) {
					html += '<span class="ibx-notification-row-third">' + value + '</span>';
				}
			});

			var tags = html.match( /{{([^}]*)}}/g );

			if ( null === tags ) {
				return;
			}

			tags.forEach(function(tag) {
				if ( 'undefined' !== typeof tag ) {
					var tagParams = tag.replace('{{', '').replace('}}', '').split('|');
					var actualTag = '{{' + tagParams[0].trim() + '}}';
					var params = ['test'];
					var value = 'undefined' !== typeof tagsData[actualTag] ? tagsData[actualTag] : actualTag;

					if ( tagParams.length > 1 ) {
						params = tagParams[1].split(':');
					}

					switch ( params[0].trim() ) {
						case 'bold':
							html = html.replace(tag, '<strong>' + value + '</strong>');
							break;
						case 'italic':
							html = html.replace(tag, '<em>' + value + '</em>');
							break;
						case 'color':
							if ( 'undefined' !== typeof params[1] ) {
								html = html.replace(tag, '<span style="color: ' + params[1] + '">' + value + '</span>');
							}
							break;
						case 'bold+color':
							if ( 'undefined' !== typeof params[1] ) {
								html = html.replace(tag, '<span style="color: ' + params[1] + '"><strong>' + value + '</strong></span>');
							}
							break;
						case 'italic+color':
							if ( 'undefined' !== typeof params[1] ) {
								html = html.replace(tag, '<span style="color: ' + params[1] + '"><em>' + value + '</em></span>');
							}
							break;
						case 'propercase':
							html = html.replace(tag, '<span style="text-transform: capitalize;">' + value + '</span>');
							break;
						case 'upcase':
							html = html.replace(tag, '<span style="text-transform: uppercase;">' + value + '</span>');
							break;
						case 'downcase':
							html = html.replace(tag, '<span style="text-transform: lowercase;">' + value + '</span>');
							break;
						case 'fallback':
							if ( 'undefined' !== typeof params[1] ) {
								var val = params[1].replace('[', '').replace(']', '').trim();
								val = '' !== val ? val : value;
								html = html.replace(tag, val);
							}
							break;
						default:
							html = html.replace(tag, value);
							break;
					}
				}
			});

			if ( '' !== html ) {
				selector.html(html);
			}
		}
	});

	//if required or any error then focus to perticular tab.
	var form = document.querySelector("form[name=post]");
	if (typeof form !== "undefined" && form !== null) {
		form.addEventListener(
			"invalid",
			e => {
				var el = e.target.closest(".mbt-metabox-tab-content");
				var els = document.querySelector("a[href='#" + el.id.toString() + "']");
				event = new CustomEvent("click");
				els.dispatchEvent(event);
			},
			true
		);
	}
})(jQuery);
