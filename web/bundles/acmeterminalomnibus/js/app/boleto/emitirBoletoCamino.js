emitirBoletoCamino = {
    
    procesandoDatos : false,
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
    			
    _init : function() {
        
         $("#emitir_boleto_command_identificadorWeb").val(core.uniqIdCompuesto());
         
         var pathlistarclientespaginando = $("#emitir_boleto_command_clienteDocumento").data("pathlistarclientespaginando");
         $("#emitir_boleto_command_clienteDocumento").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: pathlistarclientespaginando,
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
        
        $('#emitir_boleto_command_fechaSalida').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-1d",
            endDate: "+2m",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        $('#emitir_boleto_command_fechaSalida').datepicker("setDate", new Date());
        
        $("#emitir_boleto_command_estacionOrigen").select2({
            allowClear: true
        });
        $("#emitir_boleto_command_estacionFacturacionEspecialVirtual").select2({
            allowClear: true
        });
        
        $("#salidaGrid").flexigrid({
            url: $("#salidaGrid").data("url"),
            dataType: 'json',
            singleSelect: true,
            query: emitirBoletoCamino.getQueryString(),
            rpOptions: [5, 10, 15, 20, 25, 30, 40, 50],
            colModel : [
                    {display: 'ID', name : 'id', width : 70, sortable : true, align: 'center', hide: true},
                    {display: 'Fecha y Hora', name : 'fecha', width : 120, sortable : false, align: 'center'},
                    {display: 'Origen', name : 'origen', width : 120, sortable : false, align: 'left'},
                    {display: 'Destino', name : 'destino', width : 120, sortable : false, align: 'left'},
                    {display: 'Empresa', name : 'empresa', width : 80, sortable : false, align: 'left'},
//                    {display: 'Tipo Bus', name : 'tipoBus', width : 80, sortable : false, align: 'left'},
//                    {display: 'Clase de Bus', name : 'claseBus', width : 80, sortable : false, align: 'left'},
                    {display: 'Itinerario', name : 'itinerario', width : 130, sortable : false, align: 'left'},
                    {display: 'Bus', name : 'bus', width : 130, sortable : false, align: 'left'},
                    {display: 'Piloto', name : 'piloto', width : 130, sortable : false, align: 'left', hide: true},
                    {display: 'Estado', name : 'estado', width : 70, sortable : false, align: 'center'}
                    ],
            usepager: true,
            useRp: true,
            rp: 15,
            showTableToggleBtn: false,
            height: 250,
            onSuccess : function() {
                $("#salidaGrid tr").on('click', emitirBoletoCamino.checkSelectedSalida);
                emitirBoletoCamino.checkSelectedSalida();
                emitirBoletoCamino.cargarMonedas();
            }
        });
        
        $("#emitir_boleto_command_estacionSubeEn").select2({
            allowClear: true,
            data: []
        });
        
        $("#emitir_boleto_command_estacionBajaEn").select2({
            allowClear: true,
            data: []
        });
        
        $("#emitir_boleto_command_tipoPagoVirtual").select2({
            allowClear: false
        });
        
        $("#emitir_boleto_command_monedaPagoVirtual").select2({
            allowClear: false,
            data: []
        });
        
        $("#emitir_boleto_command_serieFacturaVirtual").select2({
            allowClear: false,
            data: []
        });
        $("#emitir_boleto_command_serieFacturacionEspecialVirtual").select2({
            allowClear: false,
            data: []
        });
        
        emitirBoletoCamino.checkSelectedSalida();
     },
     
    _conectEvents : function() {
        
        $("#cancelar").click(function(e) {
//            console.debug("clic");
//            console.debug($(this));
            e.preventDefault();
            e.stopPropagation();
            confirm("¿Está seguro que desea cancelar la operación?", function(confirmed){
//                console.debug(confirmed);
                if(confirmed === true){
                    core.getPageForMenu($("#cancelar").attr('href'));
                }
            });
         });
         
         $("#addCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                var element = $("#emitir_boleto_command_clienteDocumento");
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#emitir_boleto_command_clienteDocumento').select2('data', data.options[0]); 
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
                var element = $("#emitir_boleto_command_clienteDocumento");
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
                                $('#emitir_boleto_command_clienteDocumento').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#seachCliente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
//                console.debug("Seteando elemento seleccionado..."); 
//                console.debug(id);
                if (id !== "") {
                    var element = $("#emitir_boleto_command_clienteDocumento");
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
//                            console.debug("Actulizando datos del combo...data");
//                            console.debug(data);
                            if( data.options && data.options[0]){
                                $('#emitir_boleto_command_clienteDocumento').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         
         $("#cortesia").click(emitirBoletoCamino.clickCortesia);
         $("#facturar").click(emitirBoletoCamino.clickFacturar);
         $("#facturarEspecial").click(emitirBoletoCamino.clickFacturarEspecial);
         $("#addBoleto").click(emitirBoletoCamino.clickAddBoleto);
         
         $("#emitir_boleto_command_fechaSalida").on("change", emitirBoletoCamino.changeFiltersSalida);
         $("#emitir_boleto_command_estacionOrigen").on("change", emitirBoletoCamino.changeFiltersSalida);
         
         $("#emitir_boleto_command_tipoPagoVirtual").on("change", emitirBoletoCamino.changeTipoPago);
         $("#emitir_boleto_command_monedaPagoVirtual").on("change", emitirBoletoCamino.changeMonedaPago);
         $("#emitir_boleto_command_efectivoVirtual").on("change", emitirBoletoCamino.changeEfectivo);

         $("#emitir_boleto_command_estacionFacturacionEspecialVirtual").on("change", emitirBoletoCamino.changeEstacionFacturacionEspecial);
         $("#emitir_boleto_command_serieFacturacionEspecialVirtual").on("change", emitirBoletoCamino.changeSerieFacturacionEspecial);
         $("#facturaEspecialVoucher").bind("click", emitirBoletoCamino.changeSerieFacturacionEspecial);
         
         $("#emitir_boleto_command_serieFacturaVirtual").on("change", function (){
             console.debug("serieFacturaVirtual-init");
             var idSerieFactura = $("#emitir_boleto_command_serieFacturaVirtual").select2('val');
             var idEmpresa = $("#emitir_boleto_command_serieFacturaVirtual").select2('data').idEmpresa;
             sessionStorage.setItem("sist_last_id_serie_factura_" + idEmpresa, idSerieFactura);
         });
         
         $('#facturaVoucher').bind("click", emitirBoletoCamino.checkFacturaVoucher);
         $('#facturaEspecialVoucher').bind("click", emitirBoletoCamino.checkFacturaEspecialVoucher);
    },
    
    checkFacturaVoucher : function (){
        var checked = $("#facturaVoucher").prop("checked");
        if(checked){
            $(".voucherDIV").show();
        }else{
            $(".voucherDIV").hide();
        }
    },
    
    checkFacturaEspecialVoucher : function (){
        var checked = $("#facturaEspecialVoucher").prop("checked");
        if(checked){
            $(".voucherDIV").show();
        }else{
            $(".voucherDIV").hide();
        }
    },
    
    checkSelectedSalida : function() {
//        console.log("changeSelectedSalida-init");
        emitirBoletoCamino.clearItemGridBoleto();
        var selected = core.getSelectedItemId("#salidaGrid");
        $("#emitir_boleto_command_salida").prop("value", selected);
        if(selected === null){
            $("#emitir_boleto_command_estacionSubeEn").select2({ data: [] });
            $("#emitir_boleto_command_estacionBajaEn").select2({ data: [] });
            $("#emitir_boleto_command_serieFacturaVirtual").select2({ data: [] });
            $("#emitir_boleto_command_serieFacturacionEspecialVirtual").select2({ data: [] });
            $("#dependenciasSelecccionSalidaGrid").hide();
        }else{
            core.request({
                url : $("#pathGetInformacionPorSalida").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { idSalida: selected, camino: true, showTime : true },
                successCallback: function(data){
//                    console.debug("Actulizando datos del combo...data");
//                    console.debug(data);
                    $("#dependenciasSelecccionSalidaGrid").show();
                    var optionEstacionOrigen = data.optionEstacionOrigen;
                    var optionEstacionDestino = data.optionEstacionDestino;
                    var optionsEstacionesIntermedias = data.optionsEstacionesIntermedias;
                    
                    $.each(optionEstacionOrigen, function(){
                        this.text = (this.time !== "" ? this.time + " " : "") + this.text;
                    });
                    $.each(optionEstacionDestino, function(){
                        this.text = (this.time !== "" ? this.time + " " : "") + this.text;
                    });
                    $.each(optionsEstacionesIntermedias, function(){
                        this.text = (this.time !== "" ? this.time + " " : "") + this.text;
                    });
                    
                    if( data.optionEstacionOrigen && data.optionsEstacionesIntermedias){
                        var results = optionEstacionOrigen.concat(optionsEstacionesIntermedias);
                        $("#emitir_boleto_command_estacionSubeEn").select2({
                            allowClear: true,
                            data: { results: results }
                        });
                        $("#emitir_boleto_command_estacionSubeEn").select2('val', optionEstacionOrigen[0].id);
                    }
                    if( data.optionEstacionDestino && data.optionsEstacionesIntermedias){
                        var results = optionEstacionDestino.concat(optionsEstacionesIntermedias); 
                        $("#emitir_boleto_command_estacionBajaEn").select2({
                            allowClear: true,
                            data: { results: results }
                        });
                        $("#emitir_boleto_command_estacionBajaEn").select2('val', optionEstacionDestino[0].id);
                    }
                    
                    var optionSeriesFacturas = data.optionSeriesFacturas;
                    if( optionSeriesFacturas ){
                        $("#emitir_boleto_command_serieFacturaVirtual").select2({
                            allowClear: false,
                            data: { results: optionSeriesFacturas }
                        });
                        
                        var cantidad = optionSeriesFacturas.length;
                        $("#emitir_boleto_command_serieFacturaVirtual").data("sizeItems", cantidad);
                        if(cantidad > 0){
                            var idEmpresa = optionSeriesFacturas[0].idEmpresa;
                            var idSerieFactura = sessionStorage.getItem("sist_last_id_serie_factura_" + idEmpresa);
                            if(idSerieFactura){
                                $("#emitir_boleto_command_serieFacturaVirtual").select2('val', idSerieFactura); 
                            }else if(optionSeriesFacturas[0]){
                                $("#emitir_boleto_command_serieFacturaVirtual").select2('val', optionSeriesFacturas[0].id); 
                            }
                        }
                    }
                    
                    //Se borra pq se carga cuando se seleccione la estacion.
                    $("#emitir_boleto_command_serieFacturacionEspecialVirtual").select2({ data: [] });
                }
             });
        }
        $("#cantidadBoletosSpan").text($("#clienteBoletoBody").find(".inputSelect").length);
    },
    
    changeFiltersSalida : function() {
        $("#salidaGrid").flexOptions({
            newp: 1, 
            query: emitirBoletoCamino.getQueryString()
        }).flexReload(); 
    },
     
    getQueryString : function() {
        return $('.filterSalida').fieldSerialize();
    },
       
    clickAddBoleto : function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var cantidadBoletos = $("#clienteBoletoBody").find(".inputSelect").length;
        if(cantidadBoletos >= 50){
            alert("No se pueden selecciona más de 50 boletos en una operación.");
            return;
        }
        
        var listaClienteBoleto = $("#emitir_boleto_command_listaClienteBoleto").val();
        if(listaClienteBoleto){ listaClienteBoleto = JSON.parse(listaClienteBoleto); }
        else{ listaClienteBoleto = []; }
        
        var idBoleto = core.uniqId("boleto_");
        
        var item = {
            numero : -1,
            id : idBoleto,
            idCliente : '',
            idReservacion : '',
            idClinteReservacion : ''
        };
        
        listaClienteBoleto.push(item);
        $("#emitir_boleto_command_listaClienteBoleto").val(JSON.stringify(listaClienteBoleto));
        
        $("#clienteBoletoBody").find("#clienteBoletoVacioTR").hide(); //Oculto el TR vacio
        var idSelect = core.uniqId("cliente_boleto");
        var placeholder = "Seleccione el cliente boleto";
        var inputSelect = $("#emitir_boleto_command_clienteDocumento").clone();
        inputSelect.prop("id", idSelect);
        inputSelect.prop("name", idSelect);
        inputSelect.prop("placeholder", placeholder);
        inputSelect.attr("placeholder", placeholder);
        inputSelect.data("placeholder", placeholder);
        inputSelect.val("");
        inputSelect.removeClass("select2-offscreen");
        var itemTR = $("<tr id='"+idBoleto+"'><td class='inputSelect'></td><td><a class='deleteBoleto' data-title='Adicionar Boleto'><i class='icon-minus'></i></a></td></tr>");
        itemTR.find(".inputSelect").append(inputSelect);
        itemTR.find(".deleteBoleto").addClass("btn btn-small");
        var clienteActionsHidden = $("#clienteActionsHidden").clone();
        clienteActionsHidden.prop("id", idSelect);
        clienteActionsHidden.removeClass("hidden");
        itemTR.find(".inputSelect").append(clienteActionsHidden);
        $("#clienteBoletoBody").append(itemTR);
        clienteActionsHidden.find(".addClienteHidden").click(emitirBoletoCamino.addClienteHidden);
        clienteActionsHidden.find(".updateClienteHidden").click(emitirBoletoCamino.updateClienteHidden);
        clienteActionsHidden.find(".updateClienteHidden").data("index", idSelect);
        clienteActionsHidden.find(".seachClienteHidden").click(emitirBoletoCamino.seachClienteHidden);
        itemTR.find(".deleteBoleto").click(emitirBoletoCamino.renderItemGridDeletedBoleto);
        
        var pathlistarclientespaginando = inputSelect.data("pathlistarclientespaginando");
        inputSelect.select2({
            minimumInputLength: 1,
            allowClear: true,
            placeholder: placeholder,
            ajax: { 
                url: pathlistarclientespaginando,
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
        
        if(inputSelect.val() === ""){
            $(inputSelect).select2('data', $('#emitir_boleto_command_clienteDocumento').select2('data'));
        }
        
        $("#cantidadBoletosSpan").text($("#clienteBoletoBody").find(".inputSelect").length);
    },
    
    addClienteHidden: function(e) {
        var inputCliente = $(this).parent().parent().find("input[id^='cliente_boleto']");
        frondend.loadSubPage(e, $(this), function(id) {
            if (id !== "") {
                core.request({
                    url : inputCliente.data("pathlistarclientespaginando"),
                    type: "POST",
                    dataType: "json",
                    async: false,
                    extraParams : { id: id },
                    successCallback: function(data){
                        if( data.options && data.options[0]){
                            inputCliente.select2('data', data.options[0]); 
                        }
                    }
                });
            }
        });
    },
    
    updateClienteHidden: function(e) {
        var inputCliente = $(this).parent().parent().find("input[id^='cliente_boleto']");
        frondend.loadSubPage(e, $(this), function() {
            var id = inputCliente.val();
            if (id !== "") {
                core.request({
                    url : inputCliente.data("pathlistarclientespaginando"),
                    type: "POST",
                    dataType: "json",
                    async: false,
                    extraParams : { id: id },
                    successCallback: function(data){
                        if( data.options && data.options[0]){
                            inputCliente.select2('data', data.options[0]); 
                        }
                    }
                });
            }
        });
    },
    seachClienteHidden: function(e) {
        var inputCliente = $(this).parent().parent().find("input[id^='cliente_boleto']");
        frondend.loadSubPage(e, $(this), function(id) {
            if (id !== "") {
                core.request({
                    url : inputCliente.data("pathlistarclientespaginando"),
                    type: "POST",
                    dataType: "json",
                    async: false,
                    extraParams : { id: id },
                    successCallback: function(data){
                        if( data.options && data.options[0]){
                            inputCliente.select2('data', data.options[0]); 
                        }
                    }
                });
            }
        });
    },
    
    //Elimina un item especifico de la tabla
    renderItemGridDeletedBoleto: function(e) {
        e.preventDefault();
        e.stopPropagation();

        var listaClienteBoleto = $("#emitir_boleto_command_listaClienteBoleto").val();
        if(listaClienteBoleto){ listaClienteBoleto = JSON.parse(listaClienteBoleto); }
        else{ listaClienteBoleto = []; }
        
        var idItem = $(this).parent().parent().attr("id");
        if(idItem === undefined || idItem === null){
            console.debug($(this));
            throw new Error("No se puedo determinar el id a eliminar");
        }
        
        var item = emitirBoletoCamino.findAsiento(listaClienteBoleto, idItem);
        if(item !== null){
            listaClienteBoleto = core.removeItemArray(listaClienteBoleto, item);
            if(listaClienteBoleto.length === 0){
                $("#clienteBoletoBody").find("tr").not("#clienteBoletoVacioTR").remove(); //Elimino todos los tr
                $("#clienteBoletoBody").find("#clienteBoletoVacioTR").show(); //Muestro el vacio
            }else{
                $("#clienteBoletoBody").find("#clienteBoletoVacioTR").hide(); //Oculto el vacio
                $("#clienteBoletoBody").find("#"+idItem).remove();
            }
            $("#emitir_boleto_command_listaClienteBoleto").val(JSON.stringify(listaClienteBoleto));
        }
        
        $("#cantidadBoletosSpan").text($("#clienteBoletoBody").find(".inputSelect").length);   
    },
    
    //Se elimina toda la informacion de boletos hasta el momento, y se muestra el tr vacio
    clearItemGridBoleto: function() {
        $("#emitir_boleto_command_listaClienteBoleto").val(JSON.stringify([]));
        $("#clienteBoletoBody").find("tr").not("#clienteBoletoVacioTR").remove(); //Elimino todos los tr
        $("#clienteBoletoBody").find("#clienteBoletoVacioTR").show(); //Muestro el vacio
    },
    
    findAsiento : function(listaClienteBoleto, id) {
        var result = null;
        id = $.trim(id);
        $.each(listaClienteBoleto, function() {  
           if($.trim(this.id) === id){
               result = this;
               return;
           }
        });
        return result;
    },  
    
    
    clickCortesia: function(e) {
        console.debug("clickCortesia-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var boletoForm = $("#boletoForm");
        if(core.customValidateForm(boletoForm) === true && emitirBoletoCamino.customPreValidateCortesia() === true){
             core.showMessageDialog({
                 title : "Registrar Cortesía",
                 selector: $("#cortesiaDIV"),
                 removeOnClose : false,
                 uniqid : false,
                 buttons: {
                    Aceptar: {
                        click: function() {
                            if(emitirBoletoCamino.procesandoDatos === false){
                                emitirBoletoCamino.procesandoDatos = true;
                                var dialogActual = this;
                                if(core.customValidateForm(boletoForm) === true && emitirBoletoCamino.customPostValidateCortesia(this) === true){
                                    $("#emitir_boleto_command_movil").prop("value", core.isMovil());
                                    $("#emitir_boleto_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                    $("#emitir_boleto_command_autorizacionCortesia").prop("value", $("#pinAutorizacionCortesia").prop("value"));
                                    $("#emitir_boleto_command_tipoDocuemento").prop("value", 3); //Cortesia
                                    emitirBoletoCamino.syncronizarListaBoletos();
                                    $(boletoForm).ajaxSubmit({
                                        target: boletoForm.attr('action'),
                                        type : "POST",
                                        dataType: "html",
                                        cache : false,
                                        async:false,
                                        beforeSubmit: function() { 
                                            core.showLoading({showLoading:true});
                                        },
                                        error: function() {
                                            core.hideLoading({showLoading:true});
                                            emitirBoletoCamino.procesandoDatos = false;
                                        },
                                        success: function(responseText) {
                                            core.hideLoading({showLoading:true});
                                            if(!core.procesarRespuestaServidor(responseText)){
                                                dialogActual.dialog2("close");
                                                emitirBoletoCamino.imprimirVoucher(responseText, function (){
                                                    core.getPageForMenu(boletoForm.attr('action'));
                                                });
                                            }
                                            emitirBoletoCamino.procesandoDatos = false;
                                       }
                                    });
                                }else{
                                    emitirBoletoCamino.procesandoDatos = false;
                                }
                            }
			}, 
			primary: true,
			type: "info"
                    }, 
                    Cancelar: function() {
                        this.dialog2("close");
                    }				    
                }
             });            
        }
    },
    
    imprimirFacturas: function(responseText, successCallback) {
        var info = core.getValueFromResponse(responseText, "info");
        core.showNotification({text : info});
        var autoPrint = core.getValueFromResponse(responseText, 'autoPrint');
        if(eval(autoPrint) === true){
            frondend.printFacturaInternal(responseText, function (){
                alert("Operación realizada satisfactoriamente. " + info, function() {
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback(responseText);
                    }
                }); 
            });                                        
        }else{
            alert("Operación realizada satisfactoriamente. " + info, function() {
                frondend.printFacturaInternal(responseText, successCallback);                                         
            }); 
        }
    },
    
    imprimirVoucher: function(responseText, successCallback) {
        var info = core.getValueFromResponse(responseText, "info");
        core.showNotification({text : info});
        var autoPrint = core.getValueFromResponse(responseText, 'autoPrint');
        if(eval(autoPrint) === true){
            var data = core.getValueFromResponse(responseText, 'data');
            frondend.printVoucherBoleto($("#pathPrintVoucherBoleto").attr("value"), { ids : data }, function (){
                alert("Operación realizada satisfactoriamente. " + info, function() {
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback(responseText);
                    }
                }); 
            });
        }else{
            alert("Operación realizada satisfactoriamente. " + info, function() {
                var data = core.getValueFromResponse(responseText, 'data');
                frondend.printVoucherBoleto($("#pathPrintVoucherBoleto").attr("value"), { ids : data }, successCallback);
            }); 
        }
    },
    
    syncronizarListaBoletos: function() {
        var listaClienteBoleto = $("#emitir_boleto_command_listaClienteBoleto").val();
        if(listaClienteBoleto){ listaClienteBoleto = JSON.parse(listaClienteBoleto); }
        else{ listaClienteBoleto = []; }
        $.each(listaClienteBoleto, function(){
            this.idCliente = $("#"+this.id).find('input[id^="cliente_boleto"]').val();
        });
//        console.debug(listaClienteBoleto);
        $("#emitir_boleto_command_listaClienteBoleto").val(JSON.stringify(listaClienteBoleto));
    },
    
    cargarMonedas: function() {
        var moneda = $("#emitir_boleto_command_monedaPagoVirtual").select2('val');
        if($.trim(moneda) === ""){
            core.request({
                url : $("#pathListarDatosIniciales").val(),
                type: "GET",
                dataType: "json",
                async: true,
                showLoading : false,
                successCallback: function(data){
                    if( data.optionMonedas){
                        var optionMonedas = data.optionMonedas;
                        $("#emitir_boleto_command_monedaPagoVirtual").select2({
                            allowClear: false,
                            data: { results: optionMonedas }
                        });
                        if(optionMonedas[0]){
                            $("#emitir_boleto_command_monedaPagoVirtual").select2('val', optionMonedas[0].id);
                        }
                    }
                    if(data.showVoucher && eval(data.showVoucher) === true){
                        $("#voucher").removeClass("hidden");
                        $("#facturaVoucher").removeClass("hidden");
                    }else{
                        $("#voucher").addClass("hidden");
                        $("#facturaVoucher").addClass("hidden");
                    }
                }
            });
        }
    },

    clickFacturar: function(e) {
//        console.debug("clickFacturar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var boletoForm = $("#boletoForm");
        if(core.customValidateForm(boletoForm) === true && emitirBoletoCamino.customPreValidateFactura() === true){
             $("#facturaVoucher").prop("checked", true);
             emitirBoletoCamino.checkFacturaVoucher();
             core.showMessageDialog({
                 title : "Registrar Facturar",
                 selector: $("#facturaDIV"),
                 removeOnClose : false,
                 uniqid : false,
                 buttons: {
                    Aceptar: {
                        click: function() {
                            if(emitirBoletoCamino.procesandoDatos === false){
                                emitirBoletoCamino.procesandoDatos = true;
                                var dialogActual = this;
    //                            console.debug("Aceptar-click...");
                                if(core.customValidateForm(boletoForm) === true && emitirBoletoCamino.customPostValidateFactura(this) === true){
                                    $("#emitir_boleto_command_movil").prop("value", core.isMovil());
                                    $("#emitir_boleto_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                    $("#emitir_boleto_command_referenciaExterna").prop("value", $("#emitir_boleto_command_referenciaExternaVirtual").prop("value"));
                                    $("#emitir_boleto_command_tipoPago").prop("value", $("#emitir_boleto_command_tipoPagoVirtual").prop("value"));
                                    $("#emitir_boleto_command_autorizacionTarjeta").prop("value", $("#emitir_boleto_command_autorizacionTarjetaVirtual").prop("value"));
                                    $("#emitir_boleto_command_totalNeto").prop("value", $("#emitir_boleto_command_totalNetoVirtual").prop("value"));
                                    $("#emitir_boleto_command_monedaPago").prop("value", $("#emitir_boleto_command_monedaPagoVirtual").prop("value"));
                                    $("#emitir_boleto_command_tasa").prop("value", $("#emitir_boleto_command_tasaVirtual").prop("value"));
                                    $("#emitir_boleto_command_totalPago").prop("value", $("#emitir_boleto_command_totalPagoVirtual").prop("value"));
                                    $("#emitir_boleto_command_efectivo").prop("value", $("#emitir_boleto_command_efectivoVirtual").prop("value"));
                                    $("#emitir_boleto_command_vuelto").prop("value", $("#emitir_boleto_command_vueltoVirtual").prop("value"));
                                    $("#emitir_boleto_command_serieFactura").prop("value", $("#emitir_boleto_command_serieFacturaVirtual").prop("value"));
                                    if($("#facturaVoucher").prop("checked") === true){
                                        $("#emitir_boleto_command_tipoDocuemento").prop("value", 1); //Factura
                                    }else{
                                        $("#emitir_boleto_command_tipoDocuemento").prop("value", 6); //Voucher
                                    }
                                    emitirBoletoCamino.syncronizarListaBoletos();
                                    $(boletoForm).ajaxSubmit({
                                        target: boletoForm.attr('action'),
                                        type : "POST",
                                        dataType: "html",
                                        cache : false,
                                        async:false,
                                        beforeSubmit: function() { 
                                            core.showLoading({showLoading:true});
                                        },
                                        error: function() {
                                            core.hideLoading({showLoading:true});
                                            emitirBoletoCamino.procesandoDatos = false;
                                        },
                                        success: function(responseText) {
    //                                        console.log("submitHandler....success");
    //                                        console.debug(responseText);  
                                            core.hideLoading({showLoading:true});
                                            if(!core.procesarRespuestaServidor(responseText)){
                                                dialogActual.dialog2("close");
                                                if($("#facturaVoucher").prop("checked") === true){
                                                    emitirBoletoCamino.imprimirFacturas(responseText, function (){
                                                        core.getPageForMenu(boletoForm.attr('action'));
                                                    });
                                                }else{
                                                    emitirBoletoCamino.imprimirVoucher(responseText, function (){
                                                        core.getPageForMenu(boletoForm.attr('action'));
                                                    });
                                                }
                                            }
                                            emitirBoletoCamino.procesandoDatos = false;
                                       }
                                    });
                                }else{
                                    emitirBoletoCamino.procesandoDatos = false;
                                }
                            }
                            
			}, 
			primary: true,
			type: "info"
                    }, 
                    Cancelar: function() {
                        this.dialog2("close");					
                    }				    
                }
             });   
             emitirBoletoCamino.checkBloque2();   //Se chequea los div en cascada
             emitirBoletoCamino.changeTipoPago(); //Se chequea los valores en cascada
        }

    },
    
    clickFacturarEspecial: function(e) {
//        console.debug("clickFacturarEspecial-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var boletoForm = $("#boletoForm");
        if(core.customValidateForm(boletoForm) === true && emitirBoletoCamino.customPreValidateFacturaEspecial() === true){
            $("#detalleFacturaBoletoBody").find("tr").remove();
            $("#emitir_boleto_command_estacionFacturacionEspecialVirtual").select2('val', "");
            $("#emitir_boleto_command_serieFacturacionEspecialVirtual").select2('val', "");
            $("#pingFacturacionEspecial").val("");
            $("#totalFacturarEspecial").val("");
            $("#facturaEspecialVoucher").prop("checked", true);
            emitirBoletoCamino.checkFacturaEspecialVoucher();
            emitirBoletoCamino.calcularImporteTotalMonedaBase(function (success){
                console.debug(success);
                if(success.error && $.trim(success.error) !== ""){
                    alert(success.error);
                }else{
                   $("#totalFacturarEspecial").text("Total: GTQ " + success.total);
                   core.showMessageDialog({
                     title : "Registrar Facturar Otra Estación",
                     selector: $("#facturacionEspecialDIV"),
                     removeOnClose : false,
                     uniqid : false,
                     buttons: {
                        Aceptar: {
                            click: function() {
                                if(emitirBoletoCamino.procesandoDatos === false){
                                    emitirBoletoCamino.procesandoDatos = true;
                                    var dialogActual = this;
        //                            console.debug("Aceptar-click...");
                                    if(core.customValidateForm(boletoForm) === true && emitirBoletoCamino.customPostValidateFacturaEspecial(this) === true){
                                        $("#emitir_boleto_command_movil").prop("value", core.isMovil());
                                        $("#emitir_boleto_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                        $("#emitir_boleto_command_estacionFacturacionEspecial").prop("value", $("#emitir_boleto_command_estacionFacturacionEspecialVirtual").prop("value"));
                                        $("#emitir_boleto_command_serieFacturacionEspecial").prop("value", $("#emitir_boleto_command_serieFacturacionEspecialVirtual").prop("value"));
                                        $("#emitir_boleto_command_pingFacturacionEspecial").prop("value", $("#pingFacturacionEspecial").prop("value"));
                                        if($("#facturaEspecialVoucher").prop("checked") === true){
                                            $("#emitir_boleto_command_tipoDocuemento").prop("value", 4); //Factura
                                        }else{
                                            $("#emitir_boleto_command_tipoDocuemento").prop("value", 7); //Voucher
                                        }
                                        emitirBoletoCamino.syncronizarListaBoletos();
                                        $(boletoForm).ajaxSubmit({
                                            target: boletoForm.attr('action'),
                                            type : "POST",
                                            dataType: "html",
                                            cache : false,
                                            async:false,
                                            beforeSubmit: function() { 
                                                core.showLoading({showLoading:true});
                                            },
                                            error: function() {
                                                core.hideLoading({showLoading:true});
                                                emitirBoletoCamino.procesandoDatos = false;
                                            },
                                            success: function(responseText) {
        //                                        console.log("submitHandler....success");
        //                                        console.debug(responseText);  
                                                core.hideLoading({showLoading:true});
                                                if(!core.procesarRespuestaServidor(responseText)){
                                                    dialogActual.dialog2("close");
                                                    var info = core.getValueFromResponse(responseText, "info");
                                                    core.showNotification({text : info});
                                                    alert("Operación realizada satisfactoriamente. " + info, function() {
                                                        core.getPageForMenu(boletoForm.attr('action'));
                                                    });  
                                                 }
                                                emitirBoletoCamino.procesandoDatos = false;
                                           }
                                        });
                                    }else{
                                        emitirBoletoCamino.procesandoDatos = false;
                                    }
                                }

                            }, 
                            primary: true,
                            type: "info"
                        }, 
                        Cancelar: function() {
                            this.dialog2("close");					
                        }				    
                    }
                 });
               }
            }, false, { });
        }
    },
    
    customCommunValidate: function(dialog) {
        var cantidadBoletos = $("#clienteBoletoBody").find("tr").not("#clienteBoletoVacioTR").length;
        if(cantidadBoletos <= 0){
            core.hiddenDialog2(dialog);
            alert("Debe definir al menos un boleto.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        
        return true;
    },
    
    customPreValidateCortesia: function(dialog) {
        
        if(emitirBoletoCamino.customCommunValidate(dialog) === false){
            return false;
        }
        
        var cantidadBoletos = $("#clienteBoletoBody").find("tr").not("#clienteBoletoVacioTR").length;
        if(cantidadBoletos > 1){
            core.hiddenDialog2(dialog);
            alert("Para las cortesías no puede definir más de un boleto.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        

        return true;
    },
    
    customPostValidateCortesia: function(dialog) {
        
        var pinAutorizacionCortesia = $("#pinAutorizacionCortesia").prop("value");
        if(pinAutorizacionCortesia === undefined || pinAutorizacionCortesia === null || $.trim(pinAutorizacionCortesia) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe especificar un PIN de autorización.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        return true;
    },
    
    customPreValidateFactura: function(dialog) {
        
        if(emitirBoletoCamino.customCommunValidate(dialog) === false){
            return false;
        }
        
        var moneda = $("#emitir_boleto_command_monedaPagoVirtual").select2("val");
        if( $.trim(moneda) === "" ){
            core.hiddenDialog2(dialog);
            alert("Debe abrir una caja para facturar.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        var sizeItems = $("#emitir_boleto_command_serieFacturaVirtual").data("sizeItems");
        if(sizeItems <= 0){
            core.hiddenDialog2(dialog);
            alert("No existen series de factura activa en la estación.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    customPreValidateFacturaEspecial: function(dialog) {
        
        if(emitirBoletoCamino.customCommunValidate(dialog) === false){
            return false;
        }

        return true;
    },
    
    customPostValidateFactura: function(dialog) {
        
        if($("#facturaVoucher").prop("checked") === true){
            if(frondend.listaImpresorasDisponibles === null || $.trim(frondend.listaImpresorasDisponibles) === ""){
                core.hiddenDialog2(dialog);
                alert("El sistema aún no ha podido detectar las impresoras para la facturación.", function(){
                    core.showDialog2(dialog);
                });
                return false;
            }
            
            var serieFactura = $("#emitir_boleto_command_serieFacturaVirtual").select2("val");
            if( $.trim(serieFactura) === "" ){
                core.hiddenDialog2(dialog);
                alert("Debe seleccionar una serie de factura.", function(){
                    core.showDialog2(dialog);
                });
                return false;
            }
        }
        
        var tipoPago = $("#emitir_boleto_command_tipoPagoVirtual").prop("value");
        if(tipoPago === undefined || tipoPago === null || $.trim(tipoPago) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe especificar un tipo de pago.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }else{
            
            if(tipoPago === "1"){  //Efectivo
                
               var monedaPago = $("#emitir_boleto_command_monedaPagoVirtual").prop("value");
                if(monedaPago === undefined || monedaPago === null || $.trim(monedaPago) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar una moneda de pago.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
                var efectivo = $("#emitir_boleto_command_efectivoVirtual").prop("value");
                if(efectivo === undefined || efectivo === null || $.trim(efectivo) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar un efectivo.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                } 
            }else{
                
                var autorizacionTarjeta = $("#emitir_boleto_command_autorizacionTarjetaVirtual").prop("value");
                if(autorizacionTarjeta === undefined || autorizacionTarjeta === null || $.trim(autorizacionTarjeta) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar un código de autorización.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
            }
        }
        
        var totalNeto = $("#emitir_boleto_command_totalNetoVirtual").prop("value");
        if(totalNeto === undefined || totalNeto === null || $.trim(totalNeto) === ""){
            core.hiddenDialog2(dialog);
            alert("No se ha podido determinar el total neto.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    customPostValidateFacturaEspecial: function(dialog) {
        
        var totalNeto = $("#totalFacturarEspecial").text();
        if(totalNeto === undefined || totalNeto === null || $.trim(totalNeto) === ""){
            core.hiddenDialog2(dialog);
            alert("No se ha podido determinar el total.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        var estacionFacturacion = $("#emitir_boleto_command_estacionFacturacionEspecialVirtual").prop("value");
        if(estacionFacturacion === undefined || estacionFacturacion === null || $.trim(estacionFacturacion) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe especificar la estación solicitante de la facturación.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        if($("#facturaEspecialVoucher").prop("checked")){
            var serieFacturacion = $("#emitir_boleto_command_serieFacturacionEspecialVirtual").prop("value");
            if(serieFacturacion === undefined || serieFacturacion === null || $.trim(serieFacturacion) === ""){
                core.hiddenDialog2(dialog);
                alert("Debe especificar una serie de facturación.", function(){
                    core.showDialog2(dialog);
                });
                return false;
            }    
        }
        
        var pingFacturacion = $("#pingFacturacionEspecial").prop("value");
        if(pingFacturacion === undefined || pingFacturacion === null || $.trim(pingFacturacion) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe definir el ping de autorización de facturación.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        var cantidadFacturasBoletos = $("#detalleFacturaBoletoBody").find("tr").length;
        if(cantidadFacturasBoletos <= 0){
            core.hiddenDialog2(dialog);
            alert("La estación seleccionada no tiene facturas disponibles.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    customValidateBoleto: function() {
        
        if(emitirBoletoCamino.customCommunValidate() === false){
            return false;
        }
            
        
        return true;
    },
    
    checkBloque2: function(tipoPago) {
//        console.log("checkBloque2-init");
        if(!tipoPago){
            tipoPago = $("#emitir_boleto_command_tipoPagoVirtual").val();
        }
        if(tipoPago === ""){  //Vacio
            $("#emitir_boleto_command_totalNetoVirtual").val("");
            $(".bloque2").hide();
            $(".bloque5").hide();
            emitirBoletoCamino.checkBloque3(tipoPago);
        }
        else if(tipoPago === "1"){ //Efectivo
            $(".bloque2").show();
            $(".bloque5").hide();
            emitirBoletoCamino.checkBloque3(tipoPago);
        }
        else if(tipoPago === "2"){
            $(".bloque2").hide();
            $(".bloque5").show();
            emitirBoletoCamino.checkBloque3(tipoPago);
        }
        else if(tipoPago === "3"){
            $(".bloque2").hide();
            $(".bloque5").show();
            emitirBoletoCamino.checkBloque3(tipoPago);
        }
    },
    
    checkBloque3: function(tipoPago) {
//        console.log("checkBloque3-init");
        if(!tipoPago){
            tipoPago = $("#emitir_boleto_command_tipoPagoVirtual").val();
        }
        var monedaPago = $("#emitir_boleto_command_monedaPagoVirtual").val();
        if(tipoPago === "1" && monedaPago !== "" && monedaPago !== "1"){  //Efectivo
            $(".bloque3").show();
        }else{ //Vacio y Tarjetas CR y DB
            $(".bloque3").hide();
        }
        emitirBoletoCamino.checkBloque4(tipoPago);
    },
    
    checkBloque4: function(tipoPago) {
//        console.log("checkBloque4-init");
        if(!tipoPago){
            tipoPago = $("#emitir_boleto_command_tipoPagoVirtual").val();
        }
        if(tipoPago === "1"){  //Efectivo
            $(".bloque4").show();
        }else{ //Vacio y Tarjetas CR y DB
            $(".bloque4").hide();
        }
    },
   
    calcularImporteTotalMonedaBase: function(successCallback, async, extra) {
        emitirBoletoCamino.syncronizarListaBoletos();
        var idEstacionOrigen = $("#emitir_boleto_command_estacionSubeEn").val();
        var idEstacionDestino = $("#emitir_boleto_command_estacionBajaEn").val();
        var idTipoPago = $("#emitir_boleto_command_tipoPagoVirtual").val();
        var idSalida = core.getSelectedItemId("#salidaGrid");
        var listaClienteBoleto = $("#emitir_boleto_command_listaClienteBoleto").val();
        var utilizarDesdeEstacionOrigenSalida = $("#emitir_boleto_command_utilizarDesdeEstacionOrigenSalida").prop('checked');
        if(idEstacionOrigen !== null && $.trim(idEstacionOrigen) !== "" && 
           idEstacionDestino !== null && $.trim(idEstacionDestino) !== "" && 
           idTipoPago !== null && $.trim(idTipoPago) !== "" && 
           idSalida !== null && $.trim(idSalida) !== "" && 
           listaClienteBoleto !== null && $.trim(listaClienteBoleto) !== ""){
           var extraParams = {
                idEstacionOrigen : idEstacionOrigen,
                idEstacionDestino : idEstacionDestino,
                idTipoPago : idTipoPago,
                idSalida: idSalida,
                listaClienteBoleto: listaClienteBoleto,
                pagadoDesdeOrigenRuta: utilizarDesdeEstacionOrigenSalida
           };
           if(extra){ jQuery.extend(extraParams, extra); }
           core.request({
                url : $("#pathCalcularImporteTotalMonedaBase").attr('value'),
                method: 'POST', //Obligatorio
                extraParams: extraParams, 
                dataType: "json",
                async: async,
                successCallback: function(success){
                    successCallback(success, extraParams);
                }
           });
       }
    },
    
    changeTipoPago: function() {
//        console.log("changeTipoPago-init");
        emitirBoletoCamino.checkBloque2();
        $("#emitir_boleto_command_totalNetoVirtual").val("");
        $("#emitir_boleto_command_tasaVirtual").val("");
        $("#emitir_boleto_command_totalPagoVirtual").val("");
        $("#emitir_boleto_command_vueltoVirtual").val("");
        $("#emitir_boleto_command_efectivoVirtual").val("");
        emitirBoletoCamino.calcularImporteTotalMonedaBase(function (success, extraParams){
            if(success.error && $.trim(success.error) !== ""){
                var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                modal.hide();
                alert(success.error, function (){
                    modal.show();  //Quito el popup ya que es innecesario, al final hay que cancelar         	
                });
             }else{
                $("#emitir_boleto_command_totalNetoVirtual").val(success.total);
                if(extraParams.idTipoPago === "1"){  //Solo si es efectivo, en otro caso no hace falta.. ya estan limpios los inputs...
                    emitirBoletoCamino.changeMonedaPago();
                }
                $('#emitir_boleto_command_efectivoVirtual').focus();
            }
        }, true, {});
    },
    
    changeMonedaPago: function() {
//      console.log("changeMonedaPago-init");  
      var monedaPago = $("#emitir_boleto_command_monedaPagoVirtual").val();
      $("#emitir_boleto_command_tasaVirtual").val("");
      $("#emitir_boleto_command_totalPagoVirtual").val("");
      $("#emitir_boleto_command_vueltoVirtual").val("");
      if(monedaPago === null || $.trim(monedaPago) === ""){
        $("#prependTotalPagoVirtual").text("???");
        $("#prependEfectivoVirtual").text("???");
      }
      else if($.trim(monedaPago) === "1"){
        $("#prependTotalPagoVirtual").text("GTQ");
        $("#prependEfectivoVirtual").text("GTQ");
        emitirBoletoCamino.changeEfectivo();
      }
      else{
        //NO SON QUETSALES
        var totalNeto = $("#emitir_boleto_command_totalNetoVirtual").val();
        var idMonedaPago = $("#emitir_boleto_command_monedaPagoVirtual").val();
        $("#prependTotalPagoVirtual").text("GTQ");
        $("#prependEfectivoVirtual").text("GTQ");
        if(totalNeto !== null && $.trim(totalNeto) !== "" && 
           idMonedaPago !== null && $.trim(idMonedaPago) !== ""){
           core.request({
                url : $("#pathCalcularImporteTotalPorMoneda").attr('value'),
                method: 'POST',
                extraParams: {
                    totalNeto : totalNeto,
                    idMoneda : idMonedaPago
                }, 
                dataType: "json",
                async:true,
                successCallback: function(success){
//                    console.debug(success);
                    if(success.error && $.trim(success.error) !== ""){
                        var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                        modal.hide();
                        alert(success.error, function (){
                           modal.show(); 
                        });
                    }else{
                        $("#prependTotalPagoVirtual").text(success.sigla);
                        $("#prependEfectivoVirtual").text(success.sigla);
                        $("#emitir_boleto_command_tasaVirtual").val(success.tasa);
                        $("#emitir_boleto_command_totalPagoVirtual").val(success.total);
                        emitirBoletoCamino.changeEfectivo();
                    }
                }
           });
        }
      }
      emitirBoletoCamino.checkBloque3();
    },
    
    
    changeEfectivo: function() {
//      console.log("changeEfectivo-init");
      var monedaPago = $("#emitir_boleto_command_monedaPagoVirtual").val();
      $("#emitir_boleto_command_vueltoVirtual").val("");
      
      var efectivo = $("#emitir_boleto_command_efectivoVirtual").val();
      if(!$.isNumeric(efectivo)){
          $("#emitir_boleto_command_efectivoVirtual").val("");
          return;
      }else{
          efectivo = parseFloat(efectivo).toFixed(2);
          $("#emitir_boleto_command_efectivoVirtual").val(efectivo);
      }
      
      if($.trim(monedaPago) === "1"){ //GTQ
         var totalNeto = $("#emitir_boleto_command_totalNetoVirtual").val();
         var vuelto = efectivo - parseFloat(totalNeto);
         vuelto = parseFloat(vuelto).toFixed(2);
         if(vuelto === 0){
              $("#emitir_boleto_command_vueltoVirtual").val(0);
         }else if(vuelto < 0){
             var modal = $('.modal[style^="display: block;"]'); //Modal Activo
             modal.hide();
             alert("El efectivo no alcanza, como mínimo son:" + totalNeto + ".", function (){
                modal.show();
                $("#emitir_boleto_command_efectivoVirtual").val(totalNeto); // Se le setea el importe minimo.
                $("#emitir_boleto_command_vueltoVirtual").val(0);
             });
         }else{
             $("#emitir_boleto_command_vueltoVirtual").val(vuelto);
         }
      }
      else{
        //NO SON QUETSALES
        var totalPago = $("#emitir_boleto_command_totalPagoVirtual").val();
        var vuelto = efectivo - parseFloat(totalPago);
         vuelto = parseFloat(vuelto).toFixed(2); //Vuelto en EUR O USD
         if(vuelto === 0){
              $("#emitir_boleto_command_vueltoVirtual").val(0);
         }else if(vuelto < 0){
             var modal = $('.modal[style^="display: block;"]'); //Modal Activo
             modal.hide();
             alert("El efectivo no alcanza, como mínimo son:" + totalPago + ".", function (){
                modal.show();
                $("#emitir_boleto_command_efectivoVirtual").val(totalPago); // Se le setea el importe minimo.
                $("#emitir_boleto_command_vueltoVirtual").val(0);
             });
         }else{

            //Convirtiendo el vuelto a Quetsales
            var idMonedaPago = $("#emitir_boleto_command_monedaPagoVirtual").val();
            if(idMonedaPago !== null && $.trim(idMonedaPago) !== ""){
                core.request({
                    url : $("#pathCalcularImporteTotalPorMoneda").attr('value'),
                    method: 'POST',
                    extraParams: {
                        totalNeto : vuelto,
                        idMoneda : idMonedaPago,
                        dir : false
                    }, 
                    dataType: "json",
                    async:true,
                    successCallback: function(success){
//                    console.debug(success);
                    if(success.error && $.trim(success.error) !== ""){
                        var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                        modal.hide();
                        alert(success.error, function (){
                           modal.show(); 
                           $("#emitir_boleto_command_efectivoVirtual").val(totalPago); // Se le setea el importe minimo.
                           $("#emitir_boleto_command_vueltoVirtual").val(0);
                        });
                    }else{
                        $("#emitir_boleto_command_vueltoVirtual").val(success.total);
                    }
                }
           });
        }  
             
         } 
      }
    },
    
    changeEstacionFacturacionEspecial : function() {
        var estacionFacturacionEspecial = $("#emitir_boleto_command_estacionFacturacionEspecialVirtual").select2('val');
        var idSalida = core.getSelectedItemId("#salidaGrid");
        $("#detalleFacturaBoletoBody").find("tr").remove(); //Elimino todos los tr
        $("#emitir_boleto_command_serieFacturacionEspecialVirtual").select2({ data: [] });
        $("#facturaEspecialVoucher").prop("checked", true);
        emitirBoletoCamino.checkFacturaEspecialVoucher();
        if(estacionFacturacionEspecial !== null && $.trim(estacionFacturacionEspecial) !== "" && 
                idSalida !== null && $.trim(idSalida) !== "" ){
            core.request({
                url : $("#pathSeriesActivaPorEstacion").attr('value'),
                method: 'POST',
                extraParams: {
                    idEstacion : estacionFacturacionEspecial,
                    idSalida : idSalida
                }, 
                dataType: "json",
                async:true,
                successCallback: function(success){
                    var optionSeriesFacturas = success.optionSeriesFacturas;
                    if( optionSeriesFacturas ){
                        $("#emitir_boleto_command_serieFacturacionEspecialVirtual").select2({
                            allowClear: false,
                            data: { results: optionSeriesFacturas }
                        });
                        
                        var cantidad = optionSeriesFacturas.length;
                        $("#emitir_boleto_command_serieFacturacionEspecialVirtual").data("sizeItems", cantidad);
                        if(cantidad > 0){
                            if(optionSeriesFacturas[0]){
                                $("#emitir_boleto_command_serieFacturacionEspecialVirtual").select2('val', optionSeriesFacturas[0].id);
                                emitirBoletoCamino.changeSerieFacturacionEspecial();
                            }
                        }else{
                            var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                            modal.hide();
                            alert("La estación no tiene series de facturas activas.", function (){
                                modal.show();  //Quito el popup ya que es innecesario, al final hay que cancelar         	
                            });
                        }
                    }
                    if(success.showVoucher && eval(success.showVoucher) === true){
                       $("#facturaEspecialVoucher").removeClass("hidden");
                    }else{
                       $("#facturaEspecialVoucher").addClass("hidden");
                    }
                }
           });
        }
    },
    
    changeSerieFacturacionEspecial : function() {
        var estacionFacturacionEspecial = $("#emitir_boleto_command_estacionFacturacionEspecialVirtual").select2('val');
        var serieFacturacionEspecial = $("#emitir_boleto_command_serieFacturacionEspecialVirtual").select2('val');
        $("#detalleFacturaBoletoBody").find("tr").remove(); //Elimino todos los tr
        emitirBoletoCamino.calcularImporteTotalMonedaBase(function (success){
            if(success.error && $.trim(success.error) !== ""){
                var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                modal.hide();
                alert(success.error, function (){
                    modal.show();  //Quito el popup ya que es innecesario, al final hay que cancelar         	
                });
            }else{
                var boletos = success.boletos;
                $.each(boletos, function() {  
                    var itemTR = $("<tr><td>"+this.numero+"</td><td>"+this.factura+"</td><td>"+this.importe+"</td></tr>");
                    $("#detalleFacturaBoletoBody").append(itemTR);
                });
            }
        }, false, { 
            detalle : true,
            estacionDetalle : estacionFacturacionEspecial,
            facturar : $("#facturaEspecialVoucher").prop("checked"),
            serieFacturacionEspecial : serieFacturacionEspecial
        });
    },
    
//    clickVoucher: function(e) {
//        e.preventDefault();
//        e.stopPropagation();
//        var boletoForm = $("#boletoForm");
//        if(core.customValidateForm(boletoForm) === true && emitirBoletoCamino.customPreValidateVoucher() === true){
//            $("#emitir_boleto_command_tipoPagoVirtual").val("1");  //Solo pago en efectivo
//            emitirBoletoCamino.calcularImporteTotalMonedaBase(function (success){
//                console.debug(success);
//                if(success.error && $.trim(success.error) !== ""){
//                    alert(success.error);
//                }else{
//                    
//                    var importeTotal = core.customParseFloat(success.total);
//                    if(importeTotal <= 0){
//                        alert("No se pudo determinar el importe a cobrar.");
//                        return;
//                    }
//                    var cantidadBoletos = $("#clienteBoletoBody").find("tr").not("#clienteBoletoVacioTR").length;
//                    var text = "Se seleccionaron "+cantidadBoletos+" boletos. El precio total es de GTQ " + importeTotal + ".";
//                    core.showMessageDialog({
//                        title : "Registrar Voucher",
//                        text: text,
//                        buttons: {
//                            Aceptar: {
//                            click: function() {
//                                if(emitirBoletoCamino.procesandoDatos === false){
//                                    emitirBoletoCamino.procesandoDatos = true;
//                                    var dialogActual = this;
//        //                            console.debug("Aceptar-click...");
//                                    if(core.customValidateForm(boletoForm) === true){
//                                        $("#emitir_boleto_command_movil").prop("value", core.isMovil());
//                                        $("#emitir_boleto_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
//                                        $("#emitir_boleto_command_tipoPago").prop("value", "1");
//                                        $("#emitir_boleto_command_totalNeto").prop("value", importeTotal);
//                                        $("#emitir_boleto_command_monedaPago").prop("value", "1");
//                                        $("#emitir_boleto_command_tipoDocuemento").prop("value", 6); //Voucher
//                                        emitirBoletoCamino.syncronizarListaBoletos();
//                                        $(boletoForm).ajaxSubmit({
//                                            target: boletoForm.attr('action'),
//                                            type : "POST",
//                                            dataType: "html",
//                                            cache : false,
//                                            async:false,
//                                            beforeSubmit: function() { 
//                                                core.showLoading({showLoading:true});
//                                            },
//                                            error: function() {
//                                                core.hideLoading({showLoading:true});
//                                                emitirBoletoCamino.procesandoDatos = false;
//                                            },
//                                            success: function(responseText) {
//                                                core.hideLoading({showLoading:true});
//                                                if(!core.procesarRespuestaServidor(responseText)){
//                                                    dialogActual.dialog2("close");
//                                                    emitirBoletoCamino.imprimirVoucher(responseText, function (){
//                                                        core.getPageForMenu(boletoForm.attr('action'));
//                                                    });
//                                                    
//                                                 }
//                                                emitirBoletoCamino.procesandoDatos = false;
//                                           }
//                                        });
//                                    }else{
//                                        emitirBoletoCamino.procesandoDatos = false;
//                                    }
//                                  }
//
//                                }, 
//                                primary: true,
//                                type: "info"
//                            }, 
//                            Cancelar: function() {
//                                this.dialog2("close");					
//                            }				    
//                        }
//                    });
//                }
//            }, false, { });
//        }
//    },
//    
//    customPreValidateVoucher: function(dialog) {
//        
//        if(emitirBoletoCamino.customCommunValidate(dialog) === false){
//            return false;
//        }
//        
//        var moneda = $("#emitir_boleto_command_monedaPagoVirtual").select2("val");
//        if( $.trim(moneda) === "" ){
//            core.hiddenDialog2(dialog);
//            alert("Debe abrir una caja para facturar.", function(){
//                core.showDialog2(dialog);
//            });
//            return false;
//        }else{
//            if( $.trim(moneda) !== "1" ){
//                alert("Debe abrir una caja en GTQ.");
//                return false;
//            }
//        }
//        
//        return true;
//    },
    
};
