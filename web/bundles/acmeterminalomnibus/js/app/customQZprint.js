customQZprint = {
    
        /**
	* Deploys different versions of the applet depending on Java version.
	* Useful for removing warning dialogs for Java 6.  This function is optional
	* however, if used, should replace the <applet> method.  Needed to address 
	* MANIFEST.MF TrustedLibrary=true discrepency between JRE6 and JRE7.
	*/
	deployQZ : function () {
            if(customQZprint.isMovil() === false){
                var attributes = {
                    id: "qz", 
                    code:'qz.PrintApplet.class', 
                    archive: document.getElementById("idPathApple").value, 
                    width:1, 
                    height:1
                };
                var parameters = {
                    jnlp_href: document.getElementById("idPathJNLPApple").value, 
                    cache_option:'plugin', 
                    disable_logging:'false', 
                    initial_focus:'false',
                    java_status_events: 'true', 
                    permissions:'sandbox'
                };
                if (deployJava.versionCheck("1.7+") == true) {}
                else if (deployJava.versionCheck("1.6+") == true) {
                    delete parameters['jnlp_href'];
                }
                console.log("runApplet-init");
                deployJava.runApplet(attributes, parameters, '1.7.0');
                console.log("runApplet-end");
            }else{
                console.log("El navegador es movil, no se carga el componente java!");
            }
	},
        
        isMovil :  function (){  
            try {
                var value = navigator.userAgent||navigator.vendor||window.opera;
                if(/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|meego.+mobile|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(value)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(value.substr(0,4))){  
                    return true;
                }else{
                    return false;
                }
            }catch (e){
                return true;
            }
        },
        
        /**
	* Automatically gets called when applet has loaded.
	*/
	qzReady : function () {
            console.log("qzReady-init");
            window["qz"] = document.getElementById('qz');
            if (qz) {
		console.log("qzReady-ok");	
            }else{
                console.log("qzReady-not yet");
            }
	},
        
        /**
	* Returns whether or not the applet is not ready to print.
	* Displays an alert if not ready.
	*/
	notReady : function() {
		// If applet is not loaded, display an error
		if (!customQZprint.isLoaded()) {
                    return true;
		}
		return false;
	},
        
        /**
	* Returns is the applet is not loaded properly
	*/
	isLoaded : function () {
            if (typeof(qz) === 'undefined' || !qz) {
                console.log('Error:\n\n\tPrint plugin is NOT loaded!');
		return false;
            } else {
                try {
                    if (!qz.isActive()) {
                        console.log('Error:\n\n\tPrint plugin is loaded but NOT active!');
			return false;
                    }
                } catch (err) {
                    console.log('Error:\n\n\tPrint plugin is NOT loaded properly!');
                    alert("El plugin de impresión no cargo correctamente. Presione las teclas Ctrl + F5.");
                    return false;
		}
            }
            return true;
	},
        
        /**
	* Automatically gets called when "qz.print()" is finished.
	*/
	qzDonePrinting : function() {
		// Alert error, if any
		if (qz.getException()) {
                    alert('Error printing:\n\n\t' + qz.getException().getLocalizedMessage());
                    qz.clearException();
                    return; 
		}
		
		// Alert success message
		alert('Successfully sent print data to "' + qz.getPrinter() + '" queue.');
	},
        
        findDefaultPrinter : function(successCallback) {
            if (customQZprint.isLoaded()) {
		qz.findPrinter();
		window['qzDoneFinding'] = function() {
                    var printer = qz.getPrinter();
                    if(printer !== null){
                        console.log('Default printer found: "' + printer + '".');
                    }else{
                        console.log('Default printer not found');
                    }
                    window['qzDoneFinding'] = null;
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback();
                    }
		};
            }
	},
        
        findPrinter : function (name, successCallback) {
            console.log("Finding Printer " + name + "...");
            if (customQZprint.isLoaded()) {
                if(!name){
                    customQZprint.findDefaultPrinter(successCallback);
                    return;
                }else{
                    qz.findPrinter(name);
                    window['qzDoneFinding'] = function() {

                        var printer = qz.getPrinter();
                        // Alert the printer name to user
                        if(printer !== null){
                            console.log('Printer found: "' + printer +  '".');
                        }else{
                            console.log('Printer "' + name + '" not found.');
                        }
                        // Remove reference to this function
                        window['qzDoneFinding'] = null;
                        console.debug(successCallback);
                        if(successCallback && $.isFunction(successCallback)){
                            successCallback();
                        }
                    };    
                }
            }
	},
        
        findPrinters : function(successCallback) {
            if (customQZprint.isLoaded()) {
                // Searches for a locally installed printer with a bogus name
		qz.findPrinter('\\{bogus_printer\\}');
		window['qzDoneFinding'] = function() {
                    var listaImpresoras = qz.getPrinters();
                    var printers = listaImpresoras.split(',');
                    for (i in printers) {
                        console.log(printers[i] ? printers[i] : 'Unknown');      
                    }
                    window['qzDoneFinding'] = null;
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback(listaImpresoras);
                    }
		};
            }
	},
        
        /***************************************************************************
	* Prototype function for printing raw ESC/POS commands
	* Usage:
	*    qz.append('\n\n\nHello world!\n');
	*    qz.print();
	***************************************************************************/
	printESCPTest : function (name, data, successCallback) {
            
            if (customQZprint.notReady()) { return; }
            
            customQZprint.findPrinter(name, function(){
                console.debug("printer loader");
                qz.setEncoding("UTF-8");
                qz.append("\x1B\x40");      //Init
                qz.append("\x1B\x6B\x01");  //Font Sans serif
                qz.append("\x1B\x4D");      //12 cpi
                
//                qz.append("\x1B\x43\x20");  //20 lineas
//                qz.append("\x1B\x43\x10");  //Cantidad de lineas
//                qz.append("\x1B\x61\x00");  //Alineacion izquierda (00-izquierda, 01-centrada, 02-derecha)
                
                if(!data){
                    data = 'Hello world!';
                }
                
                qz.append(data);
                
//                qz.append("\x1C\x0C"); //next page

                console.log("printing-init");
                qz.print();
                console.log("printing-end");
                if(successCallback && $.isFunction(successCallback)){
                    successCallback();
                }
            });
	},
        
        _getSizeHex : function (responseText){
            var idtamanoimpresion = core.getValueFromResponse(responseText, "idtamanoimpresion");
            if(!idtamanoimpresion){
                throw new Error("No se pudo obtener el id de tamano de impresion del response.");
            }
            if(idtamanoimpresion === "1" || idtamanoimpresion === 1){
                return '\x1B'+'\x43'+'\x21';
            }else if(idtamanoimpresion === "2" || idtamanoimpresion === 2){
                return '\x1B'+'\x43'+'\x21';
            }else if(idtamanoimpresion === "3" || idtamanoimpresion === 3){
                return '\x1B'+'\x43'+'\x16';
            }else if(idtamanoimpresion === "4" || idtamanoimpresion === 4){
                return '\x1B'+'\x43'+'\x21';
            }else{
                return '\x1B'+'\x43'+'\x21';
            }
        },
        
        printESCP : function (responseText, successCallback) {
            
            if (customQZprint.notReady()) { return; }
            
            var impresora = core.getValueFromResponse(responseText, "impresora");
            if(!impresora){
                if(successCallback && $.isFunction(successCallback)){
                    successCallback(responseText);
                }
                return;
            }
            var pathImpresora = escape(impresora).replace(new RegExp("%5C%5C", "g"), '%5C');
            pathImpresora = pathImpresora.replace(new RegExp("%20", "g"), ' ');
            var nameImpresora = pathImpresora.substr(pathImpresora.indexOf("%5C", 1)+3); 
            
            customQZprint.findPrinter(nameImpresora, function(){
                console.debug("impresora cargada");
                var container = core.getElementFromResponse(responseText, "container", "div");
                if(!container){
                    throw new Error("No se pudo obtener los datos a imprimir del response.");
                }
                var data = container.innerHTML;
                if(!data){
                    data = 'Hello world!';
                }
                
                qz.setEncoding("UTF-8");
                qz.append("\x1B\x40");          //Init
                
                qz.append("\x1B\x52\x0C");      //Idioma Lat America
                qz.append("\x1B\x6B\x01");      //Font Sans serif
                qz.append("\x1B\x21\x04");      //Texto Comprimido
                
                qz.append(customQZprint._getSizeHex(responseText));
                qz.append("\x1B\x50");          //12 cpi
                
                var count = (data.match(/<hex>/g) || []).length;
                if(count % 2 !== 0){
                    alert("Error en los datos a imprimir.");
                }
                
                var items = data.split("|HEX|");
                $.each(items, function( index, item ){
                    if(index % 2 === 0){
                        qz.append(item);
                    }else{
                        qz.appendHex(item);
                    }
                });
                
                console.log("printing-init");
                qz.print();
                console.log("printing-end");
                
                if(successCallback && $.isFunction(successCallback)){
                    successCallback();
                }
            });
	},
        
        /***************************************************************************
	* Prototype function for printing a text or binary file containing raw 
	* print commands.
	* Usage:
	*    qz.appendFile('/path/to/file.txt');
	*    window['qzDoneAppending'] = function() { qz.print(); };
	***************************************************************************/ 
	printFile : function(name, file, successCallback) {
            
            if (customQZprint.notReady()) { return; }
		
            customQZprint.findPrinter(name, function(){
                console.debug("printer loader");
                qz.appendFile(customQZprint.getPath() + file);
                window['qzDoneAppending'] = function() {
                    console.log("printing-init");
                    qz.print();
                    console.log("printing-end");
                    window['qzDoneAppending'] = null;
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback();
                    }
		};
            });	
	},
        
        /***************************************************************************
	* Prototype function for printing a PDF to a PostScript capable printer.
	* Not to be used in combination with raw printers.
	* Usage:
	*    qz.appendPDF('/path/to/sample.pdf');
	*    window['qzDoneAppending'] = function() { qz.printPS(); };
	***************************************************************************/ 
	printPDF : function(name, file, successCallback) {
            
            if (customQZprint.notReady()) { return; }
            
            customQZprint.findPrinter(name, function(){
                console.debug("printer loader");
                qz.appendPDF(customQZprint.getPath() + file);
                window['qzDoneAppending'] = function() {
                    console.log("printing-init");
                    qz.printPS();
                    console.log("printing-end");
                    window['qzDoneAppending'] = null;
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback();
                    }
		};
            });
	},
        
        /***************************************************************************
	* Prototype function for printing plain HTML 1.0 to a PostScript capable 
	* printer.  Not to be used in combination with raw printers.
	* Usage:
	*    qz.appendHTML('<h1>Hello world!</h1>');
	*    qz.printPS();
	***************************************************************************/ 
	printHTML : function(name, data, successCallback) {
	    if (customQZprint.notReady()) { return; }
            
            customQZprint.findPrinter(name, function(){
                console.debug("printer loader");
                if(!data){
                    data = "<html>" + 'No data...!' + "</html>";
                }else if(data.indexOf("<html>") === -1){
                    data = "<html><body>" + data + "<body></html>";
                }
                data = data.replace(/ /g, "&nbsp;").replace(/'/g, "'").replace(/-/g,"&#8209;");
                qz.appendHTML(data);
                console.log("printing-init");
                qz.setCopies(1);
                qz.printHTML();
                console.log("printing-end");
                if(successCallback && $.isFunction(successCallback)){
                    successCallback();
                }
            });
	},
        
        /***************************************************************************
	* Prototype function for getting the primary IP or Mac address of a computer
	* Usage:
	*    qz.findNetworkInfo();
	*    window['qzDoneFindingNetwork'] = function() {alert(qz.getMac() + ',' +
	*       qz.getIP()); };
	***************************************************************************/ 
	listNetworkInfo : function() {
            if (customQZprint.isLoaded()) {
                qz.findNetworkInfo();
                window['qzDoneFindingNetwork'] = function() {
                    console.log("Primary adapter found: " + qz.getMac() + ", IP: " + qz.getIP());		
                    window['qzDoneFindingNetwork'] = null;
		};
            }
	},
        
        getPath : function() {
            var path = window.location.href;
            return path.substring(0, path.lastIndexOf("/")) + "/";
	},
	
	/**
	* Fixes some html formatting for printing. Only use on text, not on tags!
	* Very important!
	*   1.  HTML ignores white spaces, this fixes that
	*   2.  The right quotation mark breaks PostScript print formatting
	*   3.  The hyphen/dash autoflows and breaks formatting  
	*/
	fixHTML : function (html) {
            return html.replace(/ /g, "&nbsp;").replace(/’/g, "'").replace(/-/g,"&#8209;"); 
	},
	
	/**
	* Equivelant of VisualBasic CHR() function
	*/
	chr : function(i) {
            return String.fromCharCode(i);
	}
};

customQZprint.deployQZ();


