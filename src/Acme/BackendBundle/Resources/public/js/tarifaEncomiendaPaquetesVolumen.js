tarifaEncomiendaPaquetesVolumen = {
    
     funcionesAddOnload : function() {
        console.debug("tarifaEncomiendaPaquetesVolumen.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("tarifaEncomiendaPaquetesVolumen.funcionesAddOnload-end");
    },
			
    _init : function() {
        tarifaEncomiendaPaquetesVolumen.checkPorcentual();
     },
    
    _conectEvents : function() {
        
         $("input[id$='tarifaPorcentual']").bind("click", function() { 
              tarifaEncomiendaPaquetesVolumen.checkPorcentual();
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