frondendClientes = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        
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
                    {display: 'Nombre Completo', name : 'nombre', width : 200, sortable : true, align: 'left'},
                    {display: 'NIT', name : 'nit', width : 120, sortable : true, align: 'left'},
                    {display: 'Documento', name : 'documento', width : 120, sortable : true, align: 'left'},
                    {display: 'Nacionalidad', name : 'nacionalidad', width : 90, sortable : false, align: 'left'},
                    {display: 'Nacimiento', name : 'fechaNacimiento', width : 100, sortable : false, align: 'center'},
                    {display: 'Vcto Documento', name : 'fechaNacimiento', width : 100, sortable : false, align: 'center'},
                    {display: 'Sexo', name : 'sexo', width : 50, sortable : false, align: 'center'},
                    {display: 'Empleado', name : 'empleado', width : 50, sortable : false, align: 'center'}              
                    ],
            usepager: true,
            useRp: true,
            rp: 15,
            showTableToggleBtn: false
        });
        // -------------------  GRID END ------------------
        
     },
     
    _conectEvents : function() {
        
        $("#linkFilter").click(function(e) {
            frondend.linkFilter(e, $(this), $(".clienteGrid") ,$(".clienteFiltersForm"));
        });
        $("li a.menuClientes").not("a[data-print=true]").click(frondend.loadSubPage);
        
    }
    
    
};
