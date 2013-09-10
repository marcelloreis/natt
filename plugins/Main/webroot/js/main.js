$(document).ready(function(){
    /**
     * Configurações do plugin ACL
     */
     $('.bulkActionPermission').click(function(){
        if($('select[name=bulkActionPermission]').val()){
            Acl.bulkAction($('a[class*=permsBtn]'), $('select[name=bulkActionPermission]').val());
            return false;
        }
    });
    $('.bulkActionColumnPermission').click(function(){
        Acl.bulkAction($('a.permsBtn-' + $(this).attr('id')), $(this).attr('rel'));
        return false;
    });
    $('a[class*=permsBtn]').click(function(){
        Acl.bulkAction(this);
        return false;
    });

    /**
     * Configurações da GRID
     */
     $('.bulkActionRows').click(function(a){
        $(this).parent().eq(0).find(':checkbox').check();
     });

})

var Acl = {
    bulkAction:function(a, bulkAction){
        var label;
        var color;
        var dayBlock;
        var action;
        var btn;
        $(a).each(function(){
            btn = this;
            dayBlock = $(btn).parents('.day-block:eq(0)');
            action = (bulkAction)?bulkAction:$(btn).attr('rel');

            dayBlock.find(':hidden:eq(0)').val(action);
            dayBlock.find('a[class*=permsBtn-]')
            .removeClass('custom-green')
            .removeClass('custom-red')
            .removeClass('custom-grey');
            switch(action){
                case 'allow':
                    label = 'Permitir';
                    color = 'green';
                break;
                case 'deny':
                    label = 'Negar';
                    color = 'red';
                break;
                case 'inherit':
                    label = 'Herdar';
                    color = 'grey';
                break;
            }
            dayBlock.find('a[rel=' + action + ']').addClass('custom-' + color);

            dayBlock.find('.txt-info')
            .css('color', color)
            .html(label);
        });
    }
}

/**
 * Plugin para textos padroes em inputs
 */
 jQuery.fn.labelify=function(a){a=jQuery.extend({text:"title",labelledClass:""},a);var b={title:function(a){return $(a).attr("title")},label:function(a){return $("label[for="+a.id+"]").text()}};var c;var d=$(this);return $(this).each(function(){if(typeof a.text==="string"){c=b[a.text]}else{c=a.text}if(typeof c!=="function"){return}var e=c(this);if(!e){return}$(this).data("label",c(this).replace(/\n/g,""));$(this).focus(function(){if(this.value===$(this).data("label")){this.value=this.defaultValue;$(this).removeClass(a.labelledClass)}}).blur(function(){if(this.value===this.defaultValue){this.value=$(this).data("label");$(this).addClass(a.labelledClass)}});var f=function(){d.each(function(){if(this.value===$(this).data("label")){this.value=this.defaultValue;$(this).removeClass(a.labelledClass)}})};$(this).parents("form").submit(f);$(window).unload(f);if(this.value!==this.defaultValue){return}this.value=$(this).data("label");$(this).addClass(a.labelledClass)})}