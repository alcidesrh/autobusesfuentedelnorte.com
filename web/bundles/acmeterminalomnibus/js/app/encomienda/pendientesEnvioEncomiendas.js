pendientesEnvioEncomiendas = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $("#pendiente_envio_command_estacion").select2({
             allowClear: $("#pendiente_envio_command_estacion option[value='']").length === 1
        });
        $("#pendiente_envio_command_estacion").select2("readonly", ($("#pendiente_envio_command_estacion").attr("readonly") === "readonly"));
        
        pendientesEnvioEncomiendas.buscarDatos();
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
        
        $("#pdf").data("index", "pendiente_envio_command_estacion");
        $("#pdf").data("autoopenfile", "PDF");
        $("#pdf").click(frondend.loadSubPage);
        
        $("#pendiente_envio_command_estacion").on("change", pendientesEnvioEncomiendas.buscarDatos);
//        $("#aceptar").click(pendientesEnvioEncomiendas.clickAceptar);
    },
    
    buscarDatos : function(e) {
        console.debug("buscarDatos-init");
        $("#encomiendasBody").find("tr").not("#encomiendasVacioTR").remove(); //Elimino todos los tr
        $("#encomiendasBody").find("#encomiendasVacioTR").show();
        
        var estacion = $("#pendiente_envio_command_estacion").val();
        if(estacion !== null && $.trim(estacion) !== ""){
           core.request({
                url : $("#pathListarEncomiendasPendientesPorEstacion").attr('value'),
                method: 'POST',
                extraParams: {
                    estacion : estacion
                }, 
                dataType: "json",
                async:false,
                successCallback: function(success){
                    console.debug(success);
                    var encomiendas = success.encomiendas;
                    var pathConsultarEncomienda = $("#pathConsultarEncomienda").val();
                    $.each(encomiendas, function(){
                        console.debug(this);
                        $("#encomiendasBody").find("#encomiendasVacioTR").hide();
                        var itemTR = $("<tr id='EN"+this.id+"'>"+
                                       "<td class='center'><a class='id btn'>"+this.id+"</a></td>"+
                                       "<td class='center'>"+this.empresa+"</td>"+
                                       "<td class='center'>"+this.fecha+"</td>"+
                                       "<td class='center'>"+this.doc+"</td>"+
                                       "<td class='center'>"+
                                       "<a class='ruta btn' "+
                                       "data-codigoruta='"+this.codigoPrimeraRuta+"'"+
                                       "data-idestacionorigen='"+this.idEstacionOrigen+"'"+
                                       "data-idestacionprimerdestino='"+this.idEstacionPrimerDestino+"'"+
                                       "data-idencomienda='"+this.id+"'"+
                                       " >"+this.codigoPrimeraRuta+"</a><BR>"+this.nombrePrimeraRuta+"</td>"+
                                       "<td class='center'>"+this.proxDestino+"</td>"+
                                       "<td class='center'>"+this.desc+"</td>"+
                                       "</tr>");
                        itemTR.val(this.id); 
                        itemTR.find("a.id").attr('href', pathConsultarEncomienda);
                        itemTR.find("a.id").data("index", "EN"+this.id);
                        itemTR.find("a.id").data("title", "Consultar Encomienda");
                        itemTR.find("a.id").data("fullscreen", true);
                        itemTR.find("a.id").click(frondend.loadSubPage);
                        if(this.puedeModificar){
                            itemTR.find("a.ruta").click(pendientesEnvioEncomiendas.modificarRuta);
                        }else{
                            itemTR.find("a.ruta").addClass("disabled");
                        }
                        console.debug(itemTR);
                        $("#encomiendasBody").append(itemTR);
                    });
                }
           });
        }
    },
    
    buscarDatosById : function(idEncomienda) {
        console.debug("buscarDatosById-init");
        if(idEncomienda !== null && $.trim(idEncomienda) !== ""){
           core.request({
                url : $("#pathGetEncomiendaPendientesById").attr('value'),
                method: 'POST',
                extraParams: {
                    idEncomienda : idEncomienda
                }, 
                dataType: "json",
                async:false,
                successCallback: function(success){
                    console.debug(success);
                    var item = success.encomienda;
                    if(!item || item === null){
                        alert("No se pudo obtener la encomienda con identificador : " + idEncomienda + ".");
                        return;
                    }
                    var pathConsultarEncomienda = $("#pathConsultarEncomienda").val();
                    console.debug(item);
                    var itemTR = $("<tr id='EN"+item.id+"'>"+
                                       "<td class='center'><a class='id btn'>"+item.id+"</a></td>"+
                                       "<td class='center'>"+item.empresa+"</td>"+
                                       "<td class='center'>"+item.fecha+"</td>"+
                                       "<td class='center'>"+item.doc+"</td>"+
                                       "<td class='center'>"+
                                       "<a class='ruta btn' "+
                                       "data-codigoruta='"+item.codigoPrimeraRuta+"'"+
                                       "data-idestacionorigen='"+item.idEstacionOrigen+"'"+
                                       "data-idestacionprimerdestino='"+item.idEstacionPrimerDestino+"'"+
                                       "data-idencomienda='"+item.id+"'"+
                                       " >"+item.codigoPrimeraRuta+"</a><BR>"+item.nombrePrimeraRuta+"</td>"+
                                       "<td class='center'>"+item.proxDestino+"</td>"+
                                       "<td class='center'>"+item.desc+"</td>"+
                                       "</tr>");
                        itemTR.val(item.id); 
                        itemTR.find("a.id").attr('href', pathConsultarEncomienda);
                        itemTR.find("a.id").data("index", "EN"+item.id);
                        itemTR.find("a.id").data("title", "Consultar Encomienda");
                        itemTR.find("a.id").data("fullscreen", true);
                        itemTR.find("a.id").click(frondend.loadSubPage);
                        if(item.puedeModificar){
                            itemTR.find("a.ruta").click(pendientesEnvioEncomiendas.modificarRuta);
                        }else{
                            itemTR.find("a.ruta").addClass("disabled");
                        }
                        console.debug(itemTR);
                        $("#encomiendasBody").find("#EN"+idEncomienda).replaceWith(itemTR);                    
                }
           });
        }
    },
    
    modificarRuta : function(e) {
        console.debug("modificarRuta-init");
        e.preventDefault();
        e.stopPropagation();
        var item = $(this);
        core.request({
            url : $("#pathGetRutasAlternas").prop("value"),
            type: "POST",
            dataType: "json",
            async: false,
            extraParams : { 
                idEstacionOrigen: item.data("idestacionorigen"),
                idEstacionDestino: item.data("idestacionprimerdestino") ,
                idEncomienda: item.data("idencomienda")
            },
            successCallback: function(data){
                var optionRutas = data.optionRutas;
                $("#ruta").select2({
                    allowClear: false,
                    data: { results: optionRutas }
                });
                $("#ruta").select2('val', item.data("codigoruta"));
                core.showMessageDialog({
                    title : "Modificar Ruta - Empresa",
                    selector: $("#modificarRutaDIV"),
                    removeOnClose : false,
                    uniqid : false,
                    buttons: {
                        Aceptar: {
                            click: function() {
                                console.debug("clic...");
                                var idEncomienda = item.data("idencomienda");
                                console.debug("IdEncomienda: "+idEncomienda);
                                var codigoRuta = $("#ruta").val();
                                console.debug("Ruta: "+codigoRuta);
                                var dialog = this;
                                
                                core.request({
                                    url : $("#encomiendaPendienteEnvio").attr('action'),
                                    method: 'POST',
                                    extraParams: {
                                        idEncomienda : idEncomienda,
                                        codigoRuta : codigoRuta
                                    }, 
                                    dataType: "html",
                                    async:false,
                                    successCallback: function(responseText){
                                        console.debug(responseText);
                                        if(!core.procesarRespuestaServidor(responseText)){
                                            dialog.dialog2("close");
                                            alert("Operación realizada satisfactoriamente.", function() {
                                                pendientesEnvioEncomiendas.buscarDatosById(idEncomienda);
                                            });
                                        }
                                    }
                                });
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
          });
    }
    
};
