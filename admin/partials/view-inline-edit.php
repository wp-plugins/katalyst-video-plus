<?php if( ! defined('ABSPATH') ) { header('Status: 403 Forbidden'); header('HTTP/1.1 403 Forbidden'); exit(); }

/**
* Displays the log
*
* @link       http://katalystvideoplus.com
* @since      2.0.0
*
* @package    Katalyst_Video_Plus
* @subpackage Katalyst_Video_Plus/admin/partials
*/

$screen = $this->screen;

$post = get_default_post_to_edit( $screen->post_type );
$post_type_object = get_post_type_object( $screen->post_type );

$taxonomy_names = get_object_taxonomies( 'post' );
$hierarchical_taxonomies = array();
$flat_taxonomies = array();
foreach ( $taxonomy_names as $taxonomy_name ) {
        $taxonomy = get_taxonomy( $taxonomy_name );

        if ( !$taxonomy->show_ui )
                continue;

        if ( $taxonomy->hierarchical )
                $hierarchical_taxonomies[] = $taxonomy;
        else
                $flat_taxonomies[] = $taxonomy;
}

$can_publish = current_user_can( 'publish_posts' );
$core_columns = array( 'cb' => true, 'date' => true, 'title' => true, 'categories' => true, 'tags' => true, 'comments' => true, 'author' => true );

?>

<form method="get" action=""><table style="display: none"><tbody id="inlineedit">
        <tr id="inline-edit" class="inline-edit-row inline-edit-row-post inline-edit-account quick-edit-row quick-edit-row-post inline-edit-account" style="display: none">
        <td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">

        <fieldset class="inline-edit-col-left"><div class="inline-edit-col">
                <h4><?php _e( 'Edit' ); ?></h4>
                <label>
                        <span class="title"><?php _e('Username', 'kvp'); ?></span>
                        <span class="input-text-wrap"><input type="text" name="edit_account[username]" class="ptitle" value="" /></span>
                </label>

                <?php
                $authors_dropdown = '';

                if ( is_super_admin() || current_user_can( 'edit_others_posts' ) ) :
                        $users_opt = array(
                                'hide_if_only_one_author' => false,
                                'who' => 'authors',
                                'name' => 'edit_account[author]',
                                'class'=> 'authors',
                                'multi' => 1,
                                'echo' => 0
                        );

                        if ( $authors = wp_dropdown_users( $users_opt ) ) :
                                $authors_dropdown  = '<label class="inline-edit-author">';
                                $authors_dropdown .= '<span class="title">' . __( 'Author' ) . '</span>';
                                $authors_dropdown .= $authors;
                                $authors_dropdown .= '</label>';
                        endif;
                endif; // authors

				echo $authors_dropdown; ?>
				
				<label>
                        <span class="title"><?php _e('Developer Key', 'kvp'); ?></span>
                        <span class="input-text-wrap"><input type="text" name="edit_account[developer_key]" value="" /></span>
                </label>
				<label>
                        <span class="title"><?php _e('OAuth ID', 'kvp'); ?></span>
                        <span class="input-text-wrap"><input type="text" name="edit_account[oauth_id]" value="" /></span>
                        <span class="title"><?php _e('OAuth Secret', 'kvp'); ?></span>
                        <span class="input-text-wrap"><input type="text" name="edit_account[oauth_secret]" value="" /></span>
                </label>
        </div></fieldset>

<?php if ( count( $hierarchical_taxonomies ) ) : ?>

        <fieldset class="inline-edit-col-center inline-edit-categories"><div class="inline-edit-col">

<?php foreach ( $hierarchical_taxonomies as $taxonomy ) : ?>

                <span class="title inline-edit-categories-label"><?php echo esc_html( $taxonomy->labels->name ) ?></span>
                <input type="hidden" name="post_category[]" value="0" />
                <ul class="cat-checklist <?php echo esc_attr( $taxonomy->name )?>-checklist">
                        <?php wp_terms_checklist( null ) ?>
                </ul>

<?php endforeach; //$hierarchical_taxonomies as $taxonomy ?>

        </div></fieldset>

<?php endif; // count( $hierarchical_taxonomies ) ?>

        <fieldset class="inline-edit-col-right"><div class="inline-edit-col">
            <div class="inline-edit-group">
            <?php
            $extension_status = apply_filters( 'kvp_accounts_extension_status', array( 'video' => __( 'Import Videos', 'kvp' ) ) );
            
            if( count($extension_status) > 1 ) :
                foreach( $extension_status as $ext => $title ) : ?>
                <label class="alignleft">
                    <input type="checkbox" name="ext_status[<?php echo $ext; ?>]" value="active">
                    <span class="checkbox-title"><?php echo $title; ?></span>
                </label>
            <?php
                endforeach;
            endif;
            ?>    
            
            </div>
                <!--
                <label class="inline-title-filter">
                    <span class="title"><?php _e('Title Filter', 'kvp'); ?></span>
                    <textarea cols="22" rows="1" name="edit_account[title_filter]" class="title_filter"></textarea>
                </label>
                <label class="inline-content-filter">
                    <span class="title"><?php _e('Content Filter', 'kvp'); ?></span>
                    <textarea cols="22" rows="1" name="edit_account[content_filter]" class="content_filter"></textarea>
                </label>
                <label class="inline-tags-filter">
                    <span class="title"><?php _e('Tags Filter', 'kvp'); ?></span>
                    <textarea cols="22" rows="1" name="edit_account[tags_filter]" class="tags_filter"></textarea>
                </label>


                <div class="inline-edit-group">
                	<label class="inline-edit-status alignleft">
                    </label>
                </div> -->

        </div></fieldset>

<?php
        list( $columns ) = $this->get_column_info();  
        foreach ( $columns as $column_name => $column_display_name ) {
                if ( isset( $core_columns[$column_name] ) )
                        continue;
                do_action( 'kvp_edit_box', $column_name );
        }
?>
        <p class="submit inline-edit-save">
                <a accesskey="c" href="#inline-edit" class="button-secondary cancel alignleft"><?php _e( 'Cancel' ); ?></a>
                <?php wp_nonce_field( 'kvp_edit_account', 'kvp_inline_edit', false ); ?>
                        <a accesskey="s" href="#inline-edit" class="button-primary save alignright"><?php _e( 'Update' ); ?></a>
                        <span class="spinner"></span>
                <span class="error" style="display:none"></span>
                <br class="clear" />
        </p>
        </td></tr>
        </tbody></table></form>