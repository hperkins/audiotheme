<?php
/**
 * Videos module.
 *
 * @package AudioTheme\Videos
 * @since 1.9.0
 */

/**
 * Videos module class.
 *
 * @package AudioTheme\Videos
 * @since 1.9.0
 */
class AudioTheme_Module_Videos extends AudioTheme_Module {
	/**
	 * Admin menu item HTML id.
	 *
	 * Used for hiding menu items when toggling modules.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $admin_menu_id = 'menu-posts-audiotheme_video';

	/**
	 * Whether the module is a core module.
	 *
	 * @since 1.9.0
	 * @var bool
	 */
	protected $is_core_module = true;

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {
		$this->set_name( __( 'Videos', 'audiotheme' ) );
		$this->set_description( __( 'Embed videos from services like YouTube and Vimeo to create your own video library.', 'audiotheme' ) );
	}
}