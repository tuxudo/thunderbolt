<div id="thunderbolt-tab"></div>
<h2 data-i18n="thunderbolt.clienttab"></h2>

<div id="thunderbolt-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

<script>
$(document).on('appReady', function(){
	$.getJSON(appUrl + '/module/thunderbolt/get_data/' + serialNumber, function(data){
        
        // Check if we have data
        if( data == "" || ! data){
            $('#thunderbolt-msg').text(i18n.t('thunderbolt.nothunderbolt'));
        } else {

            // Hide
            $('#thunderbolt-msg').text('');
            $('#thunderbolt-count-view').removeClass('hide');
        
            // Set count of thunderbolt devices
            $('#thunderbolt-cnt').text(data.length);
            var skipThese = ['id','name'];
            $.each(data, function(i,d){

                // Generate rows from data
                var rows = ''
                for (var prop in d){
                    // Skip skipThese
                    if(skipThese.indexOf(prop) == -1){
                        if (d[prop] == null || d[prop] == ""){
                            // Do nothing for the nulls to blank them
                        } else {
                            rows = rows + '<tr><th>'+i18n.t('thunderbolt.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                        }
                    }
                }
                $('#thunderbolt-tab')
                    .append($('<h4>')
                        .append($('<i>')
                            .addClass('fa fa-bolt'))
                        .append(' '+d.name))
                    .append($('<div style="max-width:390px;">')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(rows))))
            })
        }
    });
});
</script>
