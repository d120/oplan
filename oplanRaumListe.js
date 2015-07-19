
var module = angular.module("oplanRaumListe", ["angularGrid"]);
module.config(function($locationProvider) {
    //$locationProvider.html5Mode(true);
});
module.controller("OplanRaumListeCtrl", function($scope, $http, $location, $filter) {
    var columnDefs = [
        {headerName: "Raum-Nummer", field: "raum_nummer", editable: true, width:120},
        {headerName: "Tag", field: "von_day", editable: true, width: 80},
        {headerName: "von", field: "von_time", editable: true, width: 80},
        {headerName: "bis", field: "bis_time", editable: true, width: 80},
        {headerName: "Status", field: "status", editable: true, width: 80},
        {headerName: "Kommentar", field: "kommentar", editable: true, width: 190},
        {headerName: "Belegt", field: "belegt", editable: false, width: 260, cellClicked: onClick, cellClass: 'linkstyle'}
    ];

    var frei = [];
    var rowData = [];

    $scope.gridOptions = {
        columnDefs: columnDefs,
        rowData: [],
        angularCompileRows: true,
        enableSorting: true,
        showToolPanel: true,
        groupKeys: ['von_day'],
        groupDefaultExpanded: true,
        groupUseEntireRow: true,
        enableColResize: true
    };
    
    $http.get("raum.php").success(function(result) {
        for(var k in result.frei) {
            if (!result.frei[k].belegt) result.frei[k].belegt = "(frei)";
        }
        $scope.gridOptions.rowData = result.frei;
        $scope.gridOptions.api.setSortModel([
            {field: 'raum_nummer', sort: 'asc'},
            {field: 'von_time', sort: 'asc'}
        ]);
        try {
            var opts = JSON.parse(window.localStorage.raumlisteViewOpts);
            $scope.gridOptions.groupKeys = opts.group;
            $scope.gridOptions.api.onNewCols();
            console.log($scope.gridOptions);
            $scope.gridOptions.api.setSortModel(opts.sort);
        }catch(ex){}
        $scope.gridOptions.api.onNewRows();
    });
    
    $scope.persistView = function() {
        window.localStorage.raumlisteViewOpts = JSON.stringify({
            sort: $scope.gridOptions.api.getSortModel(),
            group: $scope.gridOptions.api.getColumnState().filter(function(x) { return x.pivotIndex!==null; }).sort(function(a,b){return b.pivotIndex-a.pivotIndex;}).map(function(x){ return x.colId })
        });

    };

    function onClick(e) {
        $scope.$apply(function() {
            console.log(e);
            var week = moment(e.data.von).isoWeek();
            $location.path("/raumplan/" + e.data.raum_nummer).search("w", week);
        });
    }
});
module.controller("OplanTucanRaumListeCtrl", function($scope, $http) {
    
    $http.get("raum.php?order=tucan").success(function(result) {
        var data = {};
        result.frei.forEach(function(row) {
            var key = row.von_day + " " + row.von_time + " " + row.bis_time;
            if (!data[key]) data[key] = [];
            row.von = new Date(row.von);
            data[key].push(row);
        });
        $scope.data = data;
    });
});


