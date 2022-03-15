consultarDetalleSalida = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        $('#fecha').datepicker({
            format: "dd/mm/yyyy",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        $('#fecha').datepicker("setDate", new Date());
        
        consultarDetalleSalida.changeFecha();
     },
     
    _conectEvents : function() {
        
        $('#fecha').datepicker().on('changeDate', function (e) {
            e.preventDefault();
            e.stopPropagation();
            consultarDetalleSalida.changeFecha();
        });
        $("#cancelar").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            core.getPageForMenu($("#cancelar").attr('href'));
        });
    },
    
    changeFecha : function() {
        console.log("changeFecha-int");
        $("#salidaBody").find("tr").not("#salidaVacioTR").remove(); //Elimino todos los tr
        $("#salidaBody").find("#salidaVacioTR").show(); //Muestro el vacio
        var fecha = $("#fecha").val();
        if(fecha === null || $.trim(fecha) === ""){
            return;
        }else{
            core.request({
                url : $("#pathListarSalidasByFecha2").val(),
                type: "GET",
                dataType: "json",
                async: false,
                extraParams : {
                    'fecha':fecha
                },
                successCallback: function(data){
                    console.debug(data);
                    if(data && data.optionSalidas.length > 0){
                        $("#salidaBody").find("#salidaVacioTR").hide();
                        $.each(data.optionSalidas, function (i, item) {
                            var estado = "success";
                            if(item.idEstado === "1" || item.idEstado === "4"){
                                estado = "error";
                            }else if(item.idEstado === "2"){
                                estado = "warning";
                            }
                            var itemTR =    "<tr class='"+estado+"'>"+
                                            "<td style='text-align:center; vertical-align:middle;'>"+item.id+"</td>"+
                                            "<td style='text-align:center; vertical-align:middle;'>"+item.hora+"</td>"+
                                            "<td style='text-align:left; vertical-align:middle;'>"+item.ruta+"</td>"+
                                            "<td style='text-align:center; vertical-align:middle;'>"+item.clase+"</td>"+
                                            "<td style='text-align:center; vertical-align:middle;'>"+item.empresa+"</td>"+
                                            "<td style='text-align:center; vertical-align:middle;'>"+item.estado+"</td>"+
                                            "<td style='text-align:center; vertical-align:middle;'>"+(item.bus === "" ? "N/D" : item.bus)+"</td>"+
                                            "<td style='text-align:left; vertical-align:middle;'>"+(item.piloto1 === "" ? "N/D" : item.piloto1)+(item.piloto2 === "" ? "" : "<BR>" + item.piloto2) +"</td>"+
                                            "</tr>";
                            $("#salidaBody").append($(itemTR));
                        });
                    }
                }
            });
        }
    }
};
