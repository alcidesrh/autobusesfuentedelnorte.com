frondendAutorizacionCortesia = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendAutorizacionCortesia.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendAutorizacionCortesia.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        $('#fechaCreacion').datepicker({
            format: "dd/mm/yyyy",
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
                    {display: 'ID', name : 'id', width : 50, sortable : false, align: 'center'},
                    {display: 'Servicio', name : 'servicio', width : 90, sortable : false, align: 'left'},
                    {display: 'Fecha Creación', name : 'fecha', width : 120, sortable : false, align: 'center'},
                    {display: 'PIN', name : 'codigo', width : 120, sortable : false, align: 'center'},
                    {display: 'Cliente', name : 'cliente', width : 200, sortable : false, align: 'left'},
                    {display: 'Motivo', name : 'motivo', width : 500, sortable : false, align: 'left'}
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
        $("li a.menuAutorizacionCortesia").not("a[data-print=true]").click(frondend.loadSubPage);
        
    }
    
    
};
