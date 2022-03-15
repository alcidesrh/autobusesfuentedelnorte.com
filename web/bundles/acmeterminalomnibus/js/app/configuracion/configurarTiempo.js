configurarTiempo = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $("#configurar_tiempo_command_ruta").select2({
            allowClear: true
        });
        
        $("#configurar_tiempo_command_claseBus").select2({
            allowClear: true
        });
         
     },
    
    _conectEvents : function() {
        $("#configurar_tiempo_command_ruta").on("change", configurarTiempo.loadTiempos);
        $("#configurar_tiempo_command_claseBus").on("change", configurarTiempo.loadTiempos);
        $("#cancelar").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            confirm("¿Está seguro que desea cancelar la operación?", function(confirmed){
                if(confirmed === true){
                    core.getPageForMenu($("#cancelar").attr('href'));
                }
            });
        });
         
        $("#aceptar").click(configurarTiempo.clickAceptar);
    },
    
    clickAceptar: function(e) {
        e.preventDefault();
        e.stopPropagation();
        var configurarTiempoForm = $("#configurarTiempoForm");
        if(core.customValidateForm(configurarTiempoForm) === true){
            configurarTiempo.syncData();
            $(configurarTiempoForm).ajaxSubmit({
                target: configurarTiempoForm.attr('action'),
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
                            configurarTiempo.loadTiempos();
                        });
                    }
                }
           });
        }
    },
    
    syncData : function() {
        
        var listaItems = [];
        var items = $("#estacionesBody").find("input");
        $.each(items, function (index, item){
            item = $(item);
            var tr = item.parent().parent();
            listaItems.push({
                id : tr.data("id"),
                minutos : item.val()
            });
        });
        $("#configurar_tiempo_command_listaItems").val(JSON.stringify(listaItems));
    },
    
    loadTiempos : function() {
        console.log("loadTiempos-init");
        
        $("#estacionesBody").find("tr").not("#estacionesVacioTR").remove();
        $("#estacionesBody").find("#estacionesVacioTR").show();
        
        var ruta = $("#configurar_tiempo_command_ruta").val();
        var claseBus = $("#configurar_tiempo_command_claseBus").val();
        if(ruta !== null && $.trim(ruta) !== "" && claseBus !== null && $.trim(claseBus) !== ""){
           core.request({
                url : $("#pathListarTiempos").prop("value"),
                type: "POST",
                dataType: "json",
                async: false,
                extraParams : { 
                    ruta: ruta,
                    claseBus: claseBus
                },
                successCallback: function(data){
                    var optionsEstaciones = data.optionsEstaciones;
                    if( optionsEstaciones && optionsEstaciones.length !== 0){
                        $("#estacionesBody").find("#estacionesVacioTR").hide();
                        console.log("renderEstaciones: " + optionsEstaciones.length + " items");
                        jQuery.each(optionsEstaciones, function() {
                            var itemTR = $("<tr class='trSelect' data-id='"+this.id+"'><td>"+this.text+"</td><td class='minutoTD'><input type='number' min=0 max=9999 value='"+this.minutes+"' /></td></tr>");
                            $("#estacionesBody").append(itemTR);
                        });
                    }
                }
             });
       }
    }
};
