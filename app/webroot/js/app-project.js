$(document).ready(function(){
 });


/**
 * Objeto Main
 */
 var AppProject = {
	bulkAction:function(action){
		$('form.e-checkbox-section').attr('action', action);
		$('form.e-checkbox-section').submit();
	}
}






