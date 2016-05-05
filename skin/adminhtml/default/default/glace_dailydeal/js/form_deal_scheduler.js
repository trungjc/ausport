Validation.add('ajaxIsValidDealPrice', "Please enter a valid format in this field.", function(v) {
    var result = false;
    
    new Ajax.Request(url_ajaxIsValidDealPrice + (url_ajaxIsValidDealPrice.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true' ), {
        parameters :  {
            deal_price : v
        },
        asynchronous: false,
        method :'post',
        onSuccess : function(transport) {
            if(transport.responseText=='true'){
                result = true;
            }else{
                result = false;
            }
        }
    });
    
    return result;
});

Validation.add('ajaxIsValidDealQty', "Please enter a valid format in this field.", function(v) {
    var result = false;
    
    new Ajax.Request(url_ajaxIsValidDealQty + (url_ajaxIsValidDealQty.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true' ), {
        parameters :  {
            deal_qty : v
        },
        asynchronous: false,
        method :'post',
        onSuccess : function(transport) {
            if(transport.responseText=='true'){
                result = true;
            }else{
                result = false;
            }
        }
    });
    
    return result;
});