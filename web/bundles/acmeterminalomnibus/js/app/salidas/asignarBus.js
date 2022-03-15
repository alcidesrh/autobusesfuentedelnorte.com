asignarBus = {
    
    funcionesAddOnload : function() {
//        console.debug("asignarBus.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("asignarBus.funcionesAddOnload-end");
    },
			
    _init : function() {
        
//        function format(item) {
//            return "<label>" + item.id + " - " + item.text + "</label>";
//            console.debug(item);
//        }
        $("#asignar_salida_command_bus").select2({
            allowClear: true,
//            formatResult: format,
//            formatSelection: format,
//            escapeMarkup: function(m) { return m; }
        });
        
        $("#asignar_salida_command_piloto").select2({
        });
        $("#asignar_salida_command_piloto").select2('readonly', 'true');
        $("#asignar_salida_command_pilotoAux").select2({
        });
        $("#asignar_salida_command_pilotoAux").select2('readonly', 'true');
        this.checkReasignada();
     },
     
    _conectEvents : function() {
        
        $("#asignar_salida_command_bus").on("change", function(e) {
            $("#asignar_salida_command_piloto").select2("val", "");
            $("#asignar_salida_command_pilotoAux").select2("val", "");
            var codigoBus = e.val;
            core.request({
                async: true,
                extraParams : {
                    codigoBus: codigoBus
                },
                url: $("#asignar_salida_command_bus").data("onchangeurl"),
                successCallback: function(success){
                    var options = {};
                    if(success.options){
                        options = success.options;
                    }
                    if(options['piloto1'] && options['piloto1'].id && $.trim(options['piloto1'].id) !== ""){
                        $("#asignar_salida_command_piloto").select2("val", options['piloto1'].id);
                    }else{
                        $("#asignar_salida_command_piloto").select2("val", "");
                    }
                    if(options['piloto2'] && options['piloto2'].id && $.trim(options['piloto2'].id) !== ""){
                        $("#asignar_salida_command_pilotoAux").select2("val", options['piloto2'].id);
                    }else{
                        $("#asignar_salida_command_pilotoAux").select2("val", "");
                    }
                }
            });
            
        });
        
        $('#asignar_salida_command_reasignado').bind("click", function() { 
//              console.debug("asignar_salida_command_reasignado - click");
              asignarBus.checkReasignada();
         });
    },
    
    checkReasignada : function() {
//        console.debug("checkReasignada-init");
        var checked = $("#asignar_salida_command_reasignado").prop("checked");
        if(checked){
            $("#asignar_salida_command_motivoReasignado").parent().parent().show();
        }else{
            $("#asignar_salida_command_motivoReasignado").val("");
            $("#asignar_salida_command_motivoReasignado").parent().parent().hide();   
            
        }
    }
    
    
    
};
