frondendReservacion = {
    
    funcionesAddOnload : function() {
//        console.debug("frondendReservacion.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
//	console.debug("frondendReservacion.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------
        $('#rangoFecha').daterangepicker({ 
            format: 'DD/MM/YYYY',
            startDate: core.modDate(-30),
            endDate: core.modDate(+30),
            dateLimit : moment.duration(6, 'months')
        });
        $('#rangoFecha').data('daterangepicker').clickApply();
        
        var pathlistarclientespaginando = $("#cliente").data("pathlistarclientespaginando");
         $("#cliente").select2({
            minimumInputLength: 1,
            allowClear: true,
            ajax: { 
                url: pathlistarclientespaginando,
                dataType: 'json',
                type: "POST",
                data: function (term, page) {
                    return {term: term, page_limit: 5};
                },
                results: function (data, page) {
                    return {results: data.options};
                }
            }
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
                    {display: 'ID', name : 'id', width : 60, sortable : true, align: 'center'},
                    {display: 'Fecha Salida', name : 'fecha', width : 110, sortable : true, align: 'center'},
                    {display: 'Número', name : 'numeroAsiento', width : 40, sortable : false, align: 'center'},
                    {display: 'Ruta', name : 'ruta', width : 200, sortable : false, align: 'left'},
                    {display: 'Cliente', name : 'cliente', width : 200, sortable : false, align: 'left'},
                    {display: 'Externa', name : 'externa', width : 40, sortable : false, align: 'center'},
                    {display: 'Fecha Creación', name : 'fechaCreacion', width : 100, sortable : false, align: 'center'},
                    {display: 'Usuario Creación', name : 'usuarioCreacion', width : 100, sortable : false, align: 'left'},
                    {display: 'Estación Creación', name : 'estacionCreacion', width : 100, sortable : false, align: 'left'}
                    ],
            usepager: true,
            useRp: true,
            rp: 15,
            height: 300,
            showTableToggleBtn: false
        });
        // -------------------  GRID END ------------------
        
     },
     
    _conectEvents : function() {
        
        $("#linkFilter").click(frondend.linkFilter);
        $("li a.menuReservacion").not("a[data-print=true]").click(frondend.loadSubPage);
        
    }
    
    
};
