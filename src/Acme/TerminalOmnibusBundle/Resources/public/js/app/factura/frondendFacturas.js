frondendFacturas = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendCajas.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendCajas.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        $('#fechaEmision').datepicker({
            format: "dd/mm/yyyy",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        $('#fechaVencimiento').datepicker({
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
                    {display: 'Serie', name : 'serieValor', width : 100, sortable : false, align: 'center'},
                    {display: 'Sigla', name : 'serie', width : 60, sortable : false, align: 'center', hide: true},
                    {display: 'Valor', name : 'valor', width : 80, sortable : false, align: 'center', hide: true},
                    {display: 'Servicio', name : 'servicio', width : 85, sortable : false, align: 'left'},
                    {display: 'Estación', name : 'estacion', width : 150, sortable : false, align: 'left'},
                    {display: 'Empresa', name : 'empresa', width : 100, sortable : false, align: 'left'},
                    {display: 'Mínimo', name : 'minimo', width : 80, sortable : false, align: 'center'},
                    {display: 'Máximo', name : 'maximo', width : 80, sortable : false, align: 'center'},
                    {display: 'Emisión', name : 'fechaEmision', width : 100, sortable : true, align: 'center'},
                    {display: 'Vencimiento', name : 'fechaVencimiento', width : 100, sortable : true, align: 'center'},
                    {display: 'Activo', name : 'activo', width : 50, sortable : false, align: 'center'},
                    {display: 'Impresora', name : 'impresora', width : 100, sortable : false, align: 'left'}
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
        $("li a.menuFacturas").not("a[data-print=true]").click(frondend.loadSubPage);
        
    }
    
    
};
