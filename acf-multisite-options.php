<?php
/*
Plugin Name: ACF Multisite Options
Plugin URI: https://owlwatch.com
Description: Allow multisite options pages
Author: Mark Fabrizio
Version: 2.0.0
Author URI: http://owlwatch.com/
*/
namespace ACF\Multisite\Options;

/**
 * This plugin provides functionality for Network level options
 * pages using the normal ACF API
 */
class Plugin
{

	/**
	 * flag used to indiciate when we need to filter options pages
	 * @var boolean
	 */
	private $_filter_options_pages = false;

	/**
	 * capture 'network' pages 
	 */
	private $_network_page = [];

	/**
	 * cache the current site
	 */
	private $_current_site;

	/**
	 * Singleton pattern
	 */
	public static function getInstance()
	{
		static $instance;
		if( !isset( $instance ) ){
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Wait until plugins are loaded so we can ensure ACF is available
	 */
	protected function __construct()
	{
		add_action( 'acf/init', [$this, 'init'] );
	}

	/**
	 * Setup the plugin hooks
	 * @return void
	 */
	public function init()
	{
		
		if( !function_exists('acf_options_page') ){
			return;
		}

		$this->_current_site = get_current_site();
		$acf_admin_options_page = $this->get_acf_admin_options_page();

		if( is_admin() && $acf_admin_options_page ){

			// Run the ACF Options admin_menu function on network menu
			add_action( 'network_admin_menu', [$acf_admin_options_page, 'admin_menu'], 99, 0 );

			// Filter out pages by "network" attribute when loading the pages
			// for the admin_menu depending on the context
			add_action( 'admin_menu', [$this, 'before_admin_menu'], 1 );
			add_action( 'network_admin_menu', [$this, 'before_admin_menu'], 1 );
			add_filter( 'acf/get_options_pages', [$this, 'filter_options_pages'] );

		}

		add_filter('acf/validate_options_page', [$this, 'capture_network_pages'], 3000 );
		add_filter('acf/pre_load_post_id', [$this, 'convert_post_id'], 10, 2);
		
		foreach(['image','relationship','post_object'] as $type){
			// Wrap some fields with "switch_to_blog()" calls to retrieve images/ posts
			add_filter( 'acf/format_value/type='.$type, [$this, 'format_value_start'], 1, 3 );
			add_filter( 'acf/format_value/type='.$type, [$this, 'format_value_end'], 999, 3 );
		}

	}

	public function capture_network_pages( $page )
	{
		if( isset($page['network']) && $page['network'] ){
			$this->_network_pages[$page['post_id']] = $page;
		}
		return $page;
	}

	public function convert_post_id( $preload, $post_id )
	{
		if( isset( $this->_network_pages[$post_id]) ){
			return 'site_'.$this->_current_site->id;
		}
		return $preload;
	}

	/**
	 * Sneaky way to get the instantiated "$acf_admin_options_page" object
	 * as it does not have a global reference.
	 *
	 * @return mixed Returns either the page array or false
	 */
	public function get_acf_admin_options_page()
	{
		static $acf_admin_options_page = null;
		if( isset( $acf_admin_options_page ) ){
			return $acf_admin_options_page;
		}
		$acf_admin_options_page = false;
		global $wp_filter;
		if( empty( $wp_filter['admin_menu'] ) || empty( $wp_filter['admin_menu'][99] ) ){
			return $acf_admin_options_page;
		}
		foreach( $wp_filter['admin_menu'][99] as $hook ){
			if( is_array( $hook['function'] ) && get_class( $hook['function'][0] ) === 'acf_admin_options_page' ){
				$acf_admin_options_page = $hook['function'][0];
				break;
			}
		}
		return $acf_admin_options_page;
	}

	/**
	 * Enabling page filtering for the acf_get_options_pages function
	 * when we are in the admin menu
	 * @return void
	 */
	public function before_admin_menu()
	{
		$this->_filter_options_pages = true;
	}

	/**
	 * Filter the options pages depending on the context, network or not network.
	 *
	 * @param  array  $pages ACF options pages
	 * @return array  The filtered pages.
	 */
	public function filter_options_pages( array $pages )
	{
		if( !$this->_filter_options_pages ){
			return $pages;
		}

		$this->_filter_options_pages = false;

		return array_filter( $pages, function( $page ){
			return is_network_admin() ? !empty($page['network']) : empty( $page['network'] );
		});
	}

	/**
	 * Retrieve an options page by its "post_id" attribute
	 *
	 * There could be multiple pages with the same post_id,
	 * but we require that network pages either share a post_id
	 * or each have their own.
	 *
	 * @param  string $post_id the ACF post_id
	 * @return mixed the options page or false if none found
	 */
	public function get_options_page_by_post_id( $post_id )
	{
		$pages = acf_get_options_pages();
		foreach( $pages as $page ){
			if( !empty( $page['post_id'] ) && $page['post_id'] === $post_id ){
				return $page;
			}
		}
		return null;
	}

	public function format_value_start( $value, $post_id, $field )
	{
		if( substr( $post_id, 0, 5) !== 'site_' ){
			return $value;
		}
		switch_to_blog( get_main_site_id() );
		return $value;
	}

	public function format_value_end( $value, $post_id, $field )
	{
		if( substr( $post_id, 0, 5) !== 'site_' ){
			return $value;
		}
		restore_current_blog();
		return $value;
	}

}

// Instantiate our plugin
Plugin::getInstance();
