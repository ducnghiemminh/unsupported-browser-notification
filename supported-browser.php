<?php
/*
Plugin Name: Unsupported Browser Notification
Plugin URI: Unsupported Browser Notification
Description: Check whether the user's browser is supported or not
Version:     1.0.0
Author:      Ownego Corp 
Author URI:  http://ownego.com
License: 	 GPL2
*/

if(! class_exists('OE_Supported_Browser')):

class OE_Supported_Browser {
	
	private $_name = 'oe_supported_browser';
    private $_file = __FILE__;    
	
	public function __construct() {
        $this->add_actions(array(
            array(
                'hook' => 'admin_menu',
                'callback' => array(&$this, 'plugin_menu')
            ),
            array(
                'hook' => 'admin_init',
                'callback' => array(&$this, 'register_settings')
            ),
            array(
                'hook' => 'template_redirect',
                'callback' => array(&$this, 'check_browser')
            ),
            array(
                'hook' => 'plugins_loaded',
                'callback' => array(&$this, 'register_lang')
            )
        ));
        $this->register_hooks();
	}
	
	/**
	 * Initialize data
	 */
	public function init() {
        
        // Browser versions data
		add_option('oe_supported_browser_options');
		$pluginOptions = array(
			'chrome' => range(4, 46),
			'firefox' => range(4, 41),
			'ie' => range(6, 11),
			'safari' => array(3.1, 3.2, 4, 5, 5.1, 6, 6.1, 7, 7.1, 8, 9),
			'opera' => array(10.5, 11.5, 12.1) + range(15, 32),
		);
		update_option('oe_supported_browser_options', $pluginOptions);
        
        // Set default settings
        update_option('oe_sb_opt_chrome', '32');
        update_option('oe_sb_opt_firefox', '28');
        update_option('oe_sb_opt_ie', '9');
        update_option('oe_sb_opt_safari', '5');
        update_option('oe_sb_opt_opera', '11.5');
        update_option('oe_sb_opt_background', '0');
        update_option('oe_sb_opt_message', __('We built our website using latest technology. This makes our website faster and easier to use. Unfortunately, your browser does not support those technology. Download one of these great browsers below and you will be on your way.'));
	}
	
	/**
	 * Activate plugin
	 */
	public function activate() {
		
		// Prevent unauthorized user from activating plugin
		if (!current_user_can('activate_plugins')) {
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "activate-plugin_{$plugin}" );
		
		// Activate plugin
		$this->check_version();
		$this->init();
	}
	
	/**
	 * Deactivate plugin
	 */
	public function deactivate() {
		
		// Prevent unauthorized user from deactivating plugin
		if (!current_user_can('activate_plugins')) {
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );
		
		// Deactivate plugin
		delete_option('oe_sb_opt_chrome'); 
		delete_option('oe_sb_opt_firefox');
		delete_option('oe_sb_opt_ie');
		delete_option('oe_sb_opt_safari');
		delete_option('oe_sb_opt_opera');
        delete_option('oe_sb_opt_background');
        delete_option('oe_sb_opt_message');
        delete_option('oe_supported_browser_options');
	}
	
	/**
	 * Upgrade plugin
	 */
	public function upgrade() {
		
	}
    
    /**
	 * Uninstall plugin
	 */
    public function uninstall() {
    	
    	// Prevent unauthorized user from uninstalling plugin
    	if (!current_user_can('activate_plugins')) {
    		return;
    	} 
    	check_admin_referer('bulk-plugins');
    	
    	// Important: Check if the file is the one
    	// that was registered during the uninstall hook.
    	if ($this->_file != WP_UNINSTALL_PLUGIN) {
    		return;
    	}
    	
    	// Uninstall plugin
        if (!defined('WP_UNINSTALL_PLUGIN')) {
			exit;
		}
    }
	
	/**
	 * Register language
	 */
	public function register_lang() {
		load_plugin_textdomain('oe-sb', false, dirname(plugin_basename($this->_file)) . '/langs/');	
	}
	
	/**
	 * Enqueue CSS & Javascript
	 */
	public function enqueue_scripts() {
		wp_register_style('oe_sb_app_css', $this->get_file_url('assets/css/app.css'), array(), '1.0.0', 'all');
		wp_register_script('oe_sb_app_js', $this->get_file_url('assets/js/app.js'), array('jquery'));
		wp_enqueue_style('oe_sb_app_css');
		wp_enqueue_script('oe_sb_app_js');
       
        // Loading dynamic settings
        $img_src = $this->get_file_url('assets/img');
        $settings = array(
            'browser' => array(
                'chrome' => array(
                    'name' => 'Google Chrome',
                    'icon' => $img_src . '/chrome.jpg',
                    'url' => 'https://www.google.com/chrome/browser/desktop/index.html',
                    'version' => get_option('oe_sb_opt_chrome')
                ),
                'firefox' => array(
                    'name' => 'Mozilla Firefox',
                    'icon' => $img_src . '/firefox.jpg',
                    'url' => 'https://www.mozilla.org/en-US/firefox/new/',
                    'version' => get_option('oe_sb_opt_firefox')
                ),
                'ie' => array(
                    'name' => 'Internet Explorer',
                    'icon' => $img_src . '/ie.jpg',
                    'url' => 'http://windows.microsoft.com/en-us/internet-explorer/download-ie',
                    'version' => get_option('oe_sb_opt_ie')
                ),
                'safari' => array(
                    'name' => 'Safari',
                    'icon' => $img_src . '/safari.jpg',
                    'url' => 'https://www.apple.com/safari/',
                    'version' => get_option('oe_sb_opt_safari')
                ),
                'opera' => array(
                    'name' => 'Opera',
                    'icon' => $img_src . '/opera.jpg',
                    'url' => 'http://www.opera.com/',
                    'version' => get_option('oe_sb_opt_opera')
                ),
            ),
            'msg' => array(
                'title' => ucwords(__('Improve Your Experience', 'oe-sb')),
                'content' => get_option('oe_sb_opt_message'),
            ),            
            'overlay_bg' => get_option('oe_sb_opt_background'),
            'version_text' => __('Version', 'oe-sb')
        );
        wp_localize_script('oe_sb_app_js', 'settings', $settings);
	}
	
	/**
	 * Plugin menu
	 */
	public function plugin_menu() {
		$hook = add_menu_page(
			__('Unsupported Browser Notification Settings', 'oe-sb'),
			__('Unsupported Browser', 'oe-sb'),
			'administrator',
			'supported_browser_settings',
			array(&$this, 'setting_page'),
			'dashicons-admin-generic'
		);
		add_action('load-' . $hook, array(&$this, 'update_settings'));
	}
		
	/**
	 * Setting page
	 */
	public function setting_page() {
	?>
		<div class="wrap">
			<h2><?php echo $this->get_plugin_display_name();?></h2>
			<form action="" method="post">
			<?php settings_fields('oe_sb_browser_settings_section'); ?>
			<?php settings_fields('oe_sb_custom_settings_section'); ?>
			<?php do_settings_sections('supported_browser_settings'); ?>
			<?php submit_button(); ?>
			</form>
		</div>
	<?php		
	}
	
	/**
	 * Register settings
	 */
	public function register_settings() {
        
        // Browser settings section
        add_settings_section(
            'oe_sb_browser_settings_section',
            __('Browser Settings', 'oe-sb'),
            array(&$this, 'sb_settings_section_callback'),
            'supported_browser_settings'
        );
		add_settings_field(
            'oe_sb_opt_chrome',
            __('Google Chrome', 'oe-sb'),
            array(&$this, 'sb_settings_browser_callback'),
            'supported_browser_settings',
            'oe_sb_browser_settings_section',
            array(
            	'name' => 'chrome'
            )
        );
       	add_settings_field(
        	'oe_sb_opt_firefox',
           	__('Firefox', 'oe-sb'),
           	array(&$this, 'sb_settings_browser_callback'),
           	'supported_browser_settings',
           	'oe_sb_browser_settings_section',
           	array(
           		'name' => 'firefox'
           	)
       	);
       	add_settings_field(
	       	'oe_sb_opt_ie',
	       	__('Internet Explorer', 'oe-sb'),
	       	array(&$this, 'sb_settings_browser_callback'),
	       	'supported_browser_settings',
	       	'oe_sb_browser_settings_section',
	       	array(
	       		'name' => 'ie'
       		)
       	);
       	add_settings_field(
	       	'oe_sb_opt_safari',
	       	__('Safari', 'oe-sb'),
	       	array(&$this, 'sb_settings_browser_callback'),
	       	'supported_browser_settings',
	       	'oe_sb_browser_settings_section',
	       	array(
	       		'name' => 'safari'
	       	)
       	);
       	add_settings_field(
	       	'oe_sb_opt_opera',
	       	__('Opera', 'oe-sb'),
	       	array(&$this, 'sb_settings_browser_callback'),
	       	'supported_browser_settings',
	       	'oe_sb_browser_settings_section',
	       	array(
	       		'name' => 'opera'
       		)
       	);
        register_setting('oe_sb_browser_settings_section', 'oe_sb_opt_chrome');
       	register_setting('oe_sb_browser_settings_section', 'oe_sb_opt_firefox');
       	register_setting('oe_sb_browser_settings_section', 'oe_sb_opt_ie');
       	register_setting('oe_sb_browser_settings_section', 'oe_sb_opt_safari');
       	register_setting('oe_sb_browser_settings_section', 'oe_sb_opt_opera');
        
        
        // Custom settings section
        add_settings_section(
            'oe_sb_custom_settings_section',
            __('Custom Settings', 'oe-sb'),
            array(&$this, 'sb_custom_settings_section_callback'),
            'supported_browser_settings'
        );
       	add_settings_field(
	       	'oe_sb_opt_background',
	       	__('Background', 'oe-sb'),
	       	array(&$this, 'sb_settings_background_callback'),
	       	'supported_browser_settings',
	       	'oe_sb_custom_settings_section',
	       	array()
       	);
        add_settings_field(
	       	'oe_sb_opt_message',
	       	__('Message', 'oe-sb'),
	       	array(&$this, 'sb_settings_message_callback'),
	       	'supported_browser_settings',
	       	'oe_sb_custom_settings_section',
	       	array()
       	);
       	register_setting('oe_sb_custom_settings_section', 'oe_sb_opt_background');
       	register_setting('oe_sb_custom_settings_section', 'oe_sb_opt_message');
	}
    
	/**
	 * Update setting
	 */
	public function update_settings() {
		if(isset($_POST['oe_sb_opt_chrome'])) {
			update_option('oe_sb_opt_chrome', $_POST['oe_sb_opt_chrome']);
			update_option('oe_sb_opt_firefox', $_POST['oe_sb_opt_firefox']);
			update_option('oe_sb_opt_ie', $_POST['oe_sb_opt_ie']);
			update_option('oe_sb_opt_safari', $_POST['oe_sb_opt_safari']);
			update_option('oe_sb_opt_opera', $_POST['oe_sb_opt_opera']);
			update_option('oe_sb_opt_background', $_POST['oe_sb_opt_background']);
			update_option('oe_sb_opt_message', $_POST['oe_sb_opt_message']);
		}
	}
    
    /**
     * Browser Settting section callback
     */
    public function sb_settings_section_callback($args) {
        echo '<p>' . __('Select the appropriate versions of the following browsers which support for your website.', 'oe-sb') . '</p>';
    }
    
    /**
     * Custom Settting section callback
     */
    public function sb_custom_settings_section_callback($args) {
        echo '<p>' . __('When the user\'s browser does not support for your website, the notification message will be displayed. You can specify the background of overlay and the custom message as you wish in this section.', 'oe-sb') . '</p>';
    }
    
    /**
     * Settting browser callback
     */
    public function sb_settings_browser_callback($args) {
		$options = get_option('oe_supported_browser_options');
		$versions = $options[$args['name']];
		$identity = 'oe_sb_opt_' . $args['name'];
		$value = get_option($identity);
		
		$html = '<label for="' . $identity . '">' . __('From version', 'oe-sb') . ': ';
        $html .= '<select name="' . $identity . '" id="' . $identity . '">';
		foreach ($versions as $version) {
			$html .= '<option value="' . $version . '" ' . ($value == $version ? 'selected' : '') . '>' . $version . '</option>';	
		}
		$html .= '</select>';
		$html .= '</label>';
		
        echo $html;
    }
    
    /**
     * Setting background callback 
     */
    public function sb_settings_background_callback() {
    	$value = get_option('oe_sb_opt_background');
    	$html = '<select name="oe_sb_opt_background">';
    	$html .= '<option value="0" ' . ($value == 0 ? 'selected' : '') . '>' . __('Black', 'oe-sb') . '</option>';
    	$html .= '<option value="1" ' . ($value == 1 ? 'selected' : '') . '>' . __('White', 'oe-sb') . '</option>';
    	$html .= '</select>';
    	echo $html;
    }
    
    /**
     * Setting message callback 
     */
    public function sb_settings_message_callback() {
        $value = get_option('oe_sb_opt_message');
        $html = '<textarea name="oe_sb_opt_message" rows="8" cols="50">' . $value . '</textarea>';
        echo $html;
    }
    
    /**
     * Check version
     */
    public function check_version() {
    	global $wp_version;
		$php_version_required = '5.3';
		$wp_version_required = '2.7';
		
		if(version_compare(PHP_VERSION, $php_version_required, '<')) {
 			deactivate_plugins(basename($this->_file));
 			echo '<p>' .
 			sprintf(
 			    __('This plugin can not be activated because it requires a PHP version greater than %1$s. Please update your PHP version before you activate it.', 'oe-sb'),
 			    $php_version_required
 			)
 			. '</p>';
            die;
		}
        
		if(version_compare($wp_version, $wp_version_required, '<')) {
 			deactivate_plugins(basename($this->_file));
			echo '<p>' .
			sprintf(
			__( 'This plugin can not be activated because it requires a WordPress version greater than %1$s. Please go to Dashboard &#9656; Updates to get the latest version of WordPress .', 'oe-sb' ),
			$wp_version_required
			)
			. '</p>';
			die;
		}
    }
    
    /**
     * Check if browser is supported or not
     */
    public function check_browser() {
    	$browser = $this->get_browser_version($_SERVER['HTTP_USER_AGENT']);        
        if($browser['name']) {
            $required_version = get_option('oe_sb_opt_' . $browser['name']);
            if($required_version > $browser['version']) {
                add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
            }
        }   
    }
    
    /**
	 * Check if plugin is installed or not
	 * @return boolean
	 */
	public function is_installed() {
		return get_option('oe_supported_browser_options') !== false;
	}
    
    /**
     * Add multiple actions
     * @return string
     */
    public function add_actions($actions) {
        if(!is_array($actions)) {
            $actions = array($actions);
        }
        foreach($actions as $action) {
            add_action($action['hook'], $action['callback']);
        }
    }
    
    /**
     * Register hooks
     */
    public function register_hooks() {
        register_activation_hook($this->_file, array(&$this, 'activate'));
		register_deactivation_hook($this->_file, array(&$this, 'deactivate'));
    }
    
    /**
     * Get user browser and version
     */
    public function get_browser_version($user_agent) {
        $user_agent = strtolower($user_agent);
        $browser = '';
        $version = '';
        
        // Only check if client browser is not mobile browser
        preg_match('/(mobile|opera mobi|iemobile)/', $user_agent, $matches);
        if(!$matches) {
	        // The branch MUST be in this order 
	    	if(strpos($user_agent, 'msie')) {
	    		preg_match('/msie\s((\d|\.)+)/', $user_agent, $matches);
				if($matches) {
					$browser = 'ie';
					$version = $matches[1];
				}
	    	} 
	    	elseif(strpos($user_agent, 'chrome')) {
				preg_match('/chrome\/((\d|\.)+)/', $user_agent, $matches);
				if($matches) {
					$browser = 'chrome';
					$version = $matches[1];
				}
	    	} 
	    	elseif(strpos($user_agent, 'safari')) {
				preg_match('/version\/((\d|\.)+)/', $user_agent, $matches);
				if($matches) {
					$browser = 'safari';
					$version = $matches[1];
				}
			} 
			elseif(strpos($user_agent, 'opera')) {
				preg_match('/version\/((\d|\.)+)/', $user_agent, $matches);
				if(!$matches) {
					preg_match('/opera\s((\d|\.)+)/', $user_agent, $matches);
				}
				if($matches) {
					$browser = 'opera';
					$version = $matches[1]; 
				}
			} 
			elseif(strpos($user_agent, 'firefox')) {
				preg_match('/firefox\/((\d|\.)+)/', $user_agent, $matches);
				$browser = 'firefox';
				$version = $matches[1];
			}
	
			preg_match('/^(\d+)\.(\d+)/', $version, $ver);
			if($ver) {
				$version = $ver[0];
			}
        }
        
        return array(
            'name' => $browser,
            'version' => $version
        );
    }
    
    /**
     * Get plugin display name
     * @return string display_name
     */
    public function get_plugin_display_name() {
    	return __('Unsupported Browser Notification', 'oe-sb');
    }
    
    /**
     * Get plugin name
     * @return string name
     */
    public function get_plugin_name() {
    	return $this->_name;
    }
    
    /**
     * Get plugin path
     * @return string plugin_url
     */
    public function get_plugin_url() {
    	return plugins_url('', $this->_file);
    }
    
    /**
     * Get path of file in plugin
     * @param string $url
     * @return string file_url
     */
    public function get_file_url($url) {
		return $this->get_plugin_url() . '/' . $url;
	}	
}

new OE_Supported_Browser();
endif;