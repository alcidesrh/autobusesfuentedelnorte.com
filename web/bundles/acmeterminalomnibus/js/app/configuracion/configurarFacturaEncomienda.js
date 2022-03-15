configurarFacturaEncomienda = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $("#configurar_factura_command_empresa").select2({
            allowClear: true
        });
        
        $("#configurar_factura_command_factura").select2({
            allowClear: true,
            data: []
        });
         
     },
    
    _conectEvents : function() {
        $("#configurar_factura_command_empresa").on("change", configurarFacturaEncomienda.loadFacturas);
        $("#cancelar").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            confirm("¿Está seguro que desea cancelar la operación?", function(confirmed){
                if(confirmed === true){
                    core.getPageForMenu($("#cancelar").attr('href'));
                }
            });
        });
         
        $("#aceptar").click(configurarFacturaEncomienda.clickAceptar);
    },
    
    clickAceptar: function(e) {
        e.preventDefault();
        e.stopPropagation();
        var configurarFacturaForm = $("#configurarFacturaForm");
        if(core.customValidateForm(configurarFacturaForm) === true){
            $(configurarFacturaForm).ajaxSubmit({
                target: configurarFacturaForm.attr('action'),
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
                            core.getPageForMenu($("#cancelar").attr("href"));
                        });
                    }
                }
           });
        }
    },
    
    loadFacturas : function() {
       var empresa = $("#configurar_factura_command_empresa").val();
       var servicio = $("#configurar_factura_command_servicioEstacion").val();
       if(empresa === null || $.trim(empresa) === "" || servicio === null || $.trim(servicio) === ""){
           $("#configurar_factura_command_factura").select2({
               allowClear: true,
               data: []
           });
           $("#configurar_factura_command_factura").val("");
           $("#configurar_factura_command_factura").select2('val', '');
       }else{
           core.request({
                url : $("#pathListarFacturas").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { 
                    empresa: empresa,
                    servicio: servicio
                },
                successCallback: function(data){
                    var optionFacturas = data.optionFacturas;
                    if( optionFacturas ){
                        $("#configurar_factura_command_factura").select2({
                            allowClear: true,
                            data: { results: optionFacturas }
                        });
                        var facturaSelected = data.facturaSelected;
                        if(facturaSelected && $.trim(facturaSelected) !== "") {
                            $("#configurar_factura_command_factura").select2('val', facturaSelected);
                        }else{
                            $("#configurar_factura_command_factura").val("");
                            $("#configurar_factura_command_factura").select2('val', '');
                        }
                    }
                }
             });
       }
    }
};
