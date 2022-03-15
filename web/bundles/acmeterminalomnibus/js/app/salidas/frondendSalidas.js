frondendSalidas = {
    
    timeRefreshGrid : 5, //segundos
    inAction : false,
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        var diaActual =  new Date();
        $('#rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            startDate: diaActual,
            endDate: diaActual,
            dateLimit : moment.duration(1, 'years')
        });
        $('#rangoFecha').data('daterangepicker').clickApply();
        // -------------------  FILTROS END ------------------
        // -------------------  GRID INIT ------------------
        $("#grid").flexigrid({
            url: $("#grid").data("url"),
            dataType: 'json',
            singleSelect: true,
            query: "",
            rpOptions: [5, 10, 15, 20, 25, 30, 40, 50],
            colModel : [
                    {display: 'ID', name : 'id', width : 70, sortable : true, align: 'center'},
                    {display: 'Fecha y Hora', name : 'fecha', width : 120, sortable : true, align: 'center'},
                    {display: 'Itinerario', name : 'idItinerario', width : 60, sortable : false, hide : true, align: 'center'},
                    {display: 'Cíclico', name : 'ciclico', width : 70, sortable : false, align: 'center'},
                    {display: 'Empresa', name : 'empresa', width : 100, sortable : false, align: 'left'},
                    {display: 'Origen', name : 'origen', width : 120, sortable : false, align: 'left'},
                    {display: 'Destino', name : 'destino', width : 120, sortable : false, align: 'left'},
                    {display: 'Estado', name : 'estado', width : 80, sortable : true, align: 'center'},
                    {display: 'Bus', name : 'bus', width : 80, sortable : true, align: 'center'},
                    {display: 'Piloto1', name : 'piloto1', width : 90, sortable : false, align: 'center'},
                    {display: 'Piloto2', name : 'piloto2', width : 90, sortable : false, align: 'center'},
                    {display: 'Tarjeta', name : 'tarjeta', width : 80, sortable : true, align: 'center'},
                    {display: 'Tipo de Bus', name : 'tipoBus', width : 200, sortable : true, align: 'left'},
                    {display: 'Clase', name : 'claseBus', width : 120, sortable : false, align: 'left'}
                    ],
            usepager: true,
            useRp: true,
            rp: 15,
            height: 300,
            showTableToggleBtn: false
        });
        // -------------------  GRID END ------------------
        
        if(core.checkInstalljsPrintSetup()){
            jsPrintSetup.setSilentPrint(false);
        }
     },
     
    _conectEvents : function() {
        
        $("#linkFilter").click(frondend.linkFilter);
        $("li a.menuSalida").click(frondend.loadSubPage);   

    }
    
    
};
