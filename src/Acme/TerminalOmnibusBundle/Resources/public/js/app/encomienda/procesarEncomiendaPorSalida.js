procesarEncomiendaPorSalida = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $("#procesar_encomiendas_salida_command_salida").select2({
             allowClear: $("#procesar_encomiendas_salida_command_salida option[value='']").length === 1
        });
        procesarEncomiendaPorSalida.buscarDatos();
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
        $("#procesar_encomiendas_salida_command_salida").on("change", procesarEncomiendaPorSalida.buscarDatos);
        $("#aceptar").click(procesarEncomiendaPorSalida.clickAceptar);
        
        $("#manifiesto").data("index", "procesar_encomiendas_salida_command_salida");
        $("#manifiesto").data("autoopenfile", "PDF");
        $("#manifiesto").click(frondend.loadSubPage);
    },
    
    buscarDatos : function(e) {
        console.debug("buscarDatos-init");
        $("#encomiendasAbordadasBody").find("tr").not("#encomiendasAbordadasVacioTR").remove(); //Elimino todos los tr
        $("#encomiendasAbordadasBody").find("#encomiendasAbordadasVacioTR").show();
        $("#encomiendasPendientesBody").find("tr").not("#encomiendasPendientesVacioTR").remove(); //Elimino todos los tr
        $("#encomiendasPendientesBody").find("#encomiendasPendientesVacioTR").show();
        
        var salida = $("#procesar_encomiendas_salida_command_salida").val();
        if(salida !== null && $.trim(salida) !== ""){
           core.request({
                url : $("#pathListarEncomiendasAProcesar").attr('value'),
                method: 'POST',
                extraParams: {
                    salida : salida
                }, 
                dataType: "json",
                async:false,
                successCallback: function(success){
                    console.debug(success);
                    var encomiendasAbordadas = success.encomiendasAbordadas;
                    var pathConsultarEncomienda = $("#pathConsultarEncomienda").val();
                    $.each(encomiendasAbordadas, function(){
                        $("#encomiendasAbordadasBody").find("#encomiendasAbordadasVacioTR").hide();
                        var itemTR = $("<tr id='EA"+this.id+"'>"+
                                       "<td class='center'><a class='btn'>"+this.id+"</a></td>"+
                                       "<td class='center inputCheckbox'><input data-valor='"+this.id+"' type='checkbox'></td>"+
                                       "<td class='center'>"+this.fecha+"</td>"+
                                       "<td class='center'>"+this.doc+"</td>"+
                                       "<td class='center'>"+this.origen+"</td>"+
                                       "<td class='center'>"+this.destino+"</td>"+
//                                       "<td class='center'>"+this.cliente+"</td>"+
                                       "<td class='center'>"+this.desc+"</td>"+
                                       "</tr>");
                        if(!this.puedeModificar){
                            itemTR.find("input").attr("disabled","disabled");
                        }
                        itemTR.val(this.id); 
                        itemTR.find("a").attr('href', pathConsultarEncomienda);
                        itemTR.find("a").data("index", "EA"+this.id);
                        itemTR.find("a").data("title", "Consultar Encomienda");
                        itemTR.find("a").data("fullscreen", true);
                        itemTR.find("a").click(frondend.loadSubPage);
                        $("#encomiendasAbordadasBody").append(itemTR);
                    });
                    
                    var encomiendasPendientes = success.encomiendasPendientes;
                    $.each(encomiendasPendientes, function(){
                        $("#encomiendasPendientesBody").find("#encomiendasPendientesVacioTR").hide();
                        var itemTR = $("<tr id='EP"+this.id+"'>"+
                                       "<td class='center'><a class='btn'>"+this.id+"</a></td>"+
                                       "<td class='center inputCheckbox'><input data-valor='"+this.id+"' type='checkbox'></td>"+
                                       "<td class='center'>"+this.fecha+"</td>"+
                                       "<td class='center'>"+this.doc+"</td>"+
                                       "<td class='center'>"+this.origen+"</td>"+
                                       "<td class='center'>"+this.destino+"</td>"+
//                                       "<td class='center'>"+this.cliente+"</td>"+
                                       "<td class='center'>"+this.desc+"</td>"+
                                       "</tr>");
                        if(!this.puedeModificar){
                            itemTR.find("input").attr("disabled","disabled");
                        }
                        itemTR.val(this.id);  
                        itemTR.find("a").attr('href', pathConsultarEncomienda);
                        itemTR.find("a").data("index", "EP"+this.id);
                        itemTR.find("a").data("title", "Consultar Encomienda");
                        itemTR.find("a").data("fullscreen", true);
                        itemTR.find("a").click(frondend.loadSubPage);
                        $("#encomiendasPendientesBody").append(itemTR);
                    });
                }
           });
        }
    },
    
    clickAceptar: function(e) {
        console.debug("clickAceptar-init");
        e.preventDefault();
        e.stopPropagation();
        
        var salida = $("#procesar_encomiendas_salida_command_salida").val();
        if(salida === null || $.trim(salida) === ""){
            alert("Debe seleccionar una salida.");
            return;
        }
        
        var listaIdEmbarcar = [];
        var items = $("#encomiendasPendientesBody").find("input");
        $.each(items, function (){
            var checked = $(this).prop("checked");
            if(checked){
                listaIdEmbarcar.push($(this).data("valor"));
            } 
        });
        console.debug(listaIdEmbarcar);
        
        var listaIdDesembarcar = [];
        var items = $("#encomiendasAbordadasBody").find("input");
        $.each(items, function (){
            var checked = $(this).prop("checked");
            if(checked){
                listaIdDesembarcar.push($(this).data("valor"));
            } 
        });
        console.debug(listaIdDesembarcar);
        
        if(listaIdEmbarcar.length <= 0 && listaIdDesembarcar.length <= 0){
            alert("Debe seleccionar al menos una encomienda.");
            return;
        }
        
        core.request({
            url : $("#procesarEncomiendaPorSalida").attr('action'),
            method: 'POST',
            extraParams: {
                salida : salida,
                listaIdEmbarcar: listaIdEmbarcar.join(","),
                listaIdDesembarcar: listaIdDesembarcar.join(",")
            }, 
            dataType: "html",
            async:false,
            successCallback: function(responseText){
                console.debug(responseText);
                if(!core.procesarRespuestaServidor(responseText)){
                    alert("Operación realizada satisfactoriamente.", function() {
                        procesarEncomiendaPorSalida.buscarDatos();
                    });
                }
            }
        });
        
    }
    
};
