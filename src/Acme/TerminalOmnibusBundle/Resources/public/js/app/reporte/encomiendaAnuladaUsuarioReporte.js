encomiendaAnuladaUsuarioReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("encomiendaAnuladaUsuarioReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
//	console.debug("encomiendaAnuladaUsuarioReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#encomiendaAnuladoUsuario_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(90, 'd')
        });
        
        if(!core.isMovil()){
            $("#encomiendaAnuladoUsuario_moneda").select2({
                allowClear: $("#encomiendaAnuladoUsuario_moneda option[value='']").length === 1
            });

            $("#encomiendaAnuladoUsuario_estacion").select2({
                allowClear: $("#encomiendaAnuladoUsuario_estacion option[value='']").length === 1
            });
            $("#encomiendaAnuladoUsuario_estacion").select2("readonly", ($("#encomiendaAnuladoUsuario_estacion").attr("readonly") === "readonly"));

            $("#encomiendaAnuladoUsuario_empresa").select2({
                allowClear: $("#encomiendaAnuladoUsuario_empresa option[value='']").length === 1
            });
            $("#encomiendaAnuladoUsuario_empresa").select2("readonly", ($("#encomiendaAnuladoUsuario_empresa").attr("readonly") === "readonly"));

            $("#encomiendaAnuladoUsuario_usuario").select2({
                allowClear: $("#encomiendaAnuladoUsuario_usuario option[value='']").length === 1
            });
            $("#encomiendaAnuladoUsuario_usuario").select2("readonly", ($("#encomiendaAnuladoUsuario_usuario").attr("readonly") === "readonly"));            
        }

    }
};
