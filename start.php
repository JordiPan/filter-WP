<?php
/*
Plugin Name: allergies
Description: not something
Version:     2.5.1
Author:      Jordi Pan, Xaiver Chen
*/

//initialiseert scripts en styles
function initPlugin()
{
    startScripts();
    startStyles();
}
add_action('plugins_loaded', 'initPlugin');

//scripts
function startScripts()
{
    wp_enqueue_script('jquery');
    wp_register_script('startScript', plugins_url('/js/start.js', __FILE__));
    wp_enqueue_script('startScript');
    //stuurt ajax_object met de admin-url voor ajax naar js bestand
    wp_localize_script('startScript', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
}

//styles
function startStyles()
{
    wp_register_style('startStyles', plugins_url('/css/styles.css', __FILE__));
    wp_enqueue_style('startStyles');
    wp_register_style('bootstrap', plugins_url('/css/bootstrap.min.css', __FILE__));
    wp_enqueue_style('bootstrap');
}

// laat allergieën zien via shortcode
function showAllergies()
{
    $allergies = get_field('problems');
    if (!$allergies) {
        return;
    }
?>
    <?php
    echo('<br>');
    foreach ($allergies as $allergy) {
        echo ('<img src=/wp-test/wp-content/uploads/' . $allergy . '.png></img>');
    }
    ?>
<?php
}
add_shortcode('doop', 'showAllergies');

// maakt en laat checkboxes zien als shortcode
function checkboxes()
{
    $allergies = get_field_object('problems');
    
    if (!$allergies) {
        return;
    }
?>
    <div id="filters"><?php
                        foreach ($allergies['choices'] as $allergy => $index) {
                        ?>
            <input type="checkbox" 
            id='<?php echo $allergies['choices'][$allergy]; ?>' 
            name='<?php echo $allergies['choices'][$allergy] ?>' 
            value='<?php echo $allergy ?>'
            >
            <label for='<?php echo $allergies['choices'][$allergy]; ?>'>
            <?php echo $allergies['choices'][$allergy] ?>
        </label>
        <br>

        <?php
                        }
        ?>
    </div>
<?php
}
add_shortcode('allergyFilter', 'checkboxes');

//jquery heeft een id nodig om daarin de producten in te laden. Hier wordt de id gemaakt via shortcode.
function showProducts()
{
   ?>
   <p id="products"></p>
   <?php
}
add_shortcode('showProducts', 'showProducts');

//de plaats van de iconen wordt bepaalt hiermee: 'woocommerce_product_meta_end'
function action_woocommerce_after_main_content()
{
    do_shortcode('[doop]');
}
add_action('woocommerce_product_meta_end', 'action_woocommerce_after_main_content');


function getProducts()
{
    // $_POST['keyword'] kan null of een array met allergieën namen zijn
    $checkedValues = $_POST['keyword'];
    $args = [
        'post_type' => 'product',
        'orderby' => 'title',
        'order'   => 'ASC',
    ];
    //pakt alle producten dat geen allergieën heeft in de checkedValues array als iemand een filter knop heeft gevinkt
    if ($checkedValues != null) { 
        $meta_query = ['relation' => 'AND'];
        foreach ($checkedValues as $val) {
            $meta_query[] = [
                'key'     => 'problems',
                'value'   => $val,
                'compare' => 'NOT LIKE',
            ];
        }
        $args['meta_query'] = $meta_query;
    }
    //voert de filter query uit
    $the_query = new WP_Query($args);

    $posts = $the_query->posts;

    if ($the_query->have_posts()) {

        foreach ($posts as $post) {
            //voegt link en foto toe aan elke product
            $post->{'productThumbnail'} = get_the_post_thumbnail($post, 'full', ['class' => 'custom-search-image']);
            $post->{'permaLink'} = esc_url(get_permalink($post->ID));
        }

        //verzend de gefilterde producten
        print_r(json_encode($posts));

    } 
    else {
        //geen producten gevonden met de filters
        print_r(null);
    }
    wp_die();
    wp_reset_postdata();
}
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
add_action('wp_ajax_getProducts', 'getProducts');
add_action('wp_ajax_nopriv_getProducts', 'getProducts');