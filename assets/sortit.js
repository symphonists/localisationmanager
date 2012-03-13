/*
 * Localisation Manager
 *
 * @author: Nils Hörrmann, post@nilshoerrmann.de
 * @source: http://github.com/symphonists/localisationmanager
 */
(function($) {
	$(document).ready(function() {
	
		// Language strings
		Symphony.Language.add({
			'Sort strings alphabetically': false
		}); 
	
		// Append sort option
		var context = $('#context'),
			table = $('table'),
			sort = $('<label style="float: right;"><input type="checkbox" name="sortit" /> ' + Symphony.Language.get('Sort strings alphabetically') + '</label>').appendTo(context);
		
		// Events
		sort.click(function(event) {
		
			// Get status
			var checked = $(event.target).attr('checked');
			
			// Add or remove sort option
			table.find('a').each(function(index, link) {
				link = $(link);
				var href = link.attr('href');
	
				// Is checked?
				if(checked) {
					link.attr('href', href + '?sort');
				}
				
				// Is not checked?
				else {
					link.attr('href', href.replace('?sort', ''));
				}
			});
		});
	});
})(jQuery.noConflict());
