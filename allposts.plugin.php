<?php
class AllPosts extends Plugin
{
	/**
	 * Plugin init action, executed when plugins are initialized.
	 */
	public function action_init()
	{
		$opts = Options::get_group( __CLASS__ );
		if(!isset($opts['content_types']))
		{
			$opts['content_types'] = 1;
			Options::set_group( __CLASS__, $opts );
		}
	}
	/**
	 * Add rewrite rules
	 **/
    public function filter_rewrite_rules($rules)
    {
		$opts = Options::get_group( __CLASS__ );
		if(isset($opts['enable_all']) && $opts['enable_all'] == 1)
		{
			$rules[] = RewriteRule::create_url_rule('"allposts"/"all"', 'PluginHandler', 'allposts');
		}
		if(isset($opts['enable_custom']) && $opts['enable_custom'] == 1)
		{
			$rules[] = RewriteRule::create_url_rule('"allposts"/"custom"', 'PluginHandler', 'customallposts');
		}
        return $rules;
    }
    
    /**
	 * Implement the output for all published posts
	 **/
    public function action_plugin_act_allposts($handler)
    {
        $handler->theme->act_display_entries(array('status' => Post::status('published'), 'content_type' => Post::type('any'), 'limit' => 'nolimit'));
    }
	
	/**
	 * Implement the output for all published posts of the chosen content type
	 **/
    public function action_plugin_act_customallposts($handler)
    {
		$opts = Options::get_group( __CLASS__ );
        $handler->theme->act_display_entries(array('status' => Post::status('published'), 'content_type' => $opts['content_types'], 'limit' => 'nolimit'));
    }
	
	/**
	 * Executes when the admin plugins page wants to know if plugins have configuration links to display.
	 *
	 * @param array $actions An array of existing actions for the specified plugin id.
	 * @param string $plugin_id A unique id identifying a plugin.
	 * @return array An array of supported actions for the named plugin
	 */
	public function filter_plugin_config($actions, $plugin_id)
	{
		// Is this plugin the one specified?
		if ( $plugin_id == $this->plugin_id() )
		{
			// Add a 'Configure' action in the admin's list of plugins
			$actions[] = _t( 'Configure' );
		}
		return $actions;
	}
	
	/**
	 * Executes when the admin plugins page wants to display the UI for a particular plugin action.
	 * Displays the plugin's UI.
	 *
	 * @param string $plugin_id The unique id of a plugin
	 * @param string $action The action to display
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() )
		{
			switch ( $action ) {
				case _t( 'Configure' ):
					// Get the available content types
					$types = Post::list_active_post_types();
					unset($types['any']);
					$types = array_flip($types);
					$ui = new FormUI( __CLASS__ );
					$ui->append( 'checkbox', 'enable_allposts', __CLASS__ . '__enable_all', _t( 'Enable "all posts" view', __CLASS__ ) );
					$ui->append( 'checkbox', 'enable_customallposts', __CLASS__ . '__enable_custom', _t( 'Enable "custom all posts" view', __CLASS__ ) );
					$ui->append( 'select', 'content_types', __CLASS__ . '__content_types', _t( 'Content Types to display in custom view:', __CLASS__ ) );
					$ui->content_types->size = count($types);
					$ui->content_types->multiple = true;
					$ui->content_types->options = $types;
					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->out();
					break;
			}
		}
	}
}
?>