pendientesEntregaEncomiendas = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $("#pendiente_entrega_command_estacion").select2({
             allowClear: $("#pendiente_entrega_command_estacion option[value='']").length === 1
        });
        $("#pendiente_entrega_command_estacion").select2("readonly", ($("#pendiente_entrega_command_estacion").attr("readonly") === "readonly"));
        
        $("#pendiente_entrega_command_empresa").select2({
             allowClear: $("#pendiente_entrega_command_empresa option[value='']").length === 1
        });
        $("#pendiente_entrega_command_empresa").select2("readonly", ($("#pendiente_entrega_command_empresa").attr("readonly") === "readonly"));
        
        $("#pendiente_entrega_command_cliente").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: $("#pendiente_entrega_command_cliente").data("pathlistarclientespaginando"),
                dataType: 'json',
                type: "GET",
                data: function (term, page) {
                    return {term: term, page_limit: 5};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
        });
        
        $('#pendiente_entrega_command_fecha').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-180d",
            endDate: "d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        
        $("#pendiente_entrega_command_serieFactura").select2({
            allowClear: false,
            data: []
        });
        
        pendientesEntregaEncomiendas.buscarDatos();
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
        $("#pendiente_entrega_command_estacion").on("change", pendientesEntregaEncomiendas.buscarDatos);
        $("#pendiente_entrega_command_empresa").on("change", pendientesEntregaEncomiendas.buscarDatos);
        $("#aceptar").click(pendientesEntregaEncomiendas.clickAceptar);
        $("#addCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                var element = $("#pendiente_entrega_command_cliente");
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#pendiente_entrega_command_cliente').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             }, {
                 'confirmarOperacion' : false
             });
         });
         $("#updateCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function() {
                var element = $("#pendiente_entrega_command_cliente");
                var id = element.val();
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#pendiente_entrega_command_cliente').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#seachCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                if (id !== "") {
                    var element = $("#pendiente_entrega_command_cliente");
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#pendiente_entrega_command_cliente').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#pendiente_entrega_command_serieFactura").on("change", function (){
             console.debug("serieFacturaVirtual-init");
             var idSerieFactura = $("#pendiente_entrega_command_serieFactura").select2('val');
             var idEmpresa = $("#pendiente_entrega_command_serieFactura").select2('data').idEmpresa;
             sessionStorage.setItem("sist_last_id_serie_factura_enco_" + idEmpresa, idSerieFactura);
         });
    },
    
    buscarDatos : function(e) {
        console.debug("buscarDatos-init");
        $("#encomiendasBody").find("tr").not("#encomiendasVacioTR").remove();
        $("#encomiendasBody").find("#encomiendasVacioTR").show();
        $("#pendiente_entrega_command_serieFactura").select2({ data: [] });
        $("#pendiente_entrega_command_importeTotal").val("");
        $("#pendiente_entrega_command_numeroFactura").val("");
        
        var estacion = $("#pendiente_entrega_command_estacion").val();
        var empresa = $("#pendiente_entrega_command_empresa").val();
        if(estacion !== null && $.trim(estacion) !== "" && empresa !== null && $.trim(empresa) !== ""){
           core.request({
                url : $("#pathListarEncomiendasPendientesEntrega").attr('value'),
                method: 'POST',
                extraParams: {
                    estacion : estacion,
                    empresa : empresa
                }, 
                dataType: "json",
                async:false,
                successCallback: function(success){
                    console.debug(success);
                    var encomiendas = success.encomiendas;
                    var pathConsultarEncomienda = $("#pathConsultarEncomienda").val();
                    $.each(encomiendas, function(){
                        $("#encomiendasBody").find("#encomiendasVacioTR").hide();
                        var itemTR = $("<tr id='E"+this.id+"'>"+
                                       "<td class='center'><a class='btn'>"+this.id+"</a></td>"+
                                       "<td class='center inputCheckbox'><input data-valor='"+this.id+"' data-importe='"+this.importe+"' type='checkbox'></td>"+
                                       "<td class='center'>"+this.empresa+"</td>"+
                                       "<td class='center'>"+this.fecha+"</td>"+
                                       "<td class='center'>"+this.doc+"</td>"+
                                       "<td class='center'>"+this.importe+"</td>"+
                                       "<td class='center'>"+this.desc+"</td>"+
                                       "</tr>");
                        $("#encomiendasBody").append(itemTR);
                        itemTR.val(this.id); 
                        itemTR.find("a").attr('href', pathConsultarEncomienda);
                        itemTR.find("a").data("index", "E"+this.id);
                        itemTR.find("a").data("title", "Consultar Encomienda");
                        itemTR.find("a").data("fullscreen", true);
                        itemTR.find("a").click(frondend.loadSubPage);
                    });
                    
                    $(".inputCheckbox").find("input").bind("click", pendientesEntregaEncomiendas.totalizar);
                    pendientesEntregaEncomiendas.totalizar();
                    
                    var optionSeriesFacturas = success.optionSeriesFacturas;
                    if( optionSeriesFacturas ){
                        $("#pendiente_entrega_command_serieFactura").select2({
                            allowClear: false,
                            data: { results: optionSeriesFacturas }
                        });
                        if(optionSeriesFacturas.length > 0 && optionSeriesFacturas[0]){
                            $("#pendiente_entrega_command_serieFactura").select2('val', optionSeriesFacturas[0].id);
                        }
                    }
                }
           });
        }else{
            pendientesEntregaEncomiendas.totalizar();
        }
    },
    
    syncronizarLista : function(e) {
        var listaEncomiendas = [];
        var items = $(".inputCheckbox").find("input");
        items.each(function (index, item){
            var checked = $(item).prop("checked");
            if(checked){
                listaEncomiendas.push($(item).data("valor"));
            }
        });
        $("#pendiente_entrega_command_listaIdEncomiendas").val(JSON.stringify(listaEncomiendas));
    },
    
    totalizar : function() {
        console.debug("totalizar-init");
        var total = 0;
        var items = $(".inputCheckbox").find("input");
        items.each(function (index, item){
            var checked = $(item).prop("checked");
            if(checked){
                total += core.customParseFloat($(item).data("importe"));
            }
        });
        $("#pendiente_entrega_command_importeTotal").val(total);
    },
    
    clickAceptar: function(e) {
        console.debug("clickAceptar-init");
        e.preventDefault();
        e.stopPropagation();
        
        var encomiendaPendienteEntregaForm = $("#encomiendaPendienteEntrega");
        if(core.customValidateForm(encomiendaPendienteEntregaForm) === true){
            pendientesEntregaEncomiendas.syncronizarLista();
            $(encomiendaPendienteEntregaForm).ajaxSubmit({
                target: encomiendaPendienteEntregaForm.attr('action'),
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
                    core.hideLoading({showLoading:true});
                    if(!core.procesarRespuestaServidor(responseText)){
                        alert("Operación realizada satisfactoriamente.", function() {
                            pendientesEntregaEncomiendas.buscarDatos();
                        });
                    }
                }
            });
        }
        
    }
    
};
