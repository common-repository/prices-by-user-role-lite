jQuery(document).ready(function() {
    jQuery('.tabs .tab-links a').on('click', function(e)  {
        var currentAttrValue = jQuery(this).attr('href');
        
        jQuery('.tab-content ' + currentAttrValue).show('slow').siblings().hide('slow');

        jQuery(this).parent('li').addClass('active').siblings().removeClass('active');

        e.preventDefault();
    });
    
    jQuery('.tab-content .upgrade-message a').on('click', function(e) {
        
        jQuery('.tab-content #tab_upgrade').show('slow').siblings().hide();
        
        e.preventDefault();
    });
});