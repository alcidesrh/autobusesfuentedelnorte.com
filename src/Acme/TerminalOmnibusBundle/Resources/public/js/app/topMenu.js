topMenu = {
    
    funcionesAddOnload : function() {
//        console.debug("topMenu.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("topMenu.funcionesAddOnload-end");
    },
		
    _init : function() {
        
    },
    
    _conectEvents : function() {
        
        $("#consultarTarifaBoleto").click(topMenu.clickConsultarTarifaBoleto);
        $("#consultarTarifaEncomienda").click(topMenu.clickConsultarTarifaEncomienda);
    },
    
    /**************************       BOLETO INIT      ********************************************/
    clickConsultarTarifaBoleto :function(e) {
//        console.log("clickConsultarTarifaBoleto-init");
        e.preventDefault();
        e.stopPropagation();
        core.request({
            url : $(this).attr('href'),
            method: "GET",
            async: false,
            successCallback: function(data){
                core.showMessageDialog({
                    title : "Consultar Tarifa de Boleto",
                    text: $(data),
                    removeOnClose : true,
                    showCloseHandle : true
                });
                topMenu._initBoleto();
            }
        });
//        console.log("clickConsultarTarifaBoleto-end");
    },
    
    _initBoleto : function() {
        
        $("#consultarTarifaBoletoEstacionOrigen").select2({
            allowClear: true,
            data: []
        });
        $("#consultarTarifaBoletoEstacionOrigen").on("change", topMenu.getTarifaBoleto);
        $("#consultarTarifaBoletoEstacionDestino").select2({
            allowClear: true,
            data: []
        });
        $("#consultarTarifaBoletoEstacionDestino").on("change", topMenu.getTarifaBoleto);
        $("#consultarTarifaBoletoClaseBus").select2({
            allowClear: true,
            data: []
        });
        $("#consultarTarifaBoletoClaseBus").on("change", topMenu.getTarifaBoleto);
        $("#consultarTarifaBoletoClaseAsiento").select2({
            allowClear: true,
            data: []
        });
        $("#consultarTarifaBoletoClaseAsiento").on("change", topMenu.getTarifaBoleto);
        $("#consultarTarifaBoletoTipoPago").select2({
            allowClear: true,
            data: []
        });
        $("#consultarTarifaBoletoTipoPago").on("change", topMenu.getTarifaBoleto);
        $('#consultarTarifaBoletoHoraInicialSalida').timepicker({
            minuteStep: 1,
            showInputs: true,
            template: 'modal',
//            defaultTime: false,
//            showMeridian: false
        });
        $('#consultarTarifaBoletoHoraInicialSalida').on('hide.timepicker', topMenu.getTarifaBoleto);
//        $('#consultarTarifaBoletoHoraFinalSalida').timepicker({
//            minuteStep: 1,
//            showInputs: true,
//            template: 'modal',
//            defaultTime: false
//        });
//        $('#consultarTarifaBoletoHoraFinalSalida').on('hide.timepicker', topMenu.getTarifaBoleto);
        topMenu.loadDatosConsultarTarifaBoleto();
    },
    
    loadDatosConsultarTarifaBoleto :function() {
        core.request({
            url : $("#pathLoadDatosConsultarTarifaBoletoTopMenu").prop("value"),
            method: "GET",
            dataType: "json",
            async: true,
            showLoading: false,
            successCallback: function(data){
                $("#consultarTarifaBoletoEstacionOrigen").select2({
                    allowClear: true,
                    data: { results: data.optionEstaciones }
                });
                $("#consultarTarifaBoletoEstacionDestino").select2({
                    allowClear: true,
                    data: { results: data.optionEstaciones }
                });
                $("#consultarTarifaBoletoClaseBus").select2({
                    allowClear: true,
                    data: { results: data.optionsClasesBus }
                });
                $("#consultarTarifaBoletoClaseAsiento").select2({
                    allowClear: true,
                    data: { results: data.optionClasesAsiento }
                });
                $("#consultarTarifaBoletoTipoPago").select2({
                    allowClear: true,
                    data: { results: data.optionsTipoPago }
                });
                $("#consultarTarifaBoletoTipoPago").select2('val', data.optionsTipoPago[0].id);
            }
        });
    },
    
    getTarifaBoleto :function() {
//        console.log("getTarifaBoleto-init");
        var consultarTarifaBoletoEstacionOrigen = $("#consultarTarifaBoletoEstacionOrigen").val();
//        console.log("consultarTarifaBoletoEstacionOrigen:" + consultarTarifaBoletoEstacionOrigen);
        var consultarTarifaBoletoEstacionDestino = $("#consultarTarifaBoletoEstacionDestino").val();
//        console.log("consultarTarifaBoletoEstacionDestino:" + consultarTarifaBoletoEstacionDestino);
        var consultarTarifaBoletoClaseBus = $("#consultarTarifaBoletoClaseBus").val();
//        console.log("consultarTarifaBoletoClaseBus:" + consultarTarifaBoletoClaseBus);
        var consultarTarifaBoletoClaseAsiento = $("#consultarTarifaBoletoClaseAsiento").val();
//        console.log("consultarTarifaBoletoClaseAsiento:" + consultarTarifaBoletoClaseAsiento);
        var consultarTarifaBoletoTipoPago = $("#consultarTarifaBoletoTipoPago").val();
//        console.log("consultarTarifaBoletoTipoPago:" + consultarTarifaBoletoTipoPago);
        var consultarTarifaBoletoHoraInicialSalida = $("#consultarTarifaBoletoHoraInicialSalida").val();
//        console.log("consultarTarifaBoletoHoraInicialSalida:" + consultarTarifaBoletoHoraInicialSalida);
        if(consultarTarifaBoletoEstacionOrigen !== null && $.trim(consultarTarifaBoletoEstacionOrigen) !== "" && $('#consultarTarifaBoletoEstacionOrigen')[0].checkValidity() && 
           consultarTarifaBoletoEstacionDestino !== null && $.trim(consultarTarifaBoletoEstacionDestino) !== "" && $('#consultarTarifaBoletoEstacionDestino')[0].checkValidity() &&      
           consultarTarifaBoletoClaseBus !== null && $.trim(consultarTarifaBoletoClaseBus) !== "" && $('#consultarTarifaBoletoClaseBus')[0].checkValidity() && 
           consultarTarifaBoletoClaseAsiento !== null && $.trim(consultarTarifaBoletoClaseAsiento) !== "" && $('#consultarTarifaBoletoClaseAsiento')[0].checkValidity() && 
           consultarTarifaBoletoTipoPago !== null && $.trim(consultarTarifaBoletoTipoPago) !== "" && $('#consultarTarifaBoletoTipoPago')[0].checkValidity() &&
           $('#consultarTarifaBoletoHoraInicialSalida')[0].checkValidity()){
//           console.log("buscando tarifa....");
           core.request({
                url : $("#pathGetTarifaBoletoTopMenu").prop("value"),
                method: "POST",
                dataType: "json",
                async: false,
                extraParams : { 
                    estacionOrigen: consultarTarifaBoletoEstacionOrigen,
                    estacionDestino: consultarTarifaBoletoEstacionDestino,
                    claseBus: consultarTarifaBoletoClaseBus,
                    claseAsiento: consultarTarifaBoletoClaseAsiento,
                    tipoPago: consultarTarifaBoletoTipoPago,
                    horaInicialSalida: consultarTarifaBoletoHoraInicialSalida
                },
                successCallback: function(data){
//                    console.log("successCallback....");
                    $("#consultarTarifaBoletoTarifa").val(data.result);
                }
            });
       }else{
           $("#consultarTarifaBoletoTarifa").val("");
       }
    },
    /**************************       BOLETO END      ********************************************/
    
    /**************************       Tarifa Encomienda INIT      ********************************************/
    clickConsultarTarifaEncomienda :function(e) {
        e.preventDefault();
        e.stopPropagation();
        core.request({
            url : $(this).attr('href'),
            method: "GET",
            async: false,
            successCallback: function(data){
                core.showMessageDialog({
                    title : "Consultar Tarifa de Encomienda",
                    text: $(data),
                    removeOnClose : true,
                    showCloseHandle : true
                });
                topMenu._initTarifaEncomienda();
            }
        });
    },
     _initTarifaEncomienda : function() {

        $("#consultarTarifaEncomiendaEstacionOrigen").select2({
            allowClear: true,
            data: []
        });
//        $("#consultarTarifaEncomiendaEstacionOrigen").on("change", topMenu.getTarifaEncomienda);
        
        $("#consultarTarifaEncomiendaEstacionDestino").select2({
            allowClear: true,
            data: []
        });
//        $("#consultarTarifaEncomiendaEstacionDestino").on("change", topMenu.getTarifaEncomienda);
        
       $("#consultarTarifaEncomiendaTipoEncomienda").select2({
            allowClear: true,
            data: []
        });
//        $("#consultarTarifaEncomiendaTipoEncomienda").on("change", topMenu.getTarifaEncomienda);
        $("#consultarTarifaEncomiendaTipoEncomienda").on("change", topMenu.checkTipoEncomiena);
        
        $("#consultarTarifaEncomiendaTipoEncomiendaEspecial").select2({
            allowClear: true,
            data: []
        });
//        $("#consultarTarifaEncomiendaTipoEncomiendaEspecial").on("change", topMenu.getTarifaEncomienda);
        
        //INPUTS
//        $("#consultarTarifaEncomiendaCantidad").change(topMenu.getTarifaEncomienda);
//        $("#consultarTarifaEncomiendaPeso").change(topMenu.getTarifaEncomienda);
//        $("#consultarTarifaEncomiendaAlto").change(topMenu.getTarifaEncomienda);
//        $("#consultarTarifaEncomiendaAncho").change(topMenu.getTarifaEncomienda);
//        $("#consultarTarifaEncomiendaProfundidad").change(topMenu.getTarifaEncomienda);
        
        $("input.changeTarifaTopMenu").on('change', topMenu.getTarifaEncomienda);
        
        topMenu.checkTipoEncomiena();
        topMenu.loadDatosConsultarEncomienda();
    },
    
    loadDatosConsultarEncomienda :function() {
        
        $("#consultarTarifaEncomiendaEstacionOrigen").select2("val", "");
        $("#consultarTarifaEncomiendaEstacionDestino").select2("val", "");
        $("#consultarTarifaEncomiendaTipoEncomienda").select2("val", "");
        $("#consultarTarifaEncomiendaCantidad").val("");
        $("#consultarTarifaEncomiendaTipoEncomiendaEspecial").select2("val", "");
        $("#consultarTarifaEncomiendaPeso").val("");
        $("#consultarTarifaEncomiendaAlto").val("");
        $("#consultarTarifaEncomiendaAncho").val("");
        $("#consultarTarifaEncomiendaProfundidad").val("");
        
        core.request({
            url : $("#pathLoadDatosConsultarTarifaEncomiendaTopMenu").prop("value"),
            method: "GET",
            dataType: "json",
            async: true,
            showLoading: false,
            successCallback: function(data){
                $("#consultarTarifaEncomiendaEstacionOrigen").select2({
                    allowClear: true,
                    data: { results: data.optionEstaciones }
                });
                $("#consultarTarifaEncomiendaEstacionDestino").select2({
                    allowClear: true,
                    data: { results: data.optionEstaciones }
                });
                $("#consultarTarifaEncomiendaTipoEncomienda").select2({
                    allowClear: true,
                    data: { results: data.optionTipoEncomienda }
                });
                $("#consultarTarifaEncomiendaTipoEncomiendaEspecial").select2({
                    allowClear: true,
                    data: { results: data.optionTipoEncomiendaEspecial }
                });
            }
        });
    },
    
    getTarifaEncomienda :function() {
        console.log("getTarifaEncomiendaEfectivo-init");
        var consultarTarifaEncomiendaEstacionOrigen = $("#consultarTarifaEncomiendaEstacionOrigen").val();
        var consultarTarifaEncomiendaEstacionDestino = $("#consultarTarifaEncomiendaEstacionDestino").val();
        var consultarTarifaEncomiendaTipoEncomienda = $("#consultarTarifaEncomiendaTipoEncomienda").val();
        var consultarTarifaEncomiendaCantidad = $("#consultarTarifaEncomiendaCantidad").val();
        var consultarTarifaEncomiendaTipoEncomiendaEspecial = $("#consultarTarifaEncomiendaTipoEncomiendaEspecial").val();
        var consultarTarifaEncomiendaPeso = $("#consultarTarifaEncomiendaPeso").val();
        var consultarTarifaEncomiendaAlto = $("#consultarTarifaEncomiendaAlto").val();
        var consultarTarifaEncomiendaAncho = $("#consultarTarifaEncomiendaAncho").val();
        var consultarTarifaEncomiendaProfundidad = $("#consultarTarifaEncomiendaProfundidad").val();
        
        if(
            consultarTarifaEncomiendaEstacionOrigen !== null && 
            $.trim(consultarTarifaEncomiendaEstacionOrigen) !== "" && 
            consultarTarifaEncomiendaEstacionDestino !== null && 
            $.trim(consultarTarifaEncomiendaEstacionDestino) !== "" && 
            consultarTarifaEncomiendaTipoEncomienda !== null && 
            $.trim(consultarTarifaEncomiendaTipoEncomienda) !== "" && 
            consultarTarifaEncomiendaCantidad !== null && 
            $.trim(consultarTarifaEncomiendaCantidad) !== "" && 
            $('#consultarTarifaEncomiendaCantidad')[0].checkValidity()
          )
          {
              if(consultarTarifaEncomiendaTipoEncomienda === "2" && (consultarTarifaEncomiendaTipoEncomiendaEspecial === null ||
                    $.trim(consultarTarifaEncomiendaTipoEncomiendaEspecial) === "")){
                    $("#consultarTarifaEncomiendaTarifa").val("");
                    return;
              }
              
              if(consultarTarifaEncomiendaTipoEncomienda === "3" && 
                 (  
                    consultarTarifaEncomiendaPeso === null || $.trim(consultarTarifaEncomiendaPeso) === "" || !$('#consultarTarifaEncomiendaCantidad')[0].checkValidity() ||
                    consultarTarifaEncomiendaAlto === null || $.trim(consultarTarifaEncomiendaAlto) === "" || !$('#consultarTarifaEncomiendaAlto')[0].checkValidity() ||
                    consultarTarifaEncomiendaAncho === null || $.trim(consultarTarifaEncomiendaAncho) === "" || !$('#consultarTarifaEncomiendaAncho')[0].checkValidity() ||
                    consultarTarifaEncomiendaProfundidad === null || $.trim(consultarTarifaEncomiendaProfundidad) === "" || !$('#consultarTarifaEncomiendaProfundidad')[0].checkValidity()
                  ))
              {
                    $("#consultarTarifaEncomiendaTarifa").val("");
                    return;
              }
              
              core.request({
                    url : $("#pathGetTarifaEncomiendaTopMenu").prop("value"),
                    method: "POST",
                    dataType: "json",
                    async: false,
                    extraParams : { 
                        estacionOrigen: consultarTarifaEncomiendaEstacionOrigen,
                        estacionDestino: consultarTarifaEncomiendaEstacionDestino,
                        tipoEncomienda: consultarTarifaEncomiendaTipoEncomienda,
                        cantidad: consultarTarifaEncomiendaCantidad,
                        tipoEncomiendaEspecial: consultarTarifaEncomiendaTipoEncomiendaEspecial,
                        peso: consultarTarifaEncomiendaPeso,
                        alto: consultarTarifaEncomiendaAlto,
                        ancho: consultarTarifaEncomiendaAncho,
                        profundidad: consultarTarifaEncomiendaProfundidad
                    },
                    successCallback: function(data){
                    $("#consultarTarifaEncomiendaTarifa").val(data.result);
                    }
              });
            
       }else{
           $("#consultarTarifaEncomiendaTarifa").val("");
       }
    },
    
    checkTipoEncomiena : function() {
        var tipoEncomienda = $("#consultarTarifaEncomiendaTipoEncomienda").val();
        if(tipoEncomienda === null || $.trim(tipoEncomienda) === ""){
            $("#cteTipoEncomiendaEspecialDIV").hide();
            $("#ctePaqueteDIV").hide();
            $("#consultarTarifaEncomiendaCantidad").val("");
            $("#consultarTarifaEncomiendaTipoEncomiendaEspecial").select2("val", "");
            $("#consultarTarifaEncomiendaPeso").val("");
            $("#consultarTarifaEncomiendaAlto").val("");
            $("#consultarTarifaEncomiendaAncho").val("");
            $("#consultarTarifaEncomiendaProfundidad").val("");
        }else if($.trim(tipoEncomienda) === "1"){ //Efectivo
            $("#cteTipoEncomiendaEspecialDIV").hide();
            $("#ctePaqueteDIV").hide();
            $("#consultarTarifaEncomiendaTipoEncomiendaEspecial").select2("val", "");
            $("#consultarTarifaEncomiendaPeso").val("");
            $("#consultarTarifaEncomiendaAlto").val("");
            $("#consultarTarifaEncomiendaAncho").val("");
            $("#consultarTarifaEncomiendaProfundidad").val("");
        }else if($.trim(tipoEncomienda) === "2"){ //Especial
            $("#cteTipoEncomiendaEspecialDIV").show();
            $("#ctePaqueteDIV").hide();
            $("#consultarTarifaEncomiendaTipoEncomiendaEspecial").select2("val", "");
        }else if($.trim(tipoEncomienda) === "3"){ //Paquete
            $("#cteTipoEncomiendaEspecialDIV").hide();
            $("#ctePaqueteDIV").show();
            $("#consultarTarifaEncomiendaPeso").val("");
            $("#consultarTarifaEncomiendaAlto").val("");
            $("#consultarTarifaEncomiendaAncho").val("");
            $("#consultarTarifaEncomiendaProfundidad").val("");
        }
    }
  
};

