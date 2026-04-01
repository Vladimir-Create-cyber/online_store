$(document).ready(function() {
    
    getDiscountsToProducts();
    $('button').click(function () {
        getDiscountsToProducts();
    });
    
    var discountsales_complects = new Object();
   
   //alert($('.row_carusel_40_3').outerWidth());
   //css(«width») и outerWidth()
});

function addComplectToCart(complects,cart){
    if(complects){
        var product_id;
        for(product_id in complects){
            addProductToComplectToCart('cart_discountsales_complect_data' + product_id + complects[product_id]);
        }
    }
}


function addProductToComplectToCart(product_complect_id){

    $.ajax({
            url: 'index.php?route=checkout/cart/add',
            type: 'post',
            data: $('#'+product_complect_id+' input[type=\'hidden\']'),
            dataType: 'json',
            beforeSend: function() {
                    //$('#button-cart').button('loading');
            },
            complete: function() {
                    //$('#button-cart').button('reset');
            },
            success: function(json) {
                    $('.alert, .text-danger').remove();
                    $('.form-group').removeClass('has-error');

                    if (json['error']) {
                            if (json['error']['option']) {
                                    for (i in json['error']['option']) {
                                            var element = $('#input-option' + i.replace('_', '-'));

                                            if (element.parent().hasClass('input-group')) {
                                                    element.parent().after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
                                            } else {
                                                    element.after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
                                            }
                                    }
                            }

                            if (json['error']['recurring']) {
                                    $('select[name=\'recurring_id\']').after('<div class="text-danger">' + json['error']['recurring'] + '</div>');
                            }

                            // Highlight any found errors
                            $('.text-danger').parent().addClass('has-error');
                    }

                    if (json['success']) {
                            $('.breadcrumb').after('<div class="alert alert-success">' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');

                            $('#cart > button').html('<i class="fa fa-shopping-cart"></i> ' + json['total']);

                            $('html, body').animate({ scrollTop: 0 }, 'slow');

                            $('#cart > ul').load('index.php?route=common/cart/info ul li');
                    }
            }
    });
    
}


function getDiscountsToProducts(){
    var links =[];
    var location_href = encodeURIComponent(window.location.href);
    $(".caption h4 a").map(function(el){ 
        links[el] = this.href;
    });
    var json_links = encodeURIComponent(JSON.stringify(links));
    $.ajax({
        url: 'index.php?route=total/discountsales/getDiscountsToProducts',
        type: 'post',
        data: '&location_href= ' + location_href + '&links='  +  json_links,
        beforeSend: function() {
        },
        complete: function() {
        },
        success: function(response) {
           if(response===''){
                return;
           }
           $('.discountsales_container').remove();
           $('.discountsales_container_uph4').remove();
           var result = jQuery.parseJSON(response);
           var i;
           var discount_to_product;
           var container_image;
           var container_uph4;
           for(i in result){
               discount_to_product = $('a[href = "' + result[i].link + '"]').parent('h4');
               container_image = '<div class="discountsales_container">';
               container_uph4 = '<div class="discountsales_container_uph4">';
               if(typeof result[i].discount!='undefined'){
                   if(result[i].discount.position=='image'){
                       container_image += result[i].discount.title;
                   }
                   if(result[i].discount.position=='uph4'){
                       container_uph4 += '<div style="color:'+result[i].discount.colour+'">'+result[i].discount.title_string+'</div>';
                   }
               }
               if(typeof result[i].discount_category!='undefined'){
                   if(result[i].discount_category.position=='image'){
                       container_image += result[i].discount_category.title;
                   }
                   if(result[i].discount_category.position=='uph4'){
                       container_uph4 += '<div style="color:'+result[i].discount_category.colour+'">'+result[i].discount_category.title_string+'</div>';
                   }
               }
               if(typeof result[i].discount_manufacturer!='undefined'){
                   if(result[i].discount_manufacturer.position=='image'){
                       container_image += result[i].discount_manufacturer.title;
                   }
                   if(result[i].discount_manufacturer.position=='uph4'){
                       container_uph4 += '<div style="color:'+result[i].discount_manufacturer.colour+'">'+result[i].discount_manufacturer.title_string+'</div>';
                   }
               }
               if(container_image!='<div class="discountsales_container">'){
                   container_image += '</div>';
                   discount_to_product.before(container_image);
               }
               if(container_uph4!='<div class="discountsales_container_uph4">'){
                   container_uph4 += '</div>';
                   discount_to_product.after(container_uph4);
               }
           }
        }
        
    });
    
}