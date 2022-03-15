cuadreVentaBoletoVoucherReporte = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
        frondendReporte.funcionesAddOnload();
    },
			
    _init : function() {
        
     },
     
    _conectEvents : function() {
        
        $('#cuadreVentaBoletoVoucher_rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY'
        });    
        
        if(!core.isMovil()){
            $("#cuadreVentaBoletoVoucher_estacion").select2({
                allowClear: $("#cuadreVentaBoletoVoucher_estacion option[value='']").length === 1
            });
            $("#cuadreVentaBoletoVoucher_estacion").select2("readonly", ($("#cuadreVentaBoletoVoucher_estacion").attr("readonly") === "readonly"));

            $("#cuadreVentaBoletoVoucher_empresa").select2({
                allowClear: $("#cuadreVentaBoletoVoucher_empresa option[value='']").length === 1
            });
            $("#cuadreVentaBoletoVoucher_empresa").select2("readonly", ($("#cuadreVentaBoletoVoucher_empresa").attr("readonly") === "readonly"));    
        }   
    }
};
