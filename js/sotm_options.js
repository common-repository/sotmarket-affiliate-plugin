jQuery(document).ready(function() {
    jQuery("#SOTMARKET_CPA_LINK_BEGIN").change(function(){
        setLinkTest();
    });
    jQuery("#SOTMARKET_CPA_LINK_END").change(function(){
        setLinkTest();
    });

    jQuery("#SOTMARKET_CPA_LINK_GET").change(function(){
        setLinkTest();
    });


});

function setLinkTest(){
    var test ="";
    if (jQuery("#SOTMARKET_CPA_LINK_BEGIN").val() != "" || jQuery("#SOTMARKET_CPA_LINK_END").val() != ""){
        test = jQuery("#SOTMARKET_CPA_LINK_BEGIN").val() + "http://www.sotmarket.ru/" + jQuery("#SOTMARKET_CPA_LINK_END").val();
    } else {
        test = "http://www.sotmarket.ru/?" + jQuery("#SOTMARKET_CPA_LINK_GET").val();
    }

    jQuery("#link_test").html(test);
}