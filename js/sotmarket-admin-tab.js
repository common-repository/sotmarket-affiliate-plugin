jQuery(document).ready(function() {


    jQuery('#sotmarket-info-template').change(function(){
        setInfoTag();
    });
    jQuery('#sotmarket-info-viewtype').change(function(){
        setInfoTag();
    });
    jQuery('#sotmarket-info-image-size').change(function(){
        setInfoTag();
    });

    jQuery('#sotmarket-info-ids').change(function(){
        setInfoTag();
    });
    jQuery('#sotmarket-info-subref').change(function(){
        setInfoTag();
    });


    function setInfoTag(){
        var sTag = '['+ jQuery('#sotmarket-info-template').val() + '_' +
            jQuery('#sotmarket-info-viewtype').val() + '_' +
            jQuery('#sotmarket-info-image-size').val() + '_' +
            jQuery('#sotmarket-info-ids').val() + '__' +
            jQuery('#sotmarket-info-subref').val();

            sTag = sTag + ']';
        jQuery('#sotmarket-info-tag').html(sTag);
    }

    jQuery('#sotmarket-analog-template').change(function(){
        setAnalogTag();
    });
    jQuery('#sotmarket-analog-cnt').change(function(){
        setAnalogTag();
    });
    jQuery('#sotmarket-analog-viewtype').change(function(){
        setAnalogTag();
    });
    jQuery('#sotmarket-analog-image-size').change(function(){
        setAnalogTag();
    });
    jQuery('#sotmarket-analog-ids').change(function(){
        setAnalogTag();
    });
    jQuery('#sotmarket-analog-subref').change(function(){
        setAnalogTag();
    });

    function setAnalogTag(){
        var sTag = '['+ jQuery('#sotmarket-analog-template').val() + '_' +
            jQuery('#sotmarket-analog-cnt').val() + '_' +
            jQuery('#sotmarket-analog-viewtype').val() + '_' +
            jQuery('#sotmarket-analog-image-size').val() + '_' +
            jQuery('#sotmarket-analog-ids').val() + '_' +
            jQuery('#sotmarket-analog-subref').val();

        sTag = sTag + ']';
        jQuery('#sotmarket-analog-tag').html(sTag);
    }

    jQuery('#sotmarket-related-template').change(function(){
        setRelatedTag();
    });
    jQuery('#sotmarket-related-cnt').change(function(){
        setRelatedTag();
    });
    jQuery('#sotmarket-related-cats').change(function(){
        setRelatedTag();
    });
    jQuery('#sotmarket-related-image-size').change(function(){
        setRelatedTag();
    });
    jQuery('#sotmarket-related-viewtype').change(function(){
        setRelatedTag();
    });

    jQuery('#sotmarket-related-ids').change(function(){
        setRelatedTag();
    });
    jQuery('#sotmarket-related-subref').change(function(){
        setRelatedTag();
    });


    function setRelatedTag(){
        var sTag = '['+ jQuery('#sotmarket-related-template').val() + '_' +
            jQuery('#sotmarket-related-cnt').val() + '_' +
            jQuery('#sotmarket-related-viewtype').val() + '_' +
            jQuery('#sotmarket-related-image-size').val() + '_' +
            jQuery('#sotmarket-related-cats').val() + '_' +
            jQuery('#sotmarket-related-ids').val()  + '_' +
            jQuery('#sotmarket-related-subref').val();


            sTag = sTag + ']';

        jQuery('#sotmarket-related-tag').html(sTag);
    }

    jQuery('#sotmarket-popular-template').change(function(){
        setPopularTag();
    });
    jQuery('#sotmarket-popular-cnt').change(function(){
        setPopularTag();
    });
    jQuery('#sotmarket-popular-cats').change(function(){
        setPopularTag();
    });
    jQuery('#sotmarket-popular-image-size').change(function(){
        setPopularTag();
    });
    jQuery('#sotmarket-popular-viewtype').change(function(){
        setPopularTag();
    });

    jQuery('#sotmarket-popular-brand-id').change(function(){
        setPopularTag();
    });
    jQuery('#sotmarket-popular-subref').change(function(){
        setPopularTag();
    });


    function setPopularTag(){
        var sTag = '['+ jQuery('#sotmarket-popular-template').val() + '_' +
            jQuery('#sotmarket-popular-cnt').val() + '_' +
            jQuery('#sotmarket-popular-viewtype').val() + '_' +
            jQuery('#sotmarket-popular-image-size').val() + '_' +
            jQuery('#sotmarket-popular-cats').val() + '_' +
            jQuery('#sotmarket-popular-brand-id').val()  + '_' +
            jQuery('#sotmarket-popular-subref').val();


        sTag = sTag + ']';

        jQuery('#sotmarket-popular-tag').html(sTag);
    }






});
