<?php

WP_CLI::add_command( 'ppc stats', 'ppc_cli_stats' );

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
 * : User ID to generate stats as (for personalized settings purposes). If --author is provided, it is also used as default user. Otherwise, it defaults to 1 (usually an admin).
 *
 * [--cache-full]
 * : Cache full stats snapshot. Cached stats are stored in cache/ folder, and *not removed automatically*.
 *
 * ## EXAMPLES
 *
 *     $ wp ppc stats --time-start=1 --cache-full
 *     Generates general stats for all users from the very beginning of time and cache them.
 */
function ppc_cli_stats( $args, $assoc_args ) {
	global $ppc_global_settings;

    if( isset( $assoc_args['cache-full'] ) ) {
        global $CLI_PPC_CACHE;
        $CLI_PPC_CACHE = true; // read by cache retrieval to ignore existing cache when building anew
    }

	$begin = time();

	WP_CLI::line( "Now loading stats..." );

	$general_settings = PPC_general_functions::get_settings( 'general' );
	$cache_slug = 'ppc_stats';

	//Initiliaze counting types
	$ppc_global_settings['counting_types_object'] = new PPC_counting_types();
	$ppc_global_settings['counting_types_object']->register_built_in_counting_types();

	PPC_general_functions::get_default_stats_time_range( $general_settings );

	//Try to parse dates - fallback to default ones if fail
	if( isset( $assoc_args['time-start'] ) )
		$assoc_args['time-start'] = @strtotime( $assoc_args['time-start'] );
	else
		$assoc_args['time-start'] = $ppc_global_settings['stats_tstart'];

	if( isset( $assoc_args['time-end'] ) )
		$assoc_args['time-end'] = @strtotime( $assoc_args['time-end'].' 23:59:59' );
	else
		$assoc_args['time-end'] = $ppc_global_settings['stats_tend'];

	$cache_slug .= '-tstart_'.$assoc_args['time-start'].'-tend_'.$assoc_args['time-end'];

	WP_CLI::line( 'Time range: '.date('Y-m-d', $assoc_args['time-start']).' - '.date( 'Y-m-d', $assoc_args['time-end'] ) );

	if( ! isset( $assoc_args['author'] ) ) {
		$assoc_args['author'] = null;
	} else {
		WP_CLI::line( 'For author: '.get_userdata( $assoc_args['author'] )->display_name.' (ID: '.$assoc_args['author'].')' );
		$cache_slug .= '-author_'.$assoc_args['author'];
		$assoc_args['author'] = array( (int) $assoc_args['author'] );
	}

	//Set current user. Needed for personalized settings & the like
	if( ! isset( $assoc_args['as-user'] ) ) {
		if( $assoc_args['author'] != null )
			$assoc_args['as-user'] = current( $assoc_args['author'] );
		else
			$assoc_args['as-user'] = 1;
	}

	$cache_slug .= '-as-user_'.$assoc_args['as-user'];

	WP_CLI::line( 'As user: '.get_userdata( $assoc_args['as-user'] )->display_name.' (ID: '.$assoc_args['as-user'].')' );

	wp_set_current_user( (int) $assoc_args['as-user'] );

	WP_CLI::line( "\nThis may take a while... \n" );
	$stats = PPC_generate_stats::produce_stats( (int) $assoc_args['time-start'], (int) $assoc_args['time-end'], $assoc_args['author'] );

	if( ! is_wp_error( $stats ) ) {
		if( isset( $assoc_args['cache-full'] ) ) {
			if( ! is_dir( $ppc_global_settings['dir_path'].'cache' ) )
				mkdir( $ppc_global_settings['dir_path'].'cache' );

			$cache_data = array(
				'stats' => $stats,
				'time' => current_time( 'timestamp' ),
				'args' => $assoc_args
			);
			$cache_outcome = (bool) file_put_contents( $ppc_global_settings['dir_path'].'cache/'.$cache_slug, serialize( $cache_data ) );
			$cache_size = round( strlen( serialize( $stats ) ) / 1024 / 1024, 2 );
			WP_CLI::line( "Cached stats with slug $cache_slug, size $cache_size MB, outcome $cache_outcome" );
		}

		$duration = time() - $begin;

		@WP_CLI::success( "Stats generated in $duration seconds. \nVisit them at: ".admin_url( $ppc_global_settings['stats_menu_link']."&tstart=".$assoc_args['time-start']."&tend=".$assoc_args['time-end']."&cache-full&author=".$assoc_args['author'][0] ) );
	} else {
		WP_CLI::line( "Error: ".$stats->get_error_message() );
	}
}
