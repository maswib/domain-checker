jQuery(document).ready(function($) {
    "use strict";
    
    $(document).on('click', '.calculate-btn', function(){
        var data = { 
            action         : 'wpsc_get_content',
            nonce          : Down_Payment_Calculator.nonce,
            categories     : $('.categories').val()
        };
        
        $.post(Down_Payment_Calculator.ajaxurl, data, function(res){
            if (res.success) {
                $('#block-calendar').html(res.data.content);
            } else {
                console.log(res);
            }
        }).fail(function(xhr, textStatus, e) {
            console.log(xhr.responseText);
        });
        
        return false;
    });
    
});