consultarEsquema = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $('#fecha').datepicker({
            format: "dd/mm/yyyy",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        $('#fecha').datepicker("setDate", new Date());
        
        consultarEsquema.changeFecha();
     },
     
    _conectEvents : function() {
        
        $('#fecha').datepicker().on('changeDate', function (e) {
            e.preventDefault();
            e.stopPropagation();
            consultarEsquema.changeFecha();
        });
        $("#salida").on("change", consultarEsquema.changeSalida);
        $("#cancelar").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            core.getPageForMenu($("#cancelar").attr('href'));
        });
        
        $("#manifiestoInterno").data("index", "salida");
        $("#manifiestoInterno").data("autoopenfile", "PDF");
        $("#manifiestoInterno").click(frondend.loadSubPage);
        
        $("#manifiestoPiloto").data("index", "salida");
        $("#manifiestoPiloto").data("autoopenfile", "PDF");
        $("#manifiestoPiloto").click(frondend.loadSubPage);
        
        $("#manifiestoEncominda").data("index", "salida");
        $("#manifiestoEncominda").data("autoopenfile", "PDF");
        $("#manifiestoEncominda").click(frondend.loadSubPage);
    },
    
    changeFecha : function() {
        console.log("changeFecha-int");
        $("#salida").html("");
        $("#salida").val("");
        consultarEsquema.checkShowEsquema();
        var fecha = $("#fecha").val();
        if(fecha === null || $.trim(fecha) === ""){
            return;
        }else{
            core.request({
                url : $("#pathListarSalidasByFecha").val(),
                type: "POST",
                dataType: "html",
                async: false,
                extraParams : {
                    'fecha':fecha
                },
                successCallback: function(data){
                    if(data){
                        $("#salida").html(data);
                        $("#salida").val('');
                    }
                }
            });
        }
    },
    
    changeSalida : function() {
        console.log("changeSalida-int");
        var salida = $("#salida").val();
        if(salida === null || $.trim(salida) === ""){
            $("#utilizadoSpan").text(0);
            $("#totalSpan").text(0);
            consultarEsquema.checkShowEsquema();
            return;
        }else{
            core.request({
                url : $("#pathGetInformacionPorSalida").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { idSalida: salida },
                successCallback: function(data){
                    consultarEsquema.checkShowEsquema();
                    if( data.optionListaAsientos ){
                        $("#listaAsientosHidden").val(JSON.stringify(data.optionListaAsientos));
                    }
                    
                    if( data.optionListaSenales ){
                        $("#listaSenalesHidden").val(JSON.stringify(data.optionListaSenales));
                    }
                    
                    if( data.optionBoletos ){
                        $("#listaBoletosHidden").val(JSON.stringify(data.optionBoletos));
                    }
                    
                    if( data.optionReservaciones ){
                        $("#listaReservacionesHidden").val(JSON.stringify(data.optionReservaciones));
                    }
                    
                    consultarEsquema.mostrarIconos();
                }
             });
            
        }
    },
    
    checkShowEsquema : function() {
        var salida = $("#salida").val();
        if(salida === null || salida === ""){
            $("#buttonDIV").hide();
            $("#esquemaDIV").hide();
        }else{
            $("#buttonDIV").show();
            $("#esquemaDIV").show();
        }
    },
    
    mostrarIconos : function() {
        
        $(".item").remove(); //Elimino todos los item para generarlos nuevamente con los for.
        
        var listaBoletos = $("#listaBoletosHidden").val();
        if(listaBoletos){ listaBoletos = JSON.parse(listaBoletos); }
        else{ listaBoletos = []; }
        var listaReservaciones = $("#listaReservacionesHidden").val();
        if(listaReservaciones){ listaReservaciones = JSON.parse(listaReservaciones); }
        else{ listaReservaciones = []; }
        var all = listaBoletos.concat(listaReservaciones);
        
        $("#nav2").addClass("hidden");
        var asientosPosicionados = [];
        var listaAsientos = $("#listaAsientosHidden").val();        
        if(listaAsientos){ listaAsientos = JSON.parse(listaAsientos); }
        else{ listaAsientos = []; }
        $("#totalSpan").text(listaAsientos.length);
        jQuery.each(listaAsientos, function() {                                
            var clase = this.clase; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB  
            if(clase === "1") { clase = "claseA"; }
            else if(clase === "2") { clase = "claseB"; }
            else { throw new Error("El item debe tener uno de los id de clases siguientes: 1 o 2.");  }
            asientosPosicionados[this.numero] = this;
            var estado = "libre";
            var item0 = consultarEsquema.buscarBoletoReservacionPorAsiento(this.numero, all);
            if(item0 !== null){
                estado = consultarEsquema.buscarEstadoAsientoItem(item0);
            }
            var item = $(".icono." + clase + "." + estado).clone();
            item.removeClass("ui-draggable icono");
            item.addClass("item"); 
            var nivel2 = this.nivel2; //en que contenedor tengo que ponerlo  nivel1 o nivel2 
            if(eval(nivel2)) { 
                $("#nav2").removeClass("hidden");
                $(".nivel2").append(item); 
            }
            else { $(".nivel1").append(item); }            
            item.css("left" , core.ajustarPosicion(this.coordenadaX));
            item.css("top" , core.ajustarPosicion(this.coordenadaY));
            this.jsId = core.uniqId();
            item.attr('jsId', this.jsId);
            var numero = this.numero;
            item.find(".detalle").text(this.numero);
            item.data("numero", this.numero);
            item.data("id", this.id); //Id del asiento
            if(item0 !== null){
                item.data("tipo", item0.tipo);
                if(item0.tipo === "R"){
                    item.data("idReservacion", item0.id); //Id de la reservacion
                    item.data("idClinteReservacion", item0.clinteId); //Id del cliente de la reservacion
                }
            }
            item.css('cursor', 'hand');
        });
        $("#listaAsientosHidden").val(JSON.stringify(listaAsientos));
        
//        console.debug(asientosPosicionados);
        $("#pendientes").addClass("hidden");
        $("#utilizadoSpan").text(all.length);
        jQuery.each(all, function() { 
            var asiento = asientosPosicionados[this.numero];
            if(!asiento){
//                console.log("El item no existe...");
//                console.debug(this);
                $("#pendientes").removeClass("hidden");
                var clase = this.clase; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB  
                if(clase === "1") { clase = "claseA"; }
                else if(clase === "2") { clase = "claseB"; }
                else { throw new Error("El item debe tener uno de los id de clases siguientes: 1 o 2.");  }
                var estado = consultarEsquema.buscarEstadoAsientoItem(this);
                var item = $(".icono." + clase + "." + estado).clone();
                item.removeClass("ui-draggable icono");
                item.addClass("item2");            
                $(".pendientes").append(item);
                item.css("left" , core.ajustarPosicion(this.coordenadaX));
                item.css("top" , core.ajustarPosicion(this.coordenadaY));
                this.jsId = core.uniqId();
                item.attr('jsId', this.jsId);
                var numero = this.numero;
                item.find(".detalle").text(this.numero);
                item.data("numero", this.numero);
                //item.data("id", this.id); //El id seria de la reservacion o boleto, por eso esta mal, aqui no existe una asiento valido.
                item.data("tipo", this.tipo);
                if(this.tipo === "R"){
                    item.data("idReservacion", this.id); //Id de la reservacion
                    item.data("idClinteReservacion", this.clinteId); //Id del cliente de la reservacion
                }
            }
        });
        
        $(".containment .active").removeClass("active");
        $("#tab1").addClass("active");
        $("#nav1").addClass("active");
        
        var listaSenales = $("#listaSenalesHidden").val();
        if(listaSenales){ listaSenales = JSON.parse(listaSenales); }
        else{ listaSenales = []; }
        jQuery.each(listaSenales, function() {                                
            var tipo = this.tipo; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB  
            if(tipo === "1") { tipo = "salida"; }
            else if(tipo === "2") { tipo = "chofer"; }
            else { throw new Error("El item debe tener uno de los id de tipo siguientes: 1 o 2.");  }
            var item = $(".icono." + tipo).clone();   
            item.removeClass("ui-draggable icono");
            item.addClass("item"); 
            var nivel2 = this.nivel2; //en que contenedor tengo que ponerlo  nivel1 o nivel2 
            if(eval(nivel2)) { $(".nivel2").append(item); }
            else { $(".nivel1").append(item); }            
            item.css("left" , core.ajustarPosicion(this.coordenadaX));
            item.css("top" , core.ajustarPosicion(this.coordenadaY));
            var id = this.id; 
            this.jsId = core.uniqId();
            item.attr('jsId', this.jsId);
        });
        $("#listaSenalesHidden").val(JSON.stringify(listaSenales));
        
        $('.asiento .showInfo').bind("click", consultarEsquema.info);
    },
    
    info : function(e) {
//        console.log("showInfo-init");
        e.preventDefault();
        e.stopPropagation();
        var item = $(this);
        var numero = item.parent().data("numero");
//        console.debug(numero);
        if(numero === null || $.trim(numero) === ""){
            alert("No se pudo determinar el numero de asiento.");
            return;
        }
        var idSalida = $("#salida").val();
        if(idSalida === null || $.trim(idSalida) === ""){
            alert("No se pudo determinar el id de la salida.");
            return;
        }
        
        core.request({
            url : $("#pathInfoAsientoSalida").attr('value'),
            method: 'GET', //Obligatorio
            extraParams: {
                idSalida: idSalida,
                numeroAsiento:numero
            }, 
            dataType: "json",
            async:true,
            successCallback: function(success){
                console.debug(success);
                if(success.items){
                    var str = "";
                    var items = success.items;
                    $.each(items, function(){
                        str += "<label><b>ID: " + this.id + "</b></label>";
                        str += "<label>Asiento: " + this.numeroAsiento + "</label>";
                        str += "<label>Tipo: " + this.tipo + "</label>";
                        str += "<label>Creado: " + this.fechaCreacion + "</label>";
                        str += "<label>Estación Venta: " + this.estacionVenta + "</label>";
                        str += "<label>Estación Origen: " + this.origen + "</label>";
                        str += "<label>Estación Destino: " + this.destino + "</label>";
                        str += "<label>Cliente: " + this.cliente + "</label>";
                        if(this.cortesia === true){
                            str += "<label>Usuario: " + this.autorizadoPor + "</label>";
                            str += "<label>Motivo: " + this.motivoCortesia + "</label>";
                            str += "<label><a class='btn cancelarCortesiaPopUp' data-value='"+this.id+"'>CANCELAR CORTESIA</a></label>";
                        }
                    });
                    if(str === ""){
                        str += "<label>Asiento libre</label>";
                    }
                    str += "<BR><BR>";
                    var dialog = core.showMessageDialog({
                        title : "Consultar Boleto",
                        showCloseHandle : true,
                        text: str,
                        uniqid : true,
                        buttons: {
                           Cancelar: function() {
                               this.dialog2("close");
                           }				    
                       }
                    });
                    $(".btn.cancelarCortesiaPopUp").click(function (){
                        var id = $(this).data("value");
                        core.request({
                            url : $("#pathCancelarBoleto").attr("value"),
                            type: "POST",
                            dataType: "html",
                            async: false,
                            extraParams : {
                                id : id
                            },
                            successCallback: function(responseText){
                                if(!core.procesarRespuestaServidor(responseText)){
                                    dialog.dialog2("close");
                                    consultarEsquema.changeSalida();
                                }
                            }
                       });
                    });
                }
            }
        });
    },
    
    buscarBoletoReservacionPorAsiento : function(numeroX, lista) {
//        console.debug("buscarEstadoAsiento asiento:" + numero);
        var item = null;
        $.each(lista, function() {   
           if(this.numero === numeroX){
                item = this;
                return;
           }
        });
        return item;
    },
    
    buscarEstadoAsientoItem : function(item) {   
        if(item.tipo === "R"){
            return "reservado";
        }else if(item.tipo === "B"){
            if(item.tipoDocumento === 1 || item.tipoDocumento === 2 || item.tipoDocumento === 4 || item.tipoDocumento === 5 || item.tipoDocumento === 6 || item.tipoDocumento === 7 || item.tipoDocumento === 8)
            {
                return "vendido";
            }
            else if(item.tipoDocumento === 3){
                return "cortesia";
            }else{
                throw new Error("No se pudo determinar el tipo de documento del boleto con id:" + item.id + ", y numero:" + item.numero);
            }
        }else{
            throw new Error("No se pudo determinar el tipo del asiento con id:" + item.id + ", y numero:" + item.numero);
        }
    },
};
