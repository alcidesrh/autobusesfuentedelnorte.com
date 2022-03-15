asignarSalida = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $("#asignar_salida_command_bus").select2({
            allowClear: true
        });
        
        $("#asignar_salida_command_piloto").select2({
            allowClear: true
        });
        
        $("#asignar_salida_command_pilotoAux").select2({
            allowClear: true
        });
        
     },
     
    _conectEvents : function() {
    
    }
    
};
