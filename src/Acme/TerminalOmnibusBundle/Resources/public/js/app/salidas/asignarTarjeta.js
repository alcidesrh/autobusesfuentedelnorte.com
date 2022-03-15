asignarTarjeta = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        asignarTarjeta.checkSelectedOption();
     },
     
    _conectEvents : function() {
        $("input[name='tarjeta_command[tipo]']:radio").change(function (){
            console.log("click");
            asignarTarjeta.checkSelectedOption();
            if($("#tarjeta_command_tipo_1").is(':checked')){
                $("#tarjeta_command_numero").val("");
            }
        });
    },
    
    checkSelectedOption : function() {
        
        if($("#tarjeta_command_tipo_1").is(':checked')){
            $("#numeroDiv").show();
            $("#numeroDiv span.sigla").text("M");
            $("#tarjeta_command_numero").prop("readonly", false);
            
        }else if($("#tarjeta_command_tipo_2").is(':checked')){
            $("#numeroDiv").show();
            $("#numeroDiv span.sigla").text("A");
            $("#tarjeta_command_numero").val($("#tarjeta_command_salida").val());
            $("#tarjeta_command_numero").prop("readonly", true);
            
        }else {
            $("#numeroDiv").hide();
        }
    }
    
};
