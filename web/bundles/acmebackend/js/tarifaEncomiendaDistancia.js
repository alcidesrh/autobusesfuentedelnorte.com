tarifaEncomiendaDistancia = {
    
     funcionesAddOnload : function() {
        console.debug("tarifaEncomiendaDistancia.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("tarifaEncomiendaDistancia.funcionesAddOnload-end");
    },
			
    _init : function() {
        tarifaEncomiendaDistancia.checkPorcentual();
     },
    
    _conectEvents : function() {
        
         $("input[id$='tarifaPorcentual']").bind("click", function() { 
              tarifaEncomiendaDistancia.checkPorcentual();
         });
    },
    
    checkPorcentual : function() {
        console.debug("checkPorcentual-init");
        var checked = $("input[id$='tarifaPorcentual']").attr("checked");
        if(checked){
            $("div[id$='tarifaPorcentualValorMinimo']").show();
            $("div[id$='tarifaPorcentualValorMaximo']").show();
        }else{
            $("input[id$='tarifaPorcentualValorMinimo']").val("");
            $("input[id$='tarifaPorcentualValorMaximo']").val("");
            $("div[id$='tarifaPorcentualValorMinimo']").hide();
            $("div[id$='tarifaPorcentualValorMaximo']").hide();
        }
    }
}