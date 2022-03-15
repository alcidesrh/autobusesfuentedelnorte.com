/*
  Personnalizacion de los metodos nativos alert, confirm

 */
(function($) {
	
	/*
	Función general para mostrar una alerta. 
	text: Mensaje con la alerta
	onClouse:Funcion que se ejecuta cuando se presione el botton clouse.(opcional)
	return;
	Ej.alert("Operacion satisfactoria.", function(){console.debug("todo ok.");})
	*/
	window._originalAlert = window.alert;
	window.alert = function(text, onClouse) {
//		console.debug("window.alert");

                //parche para evitar la licencia el plugin de la webcam
                if(text !== null && text === "License file corrupt."){
                    return; 
                }

		 $.extend($.fn.dialog2.helpers.defaults, {
	         alert: {
	             title: "Alerta", 
	             buttonLabelOk: "Aceptar"
	         }
	     });
		 $.fn.dialog2.helpers.alert(text, {
			 closeOnOverlayClick:false, // Should the dialog be closed on overlay click?
			 closeOnEscape:false,// Should the dialog be closed if [ESCAPE] key is pressed?
			 removeOnClose:true,// Should the dialog be removed from the document when it is closed?
			 showCloseHandle:false,// Should a close handle be shown?
			 autoOpen:true,// Should the dialog be automatically opened				
	         close: function() {
//	        	 console.debug("alert.close");
	        	 if(onClouse && jQuery.isFunction(onClouse))
	        		 onClouse();
	         }
	     });
	};
	
	
	/*
	Función general para confirmar una pregunta. 
	text: Mensaje que representa una pregunta al usuario.
	resultFunction:Funcion que se ejecuta cuando el usuario toma la desicion.
	return;
	Ej.confirm("Esta seguro?", function(confirmed){console.debug(confirmed);})
	*/
	window._originalConfirm = window.confirm;
	window.confirm = function(text, resultFunction) {
//		console.debug("window.confirm");
		 $.extend($.fn.dialog2.helpers.defaults, {	        
	         confirm: {
	             title: "Confirmación",
	             buttonLabelYes: "Si",
	             buttonLabelNo: "No"
	         }
	     });
		$.fn.dialog2.helpers.confirm(text, {
			 closeOnOverlayClick:false, // Should the dialog be closed on overlay click?
			 closeOnEscape:false,// Should the dialog be closed if [ESCAPE] key is pressed?
			 removeOnClose:true,// Should the dialog be removed from the document when it is closed?
			 showCloseHandle:false,// Should a close handle be shown?
			 autoOpen:true,// Should the dialog be automatically opened	
			 confirm: function() {
//				 console.debug("confirm.cconsoleonfirm");
				 if(resultFunction && jQuery.isFunction(resultFunction))
					 resultFunction(true);
			 }, 
			 decline: function() {
//				 console.debug("confirm.decline");
				 if(resultFunction && jQuery.isFunction(resultFunction))
					 resultFunction(false); 				 
			 }
       });
	};
	
	/*
	Función general para  mostrar una ventana que captura un texto. 
	message: Mensaje que se mustra junto a la input de texto.
	onOk:Funcion que se ejecuta cuando se presione el botton ok.(opcional)
	onCancel:Funcion que se ejecuta cuando se presione el botton cancel.(opcional)
	return;
	Ej.confirm("Entre el nombre:", function(value, event){console.debug(value);})
	*/
	window._originalPrompt = window.prompt;
	window.prompt = function(text, onOk, onCancel) {
//		console.debug("window.prompt");
		 $.extend($.fn.dialog2.helpers.defaults, {
	         prompt: {
	             title: "Entre el valor",
	             buttonLabelOk: "Aceptar", 
	             buttonLabelCancel: "Cancelar", 
	         }
	     });
		$.fn.dialog2.helpers.prompt(text, {
			 closeOnOverlayClick:false, // Should the dialog be closed on overlay click?
			 closeOnEscape:false,// Should the dialog be closed if [ESCAPE] key is pressed?
			 removeOnClose:true,// Should the dialog be removed from the document when it is closed?
			 showCloseHandle:false,// Should a close handle be shown?
			 autoOpen:true,// Should the dialog be automatically opened	
	         ok: function(event, value) { 
//	        	 console.debug("prompt.ok");
	        	 if(onOk && jQuery.isFunction(onOk))
	        		 onOk(value, event);	        	
	         }, 
	         cancel: function(event) { 
//	        	 console.debug("prompt.cancel");
	        	 if(onCancel && jQuery.isFunction(onCancel))
	        		 onCancel(event);	        	 
	         }
	    });
	};
	
	
	
	/*******************************************************************
	 * Sobreescribiendo valores del componente jquery.dialog2.helpers 
	********************************************************************/	
	/*
	  Se elimino la clase class='span6' del input con el objetivo de que no sea mas que el body por prompt
	*/
	$.fn.dialog2.helpers.prompt = function(message, options) {
         // Special: Dialog has to be closed on escape or multiple inputs
         // with the same id will be added to the DOM!
         options = $.extend({}, options, {closeOnEscape: true});
         var labels = $.extend({}, $.fn.dialog2.helpers.defaults.prompt, options);
         
         var inputId = 'dialog2.helpers.prompt.input.id';
         var input = $("<input type='text' />")
                             .attr("id", inputId)
                             .val(options.defaultValue || "");
                             
         var html = $("<form class='form-stacked'></form>");
         html.append($("<label/>").attr("for", inputId).text(message));
         html.append(input);
         
         var dialog = $("<div />");
         
         var okCallback;
         if (options.ok) {
             var fn = options.ok;
             okCallback = function(event) { fn.call(dialog, event, input.val()); };
         }
         delete options.ok;
         
         var cancelCallback = options.cancel;
         delete options.cancel;
         
         var buttons = {};
         localizedButton(labels.buttonLabelOk, __closeAndCall(okCallback, dialog), buttons);
         localizedButton(labels.buttonLabelCancel, __closeAndCall(cancelCallback, dialog), buttons);
         
			// intercept form submit (on ENTER press)
			html.bind("submit", __closeAndCall(okCallback, dialog));
			
         __open(dialog, html, labels.title, buttons, options);
     };
     
 	var localizedButton = $.fn.dialog2.localization.localizedButton;
 	
     function __closeAndCall(callback, dialog) {
         return $.proxy(function(event) {
 			event.preventDefault();
 			
             $(this).dialog2("close");
             
             if (callback) {
                 callback.call(this, event);
             }
         }, dialog || this);
     };
     
     function __closeAndCall(callback, dialog) {
         return $.proxy(function(event) {
 			event.preventDefault();
 			
             $(this).dialog2("close");
             
             if (callback) {
                 callback.call(this, event);
             }
         }, dialog || this);
     };
     
     function __open(e, message, title, buttons, options) {
         options.buttons = buttons;
         options.title = title;
         
         return e.append(message).dialog2(options);
     };
     

})(jQuery);
