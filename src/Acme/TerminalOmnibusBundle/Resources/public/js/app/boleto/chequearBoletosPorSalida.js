chequearBoletosPorSalida = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $("#chequear_boleto_salida_command_salida").select2({
            allowClear: false
        });
        chequearBoletosPorSalida.buscarBoletos();
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
        
        $("#chequear_boleto_salida_command_salida").on("change", chequearBoletosPorSalida.buscarBoletos);
        $("#aceptar").click(chequearBoletosPorSalida.clickAceptar);
        
        $("#manifiestoInterno").data("index", "chequear_boleto_salida_command_salida");
        $("#manifiestoInterno").data("autoopenfile", "PDF");
        $("#manifiestoInterno").click(frondend.loadSubPage);
        
        $("#manifiestoPiloto").data("index", "chequear_boleto_salida_command_salida");
        $("#manifiestoPiloto").data("autoopenfile", "PDF");
        $("#manifiestoPiloto").click(frondend.loadSubPage);
    },
    
    buscarBoletos : function(e) {
        console.debug("buscarBoletos-init");
        $("#boletoBody").find("tr").not("#boletoVacioTR").remove(); //Elimino todos los tr
        $("#boletoBody").find("#boletoVacioTR").show();
        var salida = $("#chequear_boleto_salida_command_salida").val();
        if(salida !== null && $.trim(salida) !== ""){
           core.request({
                url : $("#pathListarBoletosAChequear").attr('value'),
                method: 'POST',
                extraParams: {
                    salida : salida
                }, 
                dataType: "html",
                async:false,
                successCallback: function(success){
                    console.debug(success);
                    if($.trim(success) !== ""){
                        $("#boletoBody").find("#boletoVacioTR").hide();
                        $("#boletoBody").append(success);
                        $('.chequeadoSI').not($(".chequeadoSI[disabled]")).bind("click", (function(e) { 
                            $(this).parent().parent().find(".chequeadoNO").prop("checked", false);
                        }));
                        $('.chequeadoNO').not($(".chequeadoNO[disabled]")).bind("click", (function(e) {
                            $(this).parent().parent().find(".chequeadoSI").prop("checked", false);
                        }));
                    }
                }
           });
        }
    },
    
    clickAceptar: function(e) {
//        console.debug("clickAceptar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        
        var idBoletosSI = [];
        var boletosSI = $(".chequeadoSI").not($(".chequeadoSI[disabled]"));
        $.each(boletosSI, function (){
            var checked = $(this).prop("checked");
            if(checked){
                idBoletosSI.push($(this).parent().parent().data("id"));
            } 
        });
        console.debug(idBoletosSI);
        
        var idBoletosNO = [];
        var boletosNO = $(".chequeadoNO").not($(".chequeadoNO[disabled]"));
        $.each(boletosNO, function (){
            var checked = $(this).prop("checked");
            if(checked){
                idBoletosNO.push($(this).parent().parent().data("id"));
            } 
        });
        console.debug(idBoletosNO);
        
        if(idBoletosSI.length <= 0 && idBoletosNO.length <= 0){
            alert("Debe seleccionar un boleto para chequear.");
            return;
        }
        
        var salida = $("#chequear_boleto_salida_command_salida").val();
        if(salida !== null && $.trim(salida) !== ""){
           core.request({
                url : $("#chequearBoletosPorSalida").attr('action'),
                method: 'POST',
                extraParams: {
                    salida : salida,
                    idBoletosSI: idBoletosSI.join(","),
                    idBoletosNO: idBoletosNO.join(",")
                }, 
                dataType: "html",
                async:false,
                successCallback: function(responseText){
                    console.debug(responseText);
                    if(!core.procesarRespuestaServidor(responseText)){
                        alert("Operación realizada satisfactoriamente.", function() {
//                            core.getPageForMenu($("#chequearBoletosPorSalida").attr("action"));
                              chequearBoletosPorSalida.buscarBoletos();
                        });
                    }
                }
           });
        }
    }
};
