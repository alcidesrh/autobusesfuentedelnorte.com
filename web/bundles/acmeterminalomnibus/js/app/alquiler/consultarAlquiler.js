consultarAlquiler = {
    
    funcionesAddOnload : function() {
//        console.debug("consultarAlquiler.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("consultarAlquiler.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $('#consultar_alquiler_command_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            dateLimit : moment.duration(90, 'd')
        });
        
        $("#consultar_alquiler_command_empresa").select2({
            allowClear: $("#consultar_alquiler_command_empresa option[value='']").length === 1
        });
        $("#consultar_alquiler_command_empresa").select2("readonly", ($("#consultar_alquiler_command_empresa").attr("readonly") === "readonly"));
        
        $("#consultar_alquiler_command_piloto").select2({
            allowClear: $("#consultar_alquiler_command_piloto option[value='']").length === 1
        });
        $("#consultar_alquiler_command_piloto").select2("readonly", ($("#consultar_alquiler_command_piloto").attr("readonly") === "readonly"));
        
        $("#consultar_alquiler_command_pilotoAux").select2({
            allowClear: $("#consultar_alquiler_command_pilotoAux option[value='']").length === 1
        });
        $("#consultar_alquiler_command_pilotoAux").select2("readonly", ($("#consultar_alquiler_command_pilotoAux").attr("readonly") === "readonly"));
        
        $("#consultar_alquiler_command_bus").select2({
            allowClear: $("#consultar_alquiler_command_bus option[value='']").length === 1
        });
        $("#consultar_alquiler_command_bus").select2("readonly", ($("#consultar_alquiler_command_bus").attr("readonly") === "readonly"));
        
     },
     
    _conectEvents : function() {
        
        $("#cancelar").click(function(e) {
//            console.debug("clic");
//            console.debug($(this));
            e.preventDefault();
            e.stopPropagation();
            confirm("¿Está seguro que desea cancelar la operación?", function(confirmed){
//                console.debug(confirmed);
                if(confirmed === true){
                    core.getPageForMenu($("#cancelar").attr('href'));
                }
            });
        });
    }
    
};
