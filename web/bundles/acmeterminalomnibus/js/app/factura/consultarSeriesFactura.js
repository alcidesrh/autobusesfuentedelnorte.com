consultarSeriesFactura = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        if(!core.isMovil()){
            $("#consultar_serie_factura_command_estacion").select2({
                allowClear: $("#consultar_serie_factura_command_estacion option[value='']").length === 1
            });
            $("#consultar_serie_factura_command_estacion").select2("readonly", ($("#consultar_serie_factura_command_estacion").attr("readonly") === "readonly"));
        }
        
     },
     
    _conectEvents : function() {
      
       $("#consultar_serie_factura_command_estacion").on("change", consultarSeriesFactura.loadFacturas);
    },
    
    loadFacturas : function() {
        
       var estacion = $("#consultar_serie_factura_command_estacion").val();
       if(estacion === null || $.trim(estacion) === ""){
           $("#facturasBody").find("tr").not("#trNoExitenFacturas").remove(); //Elimino todos los tr
           $("#facturasBody").find("#trNoExitenFacturas").show();        
       }else{
           core.request({
                url : $("#pathAjaxListarFacturas2").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { 
                    estacion: estacion
                },
                successCallback: function(data){
                    $("#facturasBody").find("tr").not("#trNoExitenFacturas").remove();
                    var optionFacturas = data.optionFacturas;
                    if( optionFacturas && optionFacturas.length > 0){
                        $("#facturasBody").find("#trNoExitenFacturas").hide();
                        $.each(optionFacturas, function() {  
                            var itemTR = $("<tr><td>"+this.srf+"</td><td>"+this.min+"</td><td>"+this.max+"</td><td>"+this.emp+"</td><td>"+this.servicio+"</td><td>"+this.factEspecial+"</td><td>"+this.ping+"</td></tr>");
                            $("#facturasBody").append(itemTR);
                         });
                    }
                    else{
                        $("#facturasBody").find("#trNoExitenFacturas").show();   
                    }
                }
             });
       }
    }
};
