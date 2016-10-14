<?php

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

use WP_CLI\Utils;

/**
 * Generate a Movefile for Wordmove.
 *
 * @subpackage commands/community
 * @maintainer Takayuki Miyauchi
 */
class WP_CLI_Scaffold_Movefile extends WP_CLI_Command
{
	private $exclude = array(
		'.git/',
		'.gitignore',
		'.sass-cache/',
		'bin/',
		'tmp/*',
		'Gemfile*',
		'Movefile',
		'wp-config.php',
		'wp-content/*.sql',
	);

	/**
	 * Generate a Movefile for Wordmove.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Overwrite Movefile that already exist.
	 *
	 * ## EXAMPLES
	 *
	 *     wp scaffold movefile
	 *
	 *     wp scaffold movefile --environment=production
	 *
	 *     wp scaffold movefile --movefile=/path/to/Movefile
	 *
	 */
	function __invoke( $args, $assoc_args )
	{
		$vars = array(
			'site_url' => site_url(),
			'wordpress_path' => WP_CLI::get_runner()->config['path'],
			'db_name' => DB_NAME,
			'db_user' => DB_USER,
			'db_pass' => DB_PASSWORD,
			'db_host' => DB_HOST,
			'db_charset' => DB_CHARSET,
		);

		$movefile = Utils\mustache_render(
			dirname( __FILE__ ) . '/templates/Movefile.mustache',
			$vars
		);

		if ( empty( $args[0] ) ) {
			$filename = getcwd() . "/Movefile";
		} else {
			$filename = $args[0];
		}

		$force = \WP_CLI\Utils\get_flag_value( $assoc_args, 'force' );
		$result = $this->create_file( $filename, $movefile, $force );

		if ( $result ) {
			WP_CLI::success( $filename );
		} else {
			WP_CLI::success( "Movefile wasn't overwrited." );
		}
	}

	private function create_file( $filename, $contents, $force )
	{
		$wp_filesystem = $this->init_wp_filesystem();

		$should_write_file = $this->prompt_if_files_will_be_overwritten( $filename, $force );
		if ( $should_write_file ) {
			$wp_filesystem->mkdir( dirname( $filename ) );
			if ( ! $wp_filesystem->put_contents( $filename, $contents ) ) {
				WP_CLI::error( "Error creating file: $filename" );
			}
			return true;
		}

		return false;
	}

	/**
	 * Initialize WP Filesystem
	 */
	private function init_wp_filesystem()
	{
		global $wp_filesystem;
		WP_Filesystem();
		return $wp_filesystem;
	}

	private function prompt_if_files_will_be_overwritten( $filename, $force )
	{
		$should_write_file = false;
		if ( ! file_exists( $filename ) ) {
			return true;
		}
		WP_CLI::warning( 'File already exists.' );
		if ( $force ) {
			$should_write_file = true;
		} else {
			$should_write_file = cli\confirm( 'Do you want to overwrite', false );
		}

		return $should_write_file;
	}
}

WP_CLI::add_command( 'scaffold movefile', 'WP_CLI_Scaffold_Movefile'  );
