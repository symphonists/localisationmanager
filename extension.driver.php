<?php

	Class extension_localisationmanager extends Extension{
	
		public function about(){
			return array(
				'name' => 'Localisation Manager',
				'version' => '1.5.1',
				'release-date' => '2011-01-30',
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
					'location'	=> __('System'),
					'name'		=> __('Localisations'),
					'link'		=> '/localisations/'
				)
			);
		}		
	
	}
	