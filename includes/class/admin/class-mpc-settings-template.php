<?php
/**
 * Admin settings template class
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * MPC Admin Settings Template Class.
 */
class MPC_Settings_Template {
    /**
     * Plugin core data
     * @var array
     */
    private static $data;

    /**
     * Set plugin core data
     */
    public static function set(){
        global $mpc__;
        self::$data = $mpc__;
    }

    /**
     * Include correct template
     *
     * @param string $name Template name.
     */
    public static function get_template( $name ){
        self::set();

        switch ( $name ) {
            case 'all-tables':
                self::all_tables();
                break;

            case 'new-table':
                self::new_table();
                break;

            case 'column-sorting':
                self::sort_columns();
                break;

            case 'export':
                self::export();
                break;

            case 'import':
                self::import();
                break;
                
            default:
                break;
        }
    }


    public static function all_tables(){
        $notice = MPC_Shortcode::update_shortcode();
        ?>
        <div class="mpcdp_settings_section">
            <?php self::shortcode_notice( $notice ); ?>
            <?php self::shortcode_list(); ?>
        </div>
        <?php
    }
    public static function new_table(){
        $notice = MPC_Shortcode::update_shortcode();
        ?>
        <div class="mpcdp_settings_section">
            <?php self::shortcode_notice( $notice ); ?>
            <?php self::edit_shortcode(); ?>
        </div>
        <div class="mpca-content new-table">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mpc-shortcode' ) ); ?>" class="mpcasc-reset">
                <span class="button-secondary">
                    <?php echo esc_html__( 'Reset', 'multiple-products-to-cart-for-woocommerce' ); ?>
                </span>
            </a>
        </div>
        <?php
        wp_nonce_field( 'mpc_opt_sc_save', 'mpc_opt_sc' );
    }
    public static function sort_columns(){
        MPC_Admin_Helper::save_sorted_columns();
        $value = get_option( 'wmc_sorted_columns' );
        ?>
        <div class="mpcdp_settings_section">
            <div class="mpcdp_settings_section_title"><?php echo esc_html__( 'Column Sorting', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
            <?php MPC_Settings_Page::display_notice(); ?>
            <div class="mpc-banner mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
                <div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
                    <div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
                        <span class="theme_customizer_icon dashicons dashicons-list-view"></span>
                        <div class="mpcdp_settings_option_description">
                            <div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new"><?php echo esc_html__( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
                            <div class="mpcdp_option_label"><?php echo esc_html__( 'Manage Product Table Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
                            <div class="mpcdp_option_description">
                                <?php echo esc_html__( 'Utilize the convenient drag-and-drop feature below to rearrange the order of the product table columns. You also have the ability to activate or deactivate any columns as needed.', 'multiple-products-to-cart-for-woocommerce' ); ?>
                                <?php
                                    $move_icon = '<span class="dashicons dashicons-move"></span>';
                                    $sort_icon = '<span class="dashicons dashicons-sort"></span>';

                                    echo wp_kses_post(
                                        sprintf(
                                            // translators: %1$s: move dashicon html, %2$s: sort dashicon html.
                                            __( 'Also note, %1$s can move up, down, left, right, but %2$s only moves up-down.', 'multiple-products-to-cart-for-woocommerce' ),
                                            wp_kses_post( $move_icon ),
                                            wp_kses_post( $sort_icon )
                                        )
                                    );
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mpcdp_settings_toggle mpcdp_container" id="column-sorting">
                <div class="mpcdp_settings_option visible">
                    <div class="mpcdp_row">
                        <div class="mpcdp_settings_option_description col-md-6">
                            <div class="mpcdp_option_label"><?php echo esc_html__( 'Active Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
                            <div class="mpc-sortable mpca-sorted-options">
                                <ul id="active-mpc-columns" class="connectedSortable ui-sortable">
                                    <?php MPC_Admin_Helper::column_list( $value, true ); ?>
                                </ul>
                            </div>
                        </div>
                        <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                            <div class="mpcdp_option_label"><?php echo esc_html__( 'Inactive Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
                            <div class="mpc-sortable mpca-sorted-options">
                                <ul id="inactive-mpc-columns" class="connectedSortable ui-sortable">
                                    <?php MPC_Admin_Helper::column_list( $value, false ); ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        printf( '<input class="mpc-sorted-cols" type="hidden" name="wmc_sorted_columns" value="%s">', esc_html( $value ) );
        wp_nonce_field( 'mpc_col_sort_save', 'mpc_col_sort' );
    }
    public static function export(){
        $pro_cls = false === self::$data['has_pro'] ? 'mpcex-disabled' : '';
        ?>
        <div class="mpcdp_settings_section">
            <div class="mpcdp_settings_section_title"><?php echo esc_html__( 'Export Settings', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
            <div class="mpcdp_settings_toggle mpcdp_container" id="export-success">
                <div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
                    <div class="mpcdp_settings_option_field_theme_customizer first_customizer_field mpc-export-notice">
                        <span class="theme_customizer_icon dashicons dashicons-saved"></span>
                        <div class="mpcdp_settings_option_description">
                            <div class="mpcdp_option_label"><?php echo esc_html__( 'Please wait while we are getting your file ready for download...', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
                <div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
                    <div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
                        <span class="theme_customizer_icon dashicons dashicons-download"></span>
                        <div class="mpcdp_settings_option_description">
                            <?php if ( false === self::$data['has_pro'] ) : ?>
                                <div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new">
                                    <?php echo esc_html__( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?>
                                </div>
                            <?php endif; ?>
                            <div class="mpcdp_option_label">Export MPC Tables and Settings</div>
                            <div class="mpcdp_option_description">
                                <br>
                                Click on `Export` to export tables and settings.
                                <br><br>
                                You will find either a `mpc_export.json` or an enumarated `mpc_export(1).json` file in your `Downloads` folder. You can use this file to import it later or to other websites.
                                <br><br>
                                <?php if ( false === self::$data['has_pro'] ) : ?>
                                    The export feature is only available for PRO plugin.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mpcdp_settings_toggle mpcdp_container">
                <div class="mpcdp_settings_option visible">
                    <div class="mpcdp_row">
                        <div class="mpcdp_settings_option_description col-md-6">
                            <div class="mpcdp_option_label">Export</div>
                            <div class="mpcdp_option_description">Export tables and settings</div>
                        </div>
                        <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                            <div class="mpcdp_settings_submit mpc-file">
                                <div class="submit">
                                    <button id="mpc-export" class="mpcdp_submit_button <?php echo esc_attr( $pro_cls ); ?>" title="Export">
                                        <div class="save-text">Export settings</div>
                                        <div class="save-text save-text-mobile">Export</div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        wp_nonce_field( 'mpc_export_nonce', 'mpc_export' );
    }
    public static function import(){
        do_action( 'mpc_pro_import' );

        $icon     = self::$data['notice_icon'] ?? 'saved';
        $response = self::$data['response'] ?? '';
        $pro_cls  = false === self::$data['has_pro'] ? 'mpcex-disabled' : '';
        ?>
        <div class="mpcdp_settings_section">
            <div class="mpcdp_settings_section_title"><?php echo esc_html__( 'Import Settings', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
            <?php if ( ! empty( $response ) ) : ?>
                <div class="mpcdp_settings_toggle mpcdp_container">
                    <div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
                        <div class="mpcdp_settings_option_field_theme_customizer first_customizer_field mpc-import-notice">
                            <span class="theme_customizer_icon dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
                            <div class="mpcdp_settings_option_description">
                                <div class="mpcdp_option_label"><?php echo esc_html( $response ); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
                <div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
                    <div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
                        <span class="theme_customizer_icon dashicons dashicons-upload"></span>
                        <div class="mpcdp_settings_option_description">
                            <?php if ( false === self::$data['has_pro'] ) : ?>
                                <div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new">
                                    <?php echo esc_html__( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?>
                                </div>
                            <?php endif; ?>
                            <div class="mpcdp_option_label">Import MPC Tables and Settings</div>
                            <div class="mpcdp_option_description">
                                <br>
                                The file name will be `mpc_export.json` or enumarated `mpc_export(1).json`.
                                <br><br>
                                Choose the .json file and click on `Import`. This will import `Multiple products to cart for WooCommerce` tables and settings.
                                <br><br>
                                <?php if ( false === self::$data['has_pro'] ) : ?>
                                    The import feature is only available for PRO plugin.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mpcdp_settings_toggle mpcdp_container">
                <div class="mpcdp_settings_option visible">
                    <div class="mpcdp_row">
                        <div class="mpcdp_settings_option_description col-md-12">
                            <div class="mpcdp_option_label">Import</div>
                            <div class="mpcdp_option_description">Import MPC settings and tables</div>
                        </div>
                    </div>
                    <div class="mpcdp_row">
                        <div class="mpcdp_settings_option_description col-md-6">
                            <input name="mpc_import_file" type="file" class="mpc-file-uploader" accept=".json">
                        </div>
                        <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                            <div class="mpcdp_settings_submit mpc-file">
                                <div class="submit">
                                    <button class="mpcdp_submit_button <?php echo esc_attr( $pro_cls ); ?>" title="Import">
                                        <div class="save-text">Import settings</div>
                                        <div class="save-text save-text-mobile">Import</div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        wp_nonce_field( 'mpc_import_nonce', 'mpc_import' );
    }



    /**
     * Display all table shortcode list
     */
    public static function shortcode_list() {
        ?>
        <div class="mpcdp_settings_section_title">
            <?php echo esc_html__( 'All Product Tables', 'multiple-products-to-cart-for-woocommerce' ); ?>
        </div>
        <?php
        $shortcodes = MPC_Shortcode::get_all_shortcodes();
        if( empty( $shortcodes ) ){
            self::no_shortcode();
            return;
        }

        foreach( $shortcodes as $sc ){
            self::shortcode_item( $sc['ID'], $sc['post_title'], $sc['post_content'] );
        }
    }

    /**
     * Display single shortcode table content
     */
    public static function edit_shortcode() {
        $table_id = MPC_Shortcode::get_table_id_from_url();

        $atts      = MPC_Shortcode::get_shortcode( $table_id, 'only_atts' );
        $atts      = MPC_Shortcode::extract_shortcode_atts( $atts );
        $shortcode = MPC_Shortcode::get_cpt_post( $table_id );

        foreach ( self::$data['fields']['new_table'] as $section ) {
            ?>
            <div class="mpcdp_settings_section">
                <div class="mpcdp_settings_section_title">
                    <?php echo !empty( $atts ) ? __( 'Edit Product Table', 'multiple-products-to-cart-for-woocommerce' ) : wp_kses_post($section['section']); ?>
                </div>
                <?php self::shortcode_details( $table_id, $shortcode ); ?>
                <?php foreach ( $section['fields'] as $fld ) : ?>
                    <?php $value = self::get_shortcode_field_value( $fld, $atts, $shortcode ); ?>
                    <div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="wmca_default_quantity">
                        <div class="mpcdp_settings_option visible" data-field-id="wmca_default_quantity">
                            <?php self::shortcode_item_edit( $fld, $value ); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        }
    }



    /**
     * Display shortcode item
     *
     * @param int    $id    Table id.
     * @param string $title Table title.
     * @param string $desc  Table description.
     */
    public static function shortcode_item( $id, $title, $desc ) {
        $edit   = admin_url( 'admin.php?page=mpc-shortcode' );
        $delete = admin_url( 'admin.php?page=mpc-shortcodes' );
        $nonce  = wp_create_nonce( 'mpc_option_tab' );
        ?>
        <div class="mpcdp_settings_toggle mpcdp_container mpc-shortcode">
            <div class="mpcdp_settings_option visible">
                <div class="mpcdp_row">
                    <div class="mpcdp_settings_option_description col-md-12">
                        <div class="mpcdp_option_label"><?php echo esc_html( $title ); ?></div><div class="mpcdp_option_description">
                            <?php echo ! empty( $desc ) ? wp_kses_post( $desc ) : ''; ?>
                        </div>
                    </div>
                </div>
                <div class="mpcdp_row">
                    <div class="mpcdp_settings_option_description col-md-12">
                        <textarea class="mpc-opt-sc" readonly >[woo-multi-cart table="<?php echo esc_attr( $id ); ?>"]</textarea>
                    </div>
                    <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-4 mpc-sc-btns">
                        <span class="mpc-opt-sc-btn copy">
                            <span class="dashicons dashicons-admin-page"></span>
                            <span class="mpc-sc-label"><?php echo esc_html__( 'Copy', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
                        </span>
                        <a class="mpc-opt-sc-btn edit" href="<?php echo esc_url( $edit . '&tab=all-tables&mpctable=' . esc_attr( $id ) . '&nonce=' . $nonce ); ?>">
                            <span class="dashicons dashicons-welcome-write-blog"></span>
                            <span class="mpc-sc-label"><?php echo esc_html__( 'Edit', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
                        </a>
                        <a class="mpc-opt-sc-btn delete" href="<?php echo esc_url( $delete . '&tab=all-tables&mpcscdlt=' . esc_attr( $id ) . '&nonce=' . $nonce ); ?>">
                            <span class="dashicons dashicons-trash"></span>
                            <span class="mpc-sc-label"><?php echo esc_html__( 'Delete', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Show shortcode table item in header
     */
    public static function shortcode_details( $table_id, $shortcode ) {
        if( empty( $table_id ) || empty( $shortcode ) ) return;

        $delete = admin_url( 'admin.php?page=mpc-shortcodes' );
        $nonce  = wp_create_nonce( 'mpc_option_tab' );
        ?>
        <div class="mpcdp_settings_toggle mpcdp_container mpc-shortcode-item">
            <div class="mpcdp_settings_option visible">
                <div class="mpcdp_row">
                    <div class="mpcdp_settings_option_description">
                        <div class="mpcdp_option_label">
                            <span class="theme_customizer_icon dashicons dashicons-shortcode"></span>
                            <?php echo esc_html( $shortcode['post_title'] ); ?>
                        </div>
                        <?php if ( ! empty( $shortcode['post_content'] ) ) : ?>
                            <div class="mpcdp_option_description">
                                <?php echo wp_kses_post( $shortcode['post_content'] ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mpcdp_row">
                    <div class="mpcdp_settings_option_description col-md-12">
                        <textarea class="mpc-opt-sc" readonly="">[woo-multi-cart table="<?php echo esc_attr( $table_id ); ?>"]</textarea>
                    </div>
                    <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-4 mpc-sc-btns">
                        <span class="mpc-opt-sc-btn copy">
                            <span class="dashicons dashicons-admin-page"></span>
                            <span class="mpc-sc-label"><?php echo esc_html__( 'Copy', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
                        </span>
                        <a class="mpc-opt-sc-btn delete" href="<?php echo esc_url( $delete . '&tab=all-tables&mpcscdlt=' . esc_attr( $table_id ) . '&nonce=' . $nonce ); ?>">
                            <span class="dashicons dashicons-trash"></span>
                            <span class="mpc-sc-label"><?php echo esc_html__( 'Delete', 'multiple-products-to-cart-for-woocommerce' ); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function shortcode_item_edit( $fld, $value ){
        $name        = isset( $fld['key'] ) ? $fld['key'] : '';
        $label       = isset( $fld['label'] ) ? $fld['label'] : '';
        $desc        = isset( $fld['desc'] ) ? $fld['desc'] : '';
        $class       = isset( $fld['class'] ) ? $fld['class'] : '';
        $placeholder = isset( $fld['placeholder'] ) ? $fld['placeholder'] : '';
        $min         = isset( $fld['min'] ) ? $fld['min'] : '';
        $max         = isset( $fld['max'] ) ? $fld['max'] : '';

        if ( in_array( $fld['key'], array( 'ids', 'selected', 'skip_products', 'cats' ), true ) ) {
            ?>
            <div class="mpcdp_row">
                <div class="mpcdp_settings_option_description col-md-12">
                    <div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
                    <div class="mpcdp_option_description">
                        <?php echo ! empty( $desc ) ? wp_kses_post( $desc ) : ''; ?>
                    </div>
                </div>
            </div>
            <div class="mpcdp_row">
                <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-12">
                    <div class="choicesdp <?php echo esc_html( $name ); ?>">
                        <?php self::itembox( $fld, $value ); ?>
                    </div>
                </div>
            </div>
            <?php
            return;
        }

        if ( 'sortable' === $fld['type'] ) {
            ?>
            <div class="mpcdp_row">
                <div class="mpcdp_settings_option_description col-md-12">
                    <div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
                    <div class="mpcdp_option_description">
                        <?php echo esc_html__( 'Utilize the convenient drag-and-drop feature below to rearrange the order of the product table columns. You also have the ability to activate or deactivate any columns as needed.', 'multiple-products-to-cart-for-woocommerce' ); ?>
                        <br>
                        <br>
                        <?php
                            $move_icon = '<span class="dashicons dashicons-move"></span>';
                            $sort_icon = '<span class="dashicons dashicons-sort"></span>';

                            echo wp_kses_post(
                                sprintf(
                                    // translators: %1$s: move icon html, %2$s: sort icon html.
                                    __( 'Also note, %1$s can move up, down, left, right, but %2$s only moves up-down.', 'multiple-products-to-cart-for-woocommerce' ),
                                    wp_kses_post( $move_icon ),
                                    wp_kses_post( $sort_icon ),
                                )
                            );
                        ?>
                    </div>
                </div>
            </div>
            <div class="mpcdp_row row-column-sorting">
                <?php self::sortable( $fld, $value ); ?>
            </div>
            <?php
            return;
        }
        ?>
        <div class="mpcdp_row">
            <div class="mpcdp_settings_option_description col-md-6">
                <div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
                <div class="mpcdp_option_description">
                    <?php echo ! empty( $desc ) ? wp_kses_post( $desc ) : ''; ?>
                </div>
            </div>
            <div class="mpcdp_settings_option_description col-md-6">
                <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-12">
                    <?php
                        if ( 'selectbox' === $fld['type'] ) {
                            printf( '<div class="choicesdp %s">', esc_html( $name ) );

                            self::itembox( $fld, $value );

                            echo '</div>';
                        } elseif ( 'checkbox' === $fld['type'] ) {
                            self::switchbox( $fld, $value );
                        } else {
                            printf(
                                '<input type="text" name="%s" id="%s" min="%s" max="%s" value="%s" placeholder="%s" class="%s">',
                                esc_attr( $name ),
                                esc_attr( $name ),
                                esc_attr( $min ),
                                esc_attr( $max ),
                                esc_attr( $value ),
                                esc_attr( $placeholder ),
                                esc_html( $class )
                            );
                        }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    public static function get_shortcode_field_value( $fld, $atts, $shortcode ){
        $name  = $fld['key'];
        $value = '';
        if ( isset( $atts[ $name ] ) ) {
            $value = $atts[ $name ];
        } elseif ( isset( $fld['default'] ) && ! empty( $fld['default'] ) ) {
            $value = $fld['default'];
        }

        if ( 'shortcode_title' === $name ) {
            $value = $shortcode['post_title'] ?? '';
        } elseif ( 'shortcode_desc' === $name ) {
            $value = $shortcode['post_content'] ?? '';
        }

        return $value;
    }
    /**
     * Sshortcode input field item dropdown combo-box
     *
     * @param array  $fld  shortcode input field data.
     * @param string $value saved field value.
     */
    public static function itembox( $fld, $value = '' ) {
        global $mpc__;

        $name = isset( $fld['key'] ) ? $fld['key'] : '';
        $type = isset( $fld['content_type'] ) ? $fld['content_type'] : '';

        $values = array();
        if ( is_array( $value ) ) {
            $values = $value;
        } else {
            $values = explode( ',', str_replace( ' ', '', $value ) );
        }

        if ( 'columns' === $fld['key'] && ! in_array( 'variation', $values, true ) ) {
            $values[] = 'variation';
        }

        $multiple = isset( $fld['multiple'] ) && $fld['multiple'] ? 'multiple' : '';

        printf(
            '<select id="%s" class="mpc-sc-itembox" %s>',
            esc_attr( $name ),
            esc_attr( $multiple )
        );

        if ( 'static' === $type ) {
            foreach ( $fld['options'] as $val => $label ) {
                $is_selected = in_array( $val, $values, true ) ? 'selected' : '';

                if ( ! $mpc__['has_pro'] && isset( $fld['pro_options'] ) && in_array( $val, $fld['pro_options'], true ) ) {
                    $is_selected = 'disabled';
                }

                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $val ),
                    esc_attr( $is_selected ),
                    esc_html( $label )
                );
            }
        } else {
            foreach ( $values as $id ) {
                if ( empty( $id ) ) {
                    continue;
                }

                $id          = (int) $id;
                $is_selected = 'selected';

                if ( ! $mpc__['has_pro'] && isset( $fld['pro_options'] ) && in_array( $id, $fld['pro_options'], true ) ) {
                    $is_selected = 'disabled';
                }

                if ( 'cats' === $fld['key'] ) {
                    $term  = get_term( $id );
                    $label = $term->name;
                } else {
                    // skipping further checking as it will only be products.
                    $label = get_the_title( $id );
                }

                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $id ),
                    esc_attr( $is_selected ),
                    esc_html( $label )
                );
            }
        }

        echo '</select>';

        if ( is_array( $value ) ) {
            $value = implode( ',', $value );
        }

        printf(
            '<input type="hidden" class="choicesdp-field" name="%s" value="%s">',
            esc_attr( $name ),
            esc_html( $value )
        );
    }
    /**
     * Display shortcode input field switchbox
     *
     * @param array  $fld  shortcode input field data.
     * @param string $value saved field value.
     */
    public static function switchbox( $fld, $value = '' ) {
        if ( 'checkbox' !== $fld['type'] ) {
            return;
        }

        $checked    = ! empty( $value ) && ( 'on' === $value || true === $value ) ? 'on' : 'off';
        $is_checked = 'on' === $checked ? 'checked' : '';

        printf(
            '<div class="input-field" style="display:none;"><input type="checkbox" name="%s" id="%s" data-off-title="%s" data-on-title="%s" class="hurkanSwitch-switch-input" title="%s" %s></div>',
            esc_attr( $fld['key'] ),
            esc_attr( $fld['key'] ),
            esc_html( $fld['switch_text']['off'] ),
            esc_html( $fld['switch_text']['on'] ),
            esc_html( $fld['label'] ),
            esc_attr( $is_checked )
        );
        ?>
        <div class="hurkanSwitch hurkanSwitch-switch-plugin-box">
            <div class="hurkanSwitch-switch-box switch-animated-<?php echo esc_attr( $checked ); ?>">
                <a class="hurkanSwitch-switch-item <?php echo 'on' === $checked ? 'active' : ''; ?> hurkanSwitch-switch-item-color-success  hurkanSwitch-switch-item-status-on">
                    <span class="lbl"><?php echo esc_html( $fld['switch_text']['on'] ); ?></span>
                    <span class="hurkanSwitch-switch-cursor-selector"></span>
                </a>
                <a class="hurkanSwitch-switch-item <?php echo 'off' === $checked ? 'active' : ''; ?> hurkanSwitch-switch-item-color-  hurkanSwitch-switch-item-status-off">
                    <span class="lbl"><?php echo esc_html( $fld['switch_text']['off'] ); ?></span>
                    <span class="hurkanSwitch-switch-cursor-selector"></span>
                </a>
            </div>
        </div>
        <?php
    }
    /**
     * Shortcode input field column sorting
     *
     * @param array  $fld   shortcode input field data.
     * @param string $value saved field value.
     */
    public static function sortable( $fld, $value ) {
        $value = empty( $value ) ? get_option( 'wmc_sorted_columns' ) : $value;
        ?>
        <div class="mpcdp_settings_option_description col-md-6">
            <div class="mpcdp_option_label"><?php echo esc_html__( 'Active Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
            <div class="mpc-sortable mpca-sorted-options">
                <ul id="active-mpc-columns" class="connectedSortable ui-sortable">
                    <?php MPC_Admin_Helper::column_list( $value, true ); ?>
                </ul>
            </div>
        </div>
        <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
            <div class="mpcdp_option_label"><?php echo esc_html__( 'Inactive Columns', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
            <div class="mpc-sortable mpca-sorted-options">
                <ul id="inactive-mpc-columns" class="connectedSortable ui-sortable">
                    <?php MPC_Admin_Helper::column_list( $value, false ); ?>
                </ul>
            </div>
        </div>
        <?php
        printf(
            '<input type="hidden" class="mpc-sorted-cols" name="%s" value="%s">',
            esc_attr( $fld['key'] ),
            esc_html( $value )
        );
    }

    /**
     * Display no shortcode message
     */
    public static function no_shortcode() {
        ?>
        <div class="mpcdp_settings_toggle mpcdp_container" style="margin-top: 30px;">
            <div class="mpcdp_settings_option visible">
                <div class="mpcdp_row">
                    <div class="mpcdp_settings_option_description col-md-6">
                        <div class="mpcdp_option_label"><?php echo esc_html__( 'No shortcodes found.', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
                        <div class="mpcdp_option_description">
                            <?php
                                $link = sprintf(
                                    '<a href="%s">%s</a>',
                                    esc_url( admin_url( 'admin.php?page=mpc-shortcode' ) ),
                                    __( 'here', 'multiple-products-to-cart-for-woocommerce' )
                                );

                                echo wp_kses_post(
                                    sprintf(
                                        // translators: %1$s: new product table crate link.
                                        __( 'Create a product table shortcode %1$s.', 'multiple-products-to-cart-for-woocommerce' ),
                                        wp_kses_post( $link )
                                    )
                                );
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Display shortcode related notice
     *
     * @param array $notice Notice data.
     */
    public static function shortcode_notice( $notice ){
        // check for created flag.
        if ( isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
            if ( isset( $_GET['created'] ) ) {
                $notice = array(
                    'status'  => 'succcess',
                    'message' => __( 'Shortcode created.', 'multiple-products-to-cart-for-woocommerce' ),
                );
            }
        }

        if ( empty( $notice ) ) return;
        ?>
        <div class="mpc-notice mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
            <div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
                <div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
                    <span class="theme_customizer_icon dashicons dashicons-saved"></span>
                    <div class="mpcdp_settings_option_description">
                        <div class="mpcdp_option_label"><?php echo esc_html( $notice['message'] ); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
