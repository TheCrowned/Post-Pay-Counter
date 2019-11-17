<?php

WP_CLI::add_command( 'ppc', 'Post_Pay_Counter_CLI' );

/**
 * Manage Post Pay Counter.
 */
class Post_Pay_Counter_CLI extends WP_CLI_Command {

	/**
	 * Generates stats for given parameters.
	 *
	 * ## OPTIONS
	 *
	 * [--time-start=<date>]
	 * : Time range start date in YYYY-MM-DD format.
	 *
	 * [--time-end=<date>]
	 * : Time range end date in YYYY-MM-DD format.
	 *
	 * [--author=<int>]
	 * : Author ID if you want to generate specific author stats.
	 *
	 * [--as-user=<int>]
	 * : User ID to generate stats as (for personalized settings purposes).
	 *
	 * [--cache-full]
	 * : Cache full stats snapshot. Cached stats are stored in cache/ folder, and *not removed automatically*.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp ppc stats --time-start=1 --cache-full
	 *     Generates general stats for all users from the very beginning of time and cache them.
	 */
	public function stats( $args, $assoc_args ) {
		global $ppc_global_settings;

		$begin = time();

		WP_CLI::line( "Now loading stats... This may take a while..." );

		$general_settings = PPC_general_functions::get_settings( 'general' );

		//Set current user. Needed for personalized settings & the like
		if( isset( $assoc_args['as-user'] ) )
			wp_set_current_user( (int) $assoc_args['as-user'] );

		$cache_slug = 'ppc_stats';

		//Initiliaze counting types
		$ppc_global_settings['counting_types_object'] = new PPC_counting_types();
		$ppc_global_settings['counting_types_object']->register_built_in_counting_types();

		PPC_general_functions::get_default_stats_time_range( $general_settings );

		//Try to parse dates - fallback to default ones if fail
		if( isset( $assoc_args['time-start'] ) )
			$assoc_args['time-start'] = @strtotime( $assoc_args['time-start'] );
		if( isset( $assoc_args['time-end'] ) )
			$assoc_args['time-end'] = @strtotime( $assoc_args['time-end'] );

		if( ! isset( $assoc_args['time-start'] ) OR $assoc_args['time-start'] <= 0 )
			$assoc_args['time-start'] = $ppc_global_settings['stats_tstart'];

		if( ! isset( $assoc_args['time-end'] ) OR $assoc_args['time-end'] <= 0 )
			$assoc_args['time-end'] = $ppc_global_settings['stats_tend'];

		$cache_slug .= '-tstart_'.$assoc_args['time-start'].'-tend_'.$assoc_args['time-end'];

		if( ! isset( $assoc_args['author'] ) ) {
			$assoc_args['author'] = null;
		} else {
			$cache_slug .= '-author_'.$assoc_args['author'];
			$assoc_args['author'] = array( (int) $assoc_args['author'] );
		}

		$stats = PPC_generate_stats::produce_stats( (int) $assoc_args['time-start'], (int) $assoc_args['time-end'], $assoc_args['author'] );

		if( ! is_wp_error( $stats ) ) {
			if( isset( $assoc_args['cache-full'] ) ) {
				if( ! is_dir( $ppc_global_settings['dir_path'].'cache' ) )
					mkdir( $ppc_global_settings['dir_path'].'cache' );

				$cache_data = array(
					'stats' => $stats,
					'time' => current_time( 'timestamp' ),
				);
				$cache_outcome = (bool) file_put_contents( $ppc_global_settings['dir_path'].'cache/'.$cache_slug, serialize( $cache_data ) );
				$cache_size = round( strlen( serialize( $stats ) ) / 1024 );
				WP_CLI::line( "Cached stats with slug $cache_slug, size $cache_size KB, outcome $cache_outcome" );
			}

			$duration = time() - $begin;

			WP_CLI::success( "Stats generated in $duration seconds. \nVisit them at: ".admin_url( $ppc_global_settings['stats_menu_link']."&tstart=".$assoc_args['time-start']."&tend=".$assoc_args['time-end']."&cache-full&author=".$assoc_args['author'][0] ) );
		} else {
			WP_CLI::line( "Error: ".$stats->get_error_message() );
		}
	}
}
