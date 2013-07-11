$(document).ready(function(){
	$('.bulk-actions').each(function(){
		$(this).not(':hidden').eSelect(); 
	});

    $('input[type=checkbox][class*=e-checkbox-ajax]').each(function(){
    	$(this).eCheckbox({
            toggleCallback: function(){
                var trigger = $(this).find('input:eq(0)');
                $(':checkbox[class*=e-checkbox-ajax-habtm]:not([class*=e-checkbox-trigger])', $(this).parents('table:eq(0)')).each(function(){
                    App.addHabtm(this, !$(trigger).is(':checked'));
                });
            },
            singleCallback: function(){
                var chkbox = $(this).find('input:eq(0)');
                App.addHabtm(chkbox, $(chkbox).is(':checked'));
            }
        });
    });

    $(".datepicker-icon").datepicker({
        showOn: "button",
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd/mm/yy",
        buttonImage: $('meta[name=base_url]').attr('content') + "img/datepicker.png",
        buttonImageOnly: true
    });

    $('.tip-s').tipsy({
        delayIn: 0,      // delay before showing tooltip (ms)
        delayOut: 0,     // delay before hiding tooltip (ms)
        fade: false,     // fade tooltips in/out?
        fallback: '',    // fallback text to use when no tooltip text
        gravity: 's',    // gravity
        html: false,     // is tooltip content HTML?
        live: false,     // use live event support?
        offset: 0,       // pixel offset of tooltip from element
        opacity: 1.0,    // opacity of tooltip
        title: 'title',  // attribute/callback containing tooltip text
        trigger: 'hover' // how tooltip is triggered - hover | focus | manual
    });

     $('.bulkAction').click(function(){
     	var model = $(this).attr('id');
     	var action = $(':input[name=bulkAction-' + model + ']').val();	
        if(action){
            App.bulkAction(action);
            return false;
        }
    });	
     
    $('#basic-table, #tableexample1, #tableexample2, #tableexample3').eResponsiveTable({
        className: 'rt-'
    });
    $('.basic-table, .clean-table').children('tbody').children('tr:odd').addClass('odd');	
});