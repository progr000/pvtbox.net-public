( function( api ) {

	// Extends our custom "sirat" section.
	api.sectionConstructor['sirat'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );