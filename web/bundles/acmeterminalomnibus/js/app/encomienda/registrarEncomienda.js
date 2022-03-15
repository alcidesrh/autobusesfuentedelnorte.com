registrarEncomienda = {
    
    funcionesAddOnload : function() {
//        console.debug("registrarEncomienda.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
        registrarEncomienda.checkTipoEncomiena();
        registrarEncomienda.cargarMonedas();
//	console.debug("registrarEncomienda.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        
        $("#registrar_encomienda_command_identificadorWeb").val(core.uniqIdCompuesto());
        
        var pathlistarclientespaginando = $("#registrar_encomienda_command_clienteRemitente").data("pathlistarclientespaginando");
        $("#registrar_encomienda_command_clienteRemitente").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: pathlistarclientespaginando,
                dataType: 'json',
                type: "POST",
                data: function (term, page) {
                    return {term: term, page_limit: 5};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
        });
        
        var pathlistarclientespaginando = $("#registrar_encomienda_command_clienteDestinatario").data("pathlistarclientespaginando");
        $("#registrar_encomienda_command_clienteDestinatario").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: pathlistarclientespaginando,
                dataType: 'json',
                type: "POST",
                data: function (term, page) {
                    return {term: term, page_limit: 5};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
        });
        
        var pathlistarboletospaginando = $("#registrar_encomienda_command_boleto").data("pathlistarboletospaginando");
        $("#registrar_encomienda_command_boleto").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: pathlistarboletospaginando,
                dataType: 'json',
                type: "POST",
                data: function (term, page) {
                    return {term: term, page_limit: 5};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            },
            initSelection: function(element, callback) {
                var id = $(element).val();
                if (id !== "") {
                    $.ajax(pathlistarboletospaginando, {    
                        data: {
                            id: id
                        },
                        type: "POST",
                        dataType: "json"
                    }).done(function(data) { callback(data); });
                }
            }
        });
        
        $("#registrar_encomienda_command_estacionOrigen").select2({
            allowClear: $("#registrar_encomienda_command_estacionOrigen option[value='']").length === 1
        });
        $("#registrar_encomienda_command_estacionOrigen").select2("readonly", ($("#registrar_encomienda_command_estacionOrigen").attr("readonly") === "readonly"));
        
        $("#registrar_encomienda_command_tipoEncomiendaVirtual").select2({
            allowClear: true
        });
        
        var pathlistarespecialespaginando = $("#registrar_encomienda_command_tipoEncomiendaEspecialVirtual").data("pathlistartipoencomiendaespecialpaginando");
        $("#registrar_encomienda_command_tipoEncomiendaEspecialVirtual").select2({
            minimumInputLength: 0,
            allowClear: true,
            ajax: { 
                url: pathlistarespecialespaginando,
                dataType: 'json',
                type: "POST",
                data: function (term, page) {
                    return {term: term, page_limit: 20};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
        });
        
        $("#registrar_encomienda_command_tipoPagoVirtual").select2({
            allowClear: true
        });
        
        $("#registrar_encomienda_command_monedaPagoVirtual").select2({
            allowClear: false,
            data: []
        });
        
        $("#registrar_encomienda_command_rutaVirtual").select2({
            allowClear: true,
            data: []
        });
        
        $("#registrar_encomienda_command_estacionFinalVirtual").select2({
            allowClear: true,
            data: []
        });
        
        $("#registrar_encomienda_command_serieFacturaVirtual").select2({
            allowClear: false,
            data: []
        });
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
        $("#addClienteRemitente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                var element = $("#registrar_encomienda_command_clienteRemitente");
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#registrar_encomienda_command_clienteRemitente').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             }, {
                 'confirmarOperacion' : false
             });
         });
        $("#updateClienteRemitente").click(function(e) {
             frondend.loadSubPage(e, $(this), function() {
                var element = $("#registrar_encomienda_command_clienteRemitente");
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
                                $('#registrar_encomienda_command_clienteRemitente').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#seachClienteRemitente").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
//                console.debug("Seteando elemento seleccionado..."); 
//                console.debug(id);
                if (id !== "") {
                    var element = $("#registrar_encomienda_command_clienteRemitente");
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
                                $('#registrar_encomienda_command_clienteRemitente').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         }); 
         $("#addClienteDestinatario").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
                var element = $("#registrar_encomienda_command_clienteDestinatario");
                if (id !== "") {
                    core.request({
                        url : element.data("pathlistarclientespaginando"),
                        type: "POST",
                        dataType: "json",
                        async: false,
                        extraParams : { id: id },
                        successCallback: function(data){
                            if( data.options && data.options[0]){
                                $('#registrar_encomienda_command_clienteDestinatario').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             }, {
                 'confirmarOperacion' : false
             });
         });
         $("#updateClienteDestinatario").click(function(e) {
             frondend.loadSubPage(e, $(this), function() {
                var element = $("#registrar_encomienda_command_clienteDestinatario");
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
                                $('#registrar_encomienda_command_clienteDestinatario').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         });
         $("#seachClienteDestinatario").click(function(e) {
             frondend.loadSubPage(e, $(this), function(id) {
//                console.debug("Seteando elemento seleccionado..."); 
//                console.debug(id);
                if (id !== "") {
                    var element = $("#registrar_encomienda_command_clienteDestinatario");
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
                                $('#registrar_encomienda_command_clienteDestinatario').select2('data', data.options[0]); 
                            }
                        }
                    });
                }
             });
         }); 
        
        
        $("#adicionarEncomienda").click(registrarEncomienda.clickAdicionarEncomienda); 
        $("#adicionarRuta").click(registrarEncomienda.clickAdicionarRuta); 
        
        $("#facturar").click(registrarEncomienda.clickFacturar); 
        $("#porCobrar").click(registrarEncomienda.clickPorCobrar);
        $("#autorizacionCortesia").click(registrarEncomienda.clickAutorizacionCortesia);
        $("#autorizacionInterna").click(registrarEncomienda.clickAutorizacionInterna);
        
        $("#registrar_encomienda_command_tipoEncomiendaVirtual").on("change", registrarEncomienda.checkTipoEncomiena);
        $("#registrar_encomienda_command_rutaVirtual").on("change", registrarEncomienda.changeRuta);
        
        $("#registrar_encomienda_command_tipoPagoVirtual").on("change", registrarEncomienda.changeTipoPago);
        $("#registrar_encomienda_command_monedaPagoVirtual").on("change", registrarEncomienda.changeMonedaPago);
        $("#registrar_encomienda_command_efectivoVirtual").on("change", registrarEncomienda.changeEfectivo);
        $("#registrar_encomienda_command_boleto").on("change", registrarEncomienda.changeBoleto);
        
        $("#registrar_encomienda_command_serieFacturaVirtual").on("change", function (){
             console.debug("serieFacturaVirtual-init");
             var idSerieFactura = $("#registrar_encomienda_command_serieFacturaVirtual").select2('val');
             var idEmpresa = $("#registrar_encomienda_command_serieFacturaVirtual").select2('data').idEmpresa;
             sessionStorage.setItem("sist_last_id_serie_factura_enco_" + idEmpresa, idSerieFactura);
         });
    },
    
    cargarMonedas: function() {
        var moneda = $("#registrar_encomienda_command_monedaPagoVirtual").select2('val');
        if($.trim(moneda) === ""){
            core.request({
                url : $("#pathMonedasCajasAbiertas").val(),
                type: "GET",
                dataType: "json",
                async: true,
                showLoading : false,
                successCallback: function(data){
                    if( data.optionMonedas){
                        var optionMonedas = data.optionMonedas;
                        $("#registrar_encomienda_command_monedaPagoVirtual").select2({
                            allowClear: false,
                            data: { results: optionMonedas }
                        });
                        if(optionMonedas[0]){
                            $("#registrar_encomienda_command_monedaPagoVirtual").select2('val', optionMonedas[0].id);
                        }
                    }
                }
            });
        }
    },
    
    clearPopUpRuta : function() {
        $("#registrar_encomienda_command_rutaVirtual").select2("val", "");
        $("#registrar_encomienda_command_estacionFinalVirtual").select2("val", "");
        $("#estacionFinalDIV").hide();
    },
    
    clickAdicionarRuta : function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var cantidadMaxima = 4;
        var listaEncomiendaRutas = $("#registrar_encomienda_command_listaEncomiendaRutas").val();
        if(listaEncomiendaRutas){ listaEncomiendaRutas = JSON.parse(listaEncomiendaRutas); }
        else{ listaEncomiendaRutas = []; }
        if(listaEncomiendaRutas.length >= cantidadMaxima){
            alert("No se puede registrar más de " + cantidadMaxima + " rutas.");
            return;
        }
        
        registrarEncomienda.clearPopUpRuta();
        registrarEncomienda.loadRutas(function(){
            core.showMessageDialog({
                title : "Adicionar Ruta",
                selector: $("#adicionarRutaDIV"),
                removeOnClose : false,
                uniqid : false,
                buttons: {
                    Aceptar: {
                        click: function() {
                            var dialogActual = this;
                            var form = $("#adicionarEncomiendaDIV").find("form");
                            if(form.length === 0){
                                form = $("<form id='adicionarRutaPopUpForm'></form>");
                                form.append($("#adicionarRutaDIV").children());
                                $("#adicionarRutaDIV").append(form);
                            }
                            if(core.customValidateForm(form) === true && registrarEncomienda.validatePopUpRuta(dialogActual) === true){
                                var listaEncomiendaRutas = $("#registrar_encomienda_command_listaEncomiendaRutas").val();
                                if(listaEncomiendaRutas){ listaEncomiendaRutas = JSON.parse(listaEncomiendaRutas); }
                                else{ listaEncomiendaRutas = []; }
                                var rutaSelected = $("#registrar_encomienda_command_rutaVirtual").select2("data");
                                var estacionFinal = $("#registrar_encomienda_command_estacionFinalVirtual").select2("data");
                                var item = {
                                    id : core.uniqId("ruta_"),
                                    posicion : listaEncomiendaRutas.length + 1,
                                    rutaVirtual : rutaSelected.id,
                                    rutaNameVirtual : rutaSelected.text,
                                    idEmpresa : rutaSelected.idEmpresa,
                                    estacionFinalVirtual : estacionFinal.id,
                                    estacionFinalNameVirtual : estacionFinal.text
                                };
                                listaEncomiendaRutas.push(item);
                                registrarEncomienda.renderItemGridAddRuta(item);
                                $("#registrar_encomienda_command_estacionDestino").val(estacionFinal.id); //Siempre tengo en el hidden la ultima estacion
                                $("#registrar_encomienda_command_listaEncomiendaRutas").val(JSON.stringify(listaEncomiendaRutas));
                                this.dialog2("close");
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
        });
    },
    
    renderItemGridAddRuta: function(item) {
        $("#rutaBody").find("#rutaVacioTR").hide(); //Oculto el TR vacio
        var eliminarRuta = $('<a class="btn btn-small" href="#"><i class="icon-minus"></i></a>');
        var itemTR = $("<tr id='"+item.id+"'><td>"+item.posicion+"</td><td>"+item.rutaNameVirtual+"</td><td>"+item.estacionFinalNameVirtual+"</td><td class='action'></td></tr>");
        itemTR.find(".action").append(eliminarRuta);
        $("#rutaBody").append(itemTR);
        $(eliminarRuta).click(registrarEncomienda.clickEliminarRuta); 
    },
    
    clickEliminarRuta : function(e) {
        e.preventDefault();
        e.stopPropagation();
        var tr = $(this).parent().parent();
        var id = tr.attr("id");
        if(id !== null && $.trim(id) !== ""){
            var listaEncomiendaRutas = $("#registrar_encomienda_command_listaEncomiendaRutas").val();
            if(listaEncomiendaRutas){ listaEncomiendaRutas = JSON.parse(listaEncomiendaRutas); }
            else{ listaEncomiendaRutas = []; }
            
            $("#registrar_encomienda_command_estacionDestino").val("");
            var listaEncomiendaRutasPre = [];
            $.each(listaEncomiendaRutas, function() {  
               if($.trim(this.id) === id){
                   return false;
               }else{
                   listaEncomiendaRutasPre.push(this);
                   $("#registrar_encomienda_command_estacionDestino").val(this.estacionFinalVirtual);
               }
            });
            listaEncomiendaRutas = listaEncomiendaRutasPre;
            $("#rutaBody").find("tr").not("#rutaVacioTR").remove(); //Elimino todos los tr
            $("#rutaBody").find("#rutaVacioTR").show(); //Muestro el vacio
            $.each(listaEncomiendaRutas, function() {  
                registrarEncomienda.renderItemGridAddRuta(this);
            });
            $("#registrar_encomienda_command_listaEncomiendaRutas").val(JSON.stringify(listaEncomiendaRutas));
        }
                           
    },
    
    validatePopUpRuta: function(dialog) { 
        
        var rutaVirtual =  $("#registrar_encomienda_command_rutaVirtual").val();
        if(rutaVirtual === null || $.trim(rutaVirtual) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe seleccionar una ruta.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        var estacionFinalVirtual =  $("#registrar_encomienda_command_estacionFinalVirtual").val();
        if(estacionFinalVirtual === null || $.trim(estacionFinalVirtual) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe seleccionar una estación.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    loadRutas : function(successCallback) {
        
        var estacion = $("#registrar_encomienda_command_estacionOrigen").val();
        var listaEncomiendaRutas = $("#registrar_encomienda_command_listaEncomiendaRutas").val();
        if(listaEncomiendaRutas){ listaEncomiendaRutas = JSON.parse(listaEncomiendaRutas); }
        else{ listaEncomiendaRutas = []; }
        if(listaEncomiendaRutas.length !== 0 ){
            estacion = listaEncomiendaRutas[listaEncomiendaRutas.length-1].estacionFinalVirtual;
        }
        core.request({
            url : $("#pathGetRutasPorEstacion").prop("value"),
            type: "POST",
            dataType: "json",
            async: false,
            extraParams : { 
                idEstacionOrigen: estacion,
                rutaInicial : listaEncomiendaRutas.length === 0
            },
            successCallback: function(data){
                var optionRutas = data.optionRutas;
                $("#registrar_encomienda_command_rutaVirtual").select2({
                    allowClear: true,
                    data: { results: optionRutas }
                });
//                $("#registrar_encomienda_command_rutaVirtual").select2('val', optionRutas[0].id);  
                registrarEncomienda.changeRuta();
                if(successCallback && $.isFunction(successCallback)){
                    successCallback();
                }
            }
          });
    },
    
    changeRuta : function() {
        
        var ruta = $("#registrar_encomienda_command_rutaVirtual").val();
        if(ruta === null || $.trim(ruta) === ""){
            $("#estacionFinalDIV").hide();
        }else{
           core.request({
                url : $("#pathGetEstacionesDestinosPorRuta").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { codigoRuta: ruta },
                successCallback: function(data){
                    $("#estacionFinalDIV").show();
                    var optionEstacionesDestino = data.optionEstacionesDestino;
                    $("#registrar_encomienda_command_estacionFinalVirtual").select2({
                        allowClear: true,
                        data: { results: optionEstacionesDestino }
                    });
                    $("#registrar_encomienda_command_estacionFinalVirtual").select2('val', optionEstacionesDestino[0].id);    

                }
            }); 
        }
    },

    checkTipoEncomiena : function() {
//        console.log("checkTipoEncomiena-init");
        var tipoEncomienda = $("#registrar_encomienda_command_tipoEncomiendaVirtual").val();
        if(tipoEncomienda === null || $.trim(tipoEncomienda) === ""){
            $("#tipoEncomiendaEspecialVirtualDIV").hide();
            $("#valorDeclaradoVirtualDIV").hide();
            $("#registrar_encomienda_command_tipoEncomiendaEspecialVirtual").select2("val", "");
            $("#paqueteVirtualDIV").hide();
            $("#registrar_encomienda_command_valorDeclaradoVirtual").val("");
            $("#registrar_encomienda_command_pesoVirtual").val("");
            $("#registrar_encomienda_command_volumenAltoVirtual").val("");
            $("#registrar_encomienda_command_volumenAnchoVirtual").val("");
            $("#registrar_encomienda_command_volumenProfundidadVirtual").val("");
        }else if($.trim(tipoEncomienda) === "1"){ //Efectivo
            $("#tipoEncomiendaEspecialVirtualDIV").hide();
            $("#valorDeclaradoVirtualDIV").hide();
            $("#registrar_encomienda_command_tipoEncomiendaEspecialVirtual").select2("val", "");
            $("#paqueteVirtualDIV").hide();
            $("#registrar_encomienda_command_valorDeclaradoVirtual").val("");
            $("#registrar_encomienda_command_pesoVirtual").val("");
            $("#registrar_encomienda_command_volumenAltoVirtual").val("");
            $("#registrar_encomienda_command_volumenAnchoVirtual").val("");
            $("#registrar_encomienda_command_volumenProfundidadVirtual").val("");
        }else if($.trim(tipoEncomienda) === "2"){ //Especial
            $("#tipoEncomiendaEspecialVirtualDIV").show();
            $("#valorDeclaradoVirtualDIV").show();
            $("#paqueteVirtualDIV").hide();
            $("#registrar_encomienda_command_valorDeclaradoVirtual").val("");
             $("#registrar_encomienda_command_pesoVirtual").val("");
            $("#registrar_encomienda_command_volumenAltoVirtual").val("");
            $("#registrar_encomienda_command_volumenAnchoVirtual").val("");
            $("#registrar_encomienda_command_volumenProfundidadVirtual").val("");
        }else if($.trim(tipoEncomienda) === "3"){ //Paquete
            $("#tipoEncomiendaEspecialVirtualDIV").hide();
            $("#valorDeclaradoVirtualDIV").show();
            $("#registrar_encomienda_command_valorDeclaradoVirtual").val("");
            $("#registrar_encomienda_command_pesoVirtual").val("1");
            $("#registrar_encomienda_command_volumenAltoVirtual").val("1");
            $("#registrar_encomienda_command_volumenAnchoVirtual").val("1");
            $("#registrar_encomienda_command_volumenProfundidadVirtual").val("1");
            $("#paqueteVirtualDIV").show();
        }
    },
    
    clearPopUpEncomiena : function() {
        $("#registrar_encomienda_command_cantidadVirtual").val("1");
        $("#registrar_encomienda_command_pesoVirtual").val("");
        $("#registrar_encomienda_command_volumenAltoVirtual").val("");
        $("#registrar_encomienda_command_volumenAnchoVirtual").val("");
        $("#registrar_encomienda_command_volumenProfundidadVirtual").val("");
        $("#registrar_encomienda_command_descripcionVirtual").val("");
        $("#registrar_encomienda_command_tipoEncomiendaVirtual").select2("val", "");
        $("#registrar_encomienda_command_tipoEncomiendaEspecialVirtual").select2("val", "");
        if(eval($("#aplicarTarifaVolumen").val()) === 1 || eval($("#aplicarTarifaVolumen").val()) === true){
            $(".bloqueVolumen").show();
        }else{
            $(".bloqueVolumen").hide();
        }
    },
    
    clickAdicionarEncomienda : function(e) {
//        console.log("clickAdicionarEncomienda-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        
        registrarEncomienda.clearPopUpEncomiena();
        $("#registrar_encomienda_command_tipoEncomiendaVirtual").select2("val", "3");
        registrarEncomienda.checkTipoEncomiena(); //Se chequea en el onchange del select
        core.showMessageDialog({
            title : "Adicionar Encomienda",
            selector: $("#adicionarEncomiendaDIV"),
            removeOnClose : false,
            uniqid : false,
            buttons: {
                Aceptar: {
                    click: function() {
//                        console.log("clickAceptarPopUpEncomienda-init");
                        var dialogActual = this;
                        var form = $("#adicionarEncomiendaDIV").find("form");
                        if(form.length === 0){
//                            console.log("creando formulario dinamico...");
                            form = $("<form id='adicionarEncomiendaPopUpForm'></form>")
                            form.append($("#adicionarEncomiendaDIV").children());
                            $("#adicionarEncomiendaDIV").append(form);
                        }
                        if(core.customValidateForm(form) === true && registrarEncomienda.validatePopUpEncomienda(dialogActual) === true){
//                            console.log("clickAceptarPopUpEncomienda-ok");
                            
                            var listaEncomiendas = $("#registrar_encomienda_command_listaEncomiendas").val();
                            if(listaEncomiendas){ listaEncomiendas = JSON.parse(listaEncomiendas); }
                            else{ listaEncomiendas = []; }
                            var peso = 0;
                            var volumen = 0;
                            var volumenAlto = $("#registrar_encomienda_command_volumenAltoVirtual").val();
                            var volumenAncho = $("#registrar_encomienda_command_volumenAnchoVirtual").val();
                            var volumenProfundidad = $("#registrar_encomienda_command_volumenProfundidadVirtual").val();
                            var tipoEncomienda =  $.trim($("#registrar_encomienda_command_tipoEncomiendaVirtual").val());
                            if(tipoEncomienda === "3"){
                                volumen = parseFloat(volumenAlto) * parseFloat(volumenAncho) * parseFloat(volumenProfundidad);
                                volumen = volumen.toFixed(0);
                                peso = parseFloat($("#registrar_encomienda_command_pesoVirtual").val());
                            }
                            var nombreTipoEncomiendaEspecial = "";
                            if($("#registrar_encomienda_command_tipoEncomiendaEspecialVirtual").select2("data") !== null){
                                nombreTipoEncomiendaEspecial = $("#registrar_encomienda_command_tipoEncomiendaEspecialVirtual").select2("data").text;
                            }
                            var item = {
                                id : core.uniqId("encomienda_"),
                                cantidad : $("#registrar_encomienda_command_cantidadVirtual").val(),
                                tipoEncomienda : $("#registrar_encomienda_command_tipoEncomiendaVirtual").val(),
                                valorDeclarado : $("#registrar_encomienda_command_valorDeclaradoVirtual").val(),
                                tipoEncomiendaEspecial : $("#registrar_encomienda_command_tipoEncomiendaEspecialVirtual").val(),
                                nombreTipoEncomiendaEspecial : nombreTipoEncomiendaEspecial,
                                volumenAlto : volumenAlto,
                                volumenAncho : volumenAncho,
                                volumenProfundidad : volumenProfundidad,
                                volumen : volumen,
                                peso : peso,
                                descripcion : $("#registrar_encomienda_command_descripcionVirtual").val()
                            };
                            listaEncomiendas.push(item);
                            registrarEncomienda.renderItemGridAddEncomienda(item);
                            $("#registrar_encomienda_command_listaEncomiendas").val(JSON.stringify(listaEncomiendas));
//                            console.debug(listaEncomiendas);
                            this.dialog2("close");
                        }
                    }, 
                    primary: true,
                    type: "info"
                }, 
                Cancelar: function() {
//                    console.log("clickCancelarPopUpEncomienda-init");
                    this.dialog2("close");					
                }				    
            }
        });
    },
    
    renderItemGridAddEncomienda: function(item) {
//        console.log("renderItemGridAddEncomienda-init");
        $("#clienteEncomiendaBody").find("#clienteEncomiendaVacioTR").hide(); //Oculto el TR vacio
        var cantidad = item.cantidad;
        var tipoEncomienda = "";
        var clase = "";
        if(item.tipoEncomienda === "1"){
            tipoEncomienda = "Efectivo";
            clase = "trEncomiendaEfectivo";
            cantidad = "GTQ " + cantidad;
        }else if(item.tipoEncomienda === "2"){
            clase = "trEncomiendaEspecial";
            tipoEncomienda = "Especial";
        }else if(item.tipoEncomienda === "3"){
            clase = "trEncomiendaPaquete";
            tipoEncomienda = "Paquete";
        }
        var descripcion = item.descripcion;
        if(item.tipoEncomiendaEspecial !== ""){
            descripcion = "Producto: " + item.nombreTipoEncomiendaEspecial + ". " + descripcion;
        }
        if(item.volumen !== "" && eval(item.volumen) !== 0){
            descripcion = "Dimesiones: " + item.volumenAlto + "x" + item.volumenAncho + "x" + item.volumenProfundidad + ". " + descripcion;
        }
        if(item.peso !== "" && eval(item.volumen) !== 0){
            descripcion = "Peso: " + item.peso + ". " + descripcion;
        }
        var valorDeclarado = item.valorDeclarado;
        if(valorDeclarado === null || $.trim(valorDeclarado) === ""){
            valorDeclarado = " - ";
        }else{
            valorDeclarado = "GTQ " + valorDeclarado;
        }
        var eliminarEncomienda = $('<a class="btn btn-small" href="#"><i class="icon-minus"></i></a>');
        var itemTR = $("<tr class='"+clase+"' id='"+item.id+"'><td>"+cantidad+"</td><td>"+valorDeclarado+"</td><td>"+tipoEncomienda+"</td><td>"+descripcion+"</td><td class='action'></td></tr>");
        itemTR.find(".action").append(eliminarEncomienda);
        $("#clienteEncomiendaBody").append(itemTR);
        $(eliminarEncomienda).click(registrarEncomienda.clickEliminarEncomienda); 
    },
    
    clickEliminarEncomienda : function(e) {
//        console.log("clickEliminarEncomienda-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var tr = $(this).parent().parent();
//        console.debug(tr);
        var id = tr.attr("id");
        if(id !== null && $.trim(id) !== ""){
            var listaEncomiendas = $("#registrar_encomienda_command_listaEncomiendas").val();
            if(listaEncomiendas){ listaEncomiendas = JSON.parse(listaEncomiendas); }
            else{ listaEncomiendas = []; }
            var item = registrarEncomienda.findEncomienda(listaEncomiendas, id);
            listaEncomiendas = core.removeItemArray(listaEncomiendas, item);
            if(listaEncomiendas.length === 0){
                $("#clienteEncomiendaBody").find("tr").not("#clienteEncomiendaVacioTR").remove(); //Elimino todos los tr
                $("#clienteEncomiendaBody").find("#clienteEncomiendaVacioTR").show(); //Muestro el vacio
            }else{
                $("#clienteEncomiendaBody").find("#clienteEncomiendaVacioTR").hide(); //Oculto el vacio
                $("#clienteEncomiendaBody").find("#"+id).remove();
            }
            $("#registrar_encomienda_command_listaEncomiendas").val(JSON.stringify(listaEncomiendas));
//            console.debug(listaEncomiendas);
        }
                           
    },
    
    findEncomienda : function(listaEncomiendas, id) {
        var result = null;
        id = $.trim(id);
        $.each(listaEncomiendas, function() {  
           if($.trim(this.id) === id){
               result = this;
               return;
           }
        });
        return result;
    }, 
    
    validatePopUpEncomienda: function(dialog) {
//        console.log("validatePopUpEncomienda-init");
        var cantidad = $("#registrar_encomienda_command_cantidadVirtual").val();
        if(cantidad === null || $.trim(cantidad) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe especificar una cantidad del producto.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        cantidad = parseInt(cantidad);
        if(cantidad <= 0){
            core.hiddenDialog2(dialog);
            alert("La cantidad debe ser un número positivo.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        var tipoEncomienda =  $("#registrar_encomienda_command_tipoEncomiendaVirtual").val();
        if(tipoEncomienda === null || $.trim(tipoEncomienda) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe seleccionar una tipo de encomienda.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        else{
            if($.trim(tipoEncomienda) === "2"){ //Especial
                var tipoEncomiendaEspecial =  $("#registrar_encomienda_command_tipoEncomiendaEspecialVirtual").val();
                if(tipoEncomiendaEspecial === null || $.trim(tipoEncomiendaEspecial) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe seleccionar un nombre de encomienda.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
            }
            else if($.trim(tipoEncomienda) === "3"){ //Paquete
                var alto =  $("#registrar_encomienda_command_volumenAltoVirtual").val();
                if(alto === null || $.trim(alto) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar el alto de la encomienda.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
                var ancho =  $("#registrar_encomienda_command_volumenAnchoVirtual").val();
                if(ancho === null || $.trim(ancho) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar el ancho de la encomienda.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
                var profundidad =  $("#registrar_encomienda_command_volumenProfundidadVirtual").val();
                if(profundidad === null || $.trim(profundidad) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar la profundidad de la encomienda.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
                var peso =  $("#registrar_encomienda_command_pesoVirtual").val();
                if(peso === null || $.trim(peso) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar el peso de la encomienda.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
            }
        }
        
//        var descripcion =  $("#registrar_encomienda_command_descripcionVirtual").val();
//        if(descripcion === null || $.trim(descripcion) === ""){
//            core.hiddenDialog2(dialog);
//            alert("Debe especificar la descripción de la encomienda.", function(){
//                core.showDialog2(dialog);
//            });
//            return false;
//        }
        
        return true;
    },
    
    imprimirEncomiendaDatos: function(responseText, successCallback) {
        var info = core.getValueFromResponse(responseText, "info");
        core.showNotification({text : info});
        var autoPrint = core.getValueFromResponse(responseText, 'autoPrint');
        if(eval(autoPrint) === true){
            var data = core.getValueFromResponse(responseText, 'data');
            frondend.printDataEncomienda($("#pathPrintEncomiendaDatos").attr("value"), { ids : data }, function (){
                alert("Operación realizada satisfactoriamente. " + info, function() {
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback(responseText);
                    }
                }); 
            });
        }else{
            alert("Operación realizada satisfactoriamente. " + info, function() {
                var data = core.getValueFromResponse(responseText, 'data');
                frondend.printDataEncomienda($("#pathPrintEncomiendaDatos").attr("value"), { ids : data }, successCallback);
            }); 
        }
    },
    
    imprimirEncomiendaFactura: function(responseText, successCallback) {
        var info = core.getValueFromResponse(responseText, "info");
        core.showNotification({text : info});
        var autoPrint = core.getValueFromResponse(responseText, 'autoPrint');
        if(eval(autoPrint) === true){
            frondend.printFacturaInternal(responseText, function (){
                var data = core.getValueFromResponse(responseText, 'data');
                frondend.printDataEncomienda($("#pathPrintEncomiendaDatos").attr("value"), { ids : data }, function(){
                    alert("Operación realizada satisfactoriamente. " + info, function() {
                        if(successCallback && $.isFunction(successCallback)){
                            successCallback(responseText);
                        }
                    });
                }); 
            });                                        
        }else{
            alert("Operación realizada satisfactoriamente. " + info, function() {
                frondend.printFacturaInternal(responseText, function (){
                    var data = core.getValueFromResponse(responseText, 'data');
                    frondend.printDataEncomienda($("#pathPrintEncomiendaDatos").attr("value"), { ids : data }, successCallback);
                });                                         
            }); 
        }
    },
    
    customCommunValidate: function(dialog) {
        
        var cantidadRutas = $("#rutaBody").find("tr").not("#rutaVacioTR").length;
        if(cantidadRutas <= 0){
            core.hiddenDialog2(dialog);
            alert("Tiene que definir al menos una ruta.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        var cantidadEncomienda = $("#clienteEncomiendaBody").find("tr").not("#clienteEncomiendaVacioTR").length;
        if(cantidadEncomienda <= 0){
            core.hiddenDialog2(dialog);
            alert("Debe definir al menos una encomienda.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    clickAutorizacionInterna: function(e) {
//        console.debug("clickAutorizacionInterna-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var encomiendaForm = $("#encomiendaForm");
        if(core.customValidateForm(encomiendaForm) === true && registrarEncomienda.customPreValidateInterna() === true){
            core.showMessageDialog({
                 title : "Autorización Interna",
                 selector: $("#internaDIV"),
                 removeOnClose : false,
                 uniqid : false,
                 buttons: {
                    Aceptar: {
                        click: function() {
                            var dialogActual = this;
//                            console.debug("Aceptar-click...");
                            if(core.customValidateForm(encomiendaForm) === true && registrarEncomienda.customPostValidateInterna(this) === true){
                                $("#registrar_encomienda_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                $("#registrar_encomienda_command_autorizacionInterna").prop("value", $("#pinAutorizacionInterna").prop("value"));
                                $("#registrar_encomienda_command_tipoDocuemento").prop("value", "4"); //Cortesia
                                $(encomiendaForm).ajaxSubmit({
                                    target: encomiendaForm.attr('action'),
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
                                            dialogActual.dialog2("close");
                                            registrarEncomienda.imprimirEncomiendaDatos(responseText, function (){
                                                core.getPageForMenu(encomiendaForm.attr('action'));
                                            });
                                        }
                                   }
                                });
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
             registrarEncomienda.showTotalEncomiendaEfectivo();
        }
    },
    
    customPreValidateInterna: function(dialog) {
        
        if(registrarEncomienda.customCommunValidate(dialog) === false){
            return false;
        }

        var cantidadEncomienda = $("#clienteEncomiendaBody").find("tr").not("#clienteEncomiendaVacioTR").length;
        if(cantidadEncomienda > 1){
            core.hiddenDialog2(dialog);
            alert("Para las autorizaciones internas no puede definir más de una encomienda.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    customPostValidateInterna: function(dialog) {
        
        var pinAutorizacionInterna = $("#pinAutorizacionInterna").prop("value");
        if(pinAutorizacionInterna === undefined || pinAutorizacionInterna === null || $.trim(pinAutorizacionInterna) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe especificar un PIN de autorización.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        return true;
    },
    
    clickAutorizacionCortesia: function(e) {
//        console.debug("clickAutorizacionCortesia-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var encomiendaForm = $("#encomiendaForm");
        if(core.customValidateForm(encomiendaForm) === true && registrarEncomienda.customPreValidateCortesia() === true){
            core.showMessageDialog({
                 title : "Cortesía",
                 selector: $("#cortesiaDIV"),
                 removeOnClose : false,
                 uniqid : false,
                 buttons: {
                    Aceptar: {
                        click: function() {
                            var dialogActual = this;
//                            console.debug("Aceptar-click...");
                            if(core.customValidateForm(encomiendaForm) === true && registrarEncomienda.customPostValidateCortesia(this) === true){
                                $("#registrar_encomienda_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                $("#registrar_encomienda_command_autorizacionCortesia").prop("value", $("#pinAutorizacionCortesia").prop("value"));
                                $("#registrar_encomienda_command_tipoDocuemento").prop("value", "3"); //Cortesia
                                $(encomiendaForm).ajaxSubmit({
                                    target: encomiendaForm.attr('action'),
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
                                            dialogActual.dialog2("close");
                                            registrarEncomienda.imprimirEncomiendaDatos(responseText, function (){
                                                core.getPageForMenu(encomiendaForm.attr('action'));
                                            });
                                        }
                                   }
                                });
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
             registrarEncomienda.showTotalEncomiendaEfectivo();
        }
    },

    customPreValidateCortesia: function(dialog) {
        
        if(registrarEncomienda.customCommunValidate(dialog) === false){
            return false;
        }
        
        var cantidadEncomienda = $("#clienteEncomiendaBody").find("tr").not("#clienteEncomiendaVacioTR").length;
        if(cantidadEncomienda > 1){
            core.hiddenDialog2(dialog);
            alert("Para las cortesías no puede definir más de una encomienda.", function(){
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

    clickPorCobrar: function(e) {
//        console.debug("clickPorCobrar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var encomiendaForm = $("#encomiendaForm");
        if(core.customValidateForm(encomiendaForm) === true && registrarEncomienda.customPreValidatePorCobrar() === true){
            registrarEncomienda.calcularImporteTotalPorCobrarMonedaBase(function (success){
                var importeTotal = success.total;
                var descuento = success.descuento;
                confirm("El importe total por cobrar es de GTQ " + importeTotal + ". Se aplicó un descuento de GTQ "+descuento+". Desea continuar?", function(confirmed){
                    if(confirmed === true){
                        $("#registrar_encomienda_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                        $("#registrar_encomienda_command_tipoDocuemento").prop("value", "2"); //Por Cobrar
                        $(encomiendaForm).ajaxSubmit({
                            target: encomiendaForm.attr('action'),
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
                                    registrarEncomienda.imprimirEncomiendaDatos(responseText, function (){
                                        core.getPageForMenu(encomiendaForm.attr('action'));
                                    });
                                }
                            }
                        });   
                    }
                });
            });     
        }
    },
    
    calcularImporteTotalPorCobrarMonedaBase: function(successCallback) {
        var idEstacionOrigen = $("#registrar_encomienda_command_estacionOrigen").val();
        var idEstacionDestino = $("#registrar_encomienda_command_estacionDestino").val();
//        var idRuta = $("#registrar_encomienda_command_ruta").val();
        var idTipoPago = $("#registrar_encomienda_command_tipoPagoVirtual").val();
        var listaEncomiendas = $("#registrar_encomienda_command_listaEncomiendas").val();
        var idClienteRemitente = $("#registrar_encomienda_command_clienteRemitente").val();

        $("#registrar_encomienda_command_totalNetoVirtual").val("");
        if(idClienteRemitente !== null && $.trim(idClienteRemitente) !== "" && 
           idEstacionOrigen !== null && $.trim(idEstacionOrigen) !== "" &&
           idEstacionDestino !== null && $.trim(idEstacionDestino) !== "" &&
           listaEncomiendas !== null && $.trim(listaEncomiendas) !== ""){
//           console.log("buscando importe...");
           core.request({
                url : $("#pathCalcularImporteTotalMonedaBase").attr('value'),
                method: 'POST', //Obligatorio
                extraParams: {
                    listaEncomiendas: listaEncomiendas,
                    idClienteRemitente : idClienteRemitente,
                    idEstacionOrigen: idEstacionOrigen,
                    idEstacionDestino: idEstacionDestino
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
                        if(successCallback && $.isFunction(successCallback)){
                            successCallback(success);
                        }
                    }
                }
           });
       }
    },
    
    customPreValidatePorCobrar: function(dialog) {
        
        if(registrarEncomienda.customCommunValidate(dialog) === false){
            return false;
        }        

        return true;
    },
    
    clickFacturar: function(e) {
//        console.debug("clickFacturar-init");
//        console.debug($(this));
        e.preventDefault();
        e.stopPropagation();
        var encomiendaForm = $("#encomiendaForm");
        if(core.customValidateForm(encomiendaForm) === true && registrarEncomienda.customPreValidateFactura() === true){
            registrarEncomienda.loadSeriesFacturaActiva(function(){
                core.showMessageDialog({
                    title : "Facturar",
                    selector: $("#facturaDIV"),
                    removeOnClose : false,
                    uniqid : false,
                    buttons: {
                       Aceptar: {
                           click: function() {
                               var dialogActual = this;
   //                            console.debug("Aceptar-click...");
                               if(core.customValidateForm(encomiendaForm) === true && registrarEncomienda.customPostValidateFactura(this) === true){
                                   $("#registrar_encomienda_command_impresorasDisponibles").prop("value", frondend.listaImpresorasDisponibles);
                                   $("#registrar_encomienda_command_referenciaExterna").prop("value", $("#registrar_encomienda_command_referenciaExternaVirtual").prop("value"));
                                   $("#registrar_encomienda_command_tipoPago").prop("value", $("#registrar_encomienda_command_tipoPagoVirtual").prop("value"));
                                   $("#registrar_encomienda_command_totalNeto").prop("value", $("#registrar_encomienda_command_totalNetoVirtual").prop("value"));
                                   $("#registrar_encomienda_command_monedaPago").prop("value", $("#registrar_encomienda_command_monedaPagoVirtual").prop("value"));
                                   $("#registrar_encomienda_command_tasa").prop("value", $("#registrar_encomienda_command_tasaVirtual").prop("value"));
                                   $("#registrar_encomienda_command_totalPago").prop("value", $("#registrar_encomienda_command_totalPagoVirtual").prop("value"));
                                   $("#registrar_encomienda_command_efectivo").prop("value", $("#registrar_encomienda_command_efectivoVirtual").prop("value"));
                                   $("#registrar_encomienda_command_vuelto").prop("value", $("#registrar_encomienda_command_vueltoVirtual").prop("value"));
                                   $("#registrar_encomienda_command_serieFactura").prop("value", $("#registrar_encomienda_command_serieFacturaVirtual").prop("value"));
                                   $("#registrar_encomienda_command_tipoDocuemento").prop("value", "1"); //Factura
                                   $(encomiendaForm).ajaxSubmit({
                                       target: encomiendaForm.attr('action'),
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
                                               dialogActual.dialog2("close");
                                               registrarEncomienda.imprimirEncomiendaFactura(responseText, function (){
                                                    core.getPageForMenu(encomiendaForm.attr('action'));
                                               });
                                           }
                                      }
                                   });
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
                registrarEncomienda.showTotalEncomiendaEfectivo();
                registrarEncomienda.checkBloque2();   //Se chequea los div en cascada
                registrarEncomienda.changeTipoPago(); //Se chequea los valores en cascada
            });
        }
    },
    
    loadSeriesFacturaActiva : function(successCallback){
        var estacionOrigen = $("#registrar_encomienda_command_estacionOrigen").select2('val');
        var idEmpresa = null;
        var listaEncomiendaRutas = $("#registrar_encomienda_command_listaEncomiendaRutas").val();
        if(listaEncomiendaRutas){ listaEncomiendaRutas = JSON.parse(listaEncomiendaRutas); }
        else{ listaEncomiendaRutas = []; }
        if(listaEncomiendaRutas.length > 0){
            idEmpresa = listaEncomiendaRutas[0].idEmpresa;
        }
        $("#registrar_encomienda_command_serieFacturaVirtual").select2({ data: [] });
        if(estacionOrigen !== null && $.trim(estacionOrigen) !== "" && 
                idEmpresa !== null && $.trim(idEmpresa) !== "" ){
            core.request({
                url : $("#pathSeriesActivaPorEstacion").attr('value'),
                method: 'POST',
                extraParams: {
                    idEstacion : estacionOrigen,
                    idEmpresa : idEmpresa,
                    tipoServicio : '2' //Encomienda
                }, 
                dataType: "json",
                async:true,
                successCallback: function(success){
                    var optionSeriesFacturas = success.optionSeriesFacturas;
                    if( optionSeriesFacturas ){
                        $("#registrar_encomienda_command_serieFacturaVirtual").select2({
                            allowClear: false,
                            data: { results: optionSeriesFacturas }
                        });
                        
                        var cantidad = optionSeriesFacturas.length;
                        $("#registrar_encomienda_command_serieFacturaVirtual").data("sizeItems", cantidad);
                        if(cantidad > 0){
                            if(optionSeriesFacturas[0]){
                                $("#registrar_encomienda_command_serieFacturaVirtual").select2('val', optionSeriesFacturas[0].id);
                                if(successCallback && $.isFunction(successCallback)){
                                    successCallback();
                                }
                            }
                        }else{
                            var modal = $('.modal[style^="display: block;"]'); //Modal Activo
                            modal.hide();
                            alert("La estación no tiene series de facturas activas.", function (){
                                modal.show();  //Quito el popup ya que es innecesario, al final hay que cancelar         	
                            });
                        }
                    }
                }
           });
        }else{
            alert("No se ha podido determinar la empresa que factura en su estación.")
        }
    },
    
    customPreValidateFactura: function(dialog) {
        
        if(registrarEncomienda.customCommunValidate(dialog) === false){
            return false;
        }
        
        var cantidadMaxima = 4;
        var listaEncomiendas = $("#registrar_encomienda_command_listaEncomiendas").val();
        if(listaEncomiendas){ listaEncomiendas = JSON.parse(listaEncomiendas); }
        else{ listaEncomiendas = []; }
        if(listaEncomiendas.length > cantidadMaxima){
            alert("No se puede registrar más de " + cantidadMaxima + " encomiendas a la vez cuando se factura.");
            return;
        }  
        
        var moneda = $("#registrar_encomienda_command_monedaPagoVirtual").select2("val");
        if( $.trim(moneda) === "" ){
            core.hiddenDialog2(dialog);
            alert("Debe abrir una caja para facturar.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    customPostValidateFactura: function(dialog) {
        
        if(frondend.listaImpresorasDisponibles === null || $.trim(frondend.listaImpresorasDisponibles) === ""){
            core.hiddenDialog2(dialog);
            alert("El sistema aún no ha podido detectar las impresoras para la facturación.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        var tipoPago = $("#registrar_encomienda_command_tipoPagoVirtual").prop("value");
        if(tipoPago === undefined || tipoPago === null || $.trim(tipoPago) === ""){
            core.hiddenDialog2(dialog);
            alert("Debe especificar un tipo de pago.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }else{
            
            if(tipoPago === "1"){  //Efectivo
                
               var monedaPago = $("#registrar_encomienda_command_monedaPagoVirtual").prop("value");
                if(monedaPago === undefined || monedaPago === null || $.trim(monedaPago) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar una moneda de pago.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                }
                var efectivo = $("#registrar_encomienda_command_efectivoVirtual").prop("value");
                if(efectivo === undefined || efectivo === null || $.trim(efectivo) === ""){
                    core.hiddenDialog2(dialog);
                    alert("Debe especificar un efectivo.", function(){
                        core.showDialog2(dialog);
                    });
                    return false;
                } 
            }
        }
        
        var totalNeto = $("#registrar_encomienda_command_totalNetoVirtual").prop("value");
        if(totalNeto === undefined || totalNeto === null || $.trim(totalNeto) === ""){
            core.hiddenDialog2(dialog);
            alert("No se ha podido determinar el total neto.", function(){
                core.showDialog2(dialog);
            });
            return false;
        }
        
        return true;
    },
    
    showTotalEncomiendaEfectivo: function() {
        var listaEncomiendas = $("#registrar_encomienda_command_listaEncomiendas").val();
        if(listaEncomiendas){ listaEncomiendas = JSON.parse(listaEncomiendas); }
        else{ listaEncomiendas = []; }
        var totalEfectivo = 0;
        $.each(listaEncomiendas, function() {  
            if(this.tipoEncomienda === "1"){ //efectivo
                totalEfectivo += parseFloat(this.cantidad);
            }
        });
        if(totalEfectivo !== 0){
            $(".totalEncomiendasEfectivo").text("La suma total de las encomiendas de efectivo es GTQ " + totalEfectivo + ". Este valor no está sumado al total neto.");
        }else{
            $(".totalEncomiendasEfectivo").text("");
        }
    },
    
    checkBloque2: function(tipoPago) {
//        console.log("checkBloque2-init");
        if(!tipoPago){
            tipoPago = $("#registrar_encomienda_command_tipoPagoVirtual").val();
        }
        if(tipoPago === ""){  //Vacio
            $("#registrar_encomienda_command_totalNetoVirtual").val("");
            $(".bloque2").hide();
            registrarEncomienda.checkBloque3(tipoPago);
        }
        else if(tipoPago === "1"){ //Efectivo
            $(".bloque2").show();
            registrarEncomienda.checkBloque3(tipoPago);
        }
        else if(tipoPago === "2"){
            $(".bloque2").hide();
            registrarEncomienda.checkBloque3(tipoPago);
        }
        else if(tipoPago === "3"){
            $(".bloque2").hide();
            registrarEncomienda.checkBloque3(tipoPago);
        }
    },
    
    checkBloque3: function(tipoPago) {
//        console.log("checkBloque3-init");
        if(!tipoPago){
            tipoPago = $("#registrar_encomienda_command_tipoPagoVirtual").val();
        }
        var monedaPago = $("#registrar_encomienda_command_monedaPagoVirtual").val();
        if(tipoPago === "1" && monedaPago !== "" && monedaPago !== "1"){  //Efectivo
            $(".bloque3").show();
        }else{ //Vacio y Tarjetas CR y DB
            $(".bloque3").hide();
        }
        registrarEncomienda.checkBloque4(tipoPago);
    },
    
    checkBloque4: function(tipoPago) {
//        console.log("checkBloque4-init");
        if(!tipoPago){
            tipoPago = $("#registrar_encomienda_command_tipoPagoVirtual").val();
        }
        if(tipoPago === "1"){  //Efectivo
            $(".bloque4").show();
        }else{ //Vacio y Tarjetas CR y DB
            $(".bloque4").hide();
        }
    },
   
    calcularImporteTotalMonedaBase: function() {
        var idEstacionOrigen = $("#registrar_encomienda_command_estacionOrigen").val();
        var idEstacionDestino = $("#registrar_encomienda_command_estacionDestino").val();
//        var idRuta = $("#registrar_encomienda_command_ruta").val();
        var idTipoPago = $("#registrar_encomienda_command_tipoPagoVirtual").val();
        var listaEncomiendas = $("#registrar_encomienda_command_listaEncomiendas").val();
        var idClienteRemitente = $("#registrar_encomienda_command_clienteRemitente").val();

        $(".descuentoSpan").text("");
        $("#registrar_encomienda_command_totalNetoVirtual").val("");
        if(idClienteRemitente !== null && $.trim(idClienteRemitente) !== "" && 
           idEstacionOrigen !== null && $.trim(idEstacionOrigen) !== "" &&
           idEstacionDestino !== null && $.trim(idEstacionDestino) !== "" &&
           listaEncomiendas !== null && $.trim(listaEncomiendas) !== ""){
           
           core.request({
                url : $("#pathCalcularImporteTotalMonedaBase").attr('value'),
                method: 'POST', //Obligatorio
                extraParams: {
                    listaEncomiendas: listaEncomiendas,
                    idClienteRemitente : idClienteRemitente,
                    idEstacionOrigen: idEstacionOrigen,
                    idEstacionDestino: idEstacionDestino
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
                        $("#registrar_encomienda_command_totalNetoVirtual").val(success.total);
                        $(".descuentoSpan").text("Descuento: GTQ " + success.descuento + ".");
                        if(idTipoPago === "1"){  //Solo si es efectivo, en otro caso no hace falta.. ya estan limpios los inputs...
                            registrarEncomienda.changeMonedaPago();
                        }
                    }
                }
           });
       }
    },
    
    changeTipoPago: function() {
//        console.log("changeTipoPago-init");
        registrarEncomienda.checkBloque2();
        $("#registrar_encomienda_command_totalNetoVirtual").val("");
        $("#registrar_encomienda_command_tasaVirtual").val("");
        $("#registrar_encomienda_command_totalPagoVirtual").val("");
        $("#registrar_encomienda_command_vueltoVirtual").val("");
        $("#registrar_encomienda_command_efectivoVirtual").val("");
        registrarEncomienda.calcularImporteTotalMonedaBase();
    },
    
    changeMonedaPago: function() {
//      console.log("changeMonedaPago-init");  
      var monedaPago = $("#registrar_encomienda_command_monedaPagoVirtual").val();
      $("#registrar_encomienda_command_tasaVirtual").val("");
      $("#registrar_encomienda_command_totalPagoVirtual").val("");
      $("#registrar_encomienda_command_vueltoVirtual").val("");
      if(monedaPago === null || $.trim(monedaPago) === ""){
        $("#prependTotalPagoVirtual").text("???");
        $("#prependEfectivoVirtual").text("???");
      }
      else if($.trim(monedaPago) === "1"){
        $("#prependTotalPagoVirtual").text("GTQ");
        $("#prependEfectivoVirtual").text("GTQ");
        registrarEncomienda.changeEfectivo();
      }
      else{
        //NO SON QUETSALES
        var totalNeto = $("#registrar_encomienda_command_totalNetoVirtual").val();
        var idMonedaPago = $("#registrar_encomienda_command_monedaPagoVirtual").val();
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
                        $("#registrar_encomienda_command_tasaVirtual").val(success.tasa);
                        $("#registrar_encomienda_command_totalPagoVirtual").val(success.total);
                        registrarEncomienda.changeEfectivo();
                    }
                }
           });
        }
      }
      registrarEncomienda.checkBloque3();
    },
    
    
    changeEfectivo: function() {
//      console.log("changeEfectivo-init");
      var monedaPago = $("#registrar_encomienda_command_monedaPagoVirtual").val();
      $("#registrar_encomienda_command_vueltoVirtual").val("");
      
      var efectivo = $("#registrar_encomienda_command_efectivoVirtual").val();
      if(!$.isNumeric(efectivo)){
          $("#registrar_encomienda_command_efectivoVirtual").val("");
          return;
      }else{
          efectivo = parseFloat(efectivo).toFixed(2);
          $("#registrar_encomienda_command_efectivoVirtual").val(efectivo);
      }
      
      if($.trim(monedaPago) === "1"){ //GTQ
         var totalNeto = $("#registrar_encomienda_command_totalNetoVirtual").val();
         var vuelto = efectivo - parseFloat(totalNeto);
         vuelto = parseFloat(vuelto).toFixed(2);
         if(vuelto === 0){
              $("#registrar_encomienda_command_vueltoVirtual").val(0);
         }else if(vuelto < 0){
             var modal = $('.modal[style^="display: block;"]'); //Modal Activo
             modal.hide();
             alert("El efectivo no alcanza, como mínimo son:" + totalNeto + ".", function (){
                modal.show();
                $("#registrar_encomienda_command_efectivoVirtual").val(totalNeto); // Se le setea el importe minimo.
                $("#registrar_encomienda_command_vueltoVirtual").val(0);
             });
         }else{
             $("#registrar_encomienda_command_vueltoVirtual").val(vuelto);
         }
      }
      else{
        //NO SON QUETSALES
        var totalPago = $("#registrar_encomienda_command_totalPagoVirtual").val();
        var vuelto = efectivo - parseFloat(totalPago);
         vuelto = parseFloat(vuelto).toFixed(2); //Vuelto en EUR O USD
         if(vuelto === 0){
              $("#registrar_encomienda_command_vueltoVirtual").val(0);
         }else if(vuelto < 0){
             var modal = $('.modal[style^="display: block;"]'); //Modal Activo
             modal.hide();
             alert("El efectivo no alcanza, como mínimo son:" + totalPago + ".", function (){
                modal.show();
                $("#registrar_encomienda_command_efectivoVirtual").val(totalPago); // Se le setea el importe minimo.
                $("#registrar_encomienda_command_vueltoVirtual").val(0);
             });
         }else{

            //Convirtiendo el vuelto a Quetsales
            var idMonedaPago = $("#registrar_encomienda_command_monedaPagoVirtual").val();
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
                           $("#registrar_encomienda_command_efectivoVirtual").val(totalPago); // Se le setea el importe minimo.
                           $("#registrar_encomienda_command_vueltoVirtual").val(0);
                        });
                    }else{
                        $("#registrar_encomienda_command_vueltoVirtual").val(success.total);
                    }
                }
           });
        }
             
             
             
             
         }
        
      }
        
    },
    
    changeBoleto: function() {
        var boleto = $("#registrar_encomienda_command_boleto").val();
        if(boleto === null || $.trim(boleto) === ""){
           $("#porCobrar").show();
        }else{
           $("#porCobrar").hide();
           core.request({
                url : $("#pathGetInformacionBoleto").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { idBoleto: boleto },
                successCallback: function(data){
                   console.debug(data);
                   var optionCliente = data.optionCliente;
                   $("#registrar_encomienda_command_clienteRemitente").select2({
                            allowClear: true,
                            data: { results: optionCliente }
                   });
                   $("#registrar_encomienda_command_clienteRemitente").select2('val', optionCliente[0].id);
                   $("#registrar_encomienda_command_clienteDestinatario").select2({
                            allowClear: true,
                            data: { results: optionCliente }
                   });
                   $("#registrar_encomienda_command_clienteDestinatario").select2('val', optionCliente[0].id);
                   
                   var rutaSelected = data.optionRuta[0];
                   var estacionFinal = data.optionDestino[0];
                   var item = {
                        id : core.uniqId("ruta_"),
                        posicion : 1,
                        rutaVirtual : rutaSelected.id,
                        rutaNameVirtual : rutaSelected.text,
                        idEmpresa : rutaSelected.idEmpresa,
                        estacionFinalVirtual : estacionFinal.id,
                        estacionFinalNameVirtual : estacionFinal.text
                   };
                   var listaEncomiendaRutas = [];
                   listaEncomiendaRutas.push(item);
                   $("#rutaBody").find("tr").not("#rutaVacioTR").remove(); //Elimino todos los tr
                   registrarEncomienda.renderItemGridAddRuta(item);
                   $("#registrar_encomienda_command_estacionDestino").val(estacionFinal.id); //Siempre tengo en el hidden la ultima estacion
                   $("#registrar_encomienda_command_listaEncomiendaRutas").val(JSON.stringify(listaEncomiendaRutas));
                }
           });
        }
    }
    
};
