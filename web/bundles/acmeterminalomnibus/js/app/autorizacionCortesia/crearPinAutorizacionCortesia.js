crearPinAutorizacionCortesia = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        if(!core.isMovil()){
            $("#crear_autorizacion_cortesia_multiple_command_usuarioNotificacion").select2({
                allowClear: $("#crear_autorizacion_cortesia_multiple_command_usuarioNotificacion option[value='']").length === 1
            });
            $("#crear_autorizacion_cortesia_multiple_command_usuarioNotificacion").select2("readonly", ($("#crear_autorizacion_cortesia_multiple_command_usuarioNotificacion").attr("readonly") === "readonly"));
        }
    },
     
    _conectEvents : function() {
        
        $("#cancelar").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            confirm("¿Está seguro que desea cancelar la operación?", function(confirmed){
                if(confirmed === true){
                    core.getPageForMenu($("#cancelar").attr('href'));
                }
            });
        });
        
        $("#aceptar").click(crearPinAutorizacionCortesia.clickAceptar); 
        
    },
    
    clickAceptar: function(e) {
        e.preventDefault();
        e.stopPropagation();
        var autorizacionCortesiaForm = $("#autorizacionCortesiaForm");
        if(core.customValidateForm(autorizacionCortesiaForm) === true){
            $(autorizacionCortesiaForm).ajaxSubmit({
                target: autorizacionCortesiaForm.attr('action'),
                type : "POST",
                dataType: "html",
                cache : false,
                async:false,
                beforeSubmit: function() { 
                    core.showLoading({showLoading:true});
                },
               error: function() {
                    core.hideLoading({showLoading:true});
               },
               success: function(responseText) {
                    core.hideLoading({showLoading:true});
                    if(!core.procesarRespuestaServidor(responseText)){
                        alert("Operación realizada satisfactoriamente.", function() {
                            core.getPageForMenu($("#cancelar").attr('href'));
                        });
                    }
                }
           });
        }
    },
    
};
