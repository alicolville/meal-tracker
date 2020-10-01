var is_premium = false;

jQuery( document ).ready(function ($) {

    if ( 'undefined' !== typeof yk_mt_settings &&
        yk_mt_settings[ 'premium' ] ) {
        is_premium = true;
    }

    /**
     * Post to AJAX back end
     * @param action
     * @param data
     * @param callback
     */
    function yk_mt_post(action, data, callback) {

      data['action']    = action;
      data['security']  = yk_mt_sc_meal_tracker['ajax-security-nonce'];

      jQuery.post( ajaxurl, data, function (response) {

        callback(data, response);
      });
    }

    /**
     * Fetch all enabled meta fields
     * @returns {boolean|*}
     */
    function yk_mt_meta_fields() {

      if ('undefined' === typeof (yk_mt_sc_meal_tracker['meta-fields'])) {
        return false;
      }

      return yk_mt_sc_meal_tracker['meta-fields'];
    }

    /**
     * Take an entry in JSON format and render into UI
     * @param entry
     */
    function yk_mt_render_entry( entry ) {

        if ( typeof entry !== 'object' ) {
            return;
        }

        yk_mt_chart_data_set( entry[ 'calories_allowed' ],
            entry[ 'calories_remaining' ],
            entry[ 'calories_used' ],
            entry[ 'percentage_used' ],
            entry[ 'chart_title' ]
        );

        yk_mt_chart_render();

    }

    // Are we on a shortcode page and have initial data to load?
    if ( 'undefined' !== typeof yk_mt_sc_meal_tracker &&
            yk_mt_sc_meal_tracker[ 'load-entry' ] ) {
        yk_mt_render_entry( yk_mt_sc_meal_tracker [ 'todays-entry' ] );
    }

    /**
     * Foo table
     */
    if ( 'object' === typeof FooTable ) {
        $( '.yk-mt-footable-basic' ).footable();
    }

    /**
     * Zozo Tabs
     */
    if ( $.fn.zozoTabs ) {
        $( '#yk-mt-tabs' ).zozoTabs({
            rounded: false,
            multiline: true,
            theme: "silver",
            size: "medium",
            responsive: true,
            animation: {
                effects: "slideH",
                easing: "easeInOutCirc",
                type: "jquery"
            }
        });
    }

    // Show / Hide Admin allowance form.
    if ( true === is_premium ) {

        $( '#yk-mt-calorie-source' ).change( function()  {

            yk_mt_calorie_source_form_update( $( '#yk-mt-calorie-source' ).val() );
        });
    }

    /**
     * Toggle admin allowance form in admin ui
     * @param source
     */
    function yk_mt_calorie_source_form_update( source ) {

        if ( 'admin' === source ) {
            $( '#yk-mt-admin-allowance' ).removeClass( 'yk-mt-hide' );
        } else {
            $( '#yk-mt-admin-allowance' ).addClass( 'yk-mt-hide' );
        }

    }

    /**
     * Confirmation dialog
     */
    $( '.yk-mt-button-confirm' ).click( function( e ) {

        e.preventDefault();

        var title   = $( this ).data( 'title' );
        var content = $( this ).data( 'content' );
        var url     = $( this ).attr( 'href' );

        if ( 'undefined' === typeof title ) {
            title = yk_mt_sc_meal_tracker[ 'localise' ][ 'confirm-title' ];
        }

        if ( 'undefined' === typeof content ) {
            content = yk_mt_sc_meal_tracker[ 'localise' ][ 'confirm-content' ];
        }

        if ( 'url' === typeof content ) {
            url = '#';
        }

        $.confirm({
            title: title,
            content: content,
            type: 'blue',
            boxWidth: '30%',
            useBootstrap: false,
            buttons: {
                confirm: function () {
                   location.href = url;
                },
                cancel: function () {

                }
            }
        });
    });

  /**
   * Toggle show / hide of quantity field dependant on unit selected
   * ( also in admin.js )
   */
  $('#yk-mt-add-meal-unit').change(function () {
    yk_mt_add_meal_form_show_quantity();
  });

  if ( 'meal' === yk_mt_sc_meal_tracker[ 'mode' ] ) {
    yk_mt_add_meal_form_show_quantity();
  }

  /**
   * Show  / Hide quantity field depending on the unit selected
   * ( also in admin.js )
   */
  function yk_mt_add_meal_form_show_quantity() {

    let value = $('#yk-mt-add-meal-unit').val();
    let quantity_row = $('#yk-mt-add-meal-quantity-row');

    if ( true === yk_mt_hide_quantity( value ) ) {
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
   * Is this a unit that we should hide quantity for?
   * @param key
   * @returns bool
   */
  function yk_mt_hide_quantity(key) {
    return (-1 !== $.inArray(key, yk_mt_sc_meal_tracker['units-hide-quantity']));
  }

  $('#yk-mt-form-add-new-meal').submit(function (event) {

    event.preventDefault();

    let name        = $('#yk-mt-add-meal-name').val();
    let description = $('#yk-mt-add-meal-description').val();
    let calories    = $('#yk-mt-add-meal-calories').val();
    let quantity    = $('#yk-mt-add-meal-quantity').val();
    let unit        = $('#yk-mt-add-meal-unit').val();
    let id          = $('#yk-mt-add-meal-meal-id').val();

    let meta_fields = yk_mt_meta_fields();

    // If we have meta fields, populate the object from form fields
    if ( false !== meta_fields) {
      $.each(meta_fields, function (index, value) {
        meta_fields[index] = $('#yk-mt-add-meal-' + index).val();
      });
    }

    yk_mt_post_api_add_meal(
      name,
      description,
      calories,
      quantity,
      unit,
      meta_fields,
      id
    );
  });

  /**
   * Add a new meal
   * @param name
   * @param description
   * @param calories
   * @param quantity
   * @param unit
   */

  function yk_mt_post_api_add_meal(name, description, calories, quantity, unit, meta_fields, id = '') {

    var data = {
      'admin-security' : yk_mt_sc_meal_tracker['ajax-admin-security-nonce'],
      'name': name,
      'description': description,
      'calories': calories,
      'quantity': quantity,
      'unit': unit,
      'id': id,
      'meta-fields': meta_fields
    };

    yk_mt_post('add_meal_admin', data, yk_mt_post_api_add_meal_callback);
  }

  /**
   * Handle the call back to adding a meal
   * @param data
   * @param response
   */
  function yk_mt_post_api_add_meal_callback(data, response) {

    if ( false === response['error'] ) {

      if ( '#' === yk_mt_sc_meal_tracker['previous-url'] ) {
        window.location.replace( yk_mt_settings[ 'meals-url' ] + '&added=y' );
      } else {
        window.location.replace( yk_mt_sc_meal_tracker['previous-url'] )
      }

    } else {
      alert( 'There was an error saving your meal' );
    }
  }

  /**
   * Add / Edit admin meal
   */
  $( '.yk-mt-button-reset-meal-nav' ).click( function( e ) {

    e.preventDefault();

    if ( '#' === yk_mt_sc_meal_tracker['previous-url'] ) {
      window.history.back();
    } else {
      window.location.replace( yk_mt_sc_meal_tracker['previous-url'] )
    }

  });

});
