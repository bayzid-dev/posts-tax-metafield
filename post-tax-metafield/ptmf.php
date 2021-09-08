
<?php
/**
 * Plugin Name: Post and Taxonomy selector
 * Plugin Uri: https://post-tax-metafield
 * Description: By this plugin your can add your post and taxonomy from post/ you can select which post you want to display
 * Author: SeJan ahmed BayZid
 * Version: 1.0
 * License: 
 * Text Domain: post-tax-metafield
 * Domain path: /languages
 */
//textdomain 
function ptmf_plugin_textdomain()
{
    load_plugin_textdomain('post-tax-metafield', false, dirname(__FILE__) . '/languages');
} 
add_action('plugin_loaded', 'ptmf_plugin_textdomain');

// admin init for enqueueing the assets
function ptmf_init()
{
    add_action('admin_enqueue_scripts', 'ptmf_admin_assets');
}
add_action('admin_init', 'ptmf_init');

function ptmf_admin_assets()
{
    wp_enqueue_style('admin-style-css', plugin_dir_url(__FILE__) . 'assets/admin/css/input-style.css');
}

// registering metabox
function ptmf_add_metabox()
{
    add_meta_box('ptmf_select_metabox', __('Select posts', 'post-tax-metafield'), 'ptmf_display_metabox', array('page'));
}
add_action('admin_menu', 'ptmf_add_metabox');


// to save the option meta
function ptmf_save_metabox( $post_id )
{
    if ( !ptmf_is_secured('ptmf_posts_nonce', 'ptmf_posts', $post_id )) {
        return $post_id;
    }

    $selected_post_id = $_POST['ptmf_posts'];
    if ( $selected_post_id > 0 ) {
        update_post_meta( $post_id, 'ptmf_selected_post', $selected_post_id );
    }
    return $post_id;
}

add_action('save_post', 'ptmf_save_metabox');


function ptmf_display_metabox( $post ){
    $selected_post_id = get_post_meta( $post->ID, 'ptmf_selected_post', true );

    wp_nonce_field( 'ptmf_posts' , 'ptmf_posts_nonce');
    $_post = new WP_Query( array (
        'post_type' => 'post',
        'post_per_page' => -1,
    ));

    $dropdown_list = '';
    
    while ( $_post->have_posts() ) {
        $extra = '';
        $_post->the_post();
        if( get_the_ID() == $selected_post_id ){
            $extra = 'selected';
        }
        $dropdown_list .= sprintf('<option %s value="%s">%s</option>', $extra , get_the_ID(), get_the_title());
    }
    wp_reset_postdata();



    $label = __('Select A Post that you want to show', 'post-tax-metafield');
    $option_title = __('Select here', 'post-tax-metafield');
    $metabox = <<<EOD
    <div class="fields">
        <div class="field_c">
            <p class="label_c" >
                <label> {$label} </label>
            </p>
            <div class="input_c">
                <select  name="ptmf_posts" id="ptmf_posts">
                    <option value="0">{$option_title}</option>
                    {$dropdown_list}
                </select>
            </div>
        </div> 
        <div class="float_c"/></div>
    </div>
EOD;

    echo $metabox;
}


if (!function_exists("ptmf_is_secured")) {
    function ptmf_is_secured($nonce_field, $action, $post_id)
    {
        $nonce = isset($_POST[$nonce_field]) ? $_POST[$nonce_field] : '';

        if ($nonce == '') {
            return false;
        }
        if (!wp_verify_nonce($nonce, $action)) {
            return false;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return false;
        }
        if (wp_is_post_autosave($post_id)) {
            return false;
        }
        if (wp_is_post_revision($post_id)) {
            return false;
        }

        return true;
    }
}
