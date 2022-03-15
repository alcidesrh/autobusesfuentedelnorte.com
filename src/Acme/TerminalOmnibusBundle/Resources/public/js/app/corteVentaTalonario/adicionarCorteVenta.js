adicionarCorteVenta = {
    
    keyCurrentStation : "",
    optionTarjetas : [],
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $('#adicionar_corte_venta_talonario_command_fecha').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-10y",
            endDate: "d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });   
        $('#adicionar_corte_venta_talonario_command_fecha').datepicker("setDate", new Date());
        $("#adicionar_corte_venta_talonario_command_estacionCreacion").select2();
        $("#adicionar_corte_venta_talonario_command_inspector").select2();
        
        var keyCurrentStation = localStorage.getItem(adicionarCorteVenta.keyCurrentStation);
        if(keyCurrentStation !== null && $.trim(keyCurrentStation) !== ""){
            console.log('loading last key station: ' + keyCurrentStation);
            $("#adicionar_corte_venta_talonario_command_estacionCreacion").select2('val', keyCurrentStation);
        }
        
        adicionarCorteVenta.loadData();
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
         
        $("#aceptar").click(adicionarCorteVenta.clickAceptar); 
        $("#adicionar_corte_venta_talonario_command_estacionCreacion").on("change", function (){
            var keyCurrentStation = $("#adicionar_corte_venta_talonario_command_estacionCreacion").val();
            console.log('saving key station: ' + keyCurrentStation);
            localStorage.setItem(adicionarCorteVenta.keyCurrentStation, keyCurrentStation);
        });
        $("#adicionar_corte_venta_talonario_command_tarjeta").on("change", adicionarCorteVenta.changeTarjeta);
        $("#adicionar_corte_venta_talonario_command_talonario").on("change", adicionarCorteVenta.changeTalonario);
    },

    clickAceptar: function(e) {
//        console.debug("clickAceptar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var corteVentaForm = $("#corteVentaForm");
        if(core.customValidateForm(corteVentaForm) === true){
            $(corteVentaForm).ajaxSubmit({
                target: corteVentaForm.attr('action'),
                type : "POST",
                dataType: "html",
                cache : false,
                async:false,
                beforeSubmit: function() { 
                    core.showLoading({showLoading:true});
                },
               error: function() {
                    core.hideLoading({showLoading:true});
               },
               success: function(responseText) {
//                    console.log("submitHandler....success");
//                    console.debug(responseText);  
                    core.hideLoading({showLoading:true});
                    if(!core.procesarRespuestaServidor(responseText)){
//                        console.log("procesarRespuestaServidor....okook");
                        alert("Operación realizada satisfactoriamente.", function() {
                            core.getPageForMenu(corteVentaForm.attr('action'));
                        });
                    }
                }
           });
        }
    },
    
    loadData : function(e) {
        console.log("load data");
        core.request({
            url : $("#pathGetTarjetasRecientes").prop("value"),
            type: "GET",
            dataType: "json",
            async: true,
            extraParams : { },
            successCallback: function(data){
                console.debug(data.optionTarjetas);
                if(data.optionTarjetas){
                    adicionarCorteVenta.optionTarjetas = data.optionTarjetas;
                    $("#adicionar_corte_venta_talonario_command_tarjeta").select2({
                        allowClear: true,
                        data: { results: data.optionTarjetas }
                    });
                    $.each(adicionarCorteVenta.optionTarjetas, function(index, tarjeta){
                        $.each(tarjeta.talonarios, function(index, talonario){
                            this.text = this.min + " - " + this.max;
                        });
                    });
                }
                $("#talonarioDiv").addClass("hidden");
                $("#corteVentaDiv").addClass("hidden");
            },
            error : function(){
                $("#adicionar_corte_venta_talonario_command_tarjeta").select2({
                    allowClear: true,
                    data: { results: [] }
                });
                $("#talonarioDiv").addClass("hidden");
                $("#corteVentaDiv").addClass("hidden");
            }
         });
    },
    
    changeTarjeta : function(e) {
        console.log("changeTarjeta");
        var idTarjeta = $("#adicionar_corte_venta_talonario_command_tarjeta").val();
        $("#corteVentaDiv").addClass("hidden");
        $("#talonarioDiv").addClass("hidden");
        var tarjeta = adicionarCorteVenta.findTarjetaInList(idTarjeta);
        if(tarjeta !== null && $.trim(tarjeta) !== ""){
            console.debug(tarjeta);
            $("#talonarioDiv").removeClass("hidden");   
            $("#adicionar_corte_venta_talonario_command_talonario").select2({
                allowClear: true,
                data: { results: tarjeta.talonarios }
            });
        }
    },
    
    changeTalonario : function(e) {
        console.log("changeTalonario");
        $("#corteVentaDiv").addClass("hidden");
        var idTarjeta = $("#adicionar_corte_venta_talonario_command_tarjeta").val();
        var tarjeta = adicionarCorteVenta.findTarjetaInList(idTarjeta);
        if(tarjeta !== null){
            var idTalonario = $("#adicionar_corte_venta_talonario_command_talonario").val();
            var talonario = adicionarCorteVenta.findInList(idTalonario, tarjeta.talonarios);
            if(talonario !== null){
                $("#corteVentaDiv").removeClass("hidden");
                $("#adicionar_corte_venta_talonario_command_inicial").attr("min", talonario.min);
                $("#adicionar_corte_venta_talonario_command_inicial").attr("max", talonario.max);
                $("#adicionar_corte_venta_talonario_command_inicial").val(talonario.min);
                $("#adicionar_corte_venta_talonario_command_final").attr("min", talonario.min);
                $("#adicionar_corte_venta_talonario_command_final").attr("max", talonario.max);
                $("#adicionar_corte_venta_talonario_command_final").val(talonario.min);
                $("#adicionar_corte_venta_talonario_command_importeTotal").val(0);
            }   
        }
    },
    
    findTarjetaInList : function(id) {
        if(id === null || $.trim(id) === "")
            return null;
        var tarjeta = null;
        $.each(adicionarCorteVenta.optionTarjetas, function (index, item){
           if(item.id === id){
               tarjeta = item;
               return false;
           } 
        });
        return tarjeta;
    },
    
    findInList : function(id, list) {
        if(id === null || $.trim(id) === "")
            return null;
        var result = null;
        $.each(list, function (index, item){
           if(item.id === id){
               result = item;
               return false;
           } 
        });
        return result;
    }
    
};
