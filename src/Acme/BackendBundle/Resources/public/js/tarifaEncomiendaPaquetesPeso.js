tarifaEncomiendaPaquetesPeso = {
    
     funcionesAddOnload : function() {
        console.debug("tarifaEncomiendaPaquetesPeso.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("tarifaEncomiendaPaquetesPeso.funcionesAddOnload-end");
    },
			
    _init : function() {
        tarifaEncomiendaPaquetesPeso.checkPorcentual();
     },
    
    _conectEvents : function() {
        
         $("input[id$='tarifaPorcentual']").bind("click", function() { 
              tarifaEncomiendaPaquetesPeso.checkPorcentual();
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