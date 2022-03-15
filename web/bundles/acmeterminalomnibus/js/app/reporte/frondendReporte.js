frondendReporte = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendReporte.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendReporte.funcionesAddOnload-end");
    },
			
    _init : function() {
        if(core.checkInstalljsPrintSetup()){
            jsPrintSetup.setSilentPrint(false);
        }
     },
     
    _conectEvents : function() {
        
        $(".ejecutar").click(frondendReporte.ejecutarReporte);
        $("#ejecutar").click(frondendReporte.ejecutarReporte);
        
    },
    
    ejecutarReporte : function(e) {
        e.preventDefault();
        e.stopPropagation();
        var type = $(this).data("type");
        if(type === undefined || type === null || $.trim(type) === ""){
            throw new Error("Debe especificar un formato de documento.");
        }
        
        var form = $('#reporteForm');
        var values = form.formSerialize();
        var itemsCheckbox = form.find('input[type="checkbox"]');
        $.each(itemsCheckbox, function(index, item) {
            item = $(item);
            if(item.prop("checked") === false){
               values += "&"+item.prop("name")+"=0";
            }
        });
        values += "&type="+type;
//        console.log("values:");
//        console.debug(values);
        var reporteForm = $("#reporteForm");
        if(core.customValidateForm(reporteForm) === true){
            $(reporteForm).ajaxSubmit({
                target: reporteForm.attr('action'),
                type : "POST",
                data: { type: type },
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
                        var url = core.getValueFromResponse(responseText, "data");
                        if(!url){
                            throw new Error("No se pudo obtener la url del pdf del response.");
                        }
                        
                        if(type === "TXT"){
                            core.readText(url, function (output){
                                var newWindow = window.open("","Reporte","charset=utf-8,left=20,top=20,width=800,height=600,scrollbars=yes,resizable=yes,menubar=yes,status=yes,toolbar=yes");
                                newWindow.document.open();
                                newWindow.document.write("<pre>" + output + "</pre>");
                                newWindow.document.close();
                            });
                        }else{
                            var popup = window.open(url, "Reporte", "charset=utf-8,left=20,top=20,width=800,height=600,scrollbars=yes,resizable=yes,menubar=yes,status=yes,toolbar=yes");  
                            popup.focus(); 
                        }
                        
                        if(core.checkInstalljsPrintSetup()){
                            jsPrintSetup.setPaperSizeData(1); //Carta
                            jsPrintSetup.setSilentPrint(false);
                        }
                    }
                }
           });
        }
    },
    
//    ejecutarReporte : function(e) {
//        console.debug("frondendReporte.ejecutarReporte-init");
//        console.debug("linkFilter - clic");
//        e.preventDefault();
//        e.stopPropagation();
//        var type = $(this).data("type");
//        if(type === undefined || type === null || $.trim(type) === ""){
//            throw new Error("Debe especificar un formato de documento.");
//        }
//        
//        var form = $('#reporteForm');
//        var values = form.formSerialize();
//        var itemsCheckbox = form.find('input[type="checkbox"]');
//        $.each(itemsCheckbox, function(index, item) {
//            console.debug(index);
//            console.debug(item);
//            item = $(item);
//            if(item.prop("checked") === false){
//               values += "&"+item.prop("name")+"=0";
//            }
//        });
//        console.log("values:");
//        console.debug(values);
//        
//        if (core.checkInstalljsPrintSetup() === true) {
//            jsPrintSetup.setSilentPrint(false);
//        }
//        
//        var url = form.attr('action');
//        url = encodeURI(url) + "?" + frondendReporte.buildQueryString({
//            navpanes: 1,
//            statusbar: 0,
//            type: type
////              view: "FitH",
////              pagemode: "thumbs"
//        }) + "&" + values;
//        
//        console.log("load-pdf-init");
//        $("#pdf").html("");
//        core.showLoading({showLoading:true});
//        var myPDF = new PDFObject({ 
//            url: url,
//            id: "myPDF",
//            width: "100%",
//            height: "1000px"
//        }).embed("pdf");
//        
//        myPDF.onload = function(){
//            console.log("onload-file-init");
//            core.hideLoading({showLoading:true});
//        };
//        myPDF.onerror = function(){
//            console.log("onerror-file-init");
//            core.hideLoading({showLoading:true});
//            myPDF.css("width", "0px");
//            myPDF.css("height", "0px");
//        };
//        console.debug("frondendReporte.ejecutarReporte-init");
//    },
//    
//    addHandler : function (obj, evnt, handler) {
//        if (obj.addEventListener) {
//            obj.addEventListener(evnt.replace(/^on/, ''), handler, false);
//        } else {
//            if (obj[evnt]) {
//                var origHandler = obj[evnt];
//                obj[evnt] = function(evt) {
//                    origHandler(evt);
//                    handler(evt);
//                };
//            } else {
//                obj[evnt] = function(evt) {
//                    handler(evt);
//                };
//            }
//        }
//    },
//    
//    buildQueryString : function(pdfParams){
//	console.debug("frondendReporte.buildQueryString-init");	
//        var string = "", prop;
//	if(!pdfParams){ return string; }	
//        for (prop in pdfParams) {
//            if (pdfParams.hasOwnProperty(prop)) {
//                  string += prop + "=";
//                  if(prop === "search") {
//                       string += encodeURI(pdfParams[prop]);
//                  } else {
//                        string += pdfParams[prop];
//                  }	
//                  string += "&";
//            }
//        }
//        var value = string.slice(0, string.length - 1);
//        console.debug(value);
//        console.debug("frondendReporte.buildQueryString-end");	
//	return value;
//    }
};
