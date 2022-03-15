Components.utils.import("resource://gre/modules/XPCOMUtils.jsm");

function PrintHelp() { 
    this.wrappedJSObject = this;
}

PrintHelp.prototype = {
  classDescription: "Print Help Javascript XPCOM Component",
  classID:          Components.ID("{00000000-0000-0000-0000-000000000000}"),
  contractID:       "@fuentedelnorte.com/printhelp;1",
//  QueryInterface: XPCOMUtils.generateQI([Components.interfaces.nsIPrintHelp]),
  print: function(dirFoxit, impresora, url) { 
       
        Components.utils.import("resource://gre/modules/Downloads.jsm");
        Components.utils.import("resource://gre/modules/osfile.jsm");
        Components.utils.import("resource://gre/modules/Task.jsm");

        var nameFile = "amigos1ddd.pdf";

        Task.spawn(function () {

//          console.log("descargando file.");
//          console.debug(impresora);
//          console.debug(url);
//          console.debug(nameFile);
          var tempDir = OS.Path.join(OS.Constants.Path.tmpDir, nameFile);
//          console.debug(tempDir);
          var path = url + nameFile;
//          console.debug(path);

          try {
            Downloads.fetch(path, tempDir);
//            console.log("descarga satisfactoria.");
          } catch (ex if ex instanceof Downloads.Error && ex.becauseTargetFailed) {
//              console.log("Unable to write to the target file, ignoring the error.");
          }

//          console.log("Chequeando fichero....");
//          try {
//            let info = yield OS.File.stat(tempDir);
//            console.log("The file is " + info.size + " bytes long.");
//          } catch (ex if ex instanceof OS.File.Error && ex.becauseNoSuchFile) {
//            console.log("El fichero " + tempDir + " no existe.");
//          }

          try{
//              console.log("imprimiendo init.");
              var file = Components.classes["@mozilla.org/file/local;1"].createInstance(Components.interfaces.nsILocalFile);
              file.initWithPath(dirFoxit);
              var process = Components.classes["@mozilla.org/process/util;1"].createInstance(Components.interfaces.nsIProcess);
              process.init(file);
              var args = ["/t", tempDir, impresora];
              process.run(false, args, args.length);
//              console.log("imprimiendo end.");
          }catch (ex) {
//              console.log("Error intentando imprimir.");
//              console.debug(e);
          }

        }).then(null, Components.utils.reportError);
  }
};

if ("generateNSGetFactory" in XPCOMUtils)
  var NSGetFactory = XPCOMUtils.generateNSGetFactory([PrintHelp]);  // Firefox 4.0 and higher
else
  var NSGetModule = XPCOMUtils.generateNSGetModule([PrintHelp]);    // Firefox 3.x
  
  
/*
component {00000000-0000-0000-0000-000000000000} components/PrintHelp.js
contract @fuentedelnorte.com/printhelp;1 {00000000-0000-0000-0000-000000000000}
category profile-after-change PrintHelp @fuentedelnorte.com/printhelp;1
*/

