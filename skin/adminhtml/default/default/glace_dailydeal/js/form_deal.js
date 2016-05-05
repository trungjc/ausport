Validation.add('custom-deal-qty', "Please enter a number greater than deal's sold qty and smaller than 'Product's Qty' in this field.", function(v) {
    var product_id =  document.getElementById("product_id");
    var product_qty =  parseInt(document.getElementById("product_qty").value) || 0;
    var sold_qty =  parseInt(document.getElementById("sold_qty").value) || 0;
    
    var flag_return = false;
    
    if(v == 0){
        flag_return = true;
    }else if(product_id && product_qty){
        if( sold_qty <= v && v <= (product_qty + sold_qty) ){
            flag_return = true;
        }
    }

    return flag_return;
});
Validation.add('custom-deal-price', "Please enter a number greater than 0 and smaller 'Product's Price' in this field.", function(v) {
    var product_id =  document.getElementById("product_id");
    var product_price =  parseFloat(document.getElementById("product_price").value) || 0;
    var dailydeal_price =  parseFloat(document.getElementById("dailydeal_price").value) || 0;
    
    var result = false;
    
    if(product_id && product_price){
        if( dailydeal_price < product_price ){
            result = true;
        }
    }

    return result;
});

var span_change_product = false;
var max_qty_value, min_ppl_value;
var class_validate_deal_qty = '';

Event.observe(window, 'load', function() {
    setPercentPriceFollowPrice();
    addChagenProductLink();
    allowSetPriceQuantity();
    $('dailydeal_price').onchange = setPercentPriceFollowPrice;
    $('percent_price').onchange = setPriceFollowPercentPrice;
});


/**
 * checked RadioProduct jump to tag "Deal Information"
 */
function jumpDealInformationTag(){
    document.getElementById("dailydeal_tabs_conf_section").click();
    window.scrollTo(1, 1);
}

/**
 * add link "Change Product || Edit Product" form edit DailyDeal
 */
function addChagenProductLink() {
    var cur_product = document.getElementById("cur_product");
    if (cur_product && cur_product.value != ''){
            
        if(!span_change_product){
            var linkElement = new Element('p', {
                'class': "note",
                'id': 'prot_note'
            });
            linkElement.innerHTML = '<span id="span_change_product"><a href="javascript:void(0);" onClick="document.getElementById(\'dailydeal_tabs_list_product\').click();">' + text_change_product + '</a> | <a onclick="getProductUrl()" href="javascript:void(0);">' + text_edit_product + '</a></span>';
            cur_product.parentNode.appendChild(linkElement);
            span_change_product = true;
        }
    }
}
    
/**
 * Follow Product's type : allow set Deal price, Deal Quantity
 */
function allowSetPriceQuantity( ) {
    enableInput('percent_price');
    enableInput('dailydeal_price');
    enableInput('deal_qty');
    
    if($("note_dailydeal_qty"))    $("note_dailydeal_qty").remove();
    if($("note_dailydeal_price"))    $("note_dailydeal_price").remove();
    
    var type = document.getElementById("product_type").value;

    // Simple Product, Virtual Product, Downloadable Product : allow set Deal price và Deal Quantity
    if(type == 'Simple Product' || type == 'Virtual Product' || type == 'Downloadable Product'){
            
    }
        
    // Configurable Product                                 : don't allow set Deal Quantity
    if(type == 'Configurable Product'){
        disableInput('deal_qty');
        addCommentInput('deal_qty', 'note_dailydeal_qty', note_qty_configurable);
    }
        
    // Grouped Product                                      : don't allow set Deal price và Deal Quantity
    if(type == 'Grouped Product'){
        disableInput('dailydeal_price');
        addCommentInput('dailydeal_price', 'note_dailydeal_price', note_price_grouped);
        
        disableInput('percent_price');
        
        disableInput('deal_qty');
        addCommentInput('deal_qty', 'note_dailydeal_qty', note_qty_grouped);
    }
    
    // Bundle Product                                       : don't allow set Deal price và Deal Quantity
    if(type == 'Bundle Product'){
        disableInput('dailydeal_price');
        addCommentInput('dailydeal_price', 'note_dailydeal_price', note_price_bundle);
        
        disableInput('percent_price');
        
        disableInput('deal_qty');
        addCommentInput('deal_qty', 'note_dailydeal_qty', note_qty_bundle);
    }
}

/*
 * - Mới đầu thì set price, set phần trăm
 * - Nếu thay đổi price thì set lại phần trăm
 * - Nếu thay đổi phần trăm thì set lại price
 */
function setPercentPriceFollowPrice() {
    var product_price = $('product_price').value;
    var deal_price = $('dailydeal_price').value;
    var one_percent_price = product_price / 100;
    
    var percent_price = (product_price - deal_price)/one_percent_price;
    var percent_price = Math.round(percent_price * 100)/100;
    
    if(IsNumeric(percent_price)){
        $('percent_price').value = percent_price;
    }
}

function setPriceFollowPercentPrice() {
    var product_price = $('product_price').value;
    var percent_price = $('percent_price').value;
    var one_percent_price = product_price / 100;
    var deal_price = product_price - percent_price * one_percent_price;
    deal_price = Math.round(deal_price * 100) / 100;
    if(IsNumeric(deal_price)){
        $('dailydeal_price').value = deal_price;
    }
}
/**
 * auto check radio if Edit form Daily Deal
 */
function autoCheckRadioFormProduct(product_id) {
    var product_choiced = document.getElementById(product_id);
    if(product_choiced){
        product_choiced.checked = true;
    }
}

String.prototype.trim = function()
{
    return this.replace(/^\s+|\s+$/g,"");
}
		
function Zizio_Groupsale_DealqtyChanged ()
{
    var productqty = document.getElementById("product_qty").value;
    var dealqty = document.getElementById("deal_qty").value;
			
    if (parseInt(productqty) != 0){
        if (parseInt(dealqty) > parseInt(productqty)) {
            dealqty.value = null;
            document.getElementById("labeldealqty").value = "Deal qty can not be more than product qty!";
        } else if (parseInt(dealqty) < parseInt(productqty))
            document.getElementById("labeldealqty").value = null;
    }
}

/**
 * Listener event click row product
 */		
function Zizio_Groupsale_OnProductSelectGridCheckboxCheck (grid, event)
{
    var trElement = Event.findElement(event, "tr");
    if (!trElement) return;
        
    var tds = Element.select(trElement, "td");
    if (!tds[0]) return;
    
    // Check Radio - begin
    var isInput   = Event.element(event).tagName == 'INPUT';
    var checkbox = Element.getElementsBySelector(trElement, 'input');
    if(checkbox[0]){
        checkbox[0].checked = true;
        jumpDealInformationTag();
    }
        
    // Set data into tag Deal Infomation
    var selected_product_data = product_extra_data[tds[1].innerHTML.trim()];
                        
    var cur_product = document.getElementById("cur_product");
    cur_product.value = selected_product_data["name"];
    
    var product_id = document.getElementById("product_id");
    product_id.value = selected_product_data["id"];
            
    var product_sku = document.getElementById("product_sku");
    product_sku.value = selected_product_data["sku"];

    var product_qty = document.getElementById("product_qty");
    product_qty.value = selected_product_data["qty"];
        
    var price = document.getElementById("product_price");
    price.value = selected_product_data["price"];
        
    // add link "Change Product | Edit Product" under input "Product Name"
    addChagenProductLink();
        
    var product_type = document.getElementById("product_type");
    product_type.value = selected_product_data["type"];
    allowSetPriceQuantity();
}

function getProductUrl() {
    var productTemplateSyntax = /(^|.|\r|\n)({{(\w+)}})/;
    var template = new Template(url_product_id, productTemplateSyntax);
    window.open(template.evaluate({
        product_id:$('product_id').value
    }), '_blank');
} 

function disableInput(id) {
    $(id).disable();
    $(id).value = '';
    $(id).setStyle({
        background: '#F0F0F0'
    });
}

function enableInput(id) {
    $(id).enable();
    $(id).setStyle({
        background: ''
    });
}

function addCommentInput(id, id_note, comment) {
    var linkElement = new Element('p', {
                'class': "note",
                'id': id_note
            });
    linkElement.innerHTML = comment;
    $(id).parentNode.appendChild(linkElement);
}

function IsNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}