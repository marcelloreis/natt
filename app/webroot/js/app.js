$(document).ready(function(){
    /**
     * Configuracao das mascaras dos formularios
     */
    $('.msk-money').maskMoney({decimal:",", thousands:"."});
    $('.msk-phone').mask('(99)9999-9999');
	$('.msk-zipcode').mask('99999-999');
	$('.msk-cpf').mask('999.999.999-99');
	$('.msk-cnpj').mask('99.999.999/9999-99');
	$('.msk-card').mask('9999 9999 9999 9999');
	$('.msk-hour').mask('99:99:99');
	$('.msk-2Digits').mask('99');
	$('.msk-4Digits').mask('9999');
	$('.msk-int').keyup(function(){
		$(this).val($(this).val().replace(/[^0-9]/gi, ''));
	})
	.spinner({
		min: 0
	});

	/**
	* Aplica a validacao nos campos do formulario
	*/
	$("form#validation-form").eContactForm({
		labelError: 'Este campo não pode ser vazio',
		keydown: true,
		useAjax: false		
	});

	/**
	* Aplica o retorno false para todos os links q tiverem # no seu href
	*/
	$('a[href=#]').live('click', function(){
		return false;
	});

    /**
     * Acoes em massa
     */
     $('.bulkAction').click(function(){
     	var model = $(this).attr('id');
     	var action = $(':input[name=bulkAction-' + model + ']').val();	
        if(action){
			$('form.e-checkbox-section').attr('action', action);
			$('form.e-checkbox-section').submit();

            return false;
        }
    });


    /**
     * Abre a caixa de chaves estrangeiras
     */
	$(".fk-box").click(function(){
		var btn = $(this);
		$(".modal-" + btn.attr('rel'))
		.dialog({
			position: 'top',
			autoOpen: false,//remove this and the click to aut open
			bgiframe: true,
			width: 880,
			modal: true,
			resizable: true,
			close: function( event, ui ) {
				if($(btn).hasClass('reload')){
					document.location.reload(true);
				}
				
				return false;
			}
		})
		.dialog('open')
		.load($(btn).attr('data-source'));

		return false;
	});

	/**
	* Adiciona o id da chave estrangeira ao formulario do registro q esta sendo inserido/editado
	*/
	$('.addBelongsTo').live('click', function(){
		var chkbox = $(this).parents('tr:eq(0)').find(':hidden:eq(0)');
		var id = chkbox.attr('id').replace(/id[0-9]*$/gi, '_id').toLowerCase();
		var label = chkbox.attr('placeholder');
		var value = chkbox.val();
		$('input:hidden[name*=' + id + ']:eq(0)').val(value);

		if($('input[id^=fk_][id*=' + id + ']:eq(0)').size()){
			$('input[id^=fk_][id*=' + id + ']:eq(0)').val(label);
		}

		if($('a[id^=fk_][id*=' + id + ']:eq(0)').size()){
			$('a[id^=fk_][id*=' + id + ']:eq(0)').html(label + '<p>ok</p>');
		}

		$('div[class^=modal-]').dialog('close');
		return false;
	});

	/**
	* Submete o formulario com os IDs relacionados inseridos 
	*/
	$('.saveAddHabtm').live('click', function(){
		//Carrega o nome do model relacionado
		var habtmModel = $(this).attr('rel');
		//Carrega o formulario do model relacionado
		var habtmForm = $('form:eq(0)', $('div#' + habtmModel));
		//Submete/Salva o formulario do model relacionado
		habtmForm.submit();

		$(this).parents('.powerwidget:eq(0)').fadeOut('fast', function(){
			$(this).parents('#content-grid:eq(0)').append('<h2>Aguarde enquanto os registros são associados.</h2>');
			$(this).remove();
		});		

		return false;
	});

	/**
	* Submete o formulario via ajax, usado para pesquisas nas grids
	*/
	$('form.ajax').live('submit', function(){
		//Retira todos os parametros existentes do action do formualrio
		var action = $(this).attr('action').replace(/\?.*/gi, '');
		//Monta a url com os dados serializados do formulario
		var url = action + '?' + $(this).serialize();
		$(this).parents('#content-grid:eq(0)').load(url);
		return false;
	});

	/**
	 * Name        : eMainMenu
	 * Description : Main drop down menu
	 * File Name   : e_mainmenu.js
	 * Plugin Url  :  
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Richard
	**/	
	$('nav#main-menu').eMainMenu({
		activeClass: 'sub-page-active',
		closeClass: 'min-10',
		openClass: 'plus-10',
		speed: 400
	}); 

	/**
	 * Name        : jQuery UI date picker	
	 * Description : jQuery date picker widget. 
	 * File Name   : jquery-ui-1.8.16.min
	 * Plugin Url  : http://jqueryui.com/demos/datepicker/
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Richard
	**/	
	/* Icon trigger */
	$(".datepicker-icon").datepicker({
        showOn: "button",
        showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd/mm/yy",
        buttonImage: $('meta[name=base_url]').attr('content') + "img/datepicker.png",
        buttonImageOnly: true,
        showAnim: 'drop'
	});

	$('.datetimepicker-ini-icon').datepicker({
        showOn: "button",
        showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd/mm/yy",
        buttonImage: $('meta[name=base_url]').attr('content') + "img/datepicker.png",
        buttonImageOnly: true,
        showAnim: 'drop',
		onClose: function(selectedDate) {
			$('.datetimepicker-end-icon').datepicker("option", "minDate", selectedDate);
		}
	});
	$('.datetimepicker-end-icon').datepicker({
        showOn: "button",
        showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd/mm/yy",
        buttonImage: $('meta[name=base_url]').attr('content') + "img/datepicker.png",
        buttonImageOnly: true,
        showAnim: 'drop',
		onClose: function(selectedDate) {
			$('.datetimepicker-ini-icon').datepicker("option", "maxDate", selectedDate);
		}
	});	


	/**
	 * Name        : eMenu
	 * Description : Drop down menu
	 * File Name   : e_menu.js
	 * Plugin Url  :  
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Richard
	**/	
	/* Located in the header */ 	 
	$("ul#header-menu").eMenu({
		effect: 'fade',
		speed: 100,
		target: '.e-menu-sub',
		typeEvent: 'hover',
		activeClass: 'e-menu-active',
		flip:[0,1,2]
	});	

	/**
	 * Name        : Power Widgets 
	 * Description : Plugin for the widgets
	 * File Name   : e_widgets.js
	 * Plugin Url  :  
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Mark
	**/	
	 
	$('#content').powerWidgets({				
		grid: 'section',
		widgets: '.powerwidget',
		localStorage: true,
		deleteSettingsKey: '#deletesettingskey-options',
		settingsKeyLabel: 'Reset settings?',
		deletePositionKey: '#deletepositionkey-options',
		positionKeyLabel: 'Reset position?',
		sortable: true,
		buttonsHidden: false,
		toggleButton: true,
		toggleClass: 'min-10 plix-10 | plus-10 plix-10',
		toggleSpeed: 200,
		onToggle: function(){},
		deleteButton: false,
		deleteClass: 'trashcan-10',
		deleteSpeed: 200,
		onDelete: function(){},
		editButton: false,
		editPlaceholder: '.powerwidget-editbox',
		editClass: 'pencil-10 | delete-10',
		editSpeed: 200,
		onEdit: function(){},
		fullscreenButton: false,
		fullscreenClass: 'fullscreen-10 | normalscreen-10',	
		fullscreenDiff: 3,		
		onFullscreen: function(){},
		customButton: false,
		customClass: 'folder-10 | next-10',
		customStart: function(){ alert('Hello you, this is a custom button...') },
		customEnd: function(){ alert('bye, till next time...') },
		buttonOrder: '%refresh% %delete% %custom% %edit% %fullscreen% %toggle%',
		opacity: 1.0,
		dragHandle: '> header',
		placeholderClass: 'powerwidget-placeholder',
		indicator: true,
		indicatorTime: 600,
		ajax: true,
		timestampPlaceholder:'.powerwidget-timestamp',
		timestampFormat: 'Last update: %m%/%d%/%y% %h%:%i%:%s%',
		refreshButton: true,
		refreshButtonClass: 'refresh-10 plix-10',
		labelError:'Sorry but there was a error:',
		labelUpdated: 'Last Update:',
		labelRefresh: 'Refresh',
		labelDelete: 'Delete widget:',
		afterLoad: function(){},
		rtl: false	
	}); 	 

	/**
	 * Name        : Power Widgets Panel 
	 * Description : Widgets panel
	 * File Name   : e_widgetspanel.js
	 * Plugin Url  :  
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Mark
	**/	
	$('#powerwidgetspanel').powerWidgetsPanel({
		target: '#content',
		widgets: '.powerwidget',
		localStorage: true,
		trigger: '#powerwidget-panel-switch',
		triggerClass:'plus-10 | min-10',
		effectPanel: 'slide',
		speedPanel: 200,
		effectWidget: 'slide',
		speedWidget: 200,
		onToggle: function(){}
	});

	/**
	 * Name        : Tipsy	
	 * Description : Used for a the tooltips.
	 * File Name   : jquery.tipsy.js
	 * Plugin Url  : http://onehackoranother.com/projects/jquery/tipsy/
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Richard
	**/
    // main tooltip(most used theme tooltip)
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

	/**
	 * Name        : eTabs
	 * Description : Simple plugin to create tabs
	 * File Name   : e_tabs.js
	 * Plugin Url  :  
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Mark
	**/	
	$("#e-tabs-habtm").eTabs({
		storeTab: true,
		responsive: false,
		callback: function(){ }	
	});

	/**
	 * Name        : jWYSIWYGHTML5
	 * Description : HTML5 wysiwyg editor
	 * File Name   : wysihtml5-0.3.0.js
	 * Plugin Url  : https://github.com/xing/wysihtml5
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Mark
	**/	
	if($('#wysihtml5-textarea').length){
		var editor = new wysihtml5.Editor("wysihtml5-textarea", {
			toolbar:      "toolbar",
			stylesheets:  "css/wysiwyghtml5.css",
			parserRules:  wysihtml5ParserRules
		});	
	}	

	
	/**
	 * Name        : eSelect
	 * Description : Select styling
	 * File Name   : e_select.2.0.min.js
	 * Plugin Url  :  
	 * Version     : 2.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Mark
	**/	
	$('select')
	.not('select[id*=CountryId]')
	.not('select[id*=StateId]')
	.eSelect({
		exclude: '#valueA, #valueB, #set-layout-size, #set-layout-responsive, #choose-styling, #get-theme',
		speed: 200,
		onSelect: function(a){ }
	});

	$('select[id*=CountryId]').eSelect({
		exclude: '#valueA, #valueB, #set-layout-size, #set-layout-responsive, #choose-styling, #get-theme',
		speed: 200,
		onSelect: function(a){ 
			var form = $(this).parents('form:eq(0)')
			var model = form.parents('div:eq(0)').attr('id');
			var selectCountry = $('select:hidden[id*=CountryId]', form);
			var country_id = $('option[data-select-id=' + $(this).attr('data-select-id') + ']', selectCountry).val();
			var country_ds = $('option[data-select-id=' + $(this).attr('data-select-id') + ']', selectCountry).html();

			var fieldState = $(':input[id*=State]', form);
			var divState = fieldState.parents('div.g_3_4_last:eq(0)');

			var fieldCity = $(':input[id*=City]', form);
			var divCity = fieldCity.parents('div.g_3_4_last:eq(0)');

			$.get('/states/options/' + country_id + '/model:' + model + '/model:' + model, function(data){
				if(data.length > 10){
					fieldState.fadeOut('fast', function(){
						divState.html(data);
						$('select', divState).eSelect({
							exclude: '#valueA, #valueB, #set-layout-size, #set-layout-responsive, #choose-styling, #get-theme',
							speed: 200,
							onSelect: function(a){ 
								var form = $(this).parents('form:eq(0)')
								var model = form.parents('div:eq(0)').attr('id');
								var selectState = $('select:hidden[id*=StateId]', form);
								var state_id = $('option[data-select-id=' + $(this).attr('data-select-id') + ']', selectState).val();
								var state_ds = $('option[data-select-id=' + $(this).attr('data-select-id') + ']', selectState).html();

								$.get('/cities/options/' + state_id + '/model:' + model, function(data){
									if(data.length > 10){
										fieldCity.fadeOut('fast', function(){
											divCity.html(data);
											$('select', divCity).eSelect({
												exclude: '#valueA, #valueB, #set-layout-size, #set-layout-responsive, #choose-styling, #get-theme',
												speed: 200,
												onSelect: function(a){}
											});

										});
									}else{
										var inputEmpty = $('<input>');
										inputEmpty.attr({
											type: 'text',
											id: model + 'CityDs',
											placeholder: 'Desculpe, não temos as cidades do estado ' + state_ds + ', digite o nome por favor.',
											name: 'data[' + model + '][City_ds]'
										});
										divCity.html(inputEmpty);
									}
								});
							}
						});

					});
				}else{
					var inputState = $('<input>');
					var inputCity = $('<input>');
					inputState.attr({
						type: 'text',
						id: model + 'StateDs',
						placeholder: 'Desculpe, não encontramos os estados do país ' + country_ds + ', digite o nome por favor.',
						name: 'data[' + model + '][state_ds]'
					});
					divState.html(inputState);

					inputCity.attr({
						id: model + 'CityDs',
						placeholder: 'Desculpe, não econtramos as cidades do país ' + country_ds + ', digite o nome por favor.',
						name: 'data[' + model + '][city_ds]'
					});
					divCity.html(inputCity);
				}
			});
		}
	});

	$('select[id*=StateId]').eSelect({
		exclude: '#valueA, #valueB, #set-layout-size, #set-layout-responsive, #choose-styling, #get-theme',
		speed: 200,
		onSelect: function(a){ 
			var form = $(this).parents('form:eq(0)')
			var model = form.parents('div:eq(0)').attr('id');
			var selectState = $('select:hidden[id*=StateId]', form);
			var state_id = $('option[data-select-id=' + $(this).attr('data-select-id') + ']', selectState).val();
			var state_ds = $('option[data-select-id=' + $(this).attr('data-select-id') + ']', selectState).html();

			var fieldCity = $(':input[id*=City]', form);
			var divCity = fieldCity.parents('div.g_3_4_last:eq(0)');

			$.get('/cities/options/' + state_id + '/model:' + model, function(data){
				if(data.length > 10){
					fieldCity.fadeOut('fast', function(){
						divCity.html(data);
						$('select', divCity).eSelect({
							exclude: '#valueA, #valueB, #set-layout-size, #set-layout-responsive, #choose-styling, #get-theme',
							speed: 200,
							onSelect: function(a){}
						});

					});
				}else{
					var inputEmpty = $('<input>');
					inputEmpty.attr({
						type: 'text',
						id: model + 'CityDs',
						placeholder: 'Desculpe, não temos as cidades do estado ' + state_ds + ', digite o nome por favor.',
						name: 'data[' + model + '][City_ds]'
					});
					divCity.html(inputEmpty);
				}
			});
		}
	});
	
	/**
	 * Name        : eFile
	 * Description : File input  styling
	 * File Name   : e_file.1.0.min.js
	 * Plugin Url  :  
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Mark
	**/
	$('input[type="file"]').eFile({
		label: 'upload',
		exclude: '',
		onUpload: function(){ }	
	});

	/**
	 * Name        : eCheckbox
	 * Description : Checkbox replacement and mass toggle solution
	 * File Name   : e_checkbox.js
	 * Plugin Url  :  
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Mark
	**/	
	/* all */										
	$('input[type="checkbox"]').eCheckbox();
	
	/**
	 * Name        : eRadio
	 * Description : Radio button replacement solution
	 * File Name   : e_radio.js
	 * Plugin Url  :  
	 * Version     : 1.0	
	 * Updated     : --/--/---
	 * Dependency  :
	 * Developer   : Mark
	**/		
	$('input[type="radio"]:not(".notEradio")').eRadio();	
 });


/**
 * Objeto Main
 */
 var App = {
 	addHabtm: function(chkbox, checked){
	    //Verifica qual acao invocou o checkbox ajax
	    if($(chkbox).attr('class').match(/e-checkbox-ajax-habtm/gi)){
	        var id = $(chkbox).attr('id').replace(/id[0-9]*$/gi, '_id').toLowerCase();
	        var label = $(chkbox).attr('placeholder');
	        var value = $(chkbox).val();
	        var habtmModel = $(chkbox).attr('id').replace(/Id[0-9]*/gi, '');
	        var indiceInput = $(':hidden', 'div[id=addHabtm-' + habtmModel + ']').size()
	        var input = $('<input>');
	  	
	        input.attr({
	            'id': habtmModel + habtmModel + 'Id' + indiceInput,
	            'name': 'data[' + habtmModel + '][' + habtmModel + '][' + indiceInput + ']',
	            'value': value,
	            'type': 'hidden'
	        });



	        //Verifica se o registro ja esta inserido no formulario
	        if(checked){
	            //Remove o id do relacionamento
	            $(':hidden[id*=' + habtmModel + habtmModel + 'Id' + '][value=' + value + ']').remove();
	        }else{
	            //Insere o id do relacionamento
	            $('div[id=addHabtm-' + habtmModel + ']').append(input);
	        }
	    }
 	},
 	addBelongsTo: function(chkbox, checked){
	    //Verifica qual acao invocou o checkbox ajax
	    if($(chkbox).attr('class').match(/e-checkbox-ajax-habtm/gi)){
			var id = chkbox.attr('id').replace(/id[0-9]*$/gi, '_id').toLowerCase();
			var label = chkbox.attr('placeholder');
			var value = chkbox.val();
			$('input:hidden[name*=' + id + ']:eq(0)').val(value);

			if($('input[id^=fk_][id*=' + id + ']:eq(0)').size()){
				$('input[id^=fk_][id*=' + id + ']:eq(0)').val(label);
			}

			if($('a[id^=fk_][id*=' + id + ']:eq(0)').size()){
				$('a[id^=fk_][id*=' + id + ']:eq(0)').html(label + '<p>ok</p>');
			}

			$('div[class^=modal-]').dialog('close');
			return false;
	    }
 	}
}






