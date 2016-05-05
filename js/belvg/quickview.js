var Quickview = Class.create();
Quickview.prototype = {
  initialize: function(config) {
    this.config = config;	
  },
  createButton: function(){
	//var cls = this.config.class;
	var button = '<span style="position:relative"><a class="quickview-button button"  href="javascript:void(0)" style="display:none;">'+this.config.title+'</a></span>';	
	return  button;
  },  
  show: function(name) {
	var button = this.createButton();	
	$$(name+' li.item a.product-image').each(function(el){				
			new Insertion.After(el, ''+button+'');			
			Event.observe(el.next(), 'click', function(event) {							
				var url = $('baseurl').value+'quickview/ajax/popup/';
				$('quickview-loader').show();				
				new Ajax.Request(url, {
				  method: 'post',
				  parameters : {'pro_id':el.readAttribute('id')},
				  onSuccess: function(transport) {	
					$('quickview-hider').setStyle({height:document.getElementsByTagName('body')[0].clientHeight+'px'});
					$('quickview-hider').show();
					$('quickview-loader').hide();
					$('popup').update(transport.responseText);
					$('popup').innerHTML;
					var offset = window.pageYOffset+window.innerHeight*0.3;//document.getElementsByTagName('body')[0].clientHeight/2+window.pageYOffset;
					$('popup').setStyle({top:offset+'px'});					
					$('popup').show()
				  }
				});
			});
			Event.observe(el.up(), 'mouseover', function(event) {
				el.next().down().show();				
			});
			Event.observe(el.up(), 'mouseout', function(event) {
				el.next().down().hide();
			});
	});
  }
  

};
function closePopup(){
	$('popup').update(' ');
	$('popup').innerHTML;
	$('popup').hide();
	$('quickview-hider').hide();
}
