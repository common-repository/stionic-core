jQuery(document).ready(function( $ ) {
	var maintenance = $('input[name="maintenance_check"]');
	if(maintenance[0]){
		function maintenance_update(){
			if(maintenance[0].checked) $('input[name="maintenance_content"]').removeAttr('disabled');
			else $('input[name="maintenance_content"]').attr('disabled', true);
		}
		maintenance_update();
		maintenance.change(maintenance_update);
	}
});