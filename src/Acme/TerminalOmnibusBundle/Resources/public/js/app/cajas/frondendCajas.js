frondendCajas = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendCajas.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendCajas.funcionesAddOnload-end");
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
        $('#fechaApertura').datepicker({
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
                    {display: 'ID', name : 'id', width : 70, sortable : true, align: 'center'},
                    {display: 'Moneda', name : 'moneda', width : 60, sortable : true, align: 'center'},
                    {display: 'Estado', name : 'estado', width : 100, sortable : true, align: 'center'},
                    {display: 'Fecha Creación', name : 'fechaCreacion', width : 100, sortable : true, align: 'center'},
                    {display: 'Fecha Apertura', name : 'fechaApertura', width : 100, sortable : true, align: 'center'},
                    {display: 'Usuario', name : 'usuario', width : 200, sortable : false, align: 'left'},
                    {display: 'Estación', name : 'estacion', width : 150, sortable : false, align: 'left'}
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
        $("li a.menuCajas").not("a[data-print=true]").click(frondend.loadSubPage);
        
    }
    
    
};
