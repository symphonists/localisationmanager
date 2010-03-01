<?php

	Class extension_localisationmanager extends Extension{
	
		/**
		 * Extension information
		 */

		public function about(){
			return array(
				'name' => 'Localisation Manager',
				'version' => '1.3',
				'release-date' => '2010-03-01',
				'author' => array(
					'name' => 'Nils Hörrmann',
					'website' => 'http://nilshoerrmann.de',
					'email' => 'post@nilshoerrmann.de'
				)
			);
		}
		
		public function fetchNavigation() {
			return array(
				array(
					'location'	=> 'System',
					'name'		=> __('Localisations'),
					'link'		=> '/localisations/'
				)
			);
		}		
					
	}
	