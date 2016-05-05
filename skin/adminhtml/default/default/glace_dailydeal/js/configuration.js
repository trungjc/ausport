

window.onload = function() {
    /*
    if ($('dailydeal_general_schemecolor')) {
        var value_cp1 = $('dailydeal_general_schemecolor').value;
        var cp1 = new colorPicker('dailydeal_general_schemecolor', {color :value_cp1});
    }
    if ($('dailydeal_general_countdowncolor')) {
        var value_cp2 = $('dailydeal_general_countdowncolor').value;
        var cp2 = new colorPicker('dailydeal_general_countdowncolor', {color :value_cp2});
    }
    if ($('dailydeal_general_highlight_color')) {
        var value_cp3 = $('dailydeal_general_highlight_color').value;
        var cp3 = new colorPicker('dailydeal_general_highlight_color', {color :value_cp3});
    }
    */
}

function get_messages(){
    
    if ($('dailydeal_general_deal_qty_on_product_page')) {
        $('dailydeal_general_deal_qty_on_product_page').value = 'Hurry, there are only <span class="deal-qty">{{qty}} items</span> left!';
    }
    
    if ($('dailydeal_general_deal_qty_on_catalog_page')) {
        $('dailydeal_general_deal_qty_on_catalog_page').value = 'Hurry, just <span class="deal-qty">{{qty}} items</span> left!';
    }
}