jQuery( document ).ready(
	function () {
		jQuery( "ul.sortable" ).sortable(
			{
				'tolerance': 'intersect',
				'cursor': 'pointer',
				'items': '> li',
				'axi': 'y',
				'placeholder': 'placeholder',
				'nested': 'ul'
			}
		);
	}
);
jQuery( ".save-order" ).bind(
	"click",
	function () {
		// serialize the arrayserialize
		var show_all   = jQuery( "input[name='show_all']" );
		var check_show = jQuery( "input[name='check_show']" );
		jQuery.post(
			ajaxurl,
			{
				action: 'update_taxonomy_order',
				order: sortable().join( ',' ),
				hide: hideInApp().join( ',' ),
				show_all: show_all.is( ":checked" ),
				check_show: check_show.is( ":checked" ),
				taxonomy: 'category',
				nonce: Stionic.nonce
			},
			function () {
				jQuery( ".update-success" ).show();
				setTimeout( function () { jQuery( ".update-success" ).hide(); }, 2000 );
			}
		);
	}
);
function hideInApp() {
	var hideInApp = [];
	jQuery( "[name='hide_in_app[]']" ).each(
		function(index, item) {
			if (item.checked) {
				hideInApp.push( item.value );
			}
		}
	);
	return hideInApp;
}
function sortable() {
	var mySortable = [];
	jQuery( ".ui-sortable-handle" ).each(
		function (index, item) {
			mySortable.push( item.getAttribute( 'id' ) );
		}
	);
	return mySortable;
}
