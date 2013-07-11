/*
 * ************************************************************* *
 * Name       : eTour                                            *
 * Date       : June 2012                                        *
 * Owner      : CreativeMilk                                     *
 * Url        : www.creativemilk.net                             *
 * Version    : 1.0                                              *
 * Updated    : --/--/----                                       *
 * Developer  : Mark                                             *
 * Dependency : jQuery UI core                                   *
 * Lib        : jQuery 1.7+                                      *
 * Licence    : NOT free                                         *
 * http://themeforest.net/item/elite-a-powerfull-responsive-admin-theme/2997200
 * ************************************************************* *
 */
  
;(function($, window, document, undefined){
    $.fn.eTour = function(options) { 
	
		options = $.extend({}, $.fn.eTour.options, options); 
	 
			return this.each(function() {  
						
				/**
				* Variables.
				**/
				var obj        = $(this);
				var mainId     = Math.floor(Math.random()*1111);
				var arrowSize  = 8;
				var timeouts   = [];
				var totalSteps = options.step.length;

				/**
				* Check for touch support and set right click events.
				**/
				if(('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch){
					var clickEvent = 'click tap';	
				}else{
					var clickEvent = 'click';	
				}

				//*****************************************************************//
				/////////////////////// CREATE ALL STEPS LOOP ///////////////////////
				//*****************************************************************//
				 
					/**
					* Create all steps with a loop.
					**/	
					$.each(options.step, function(index, value){
						
						/**
						* Build the eTour template.
						* We need to build it inside the loop
						* this because of the dataset step.
						**/
						var tourTemp = '<div class="e-tour" style="display:none;" data-tour-id="'+mainId+'" data-tour-step="'+index+'">'+
										'<span></span>'+
										'<div class="e-tour-inner">'+
										'<header class="e-tour-header"></header>'+
										'<div class="e-tour-content"></div>'+
										'<footer class="e-tour-footer"></footer>'+
										'</div>'+
										'</div>';
													
						/**
						* Create the single step of the tour.
						**/	
						$(value.hookTo)
						.css({position: 'relative'})
						.append(tourTemp)
						.children('[data-tour-step="'+index+'"]')
						.css({width: value.width})
						.find('header.e-tour-header')
						.text(value.title)
						.next('div.e-tour-content')
						.append($(value.content))
						.children()
						.css({width: value.width - 20})
						.show();
						
						/**
						* Set the main variable.
						**/	
						var step = $(value.hookTo).children('[data-tour-id="'+mainId+'"][data-tour-step="'+index+'"]');
	
						/**
						* Append the timer or prev/stop/next buttons to the footer,
						* or if the type is 'all' remove the whole footer.
						**/	
						if(options.type == 'timer' && options.showTimer === true){
							
							/**
							* Add a placeholder to display a timer.
							**/	
							step.find('.e-tour-footer').append('<div class="e-tour-timer">'+options.timerLabel+'<span></span></div>');	
	
						}else if(options.type == 'step'){
							
							/**
							* Add a prev or/and a next button if the type is 'step' 
							* and add a end button at every last step.
							**/
							if(index == 0){
								var setButtons = '<a href="javascript:void(0);" class="e-tour-btn-next">'+value.nextLabel+'</a>';
							}else if(totalSteps == (index + 1)){
								var setButtons = '<a href="javascript:void(0);" class="e-tour-btn-prev">'+value.prevLabel+'</a><a href="javascript:void(0);" class="e-tour-btn-stop">'+options.endLabel+'</a>';	
							}else{
								var setButtons = '<a href="javascript:void(0);" class="e-tour-btn-prev">'+value.prevLabel+'</a><a href="javascript:void(0);" class="e-tour-btn-next">'+value.nextLabel+'</a>';
							}
							step.find('footer.e-tour-footer').append('<div>'+setButtons+'</div>');	
						
						}else{
							
							/**
							* The type ''all' doesn't need a footer, 
							* so lets remove it.
							**/
							step.find('footer.e-tour-footer').remove();	
						}
						
						/**
						* Getting width and height from the hook and step.
						**/	
						var hookWidth  = $(value.hookTo).outerWidth();
						var hookHeight = $(value.hookTo).outerHeight();	
						var stepWidth  = step.outerWidth();
						var stepHeight = step.outerHeight();
						
						/**
						* Remove the arrow, let the step float(if the option is set to false). 
						* Add the right arrow class.
						**/	
						if(options.showArrow === true){
							step.addClass('e-tour-arrow-'+value.arrowPosition);
							var newArrowSize = arrowSize;						
						}else{
							step.children('span').remove();	
							var newArrowSize = 0;						
						}
	
						/**
						* Add a draggable class.
						**/	
						if(options.draggable === true){
							step.addClass('e-tour-draggable');
						}
						
						/**
						* Setting the offset top.
						**/	
						if(value.offsetY){
							var offsetY = value.offsetY;
						}else{
							var offsetY = 0;
						}
										
						/**
						* Setting the offset left.
						**/	
						if(value.offsetX){
							var offsetX = value.offsetX;
						}else{
							var offsetX = 0;
						}
												
						/**
						* Settings per arrow position.
						**/	
						switch(value.arrowPosition){
							// top left
							case 'tl':
								step.css({left: offsetX , top: -stepHeight - newArrowSize - offsetY});					
							break;
							// top middle
							case 'tm':
								step.css({left: '50%', marginLeft: -((stepWidth/2) - offsetY), top: -stepHeight - newArrowSize - offsetY});					
							break;
							// top right
							case 'tr':
								step.css({right: offsetX , top: -stepHeight - newArrowSize - offsetY});					
							break;
	
							// right top
							case 'rt':
								step.css({left: (hookWidth + offsetX + newArrowSize), top: offsetY});
							break;
							// right middle
							case 'rm':
								step.css({left: (hookWidth + offsetX + newArrowSize), top: '50%', marginTop: -((stepHeight/2) - offsetY)});						
							break;						
							// right bottom
							case 'rb':
								step.css({left: (hookWidth + offsetX + newArrowSize), bottom: offsetY});						
							break;
							
							// bottom left
							case 'bl':
								step.css({left: offsetX , bottom: -stepHeight - newArrowSize - offsetY});					
							break;
							// bottom middle
							case 'bm':
								step.css({left: '50%', marginLeft: -((stepWidth/2) - offsetY), bottom: -stepHeight - newArrowSize - offsetY});					
							break;
							// bottom right
							case 'br':
								step.css({right: offsetX , bottom: -stepHeight - newArrowSize - offsetY});					
							break;
	
							// left top
							case 'lt':
								step.css({right: (hookWidth + offsetX + newArrowSize), top: offsetY});
							break;
							// left middle
							case 'lm':
								step.css({right: (hookWidth + offsetX + newArrowSize), top: '50%', marginTop: -((stepHeight/2) - offsetY)});						
							break;						
							// left bottom
							case 'lb':
								step.css({right: (hookWidth + offsetX + newArrowSize), bottom: offsetY});						
							break;																														
						};
					});
				
				//*****************************************************************//
				//////////////////////// SCROLL TO FUNCTION /////////////////////////
				//*****************************************************************//
				
					/**
					* Scroll to the target(hook or step) or reset and 
					* go back to the top. Notice that if the hook is bigger
					* than the window it will use the step as center.
					**/	
					function scrollToTarget(to, center, i, end){
						if(end != true){
							var step = $(to).children('[data-tour-id="'+mainId+'"][data-tour-step="'+i+'"]');
							if(center == 'step' || $(to).outerHeight() >= $(window).height()){ 
								var centerTo = step.offset().top - ($(window).height()/2) + (step.outerHeight()/2);
							}else{
								var centerTo = $(to).offset().top - ($(window).height()/2) + ($(to).outerHeight()/2); 
							}
						}else{
							var centerTo = 0;
						}
						$('html, body').animate({scrollTop:centerTo}, options.effectSpeed);
					}	
					
				
				//*****************************************************************//
				////////////////////////////// TIMER ////////////////////////////////
				//*****************************************************************//	
						
					/**
					* Prevent an empty time value.
					**/	
					if(options.time.length){
						
						/**
						* Check for the right time format and convert time 
						* in to millieseconds.
						**/	
						if(options.time.indexOf(':') == -1){
							alert('Please use the right time format (mm:ss)')
						}
						var nTime = options.time.split(":").reverse();
						var time  = parseInt((nTime[0] * 1000) + (nTime[1] * 60000));
					}else{
						var time = 1000;
					}
					
					/**
					* Simple countdown function used for the 'timer' type.
					* The interval will reset if a new step will run.
					**/	
					function countdownTimer(t, place){
						function timer(){
	
							//day = calc(t,216000000,24);
							//hours = calc(t,3600000,60);
							mins  = calc(t,60000,60);
							secs  = calc(t,1000,60);
							
							/**
							* Always display 2 numbers.
							**/	
							function calc(secs, num1, num2) {
								var s = ((Math.floor(secs/num1))%num2).toString();
								if (s.length < 2){
									var s = "0" + s;
								}
								return s;
							}	
												
							/**
							* Show the timer.
							**/	
							place.html(mins+':'+secs);
						}
						window.cInter = setInterval(function(){
							if(t != 0){
								t -= 1000;
								timer();
							}
						},1000);
					}

				//*****************************************************************//
				/////////////////////////// START THE TOUR //////////////////////////
				//*****************************************************************//
				
					/**
					* Start the tour with a click.
					**/	
					obj.on(clickEvent, this, function(e){
						
						/**
						* If the option type is set to "timer" it will
						* loop on it self, once done it will close.
						**/	
						if(options.type == 'timer'){
	
							/**
							* Loop all steps one at the time.
							**/				
							$.each(options.step, function(index, value){
								timeouts.push(setTimeout(function(){
									
									var step = $(value.hookTo).children('[data-tour-id="'+mainId+'"][data-tour-step="'+index+'"]');
	
									/**
									* Show countdown time.
									* Reset the countdown timer.
									**/	
									if(options.showTimer === true){
										clearInterval(window.cInter);
										countdownTimer(time, step.find('.e-tour-timer span'));
									}
									
									/**
									* Remove all open steps.
									**/	
									$('[data-tour-id="'+mainId+'"]').not(step).fadeOut(options.effectSpeed);
									
									/**
									* Show the active step.
									**/	
									step.fadeIn(options.effectSpeed);
						
									/**
									* Scroll to the right place in the window.
									**/	
									scrollToTarget(value.hookTo, value.center, index, '');
									
									/**	
									* Run the callback function.
									**/	
									if(typeof value.onShow == 'function'){
										value.onShow.call(this);
									}
									
									/**
									* On the last step, fadeout all steps and go back to the top.
									**/	
									if(totalSteps == (index + 1)){
										setTimeout(function(){
											$('[data-tour-id="'+mainId+'"]').fadeOut(options.effectSpeed);	
											scrollToTarget('', '', '', true);	
										},time);		
									}
								},time*index));
							});
											
						}else if(options.type == 'all'){
							
							/**	
							* Show all steps at once if the type is set to 'all'.
							**/	
							$('[data-tour-id="'+mainId+'"]').fadeIn(options.effectSpeed);	
							
						}else{
								
							/**	
							* If the steps are hidden.
							**/					
							if($('[data-tour-id="'+mainId+'"]').is(':hidden')){
	
								/**
								* Scroll to the right place in the window.
								**/
								scrollToTarget(options.step[0].hookTo, options.step[0].center, 0, '');
								
								/**
								* Show the first step.
								**/							
								$('[data-tour-id="'+mainId+'"][data-tour-step="0"]').fadeIn(options.effectSpeed);
							}
						}
						
						e.preventDefault();
					});
					
				//*****************************************************************//
				//////////////////////// TYPE STEP BUTTONS //////////////////////////
				//*****************************************************************//
				
					/**	
					* Type 'step' click events.
					**/	
					if(options.type == 'step'){
						
						/**	
						* Cancel the type 'step' if a user clicks outside the 
						* step, options 'easyCancel'.
						**/
						if(options.easyCancel === true){
							$(document).on(clickEvent, this, function(e){
								if(!$(e.target).is($(' *', '[data-tour-id="'+mainId+'"]')) && !$(e.target).is($(' *', obj)) && !$(e.target).is($(obj))){
									$('[data-tour-id="'+mainId+'"]').hide();
								}
								e.preventDefault();
							});
						}
						
						/**	
						* Run prev/next.
						**/
						$('body').on(clickEvent, '.e-tour-btn-prev, .e-tour-btn-next', function(e){
							
							/**
							* Get the number of the open step.
							**/	
							var thisStep = $(this).parents('[data-tour-id="'+mainId+'"]').data('tour-step');
							
							/**
							* Choose beteen step next or step prev.
							**/	
							if($(e.target).hasClass('e-tour-btn-prev')){
								var i = thisStep-1; 	
							}else{
								var i = thisStep+1; 
							}
				
							/**
							* SGetting the values from the array.
							**/	
							var hookTo = options.step[i].hookTo;
							var center = options.step[i].center;
	
							/**
							* Hide all open steps.
							**/	
							$('[data-tour-id="'+mainId+'"]:not([data-tour-step="'+i+'"])').fadeOut(options.effectSpeed)
							
							/**
							* Show the next step.
							**/	
							$('[data-tour-id="'+mainId+'"][data-tour-step="'+i+'"]').fadeIn(options.effectSpeed);
	
							/**
							* Scroll to the right place in the window.
							**/	
							scrollToTarget(hookTo, center, i, '');
	
							e.preventDefault();	
						});					
					}

				//*****************************************************************//
				/////////////////////// MASTER STOP BUTTON //////////////////////////
				//*****************************************************************//
				
					/**	
					* The type 'timer' and 'all' have a panic button, once pressed, 
					* it stops the tour immediately.
					**/
					$('body').on(clickEvent, options.stopButton+' ,a.e-tour-btn-stop',function(e){
						if(options.type == 'timer'){
							$.each(timeouts, function (_, id) {
								clearTimeout(id);
							});
							timeouts = [];
						}
						
						/**	
						* Hide all steps.
						**/	
						$('[data-tour-id="'+mainId+'"]').fadeOut(options.effectSpeed);
						
						/**	
						* Run the callback function.
						**/	
						if(typeof value.onStop == 'function'){
							value.onStop.call(this);
						}
									
						e.preventDefault();
					});
					
				//*****************************************************************//
				//////////////////////////// JQUERY UI //////////////////////////////
				//*****************************************************************//
						 
					/**
					* Allow a step to be dragged.
					**/		
					if(options.draggable === true){
						$('div.e-tour-draggable').draggable({
							handle: '.e-tour-header'
						});
					}				
			});		
		};
		
		/**
		* Default settings(dont change).
		* You can globally override these options
		* by using $.fn.pluginName.key = 'value';
		**/
		$.fn.eTour.options = {
			type: 'step',
			time: '00:30',
			showTimer: true,
			showArrow: true,
			timerLabel: 'Next step in: ',
			stopButton: '#e-tour-stop',
			draggable: false,
			effectSpeed:00,
			easyCancel: true,
			endLabel: 'End',
			step:[
				{
					hookTo: '',
					content: '',
					width: 300,
					title: '',
					arrowPosition: 'rb',
					offsetY: 0,
					offsetX: 0,
					prevLabel: 'Go back',
					nextLabel: 'Continue',
					center: 'hook',
					onShow: function(){}
				}
			],
			onStop: function(){}		
		};
		
})(jQuery, window, document);
