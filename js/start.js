jQuery(document).ready(($) => {
    //checks if there is an element id called products-page
    if ($('#products').length) {
        doAjax();
    }
    ////checks if there is an element id called filters
    if ($('#filters').length) {
        //if a checkbox gets clicked upon, it will go to doAjax() with the array of checked values
        $('#filters').on('change', 'input[type="checkbox"]', function () {
            if ($("#filters input[type=checkbox]:checked").length) {
                let checked = [];

                $("#filters input[type=checkbox]:checked").each(function () {
                    checked.push(this.value);
                });

                doAjax(checked);
                //return makes sure doAjax function doesnt get called twice
                return;
            }
            doAjax();
        })
    }

    function doAjax(action = null) {
        $.ajax({
            // ajax_object.ajax_url comes from initscripts
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'getProducts',
                keyword: action
            },
            beforeSend: () => {},
            success: (result) => {
                if (result) {
                    // put every product in container. Put container in #products
                    let products = JSON.parse(result);
                    let container = '<div class="row">';
                    $(products).each(function (index, value) {
                        container += 
                        '<div class="col-lg-4 col-md-6">' +
                            '<div class="product">' +
                            '<a href="' + value['permaLink'] + '" class="product-link">' +
                            value['productThumbnail']+
                            '<p>'+value['post_title']+'</p>' +
                            '</a>' +
                            '</div>' +
                            '</div>'
                        ;

                    });
                    container += '</div>';
                    $('#products').html(container);
                }
                else {
                    //in case of no products
                    $('#products').html('No product has been found...')
                }
            },
            error: (error) => {
                console.log('error: ', error);
            }
        })
    }
})