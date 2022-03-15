boleto = {
    
     funcionesAddOnload : function() {
        console.debug("boleto.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("boleto.funcionesAddOnload-end");
    },
			
    _init : function() {
       
         this.checkDisabledAsiento();
         $("#acme_backendbundle_boleto_type_observacionDestinoIntermedio").css("width", "486px");
         $("#acme_backendbundle_boleto_type_observacion").css("width", "486px");
//         $("#acme_backendbundle_boleto_type_codigoBarra").css("width", "486px");
         $("#acme_backendbundle_boleto_type_precioCalculado").css("width", "486px");
         $("#acme_backendbundle_boleto_type > ul").hide(); //Ocultar los error pq aparecen duplicados.
     },
    
    _conectEvents : function() {
        
        $("select[id*='salida']").change(function () {
             boleto.loadAsientos();
        });
        $("select[id*='tarifa']").change(function () {
             boleto.setPrecioCalculado();
        });
    },
    
    setPrecioCalculado : function() {
        console.debug("setPrecioCalculado-init");
        var idTarifa = $("select[id*='tarifa']").val();
        if(idTarifa === null || $.trim(idTarifa) === ""){
            $("input[id*='precioCalculado']").val(""); 
        }else{
            var precioCalculado = "";
            var itemsPrecios = $(".listaPreciosdeTarifaHidden").val();
            if(itemsPrecios){ itemsPrecios = JSON.parse(itemsPrecios); }
            else{ itemsPrecios = []; }
            if(itemsPrecios[idTarifa]){
                precioCalculado = itemsPrecios[idTarifa].tarifaValor;
            }
            $("input[id*='precioCalculado']").val(precioCalculado); 
        }
    },
    
    loadAsientos : function(init) {
        console.debug("loadAsientos-init");
        
        $("select[id*='asientoBus']").val("");
        $("select[id*='asientoBus']").parent().find(".select2-chosen").text("");
        boleto.checkDisabledAsiento(true);
        var idSalida = $("select[id*='salida']").val();
        if(idSalida === null || $.trim(idSalida) === ""){
            return;
        }
        
        var idSalida = $("select[id*='salida']").val();
        $.ajax({
            type: "GET",
            data: "data=" + idSalida,
            url: $("#acme_backendbundle_boleto_type_listaAsientosBySalidaPath").val(),
            success: function(msg){
                if (msg !== ''){
                    boleto.checkDisabledAsiento();
                    $("select[id*='asientoBus']").html(msg);
                    $("select[id*='asientoBus']").val("");
                }else{
                    boleto.checkDisabledAsiento(true);
                }
            }
        });
    },
    
   checkDisabledAsiento : function(forceDisable) {
       var idSalida = $("select[id*='salida']").val();
       if(forceDisable === true || idSalida === null || $.trim(idSalida) === ""){
           $("select[id*='asientoBus']").html("");
           $("select[id*='asientoBus']").val("");
           $("select[id*='asientoBus']").parent().parent().hide();
       }else{
           $("select[id*='asientoBus']").parent().parent().show();
       }
       
   }

}