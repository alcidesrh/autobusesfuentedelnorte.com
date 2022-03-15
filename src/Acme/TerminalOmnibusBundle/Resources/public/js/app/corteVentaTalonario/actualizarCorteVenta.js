actualizarCorteVenta = {
    
    
    funcionesAddOnload : function() {
//        console.debug("crearAlquiler.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("crearAlquiler.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        actualizarCorteVenta.renderItems();
        
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
         
        $("#aceptar").click(actualizarCorteVenta.clickAceptar); 
    },

    clickAceptar: function(e) {
        console.debug("clickAceptar-init");
        e.preventDefault();
        e.stopPropagation();
        
        var listJson = [];
        var listaItems = $("#actualizar_revision_corte_venta_talonario_command_listaItems").val();
        if(listaItems !== null && $.trim(listaItems) !== ""){
            listJson = JSON.parse(listaItems);
        }
        $.each($("input.importe"), function (index, item){
            var id = $(item).attr("data-id");
            var element = actualizarCorteVenta.findInList(id, listJson);
            if(element !== null){
                element.importe = $(item).val();
            }
        });
        $("#actualizar_revision_corte_venta_talonario_command_listaItems").val(JSON.stringify(listJson));
        
        var corteVentaTalonarioForm = $("#corteVentaTalonarioForm");
        if(core.customValidateForm(corteVentaTalonarioForm) === true){
            $(corteVentaTalonarioForm).ajaxSubmit({
                target: corteVentaTalonarioForm.attr('action'),
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
                            core.getPageForMenu($("#pathHomeCorteVentaTalonario").attr("value"));
                        });
                    }
                }
           });
        }
    },
    
    findInList: function(id, list) {
        if(id === null || $.trim(id) === "") return null;
        var result = null;
        $.each(list, function (index, item){
            if(item.id === id){
                result = item;
                return false;
            }
        });
        return result;
    },
    
    renderItems: function() {
        console.log("renderItems-init");
        var lista = $("#actualizar_revision_corte_venta_talonario_command_listaItems").val();
        if(lista !== null && $.trim(lista) !== ""){
            var listJson = JSON.parse(lista);
            if(listJson.length > 0){
                $("#listItemsBody > tr.emptyListDiv").addClass("hidden");
                var prototype = $("tr.prototype.hidden");
                $.each(listJson, function (index, item){
                    var element = prototype.clone();
                    element.removeClass("prototype");
                    element.removeClass("hidden");
                    element.find("td.numero").text(item.numero);
                    element.find("input.importe").attr("data-id", item.id);
                    element.find("input.importe").val(item.importe);
                    $("#listItemsBody").append(element);
                });
            }
        }
    }
};
