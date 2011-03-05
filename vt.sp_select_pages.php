<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * SP Select Pages
 *
 * A dropdown for page selection in Low Variables
 *
 * @author 		Jamie Rumbelow <http://jamierumbelow.net>
 * @version		1.0.0
 * @copyright 	(c)2011 Jamie Rumbelow
 */

class Sp_select_pages extends Low_variables_type {
	
	/* --------------------------------------------------------------
	 * VARIABLES
	 * ------------------------------------------------------------ */

	var $info = array(
		'name'		=> 'Select Pages',
		'version'	=> '1.0.0'
	);

	var $default_settings = array(
		'channels' => array()
	);

	/* --------------------------------------------------------------
	 * LOW VARIABLES API
	 * ------------------------------------------------------------ */

	/**
	 * Display settings sub-form for this variable type
	 */
	public function display_settings($var_id, $var_settings) {
		$r = array();
		
		// Get all the site's channels
		$query = $this->EE->db->select('channel_id, channel_title')
							  ->where('site_id', $this->EE->config->item('site_id'))
							  ->order_by('channel_title ASC')
							  ->get('channels');
		$channels = $this->flatten_results($query->result_array(), 'channel_id', 'channel_title');
		
		// Get channels from settings
		$current = $this->get_setting('channels', $var_settings);
		
		$r[] = array(
			$this->setting_label($this->EE->lang->line('channels')),
			form_multiselect($this->input_name('channels', TRUE), $channels, $current)
		);
		
		// Return it!
		return $r;
	}

	/**
	 * Display input field for user
	 */
	public function display_input($var_id, $var_data, $var_settings) {
		// Get the channels
		$channels = $this->get_setting('channels', $var_settings);
		
		// We need channels
		if (empty($channels)) {
			return $this->EE->lang->line('no_channel_selected');
		}
		
		// Get all the pages
		$pages = unserialize(base64_decode($this->EE->db->select('site_pages')
							  							->where('site_id', $this->EE->config->item('site_id'))
							  							->get('sites')
						 	  							->row('site_pages')));
		
		// Loop through them, get the IDs
		foreach ($pages[1]['uris'] as $entry_id => $uri) {
			$ids[] = $entry_id;
			$uris[$entry_id] = $uri;
		}
		
		// Get the entries that are in our channels
		$entries = $this->EE->db->select('title, entry_id')
								->where_in('entry_id', $ids)
								->where_in('channel_id', $channels)
								->get('channel_titles')
								->result();
		
		// Make a HTML safe array
		$dropdown = array();
		
		foreach ($entries as $entry) {
			$dropdown[$uris[$entry->entry_id]] = $entry->title;
		}
		
		// Build the array
		$html = $this->select_element($var_id, $dropdown, $var_data);
		
		// Return it!
		return $html;
	}

}