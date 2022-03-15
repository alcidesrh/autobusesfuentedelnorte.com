consultarSalida = {
    
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
        
        consultarSalida.changeFecha();
     },
     
    _conectEvents : function() {
        
        $("#fecha").on("change", consultarSalida.changeFecha);
        $("#salida").on("change", consultarSalida.changeSalida);
        $("#cancelar").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            core.getPageForMenu($("#cancelar").attr('href'));
        });
        
        $("#manifiestoInterno").data("index", "salida");
        $("#manifiestoInterno").data("autoopenpdf", true);
        $("#manifiestoInterno").click(frondend.loadSubPage);
        
        $("#manifiestoPiloto").data("index", "salida");
        $("#manifiestoPiloto").data("autoopenpdf", true);
        $("#manifiestoPiloto").click(frondend.loadSubPage);
        
        $("#manifiestoEncominda").data("index", "salida");
        $("#manifiestoEncominda").data("autoopenpdf", true);
        $("#manifiestoEncominda").click(frondend.loadSubPage);
    },
    
    changeFecha : function() {
        console.log("changeFecha-int");
        $("#salida").html("");
        $("#salida").val("");
        consultarSalida.checkShowEsquema();
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
            consultarSalida.checkShowEsquema();
            return;
        }else{
            core.request({
                url : $("#pathGetInformacionPorSalida").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { idSalida: salida },
                successCallback: function(data){
                    consultarSalida.checkShowEsquema();
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
                    
                    consultarSalida.mostrarIconos();
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
            var item0 = consultarSalida.buscarBoletoReservacionPorAsiento(this.numero, all);
            if(item0 !== null){
                estado = consultarSalida.buscarEstadoAsientoItem(item0);
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
                var estado = consultarSalida.buscarEstadoAsientoItem(this);
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
        
        $('.asiento .showInfo').bind("click", consultarSalida.info);
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
                        str += "Asiento: " + this.numeroAsiento + "<BR>";
                        str += "ID: " + this.id + "<BR>";
                        str += "Tipo: " + this.tipo + "<BR>";
                        str += "Creado: " + this.fechaCreacion + "<BR>";
                        str += "Estación Venta: " + this.estacionVenta + "<BR>";
                        str += "Estación Origen: " + this.origen + "<BR>";
                        str += "Estación Destino: " + this.destino + "<BR>";
                        str += "Cliente: " + this.cliente + "<BR><BR>";
                    });
                    if(str === ""){
                        str += "Asiento libre";
                    }
                    str += "<BR><BR><BR><BR>";
                    alert(str);
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
            if(item.tipoDocumento === 1 || item.tipoDocumento === 2 || item.tipoDocumento === 4 || item.tipoDocumento === 5 || item.tipoDocumento === 6 || item.tipoDocumento === 7)
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
