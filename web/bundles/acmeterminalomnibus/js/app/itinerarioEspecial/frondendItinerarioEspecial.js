frondendItinerarioEspecial = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendItinerarioEspecial.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendItinerarioEspecial.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        $('#fechaEnd').datepicker({
            format: "dd/mm/yyyy",
             startDate: "d",
            endDate: "+2m",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
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
                    {display: 'Fecha', name : 'fecha', width : 120, sortable : true, align: 'center'},
                    {display: 'Ruta', name : 'ruta', width : 50, sortable : true, align: 'left'},
                    {display: 'Empresa', name : 'empresa', width : 100, sortable : false, align: 'left'},
                    {display: 'Origen', name : 'origen', width : 150, sortable : false, align: 'left'},
                    {display: 'Destino', name : 'destino', width : 150, sortable : false, align: 'left'},
                    {display: 'Tipo Bus', name : 'tipoBus', width : 180, sortable : false, align: 'left'},
                    {display: 'Activo', name : 'activo', width : 70, sortable : true, align: 'center'},
                    {display: 'Motivo', name : 'motivo', width : 200, sortable : true, align: 'left'}
                    
                    ],
            usepager: true,
            useRp: true,
            rp: 15,
            showTableToggleBtn: false
        });
        // -------------------  GRID END ------------------
        
     },
     
    _conectEvents : function() {
        
        $("#linkFilter").click(frondend.linkFilter);
        $("li a.menuItinerarioEspecial").not("a[data-print=true]").click(frondend.loadSubPage);
        
    }
    
    
};
