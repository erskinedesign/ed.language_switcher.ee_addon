<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ed_language_switcher_ext
{
	public $settings = array();
	public $name = 'ED language switcher';
	public $version = '0.1';
	public $description = 'Sets a cookie and an early-parsed global variable for chosen language.';
	public $settings_exist = 'y';
	public $docs_url = 'http://github.com/erskinedesign/ed_language_switcher.ee_addon';
	
	public function __construct($settings = array())
	{
		$this->EE = get_instance();
		$this->settings = $settings;
	}
	
	public function activate_extension()
	{
	    $default_settings = serialize( $this->default_settings() );
	    
		$this->EE->db->insert(
			'extensions',
			array(
				'class' => __CLASS__,
				'method' => 'sessions_end',
				'hook' => 'sessions_end',
				'settings' => $default_settings,
				'priority' => 10,
				'version' => $this->version,
				'enabled' => 'y'
			)
		);
	}
	
	public function update_extension($current = '')
	{
		if ( ! $current || $current === $this->version)
		{
			return FALSE;
		}
		
		$this->EE->db->update(
			'extensions',
			array('version' => $this->version),
			array('class' => __CLASS__)
		);
	}
	
	public function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}
	
	public function settings()
	{
    	$settings = array();
    	
		$settings['allowed_languages'] = '';
    	   	
    	return $settings;
	}
	
	function default_settings()
    {    		
    	$default_settings = array(
    		'allowed_languages' => 'en|de'
    	);
    	   	
    	return $default_settings;
    }
	
	public function sessions_end()
	{
        // We set {default_language} in index.php so that subdomains of folders can have different defaults
        $default_language = ( $this->EE->config->_global_vars['default_language'] != "" ) ? $this->EE->config->_global_vars['default_language'] : "en";

        $user_language = $default_language;
        
        // Do we have a language set as a cookie?
        // If so, use it's value to set the current language    
        if ( $this->EE->input->cookie('user_language') AND $this->is_allowed_language($this->EE->input->cookie('user_language')) ) {

            $user_language = $this->EE->input->cookie('user_language');
        }
        
        // Do we have a language requested in a get variable i.e. lang=de ?
        // If so, use it's value to set the current language
        if ( $this->EE->input->get('lang') AND $this->is_allowed_language($this->EE->input->get('lang')) ) {
            
        	$user_language = $this->EE->input->get('lang');
        	
        	// Set a cookie to save the user's choice
        	$this->EE->functions->set_cookie('user_language', $user_language, 60*60*24*90);

		}
		
		// Set the user language as a global variable to use in the templates
		$this->EE->config->_global_vars['user_language'] = $user_language;

	}
	
	private function is_allowed_language($value)
	{
	    $langs = explode("|", $this->settings['allowed_languages']);
		if(in_array($value, $langs))
		{
			return TRUE;
		}
	}

}

/* End of file ext.ed_language_switcher.php */
/* Location: ./system/expressionengine/third_party/ed_language_switcher/ext.ed_language_switcher.php */