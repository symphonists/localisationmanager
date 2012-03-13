<?php

	Class extension_localisationmanager extends Extension {
	
		public function fetchNavigation() {
			return array(
				array(
					'location'	=> __('System'),
					'name'		=> __('Localisations'),
					'link'		=> '/localisations/'
				)
			);
		}		
	
	}
	