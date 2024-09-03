<?php

defined('ABSPATH') or die('Naw ya dinnie!');

class YK_MT_EXT_WP_RECIPE_MAKER extends YK_MT_EXT_SOURCE {

	public function search( $terms ) {

		$this->search_reset();

		if ( false === $this->is_authenticated() ) {
			return false;
		}

		$args       = [ 'post_type' => 'wprm_recipe', 's' => $terms ];
		$query      = new WP_Query( $args );
		$results    = [];

		while ( $query->have_posts() ) {

			$query->the_post();

			$recipe = WPRM_Recipe_Manager::get_recipe( $query->ID );

			if ( false === empty( $recipe ) &&
			        false === empty( $recipe->calories() ) ) {
				$results[] = $recipe;
			}

		}
		wp_reset_postdata();

		$this->results 		= $results;
		$no_results         = count( $results );
		$this->no_results 	= $no_results;
		$this->page_number 	= $no_results;
		$this->page_size 	= $no_results;

		$this->results = array_map( array( $this, 'format_result' ), $this->results );

		return true;

	}

	public function servings( $id ) {

		$food = $this->get( $id );

		return ( false === empty( $food[ 'servings' ] ) ) ? $food[ 'servings' ] : [];
	}

	function format_result( $result ) {

		return [
			'name'			    => $result->name(),
			'description'	    => '', // $result->summary(),
			'calories'		    => $result->calories(),
			'meta_proteins'     => 0,
			'meta_fats'		    => 0,
			'meta_carbs'	    => 0,
			'ext_id'		    => $result->id(),
			'ext_url'		    => '',
			'ext_image'		    => $result->image_url(),
			'quantity'		    => $result->servings(),
			'unit'			    => $result->servings_unit(),
			'source'		    => 'wp-recipe-maker',
			'hide-nutrition'    => 'yes',
			'servings'          => ( false === empty( $result->servings() ) ) ? $result->servings() : []
		];
	}

	/**
	 * This isn't supported as there is no need to fetch a recipe at the moment.
	 * @param $id
	 * @return bool|mixed
	 */
	public function get( $id ){


		$args       = [ 'post_type' => 'wprm_recipe', 'p' => $id ];
		$query      = new WP_Query( $args );

		if( $query->have_posts() ) {

			$query->the_post();

			$recipe = WPRM_Recipe_Manager::get_recipe( $query->ID );

			wp_reset_postdata();

			return $this->format_result( $recipe );

		}

		return NULL;
	}

	private function serving_format( $serving ) {

		$serving[ 'display' ] = sprintf( '%s - %s %s', $serving[ 'serving_description' ], $serving[ 'calories' ], esc_html__( 'kcal', 'meal-tracker' ) );

		return $serving;
	}

	public function is_authenticated(){
		return yk_mt_ext_source_wprm_enabled();
	}

	protected function authenticate() {
		return yk_mt_ext_source_wprm_enabled();
	}
}
