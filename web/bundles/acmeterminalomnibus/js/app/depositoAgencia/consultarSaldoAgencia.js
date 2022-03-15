consultarSaldoAgencia = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        if(!core.isMovil()){
            $("#consultar_saldos_command_estacion").select2({
                allowClear: $("#consultar_saldos_command_estacion option[value='']").length === 1
            });
            $("#consultar_saldos_command_estacion").select2("readonly", ($("#consultar_saldos_command_estacion").attr("readonly") === "readonly"));
        }
        
     },
     
    _conectEvents : function() {
        
        $("#consultar_saldos_command_estacion").on("change", consultarSaldoAgencia.loadFacturas);
        
        $("#cancelar").click(function(e) {
//            console.debug("clic");
//            console.debug($(this));
            e.preventDefault();
            e.stopPropagation();
            core.getPageForMenu($("#cancelar").attr('href'));
        });
    },
    
    loadFacturas : function() {
        
       var estacion = $("#consultar_saldos_command_estacion").val();
       if(estacion === null || $.trim(estacion) === ""){
           $("#estado").val("");
           $("#saldo").val("0.00");
           $("#bono").val("0.00");
           $("#total").val("0.00");
           $("#totalDepositado").val("0.00");
       }else{
           core.request({
                url : $("#pathAjaxListarSaldosAgencias").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { 
                    estacion: estacion
                },
                successCallback: function(data){
                    $("#estado").val(data.estado);
                    $("#saldo").val(data.saldo);
                    $("#bono").val(data.bonif);
                    $("#total").val(data.total);
                    $("#totalDepositado").val(data.totalDepositado);
                }
             });
       }
    }
    
};
