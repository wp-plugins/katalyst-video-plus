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

$taxonomy_names = get_object_taxonomies( 'kvp_video' );
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
        <tr id="inline-edit" class="inline-edit-row inline-edit-row-post inline-edit-source quick-edit-row quick-edit-row-post inline-edit-source" style="display: none">
        <td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">

        <fieldset class="inline-edit-col-left"><div class="inline-edit-col">
            <h4><?php _e( 'Edit' ); ?></h4>
            <label>
                    <span class="title"><?php _e('Name', 'kvp'); ?></span>
                    <span class="input-text-wrap"><input type="text" name="edit_source[name]" class="ptitle" value="" /></span>
            </label>

            <?php
            $authors_dropdown = '';

            if ( is_super_admin() || current_user_can( 'edit_others_posts' ) ) :
                    $users_opt = array(
                            'hide_if_only_one_author' => true,
                            'who' => 'authors',
                            'name' => 'edit_source[author]',
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
			<label class="inline-edit-types">
				<span class="title"><?php _e( 'Type', 'kvp' ); ?></span>
				<select name="edit_source[type]">
				<?php foreach( apply_filters( 'kvp_source_types', kvp_get_source_types() ) as $type => $label ) : ?>
					<option value="<?php echo $type; ?>"><?php echo $label; ?></option>
				<?php endforeach; ?>
				</select>
			</label>
			<label class="inline-edit-items">
				<span class="title"><?php _e( 'Items', 'kvp' ); ?></span>
				<textarea cols="22" rows="1" name="edit_source[items]" autocomplete="off"></textarea>
			</label>
        </div></fieldset>

<?php if ( count( $hierarchical_taxonomies ) ) : ?>

        <fieldset class="inline-edit-col-center inline-edit-categories"><div class="inline-edit-col">

<?php foreach ( $hierarchical_taxonomies as $taxonomy ) : ?>

                <span class="title inline-edit-categories-label"><?php echo esc_html( $taxonomy->labels->name ) ?></span>
                <input type="hidden" name="tax_input[kvp_video_category][]" value="0" />
                <ul class="cat-checklist <?php echo esc_attr( $taxonomy->name )?>-checklist">
                        <?php wp_terms_checklist( null, array( 'taxonomy' => 'kvp_video_category' ) ) ?>
                </ul>

<?php endforeach; //$hierarchical_taxonomies as $taxonomy ?>

        </div></fieldset>

<?php endif; // count( $hierarchical_taxonomies ) ?>

        <fieldset class="inline-edit-col-right"><div class="inline-edit-col">
            <div class="inline-edit-group">
                <label class="inline-edit-freq">
                    <span class="title"><?php _e( 'Schedule Frequency', 'kvp' ); ?></span>
                    <select name="edit_source[schedule_freq]">
                    <?php foreach( wp_get_schedules() as $freq => $val ) : ?>
                        <option value="<?php echo $freq; ?>" <?php selected( 'hourly', $freq ); ?>><?php echo $val['display']; ?></option>
                    <?php endforeach; ?>
                    </select>
                </label>
                <label class="inline-edit-time">
                    <span class="title"><?php _e( 'Schedule Time', 'kvp' ); ?></span>
                    <span class="input-text-wrap"><input type="text" name="edit_source[schedule_time]" class="ptitle schedule_time" autocomplete="off" /></span>
                </label>
                <script>
                jQuery(document).ready( function( $ ){
                    
                    $('.schedule_time').focus( function(){
                        
                        $(this).timepicker({
                            timeFormat: '<?php echo get_option('time_format'); ?>',
                            step: 5,
                            forceRoundTime: true,
                        });
                        
                    });
                    
                });
                </script>
                <label class="inline-edit-limit">
                    <span class="title"><?php _e( 'Upload Limit', 'kvp' ); ?></span>
                    <span class="input-text-wrap"><input type="text" name="edit_source[limit]" class="ptitle" autocomplete="off" /></span>
                </label>
                <label class="inline-edit-auto-publish alignleft">
                    <span class="title"><?php _e( 'Auto Publish', 'kvp' ); ?></span>
                    <select name="edit_source[publish]">
                        <option value="publish"><?php _e( 'Publish', 'kvp' ); ?></option>
                        <option value="draft"><?php _e( 'Draft', 'kvp' ); ?></option>
                    </select>
                </label>
                <label class="alignleft">
                    <input type="checkbox" name="edit_source[status]" value="active">
                    <span class="checkbox-title"><?php _e( 'Active Source', 'kvp' ); ?></span>
                </label>
            </div>
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
                <?php wp_nonce_field( 'kvp_edit_source', 'kvp_inline_edit', false ); ?>
                        <a accesskey="s" href="#inline-edit" class="button-primary save alignright"><?php _e( 'Update' ); ?></a>
                        <span class="spinner"></span>
                <span class="error" style="display:none"></span>
                <br class="clear" />
        </p>
        </td></tr>
        </tbody></table></form>