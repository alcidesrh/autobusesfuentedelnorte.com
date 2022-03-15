desembarcarMovil = {
    
    funcionesAddOnload : function() {
//        console.debug("desembarcarMovil.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("desembarcarMovil.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $('#zoom').slider({
            min: 1,
            max: 4,
            step: 0.5,
            value : 1
        });
        desembarcarMovil.initWebCam();
        $("#cameraNames").select2();
     },
     
     showErrorCamera : function(errorId, errorMsg){
//         console.debug("errorId:" + errorId);
//         console.debug("errorMsg:" + errorMsg);
         alert(errorMsg);
     },
     
     webcamFound : function(cameraNames,camera,microphoneNames,microphone,volume) {
         $('#cameraNames').html("");
         console.debug(cameraNames);
         $.each(cameraNames, function(index, text) {
            $('#cameraNames').append( $('<option></option>').val(index).html(text) );
	 });
	 $('#cameraNames').select2('val', camera);
         desembarcarMovil.findBarCode();
     },
    
     initWebCam : function() {
        var zoom = eval($("#zoom").slider('getValue').val());
        $("#webcam").scriptcam({
            path: '/cam/',
            width: 320,
	    height: 240,
            useMicrophone: false,
            showMicrophoneErrors: false,
            onError: desembarcarMovil.showErrorCamera,
            onWebcamReady: desembarcarMovil.webcamFound,
            readBarCodes:'CODE_128',
//            flip: 1,
//            posX: 600,
//            posY:0,
            zoom: zoom
        });
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
        
        $("#cameraNames").on("change", function (){
            $.scriptcam.changeCamera($('#cameraNames').val());
            desembarcarMovil.findBarCode();
        });
        
        $('#zoom').slider().on('slideStop', function(ev){
            desembarcarMovil.initWebCam();
         });
    },
    
    timeFind : 500, //1 segundo
    tempFunction : null,
    findBarCode: function() {
//        console.log("findBarCode...");
        tempFunction = setTimeout(function(){
//            console.log("tempFunction...");
            clearTimeout(tempFunction);
            var varcode =  null
            try {
                var varcode = $.scriptcam.getBarCode();
            }catch (e){
                return;
            }
            console.debug(varcode);
            if(!varcode || varcode === null || $.trim(varcode) === ""){
                $("#desembarcar_command_code").val("");
                desembarcarMovil.findBarCode();
            }else{
//                console.debug("procesando codigo de barra...");
                $("#desembarcar_command_code").val(varcode);
                desembarcarMovil.desembarcarEncomienda(varcode);
            }
           
        }, desembarcarMovil.timeFind);
    },
    
    desembarcarEncomienda : function(varcode) {
//        console.log("desembarcarEncomienda-init");
        core.request({
            url : $("#movilWebForm").attr("action"),
            type: "POST",
            dataType: "json",
            showLoading: true,
            async: false,
            extraParams : { id: varcode }, //Id de la encomienda
            successCallback: function(data){
                var result = core.checkExistError(data);
                if( result === false){
                    desembarcarMovil.findBarCode(); //todo ok
                }else{
                    alert(result, function (){
                        desembarcarMovil.findBarCode();
                    });
                }
            }
        });
    }
};
