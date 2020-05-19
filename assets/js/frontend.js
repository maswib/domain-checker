jQuery(document).ready(function($) {
    "use strict";
    
    $(document).on('click', '#dc-domain-check-button', function(){
        var t = $(this);
        var original_button = t.text();
        
        var tlds = [];
        
        $('.dc-tld').each(function(){
            if ($(this).is(':checked')) {
                tlds.push($(this).val());
            }
        });
        
        $('#dc-tlds').val(tlds);
        
        var data = { 
            action      : 'dc_check_domain',
            nonce       : Domain_Checker.nonce,
            domain_name : $('#dc-domain-name').val(),
            tlds        : $('#dc-tlds').val()
        };
        
        t.text(Domain_Checker.checking);
        t.attr('disabled', 'disabled');
        
        $.post(Domain_Checker.ajaxurl, data, function(res){
            if (res.success) {
                $('#dc-result').html(res.data.content);
            } else {
                console.log(res);
            }
            
            t.text(original_button);
            t.removeAttr('disabled');
        }).fail(function(xhr, textStatus, e) {
            console.log(xhr.responseText);
        });
        
        return false;
    });
    
});