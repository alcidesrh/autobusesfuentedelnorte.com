crearCaja = {
    
    funcionesAddOnload : function() {
//        console.debug("crearCaja.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("crearCaja.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        if(!core.isMovil()){
            $("#crear_caja_command_moneda").select2({
                allowClear: true
            });
            $("#crear_caja_command_usuario").select2({
                allowClear: true
            });
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
         
        $("#aceptar").click(crearCaja.clickAceptar); 
    },
    
    clickAceptar: function(e) {
        e.preventDefault();
        e.stopPropagation();
        var cajaForm = $("#cajaForm");
        if(core.customValidateForm(cajaForm) === true){
            $(cajaForm).ajaxSubmit({
                target: cajaForm.attr('action'),
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
                            core.getPageForMenu($("#pathHomeCaja").attr("value"));
                        });
                    }
                }
           });
        }
    }
};
