var yk_mt_entry_id = 0;
var yk_mt_selected_meal_type = false;
var yk_mt_meal_tracker_found = ('undefined' !== typeof (yk_mt_sc_meal_tracker));
var yk_meal_tracker_dialog = false;
var yk_meal_tracker_dialog_mode = 'add';
var yk_mt_meal_selector = false;
var yk_mt_meal_tracker_show = yk_mt_meal_tracker_found && 'default' === yk_mt_sc_meal_tracker['mode'];

jQuery( document ).ready( function ( $ ) {

  // Load config for shortcode
  if (true === yk_mt_meal_tracker_found) {
    yk_mt_entry_id = yk_mt_sc_meal_tracker['todays-entry']['id'];
  }

  /**
   * ---------------------------------------------------------------------------------------
   * Accordion ( based on https://inspirationalpixels.com/accordion-html-css-jquery/#css )
   * ---------------------------------------------------------------------------------------
   */

  if (yk_mt_meal_tracker_found && 'true' === yk_mt_sc_meal_tracker['accordion-enabled']) {

    // Close all sections (probably none open in the first place)
    yk_mt_accordion_close_sections();

    $('.yk-mt__accordion-section .initial-active').each(function () {
      var currentAttrValue = $(this).attr('href');
      $(this).addClass('active');
      $('.yk-mt__accordion ' + currentAttrValue).slideDown(300).addClass('open');
    });

    $('.yk-mt__accordion-section-title').click(function (e) {

      // Grab current anchor value
      var currentAttrValue = $(this).attr('href');

      if ($(e.target).is('.active')) {
        yk_mt_accordion_close_sections();
      } else {
        yk_mt_accordion_close_sections();

        // Add active class to section title
        $(this).addClass('active');
        // Open up the hidden content panel
        $('.yk-mt__accordion ' + currentAttrValue).slideDown(300).addClass('open');
      }

      e.preventDefault();
    });
  }

  function yk_mt_accordion_close_sections() {
    $('.yk-mt__accordion .yk-mt__accordion-section-title').removeClass('active');
    $('.yk-mt__accordion .yk-mt__accordion-section-content').slideUp(300).removeClass('open');
  }

  /**
   * ---------------------------------------------------------------------------------------
   * Add a meal dialog
   * ---------------------------------------------------------------------------------------
   */

  /**
   * If "Add a meal" button is pressed, set the meal type ID on the dialog
   */
  $('.yk-mt-add-meal-prompt').click(function (e) {

    let meal_type = $(e.target).data('meal-type');

    yk_mt_selected_meal_type = meal_type;

    $('body').attr('yk-mt-meal-type', meal_type); // remove this? Or keep above.
  });

  if (true === yk_mt_meal_tracker_show) {

    /**
     * Initialise dialog
     */
    var dialog_options = JSON.parse(yk_mt_sc_meal_tracker['dialog-options']);

    dialog_options.afterClose = function () {
      yk_mt_dialog_close()
    };
    dialog_options.beforeOpen = function () {

      yk_mk_selectize_init();

      // Depending on the dialog mode, show / hide UI components
      yk_mt_dialog_set_css_class_for_mode();

      $('body').addClass('yk-mt-dialog-is-open');
      $('#yk-mt-add-meal-dialog').removeClass('yk-mt-hide');

      yk_mt_add_meal_form_show_quantity();
    };

    yk_meal_tracker_dialog = $(".yk-mt-add-meal-prompt, .yk-mt-edit-meal-prompt").animatedModal(dialog_options);
  }

  /**
   * Tidy up after dialog closed
   */
  function yk_mt_dialog_close() {

    yk_meal_tracker_dialog_mode = 'add';

    $('body').removeClass('yk-mt-dialog-is-open');

    $('body').trigger('meal-tracker-dialog-closing');

    yk_mt_dialog_meal_type_reset();

    yk_mk_selectize_clear();

    $('#yk-mt-add-meal-dialog').removeClass('yk-mt-mode-edit');
    $('#yk-mt-add-meal-dialog').addClass('yk-mt-mode-add');

    $('#yk-mt-form-add-new-meal').trigger("reset");

    $('.yk-mt-hide-if-no-meals-results').hide();

    $('body').trigger('meal-tracker-dialog-closed');
  }

  /**
   * Open dialog box
   * @param mode
   */
  function yk_mt_dialog_open(mode = 'edit') {

    yk_mt_add_meal_form_show_quantity();

    yk_meal_tracker_dialog_mode = mode;

    if ('edit' === mode) {
      $('#yk-mt-open-dialog-edit').click();
    }
  }

  /**
   * Add CSS class to dialog for mode
   */
  function yk_mt_dialog_set_css_class_for_mode() {
    $('#yk-mt-add-meal-dialog').removeClass('yk-mt-mode-edit yk-mt-mode-add');
    $('#yk-mt-add-meal-dialog').addClass('yk-mt-mode-' + yk_meal_tracker_dialog_mode);
  }

  /**
   * Reset the meal ID required for dialog
   */
  function yk_mt_dialog_meal_type_reset() {
    yk_mt_selected_meal_type = false;
  }

  /**
   * ---------------------------------------------------------------------------------------
   * Selectize
   * ---------------------------------------------------------------------------------------
   */

  /**
   * Add an option to list of meals
   * @param option
   */
  function yk_mk_selectize_add_option(option) {

    if (false !== yk_mt_meal_selector && option) {

      var selectize = yk_mt_meal_selector[0].selectize;

      selectize.addOption(option);
      selectize.addItem(option['id']);
    }
  }

  /**
   * Clear Selectize value
   */
  function yk_mk_selectize_clear() {

    if (false !== yk_mt_meal_selector) {

      var selectize = yk_mt_meal_selector[0].selectize;

      selectize.clear();
    }
  }

  /*
      Initialise the Meal picker
   */
  function yk_mk_selectize_init() {

    yk_mt_meal_selector = $('#yk-mt-meal-id').selectize({
      preload: true,
      valueField: 'id',
      labelField: 'name',
      searchField: 'name',
      options: [],
      load: function (query, callback) {

        this.clearOptions();

        $.ajax({
          url: yk_mt['ajax-url'],
          type: 'POST',
          data: {action: 'meals', security: yk_mt['ajax-security-nonce'], search: query},
          error: function () {
            callback();
          },
          success: function (res) {

            if (false === res) {
              $('.yk-mt-hide-if-no-meals-results').fadeOut('slow');

              yk_mt_info( yk_mt_sc_meal_tracker['localise']['search-no-results'] );
            }

            callback(res);
          }
        });
      },
      onChange: function (value) {

        if ('' === value) {
          $('.yk-mt-hide-if-no-meals-results').fadeOut('slow');
          return;
        }

        $('.yk-mt-hide-if-no-meals-results').fadeIn('slow');
      }
    });
  }

  /**
   * ---------------------------------------------------------------------------------------
   * Add a meal form
   * ---------------------------------------------------------------------------------------
   */

  /**
   * Add a meal to today's entry
   */
  $('.yk-mt-meal-button-add').click(function (e) {

    e.preventDefault();

    var quantity = $(this).data('quantity');

    /**
     * Add meal to today's entry
     */
    yk_mt_post_api_add_meal_to_entry(yk_mt_entry_id,
      $('#yk-mt-meal-id').val(),
      yk_mt_selected_meal_type,
      quantity
    );
  });

  /**
   * Post to AJAX back end
   * @param action
   * @param data
   * @param callback
   */
  function yk_mt_post(action, data, callback) {

    data['action'] = action;
    data['security'] = yk_mt['ajax-security-nonce'];

    $('body').trigger('meal-tracker-ajax-started');

    jQuery.post(yk_mt['ajax-url'], data, function (response) {

      callback(data, response);

      $('body').trigger('meal-tracker-ajax-finished');
    });
  }

  /**
   * Post back to Ajax handler,
   * @param user_id
   * @param entry_id
   * @param meal_id
   * @param meal_type
   */
  function yk_mt_post_api_add_meal_to_entry(entry_id, meal_id, meal_type, quantity = 1) {

    if (meal_id) {

      if (!quantity) {
        quantity = 1;
      }

      var data = {
        'entry-id': entry_id,
        'meal-id': meal_id,
        'meal-type': meal_type,
        'quantity': quantity
      };

      yk_mt_post('add_meal_to_entry', data, yk_mt_post_api_add_meal_to_entry_callback);

    } else {
      yk_mt_warn(yk_mt_sc_meal_tracker['localise']['meal-entry-missing-meal'], '#yk-mt-form-add-meal-to-entry .selectize-control');
    }
  }

  /**
   * Handle the call back to adding a meal to an entry
   * @param data
   * @param response
   */
  function yk_mt_post_api_add_meal_to_entry_callback(data, response) {
    if (false === response['error']) {

      yk_mt_render_entry(response['entry']);

      $('#yk-mt-form-add-meal-to-entry').trigger("reset");

      yk_mt_success(yk_mt_sc_meal_tracker['localise']['meal-entry-added-success']);

      yk_mt_success(yk_mt_sc_meal_tracker['localise']['meal-entry-added-short'], '#yk-mt-button-add-meal-' + data['quantity']);

      if ($('#yk-mt-button-add-meal-close').is(':checked')) {
        $('#btn-close-modal').click();
      }

      $('body').trigger('meal-tracker-meal-added');
    } else {

      $('#btn-close-modal').click();

      $('body').trigger('meal-tracker-save-error');
    }
  }

  /**
   * Delete meal from entry
   * @param meal_entry_id
   */
  function yk_mt_post_api_delete_meal_from_entry(meal_entry_id) {

    var data = {
      'meal-entry-id': meal_entry_id,
      'entry-id': yk_mt_entry_id
    };

    yk_mt_post('delete_meal_from_entry', data, yk_mt_post_api_delete_meal_from_entry_callback);
  }

  /**
   * Handle the call back to deleting a meal from an entry
   * @param data
   * @param response
   */
  function yk_mt_post_api_delete_meal_from_entry_callback(data, response) {

    if (false === response['error']) {

      yk_mt_render_entry(response['entry']);

      yk_mt_success(yk_mt_sc_meal_tracker['localise']['meal-entry-deleted-success']);

      $('body').trigger('meal-tracker-meal-deleted');
    } else {
      $('body').trigger('meal-tracker-save-error');
    }
  }

  /**
   * Refresh entry UI
   * @param entry_id
   */
  function yk_mt_refresh_entry() {

    var data = {
      'entry-id': yk_mt_entry_id
    };

    yk_mt_post('get_entry', data, yk_mt_refresh_entry_callback);
  }

  /**
   * Update UI component with latest entry data
   * @param data
   * @param response
   */
  function yk_mt_refresh_entry_callback(data, response) {
    yk_mt_render_entry(response);
  }

  /**
   * There was an error saving the data
   */
  $('body').on('meal-tracker-save-error', function (event) {
    yk_mt_warn(yk_mt_sc_meal_tracker['localise']['db-error']);
  });

  /**
   * There was an error loading data
   */
  $('body').on('meal-tracker-loading-error', function (event) {
    yk_mt_warn(yk_mt_sc_meal_tracker['localise']['db-error-loading']);
  });

  /**
   * Listen for trigger to delete meal from an entry
   */
  $('body').on('meal-tracker-meal-entry-delete', function (event, meal_entry_id) {
    yk_mt_post_api_delete_meal_from_entry(meal_entry_id);
  });

  /**
   * ------ ---------------------------------------------------------------------------------
   * Add new meal
   * ---------------------------------------------------------------------------------------
   */

  $('#yk-mt-form-add-new-meal').submit(function (event) {

    event.preventDefault();

    let name = $('#yk-mt-add-meal-name').val();
    let description = $('#yk-mt-add-meal-description').val();
    let calories = $('#yk-mt-add-meal-calories').val();
    let quantity = $('#yk-mt-add-meal-quantity').val();
    let unit = $('#yk-mt-add-meal-unit').val();

    let meta_fields = yk_mt_meta_fields();

    // If we have meta fields, populate the object from form fields
    if (yk_mt_meal_tracker_found && false !== meta_fields) {
      $.each(meta_fields, function (index, value) {
        meta_fields[index] = $('#yk-mt-add-meal-' + index).val();
      });
    }

    // Update the meal
    if ('edit' === yk_meal_tracker_dialog_mode) {
      yk_mt_post_api_edit_meal(
        name,
        description,
        calories,
        quantity,
        unit,
        meta_fields
      );
    } else {

      yk_mt_post_api_add_meal(
        name,
        description,
        calories,
        quantity,
        unit,
        meta_fields
      );

    }
  });

  /**
   * Add a new meal
   * @param name
   * @param description
   * @param calories
   * @param quantity
   * @param unit
   */

  function yk_mt_post_api_add_meal(name, description, calories, quantity, unit, meta_fields) {

    var data = {
      'name': name,
      'description': description,
      'calories': calories,
      'quantity': quantity,
      'unit': unit,
      'entry-id': yk_mt_entry_id,
      'meal-type': yk_mt_selected_meal_type,
      'meta-fields': meta_fields
    };

    yk_mt_post('add_meal', data, yk_mt_post_api_add_meal_callback);
  }

  /**
   * Handle the call back to adding a meal
   * @param data
   * @param response
   */
  function yk_mt_post_api_add_meal_callback(data, response) {

    if (false === response['error']) {

      yk_mk_selectize_add_option(response['new-meal']);

      yk_mt_success(yk_mt_sc_meal_tracker['localise']['meal-entry-added-short'], '#yk-mt-button-meal-add');

      $('#yk-mt-form-add-new-meal').trigger("reset");

      yk_mt_success(yk_mt_sc_meal_tracker['localise']['meal-entry-added-success']);

      yk_mt_refresh_entry();

      $('#btn-close-modal').click();

      $('body').trigger('meal-tracker-new-meal-added');

    } else {
      $('body').trigger('meal-tracker-save-error');
    }
  }

  /**
   * Toggle show / hide of quantity field dependant on unit selected
   * ( also in admin.js )
   */
  $('#yk-mt-add-meal-unit').change(function () {
    yk_mt_add_meal_form_show_quantity();
  });

  /**
   * Show  / Hide quantity field depending on the unit selected
   * ( also in admin.js )
   */
  function yk_mt_add_meal_form_show_quantity() {

    let value = $('#yk-mt-add-meal-unit').val();
    let quantity_row = $('#yk-mt-add-meal-quantity-row');

    if (true === yk_mt_hide_quantity(value)) {
      $('#yk-mt-add-meal-quantity').prop('disabled', true);
      $('#yk-mt-add-meal-quantity').prop('required', false);
      $('#yk-mt-add-meal-quantity').val('');
      quantity_row.hide();
    } else {
      $('#yk-mt-add-meal-quantity').prop('disabled', false);
      $('#yk-mt-add-meal-quantity').prop('required', true);
      quantity_row.show();
    }
  }

  /**
   * ------ ---------------------------------------------------------------------------------
   * Edit Meal
   * ---------------------------------------------------------------------------------------
   */
  $( 'body' ).on('click', '.yk-mt-meal-button-edit-inline', function (e) {

    e.preventDefault();

    let meal_id = $(this).attr('data-meal-id');

    yk_mt_temp_store_set('meal-id', meal_id);

    yk_mt_post_api_load_meal();
  });

  /**
   * Fetch the data for an existing meal and populate form
   */
  function yk_mt_post_api_load_meal() {

    yk_mt_post('meal', {'meal-id': yk_mt_temp_store_get('meal-id')}, yk_mt_post_api_load_meal_callback);
  }

  /**
   * Handle the call when loading a meal
   * @param data
   * @param response
   */
  function yk_mt_post_api_load_meal_callback(data, response) {

    if (false === response['error']) {

      let meal = response['meal'];

      $('#yk-mt-add-meal-name').val(meal['name']);
      $('#yk-mt-add-meal-description').val(meal['description']);
      $('#yk-mt-add-meal-calories').val(meal['calories']);
      $('#yk-mt-add-meal-unit').val(meal['unit']);
      $('#yk-mt-add-meal-quantity').val(meal['quantity']);

      let meta_fields = yk_mt_meta_fields();

      // If we have meta fields, populate the object from form fields
      if (false !== meta_fields) {
        $.each(meta_fields, function (index, value) {
          $('#yk-mt-add-meal-' + index).val(meal[index]);
        });
      }

      yk_mt_meal_add_nav_show('manual');

      yk_mt_dialog_open();

    } else {
      $('body').trigger('meal-tracker-loading-error');
    }
  }

  /**
   * update an existing meal
   * @param id
   * @param name
   * @param description
   * @param quantity
   * @param unit
   */
  function yk_mt_post_api_edit_meal(name, description, calories, quantity, unit, meta_fields) {

    var data = {
      'id': yk_mt_temp_store_get('meal-id'),
      'name': name,
      'description': description,
      'calories': calories,
      'quantity': quantity,
      'unit': unit,
      'entry-id': yk_mt_entry_id,
      'meal-type': yk_mt_selected_meal_type,
      'meta-fields': meta_fields
    };

    yk_mt_post('add_meal', data, yk_mt_post_api_edit_meal_callback);
  }

  /**
   * Handle the call back to adding a meal
   * @param data
   * @param response
   */
  function yk_mt_post_api_edit_meal_callback(data, response) {

    if (false === response['error']) {

      yk_mt_success(yk_mt_sc_meal_tracker['localise']['meal-entry-added-success']);

      yk_mt_refresh_entry();

      $('#btn-close-modal').click();

      $('body').trigger('meal-tracker-meal-updated');

    } else {
      $('#btn-close-modal').click();
      $('body').trigger('meal-tracker-save-error');
    }
  }

  /**
   * ---------------------------------------------------------------------------------------
   * Add a meal navigation
   * ---------------------------------------------------------------------------------------
   */

  /**
   * When dialog closes, reset add meal navigation
   */
  $('body').on('meal-tracker-dialog-closing', function (event) {
    yk_mt_meal_add_nav_reset();
  });

  /**
   * Determine display mode of add meal form
   * @param state
   */
  function yk_mt_meal_add_nav_show(state) {

    switch (state) {
      case 'manual':
        $('#yk-mt-form-add-new-meal-nav').hide();
        $('.yk-mt__modal-quick-search').hide();
        $('.yk-mt-add-new-meal-form').fadeIn('slow');
        break;
      case 'search':
        $('#yk-mt-form-add-new-meal-nav').hide();
        $('.yk-mt__modal-quick-search').hide();
        $('.yk-mt-add-new-meal-form-search-external').fadeIn('slow');

        yk_mk_selectize_external_init();

        break;
      default:  // show navigation
        yk_mt_meal_add_nav_reset();
    }

  }

  /**
   * Reset meal add form back
   */
  function yk_mt_meal_add_nav_reset() {
    $('.yk-mt-add-new-meal-form').hide();
    $('.yk-mt-add-new-meal-form-search-external').hide();
    $('#yk-mt-form-add-new-meal-nav').fadeIn('slow');
    $('.yk-mt__modal-quick-search').fadeIn('slow');

    // TODO: Destroy selectize for external search?
  }

  /**
   * Display Meal Add form
   */
  $('#yk-mt-button-meal-nav-manually-add').click(function (e) {

    e.preventDefault();

    yk_mt_meal_add_nav_show('manual');

  });

  /**
   * Display Meal Search form
   */
  $('#yk-mt-button-meal-nav-search').click(function (e) {

    e.preventDefault();

    yk_mt_meal_add_nav_show('search');

  });

  /**
   * Hide meal add forms and reset navigation
   */
  $('.yk-mt-button-reset-meal-nav').click(function (e) {

    e.preventDefault();

    yk_mt_meal_add_nav_reset();
  });

  /**
   * ---------------------------------------------------------------------------------------
   * External Form
   * ---------------------------------------------------------------------------------------
   */

  /*
      Initialise the Meal picker
   */
  function yk_mk_selectize_external_init() {

    // TODO: Need to check here? Do we need to re-init if already done?

    let yk_mt_meal_selector = $( '#yk-mt-search-external' ).selectize({
      preload: false,
      valueField: 'id',
      labelField: 'name',
      searchField: 'name',
      options: [],
      render: {
        option: function( item, escape) {

          let html = '<div class="external_list_item">';

          html = html + '<h6>';

          if ( item.ext_image ) {
            html = html + '<img src="' + escape( item.ext_image ) + '" width="96" align="left"/>';
          }

          html = html + escape( item.name ) + '</h6>';
          html = html + '<p class="description">' + escape( item.description ) + '</p>';
          html = html + '<p class="nutrition">' + escape( item.nutrition ) + '</p>';
          html = html + '<div>';

          return html;
        }
      },
      load: function (query, callback) {

        if ( '' == query ) {
          return;
        }

        this.clearOptions();

        $.ajax({
          url: yk_mt['ajax-url'],
          type: 'POST',
          data: { action: 'external_search', security: yk_mt['ajax-security-nonce'], search: query },
          error: function () {
            callback();
          },
          success: function (res) {

            if ( 'error' == res ) {

              yk_mt_warn( yk_mt_sc_meal_tracker['localise']['search-error'] );

              yk_mt_hide_add_buttons();

            } else if ( false === res || 0 == res ) {

              yk_mt_info( yk_mt_sc_meal_tracker['localise']['search-no-results'] );

              yk_mt_hide_add_buttons();
              $( '.yk-mt-add-new-meal-form-search-servings' ).fadeOut( 'slow' );

            } else if ( false === yk_mt_external_servings_enabled() ) {
              $( '.yk-mt-button-external-add-and-close, .yk-mt-button-external-add' ).fadeIn('slow');
            }

            callback( res );

          }
        });
      },
      onChange: function ( value ) {

        if ( false === yk_mt_external_servings_enabled() ) {
          return;
        }

        if ( '' !== value ) {
          yk_mk_selectize_external_servings_init( value );
        } else {
          $( '.yk-mt-add-new-meal-form-search-servings' ).fadeOut( 'slow' );
          yk_mt_hide_add_buttons();
        }
      }
    });
  }

  function yk_mt_hide_add_buttons() {
    $( '.yk-mt-button-external-add-and-close, .yk-mt-button-external-add' ).fadeOut('slow');
  }

  let yk_mt_servings_selector = false;

  function yk_mk_selectize_external_servings_init( food_id ) {

    $( '.yk-mt-add-new-meal-form-search-servings' ).fadeIn( 'slow' );

    if ( false !== yk_mt_servings_selector ) {
      $( '#yk-mt-search-external-servings' ).selectize()[0].selectize.destroy();
    }

    yk_mt_servings_selector = $( '#yk-mt-search-external-servings' ).selectize({
      preload:      true,
      create:       false,
      valueField:   'serving_id',
      labelField:   'display',
      options:      [],
      load: function (query, callback) {

        if ( '' == food_id ) {
          return;
        }

        this.clearOptions();

        $.ajax({
          url: yk_mt['ajax-url'],
          type: 'POST',
          data: { action: 'external_servings', security: yk_mt['ajax-security-nonce'], search: food_id },
          error: function () {
            callback();
          },
          success: function (res) {

            if ( 'error' == res ) {

              yk_mt_warn( yk_mt_sc_meal_tracker['localise']['search-error'] );

              yk_mt_hide_add_buttons();

            } else if ( false === res || 0 == res ) {

              yk_mt_info( yk_mt_sc_meal_tracker['localise']['search-no-results'] );

              yk_mt_hide_add_buttons();

            } else {
              $( '.yk-mt-button-external-add-and-close, .yk-mt-button-external-add' ).fadeIn('slow');
            }

            callback( res );

          }
        });
      },
      onChange: function ( value ) {

        if ( '' !== value && true === yk_mt_external_servings_enabled() ) {
          $( '.yk-mt-button-external-add-and-close, .yk-mt-button-external-add' ).fadeIn('slow');
        }
      }
    });
  }

  /**
   * Take the meal from the External API, trigger it to be copied to the user's meal collection.
   */
  $('.yk-mt-button-external-add').click(function (e) {

    e.preventDefault();

    let ext_select  = $( '#yk-mt-search-external' )[0].selectize;
    let selected_id = ext_select.getValue();
    let label       = ext_select.getItem( ext_select.getValue() )[0].innerHTML;

    // No meal has been selected.
    if ( '' === selected_id ) {
      return;
    }

    if ( false !== yk_mt_servings_selector ) {
        let serving_select  = $( '#yk-mt-search-external-servings' ).selectize()[0].selectize;
        let selected_id     = serving_select.getValue();

      if ( '' !== selected_id ) {
        label += ' - ' + serving_select.getItem( serving_select.getValue() )[0].innerHTML;
      }
    }

    // Do we wish to auto close?
    let auto_close  = ( 'yk-mt-button-external-meal-add-close' === $(this).attr('id') );
    let data        = { meal_id: selected_id, name: label, close: auto_close };

    // Was a serving selected?
    if ( true === yk_mt_external_servings_enabled() ) {

      let ext_serving = $( '#yk-mt-search-external-servings' )[0].selectize;
      let serving_id  = ext_serving.getValue();

      // No serving has been selected.
      if ( '' === serving_id ) {
        yk_mt_warn( yk_mt_sc_meal_tracker['localise']['serving-missing'] );
        return;
      }

      data[ 'serving_id' ] = serving_id;
    }

    yk_mt_post('external_add_to_collection', data, yk_mt_post_api_external_add_to_collection_callback );

  });

/**
 * Handle the call back to adding a meal
 * @param data
 * @param response
 */
function yk_mt_post_api_external_add_to_collection_callback( data, response ) {

  if ( false === response['error'] ) {

    yk_mt_success(yk_mt_sc_meal_tracker['localise']['search-added'] );

    // Add automatically to meal collection selector and select.
    let new_option = { id: response[ 'meal_id'], name: data[ 'name' ] };

    yk_mk_selectize_add_option( new_option );

    // Close search and return?
    if ( true === data[ 'close' ] ) {
      yk_mt_meal_add_nav_reset();
    }

  } else {
    yk_mt_warn( yk_mt_sc_meal_tracker['localise']['db-error'] );
  }
}

  /**
   * ---------------------------------------------------------------------------------------
   * Save Settings form
   * ---------------------------------------------------------------------------------------
   */

  // TODO: Add localise variable for calling all this

  $('#yk-mt-settings-form').submit(function (e) {

    e.preventDefault();

    let data = {};

    $('#yk-mt-settings-form input[type=number], #yk-mt-settings-form select').each(function () {
      data[$(this).attr('id')] = $(this).val();
    });

    yk_mt_post('save_settings', data, yk_mt_post_api_save_settings_callback);
  });

  function yk_mt_post_api_save_settings_callback(data, response) {

    if (false === response['error']) {

      yk_mt_success( yk_mt_sc_meal_tracker['localise']['settings-saved-success'] );

      setTimeout(function () {
        window.location.replace(yk_mt['page-url']);
      }, 600);

    } else {
      $('body').trigger('meal-tracker-save-error');
    }
  }

  $('#yk-mt-calorie-source').change(function () {
    yk_mt_settings_show_hide();
  });

  /**
   * Show  / Hide setting fields dependant on selected fields
   */
  function yk_mt_settings_show_hide() {

    if ('own' === $('#yk-mt-calorie-source').val()) {
      $('#yk-mt-allowed-calories').prop('required', true);
      $('#yk-mt-allowed-calories-row').show(200);
    } else {
      $('#yk-mt-allowed-calories').prop('required', false);
      $('#yk-mt-allowed-calories-row').hide(200);
    }
  }

  yk_mt_settings_show_hide();

  /**
   * ------ ---------------------------------------------------------------------------------
   * Helper functions
   * ---------------------------------------------------------------------------------------
   */

  /**
   * Fetch all enabled meta fields
   * @returns {boolean|*}
   */
  function yk_mt_meta_fields() {

    if (false === yk_mt_meal_tracker_found) {
      return false;
    }

    if ('undefined' === typeof (yk_mt_sc_meal_tracker['meta-fields'])) {
      return false;
    }

    return yk_mt_sc_meal_tracker['meta-fields'];
  }

  /*
      Store some temp data against the shortcode div
   */
  function yk_mt_temp_store_set(key, value) {
    $('#yk-mt-shortcode-meal-tracker').attr('yk-mt-' + key, value);
  }

  /*
     Fetch some temp data against the shortcode div
  */
  function yk_mt_temp_store_get(key) {
    return $('#yk-mt-shortcode-meal-tracker').attr('yk-mt-' + key);
  }

  /**
   * Is this a unit that we should hide quantity for?
   * @param key
   * @returns bool
   */
  function yk_mt_hide_quantity(key) {
    return (-1 !== $.inArray(key, yk_mt['units-hide-quantity']));
  }

  /**
   * Add yk-mt-clickable to a button to make it clickable
   */
  $('.yk-mt-clickable').click(function (e) {

    e.preventDefault();

    let url = $(this).attr('href');

    window.location.replace(url);
  });

  /**
   * ------ ---------------------------------------------------------------------------------
   * HTML Templates and Rendering
   * ---------------------------------------------------------------------------------------
   */

  /**
   * HTML for a Meal row (within data table)
   * @param meal_entry_id
   * @param meal_type
   * @param name
   * @param calories
   * @param quantity
   * @returns {string}
   * @constructor
   */
  const MealRow = ({meal_entry_id, meal_type, name, calories, quantity, d, id, css_class}) => `
                        <div class="yk-mt__table-row" data-mt="${meal_type}">
                            <div class="yk-mt__table-cell yk-mt__table-cell-meal">
                                <span class="yk-mt__meal-name">${name}</span>
                            </div>
                            <div class="yk-mt__table-cell yk-mt__table-cell-quantity yk-mt-cq">
                                <span class="yk-mt__meal-data">${d}</span>
                            </div>
                            <div class="yk-mt__table-cell yk-mt__table-cell-controls yk-mt-o">
                                <div class="yk-mt__btn-group yk-mt-inline-flex">
                                    <button data-meal-id="${id}" class="yk-mt-act-r yk-mt-act-r--edit yk-mt-hide-if-not-pro yk-mt-meal-button-edit-inline ${css_class}" >
                                        <span class="fa fa-edit"></span>
                                        <span class="yk-mt-r__text">${yk_mt_sc_meal_tracker['localise']['edit-text']}</span>
                                    </button>
                                    <button data-id="${meal_entry_id}" class="yk-mt-act-r yk-mt-act-r--remove" onclick="yk_mt_trigger_meal_entry_delete( ${meal_entry_id} )">
                                        <span class="fa fa-close"></span>
                                        <span class="yk-mt-r__text">${yk_mt_sc_meal_tracker['localise']['remove-text']}</span>
                                    </button>
                                </div>
                            </div>
                        </div>`;

  /**
   * HTML to provide a total row
   * @param total
   * @param unit
   * @returns {string}
   * @constructor
   */
  const SummaryRow = ({total, unit, meta_totals}) => `
                        <div class="yk-mt__table-row yk-mt__table-row-totals">
                                <div class="yk-mt__table-cell yk-mt__table-cell-total-text">${yk_mt_sc_meal_tracker['localise']['total']}:</div>
                                <div class="yk-mt__table-cell yk-mt__table-cell-total yk-mt-cq">
                                    ${total}${unit}
                                    <span class="yk-mt-meta-totals yk-mt-hide-if-meta-disabled">
                                      ${meta_totals}
                                    </span>
                                </div>
                                <div class="yk-mt__table-cell yk-mt-o">
                                </div>
                        </div>`;

  /**
   * Render all meals for a given meal type
   * @param table_id
   * @param meals
   * @param total
   */
  function yk_mt_render_meal_rows(table_id, meals, total, meta_totals) {

    let html = '<p class="yk-mt__no-meals">' + yk_mt_sc_meal_tracker['localise']['no-data'] + '.' + '</p>';

    if (0 !== meals.length) {

      // Get HTML for all meal rows
      html_meals = meals.map(MealRow).join('');

      total = [ { total: total, unit: yk_mt_sc_meal_tracker['localise']['calorie-unit'], meta_totals: meta_totals } ];

      // Get HTML for total row
      html_total = total.map(SummaryRow).join('');

      html = html_meals + html_total;
    }

    $('#meals-table-' + table_id).html(html);
  }

  /**
   * Take an entry in JSON format and render into UI
   * @param entry
   */
  function yk_mt_render_entry(entry, just_chart = false) {

    if (typeof entry !== 'object') {
      return;
    }

    yk_mt_loading_start();

    // Render meal rows under each meal type
    if ( false === just_chart ) {
      $.each(entry.meals, function (meal_type_id, meals) {
        yk_mt_render_meal_rows(meal_type_id, meals, entry.counts[meal_type_id], entry.meta_counts[meal_type_id][ 'summary' ] );
      });
    }

    yk_mt_chart_data_set(entry['calories_allowed'],
      entry['calories_remaining'],
      entry['calories_used'],
      entry['percentage_used'],
      entry['chart_title']
    );

    yk_mt_chart_render();

    yk_mt_loading_stop();
  }

  // Are we on a shortcode page and have initial data to load?
  if (true === yk_mt_meal_tracker_show && yk_mt_sc_meal_tracker['load-entry']) {
    yk_mt_render_entry(yk_mt_sc_meal_tracker ['todays-entry']);
  }

  /**
   * ------ ---------------------------------------------------------------------------------
   * Loading Overlay
   * ---------------------------------------------------------------------------------------
   */

  function yk_mt_loading_start() {
    $.LoadingOverlay("show");

    setTimeout(function () {
      yk_mt_loading_stop();
    }, 3000);
  }

  function yk_mt_loading_stop() {
    $.LoadingOverlay("hide");
  }

  $('body').on('meal-tracker-ajax-started', function (event) {

    // AC: Set time out here? If "loading" for more than x seconds then hide and show error?
    // note: to cause it to fail, just remove an AJAX hook
    yk_mt_loading_start();
  });

  $('body').on('meal-tracker-ajax-finished', function (event) {
    yk_mt_loading_stop();
  });

  /**
   * ------ ---------------------------------------------------------------------------------
   * Notifications
   * ---------------------------------------------------------------------------------------
   */

  function yk_mt_warn(text, selector = null) {
    yk_mt_notification(text, 'error', selector);
  }

  function yk_mt_info(text, selector = null) {
    yk_mt_notification(text, 'info', selector);
  }

  function yk_mt_success(text, selector = null) {
    yk_mt_notification(text, 'success', selector);
  }

  function yk_mt_notification(text, type = 'warn', selector = null) {

    let options = {position: 'bottom right', className: type};

    if (null === selector) {
      $.notify(text, options);
    } else {
      $(selector).notify(text, options);
    }
  }
});

/**
 * This is a wee hack to fire an event for links clicked to remove a meal from an entry
 * @param meal_entry_id
 */
function yk_mt_trigger_meal_entry_delete(meal_entry_id) {
  jQuery('body').trigger('meal-tracker-meal-entry-delete', [meal_entry_id]);
}

/**
 * Are servings enabled for this
 * @returns {boolean|boolean}
 */
function yk_mt_external_servings_enabled() {
  return ( 'undefined' !== typeof( yk_mt[ 'external-show-servings' ] ) && '1' === yk_mt[ 'external-show-servings' ] );
}
