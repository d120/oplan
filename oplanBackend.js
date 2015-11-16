angular.module("oplanBackend", [])


.factory('oplanHttp', function($http) {
    return {
        doGet: function(apiName, params) {
            return $http.get(apiName + ".php?" + $.param(params));
        },
        
        doPost: function(url, data) {
            return $http({
                method: 'POST',
                url: url,
                data: $.param(data),
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            })
        },
        
        belegeRaum: function(nummer, slotId) {
            return this.doPost("slot.php?id="+slotId, 
              {apply: nummer});
        },
        
        updateSlot: function(slotId, data) {
            return this.doPost("slot.php?id="+slotId, 
              data);
        },
        
        moveSlot: function(slotId, start, end, moveAll) {
            return this.doPost("slot.php?id="+slotId, {
                von: start.format ? start.format("YYYY-MM-DD HH:mm:ss") : start, 
                bis: end.format ? end.format("YYYY-MM-DD HH:mm:ss") : end, 
                all: moveAll ? "true" : ""
            });
        },
        
        newSlot: function(start, end, desc, gruppe) {
            return this.doPost('slot.php', { 
                create_von: start.format ? start.format("YYYY-MM-DD HH:mm:ss") : start, 
                create_bis: end.format ? end.format("YYYY-MM-DD HH:mm:ss") : end,
                kommentar: desc,
                zielgruppe: gruppe
            });
        },
        
        deleteRaumFrei : function(id) {
          return this.doPost("raum.php", {"delete": "yes", "id": id});
        },
        
        setRaumFrei: function(id, nummer, von, bis, kom, status) {
            if (!von.format) von=moment(von);
            if (!bis.format) bis=moment(bis);
            var data = {
              raum_nummer:nummer,von:von.format("YYYY-MM-DD HH:mm:ss"), bis: bis.format("YYYY-MM-DD HH:mm:ss"),
              kommentar: kom, status: status
            };
            if (id) data.id = id;
            return this.doPost("raum.php", data);
        },
        
        listStundenplans: function() {
            return this.doGet("stundenplan", { "do" : "list" });
        }
      
    };
})


;