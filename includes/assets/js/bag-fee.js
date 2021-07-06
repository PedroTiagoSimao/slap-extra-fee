function valueChanged() {
    if(jQuery('.is_taxable').is(":checked"))   
    jQuery(".tax_class").show();
    else jQuery(".tax_class").hide();
}

(function(d, $) {
    if($('.is_taxable').is(":checked"))   
    $(".tax_class").show();
    else $(".tax_class").hide();
})(document, jQuery);