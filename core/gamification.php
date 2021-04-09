<?php

defined('ABSPATH') or die( 'Jog on!' );

/*
 * Gamification - support for myCred
 *
 * https://codex.mycred.me/chapter-vi/functions/
 */

/**
 * Add hooks for Meal Entry added as well as a meal added to an entry
 * @param $installed
 * @param $point_type
 *
 * @return mixed
 */
function yk_mt_mycred_add_hooks( $installed, $point_type ) {

	// Weight added
	$installed[ 'yk_mt_entry_new' ] = [	'title'        => __( 'Meal Tracker: Entry Added', YK_MT_SLUG ),
	                                        'description'  => __( 'Reward a user when they start a new meal entry.', YK_MT_SLUG ),
	                                        'callback'     => [ 'yk_mt_mycred_entry_added_class' ]
	];

	// Meal added to entry
	$installed[ 'yk_mt_meal_added' ] = 	[	'title'        => __( 'Meal Tracker: Meal added to an entry', YK_MT_SLUG ),
	                                        'description'  => __( 'Reward a user when they add a meal to their entry', YK_MT_SLUG ),
	                                        'callback'     => [ 'yk_mt_mycred_meal_added_to_entry_class' ]
	];

	return $installed;

}
add_filter( 'mycred_setup_hooks', 'yk_mt_mycred_add_hooks', 10, 2 );

/**
 * Load custom myCred hooks
 */
function yk_mt_mycred_load_hooks() {

	// Entry added
	class yk_mt_mycred_entry_added_class extends myCRED_Hook {

		/**
		 * Construct
		 * Used to set the hook id and default settings.
		 */
		function __construct( $hook_prefs, $type ) {

			parent::__construct( [
									'id'       => 'yk_mt_entry_new',
									'defaults' => [ 'yk_mt_entry_new'    => [	'creds'  => 10,
									                                              'log'    => __( 'Entry added', YK_MT_SLUG ),
									                                              'limit'  => '0/x' ]
									]
			], $hook_prefs, $type );
		}

		/**
		 * Run
		 * Fires by myCRED when the hook is loaded.
		 * Used to hook into any instance needed for this hook
		 * to work.
		 */
		public function run() {
			// Hook on to new entry
			add_action( 'yk_mt_entry_added', [ $this, 'entry_added' ], 10, 3 );
		}

		/**
		 * Add award for new entry
		 *
		 * do_action( 'yk_mt_entry_new', $id, $entry, $entry[ 'user_id' ] );
		 *
		 * @param $entry
		 */
		public function entry_added( $entry, $id, $user_id ) {

			// Have we reached the limit defined by admin against the myCred hook?
			if ( true === $this->over_hook_limit( 'yk_mt_entry_new', 'yk_mt_entry_new', $user_id ) ) {
				return;
			}

			$this->core->add_creds(	'yk_mt_entry_new',
				$user_id,
				$this->prefs[ 'yk_mt_entry_new' ][ 'creds' ],
				$this->prefs[ 'yk_mt_entry_new' ][ 'log' ],
				'yk_mt_entry_new'
			);
		}

		/**
		 * Hook Settings
		 * Needs to be set if the hook has settings.
		 */
		public function preferences() {

			// Our settings are available under $this->prefs
			$prefs = $this->prefs;

			?>

			<label class="subheader"><?php _e( 'Log template', YK_MT_SLUG ); ?></label>
			<ol>
				<li>
					<div class="h2"><input type="text" name="<?php echo $this->field_name( [ 'yk_mt_entry_new' => 'log' ] ); ?>" id="<?php echo $this->field_id( [ 'yk_mt_entry_new' => 'log' ] ); ?>" value="<?php echo esc_attr( $prefs[ 'yk_mt_entry_new' ][ 'log' ] ); ?>" class="long" /></div>
					<span class="description"></span>
				</li>
			</ol>
			<label class="subheader"><?php _e( 'Points', YK_MT_SLUG ); ?></label>
			<ol>
				<li>
					<div class="h2"><input type="number" name="<?php echo $this->field_name( [ 'yk_mt_entry_new' => 'creds' ] ); ?>" id="<?php echo $this->field_id( [ 'yk_mt_entry_new' => 'creds' ] ); ?>" value="<?php echo esc_attr( $prefs['yk_mt_entry_new']['creds'] ); ?>" class="long" /></div>
				</li>
			</ol>
			<label class="subheader"><?php _e( 'Limit', YK_MT_SLUG ); ?></label>
			<ol>
				<li>
					<div class="h2"><?php echo $this->hook_limit_setting( $this->field_name( [ 'yk_mt_entry_new' => 'limit' ] ), $this->field_id( [ 'yk_mt_entry_new' => 'limit' ]  ), $prefs['yk_mt_entry_new']['limit'] ); ?></div>
					<span class="description"><?php _e( 'Limit the number of times this award can be given within the specified time limit.', YK_MT_SLUG ); ?></span>
				</li>
			</ol>

			<?php
		}

		/**
		 * Sanitize Preferences
		 * If the hook has settings, this method must be used
		 * to sanitize / parsing of settings.
		 */
		public function sanitise_preferences( $data ) {

			if ( isset( $data['yk_mt_entry_new']['limit'] ) && isset( $data['yk_mt_entry_new']['limit_by'] ) ) {

				$limit = sanitize_text_field( $data['yk_mt_entry_new']['limit'] );
				if ( $limit == '' ) {
					$limit = 0;
				}
				$data['yk_mt_entry_new']['limit'] = $limit . '/' . $data['yk_mt_entry_new']['limit_by'];
				unset( $data['yk_mt_entry_new']['limit_by'] );
			}

			return $data;
		}
	}

	// Meal added to award
	class yk_mt_mycred_meal_added_to_entry_class extends myCRED_Hook {

		/**
		 * Construct
		 * Used to set the hook id and default settings.
		 */
		function __construct( $hook_prefs, $type ) {

			parent::__construct( [
				'id'       => 'yk_mt_meal_added',
				'defaults' => [ 'yk_mt_meal_added'    => [	'creds'  => 10,
				                                            'log'    => __( 'Meal added to entry', YK_MT_SLUG ),
				                                            'limit'  => '0/x'
															]
				]
			], $hook_prefs, $type );

		}

		/**
		 * Run
		 * Fires by myCRED when the hook is loaded.
		 * Used to hook into any instance needed for this hook
		 * to work.
		 */
		public function run() {
			// Hook on to MT add a meal to an entry
			add_action( 'yk_mt_meal_added_to_entry', [ $this, 'meal_added' ], 10, 1 );
		}

		/**
		 * Add award for Weight entry add
		 * @param $entry
		 */
		public function meal_added( $user_id ) {

			// Have we reached the limit defined by admin against the myCred hook?
			if ( true === $this->over_hook_limit( 'yk_mt_meal_added', 'yk_mt_meal_added', $user_id ) ) {
				return;
			}

			$this->core->add_creds(	'yk_mt_meal_added',
				$user_id,
				$this->prefs[ 'yk_mt_meal_added' ][ 'creds' ],
				$this->prefs[ 'yk_mt_meal_added' ][ 'log' ],
				'yk_mt_meal_added'
			);
		}

		/**
		 * Hook Settings
		 * Needs to be set if the hook has settings.
		 */
		public function preferences() {

			// Our settings are available under $this->prefs
			$prefs = $this->prefs;

			?>

			<label class="subheader"><?php _e( 'Log template', YK_MT_SLUG ); ?></label>
			<ol>
				<li>
					<div class="h2"><input type="text" name="<?php echo $this->field_name( [ 'yk_mt_meal_added' => 'log' ] ); ?>" id="<?php echo $this->field_id( [ 'yk_mt_meal_added' => 'log' ] ); ?>" value="<?php echo esc_attr( $prefs[ 'yk_mt_meal_added' ][ 'log' ] ); ?>" class="long" /></div>
					<span class="description"></span>
				</li>
			</ol>
			<label class="subheader"><?php _e( 'Points', YK_MT_SLUG ); ?></label>
			<ol>
				<li>
					<div class="h2"><input type="number" name="<?php echo $this->field_name( [ 'yk_mt_meal_added' => 'creds' ] ); ?>" id="<?php echo $this->field_id( [ 'yk_mt_meal_added' => 'creds' ] ); ?>" value="<?php echo esc_attr( $prefs['yk_mt_meal_added']['creds'] ); ?>" class="long" /></div>
				</li>
			</ol>
			<label class="subheader"><?php _e( 'Limit', YK_MT_SLUG ); ?></label>
			<ol>
				<li>
					<div class="h2"><?php echo $this->hook_limit_setting( $this->field_name( [ 'yk_mt_meal_added' => 'limit' ] ), $this->field_id( [ 'yk_mt_meal_added' => 'limit' ]  ), $prefs['yk_mt_meal_added']['limit'] ); ?></div>
					<span class="description"><?php _e( 'Limit the number of times this award can be given within the specified time limit.', YK_MT_SLUG ); ?></span>
				</li>
			</ol>

			<?php
		}

		/**
		 * Sanitize Preferences
		 * If the hook has settings, this method must be used
		 * to sanitize / parsing of settings.
		 */
		public function sanitise_preferences( $data ) {

			if ( isset( $data['yk_mt_meal_added']['limit'] ) && isset( $data['yk_mt_meal_added']['limit_by'] ) ) {

				$limit = sanitize_text_field( $data['yk_mt_meal_added']['limit'] );
				if ( $limit == '' ) {
					$limit = 0;
				}
				$data['yk_mt_meal_added']['limit'] = $limit . '/' . $data['yk_mt_meal_added']['limit_by'];
				unset( $data['yk_mt_meal_added']['limit_by'] );
			}

			return $data;
		}
	}
}
add_action( 'mycred_load_hooks', 'yk_mt_mycred_load_hooks' );

/**
 * Expand custom references in log filters
 * @param $list
 *
 * @return mixed
 */
function yk_mt_mycred_custom_references( $list ) {

	$list[ 'yk_mt_entry_new' ] 	= 'Meal Tracker: Entry Added';
	$list[ 'yk_mt_meal_added' ] = 'Meal Tracker: Meal added to an entry';

	return $list;
}
add_filter( 'mycred_all_references', 'yk_mt_mycred_custom_references' );
