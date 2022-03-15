reservacion = {
    
     funcionesAddOnload : function() {
        console.debug("reservacion.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("reservacion.funcionesAddOnload-end");
    },
			
    _init : function() {
       
         this.checkDisabledAsiento();
         $("#acme_backendbundle_reservacion_type_observacionDestinoIntermedio").css("width", "486px");
         $("#acme_backendbundle_reservacion_type_observacion").css("width", "486px");
         $("#acme_backendbundle_reservacion_type > ul").hide(); //Ocultar los error pq aparecen duplicados.
     },
    
    _conectEvents : function() {
        
        $("select[id*='salida']").bind("click", function() {
             reservacion.loadAsientos();
        });
    },
    
    loadAsientos : function(init) {
        console.debug("loadAsientos-init");
        
        $("select[id*='asientoBus']").val("");
        $("select[id*='asientoBus']").parent().find(".select2-chosen").text("");
        reservacion.checkDisabledAsiento(true);
        var idSalida = $("select[id*='salida']").val();
        if(idSalida === null || $.trim(idSalida) === ""){
            return;
        }
        
        var idSalida = $("select[id*='salida']").val();
        $.ajax({
            type: "GET",
            data: "data=" + idSalida,
            url: $("#acme_backendbundle_reservacion_type_listaAsientosBySalidaPath").val(),
            success: function(msg){
                if (msg !== ''){
                    reservacion.checkDisabledAsiento();
                    $("select[id*='asientoBus']").html(msg);
                    $("select[id*='asientoBus']").val("");
                }else{
                    reservacion.checkDisabledAsiento(true);
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