<?php
/**
 * Admin settings page class
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * MPC Admin Settings Page Class.
 */
class MPC_Settings_Page {
    /**
     * Core admin data
     * @var array
     */
    private static $data;

    /**
     * Settings page tab
     * @var string
     */
    private static $tab;

    public static function set(){
        global $mpc__;
        self::$data = $mpc__;
    }

    /**
     * Render All Tables settings page
     */
    public static function all_tables_page(){
        self::render_page( 'all-tables' );
    }

    /**
     * Render New Table settings page
     */
    public static function new_table_page(){
        self::render_page( 'new-table' );
    }

    /**
     * Render General settings page
     */
    public static function settings_page(){
        self::render_page( 'mpc-settings' );
    }

    /**
     * Go to Pro plugin page
     */
    public static function pro_page(){
        header( 'Location: ' . esc_url( self::$data['prolink'] ) );
        exit;
    }



    /**
     * Render admin settings page based on tab
    
     * @param string $page Settings page slug.
     */
    public static function render_page( $page ){
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        MPC_Admin_Helper::save_settings();

        self::set();

        self::$tab = sanitize_title( $page );
        if( 'mpc-settings' === $page ){
            $tab = MPC_Admin_Helper::get_tab();
            $tab = empty( $tab ) ? 'general-settings' : $tab;
            self::$tab = 'all-tables' === self::$tab || 'new-table' === self::$tab ? 'general-settings' : $tab;
        }

        // show error/update messages.
        settings_errors( 'wporg_messages' );

        self::settings_form();
        self::settings_form_popup();
    }
    
    public static function settings_form(){
        ?>
        <form method="post" action="" id="mpcdp_settings_form" enctype="multipart/form-data">
            <div id="mpcdp_settings" class="mpcdp_container">
                <?php self::settings_form_header(); ?>
                <div class="mpcdp_row">
                    <?php self::settings_form_content(); ?>
                </div>
                <?php self::settings_form_sidebar(); ?>
            </div>
        </form>
        <?php
    }

    /**
     * Settings form header section
     */
    public static function settings_form_header(){
        ?>
        <div id="mpcdp_settings_page_header">
			<div id="mpcdp_logo"><?php echo esc_html__( 'Multiple Products to Cart', 'multiple-products-to-cart-for-woocommerce' ); ?></div>
			<div id="mpcdp_customizer_wrapper"></div>
			<div id="mpcdp_toolbar_icons">
				<a class="mpcdp-tippy" target="_blank" href="<?php echo esc_url( self::$data['plugin']['support'] ); ?>" data-tooltip="<?php echo esc_html__( 'Support', 'multiple-products-to-cart-for-woocommerce' ); ?>">
				<span class="tab_icon dashicons dashicons-email"></span>
				</a>
			</div>
		</div>
        <?php
    }

    /**
     * Settings form content section
     */
    public static function settings_form_content(){
        ?>
        <div class="col-md-3" id="left-side">
            <div class="mpcdp_settings_sidebar" data-sticky-container="" style="position: relative;">
                <div class="mpcdp_sidebar_tabs">
                    <div class="inner-wrapper-sticky">
                        <?php self::navigation(); ?>
                        <?php self::save_btn(); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6" id="middle-content">
            <div class="mpcdp_settings_content">
                <?php printf(
                    '<div id="%s" class="hidden mpcdp_settings_tab active" data-tab="%s" style="display: block;">',
                    esc_attr( self::$tab ),
                    esc_attr( self::$tab )
                ); ?>
                <?php self::render_tab(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Settings form sidebar 
     */
    public static function settings_form_sidebar(){
        ?>
        <div id="right-side">
            <div class="mpcdp_settings_promo">
                <?php self::sidebar(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Settings form popup module
     */
    public static function settings_form_popup(){
        ?>
        <div id="mpcpop" class="mpc-popup">
            <div class="image-wrap">
                <span class="mpcpop-close dashicons dashicons-dismiss"></span>
                <div class="mpc-pro-tag">PRO</div>
                <div class="mpc-focus">
                    <?php
                        echo wp_kses_post(
                            sprintf(
                                // translators: %1$s: pro fiture name.
                                __( 'Please upgrade to get %1$s and other advanced features.', 'multiple-products-to-cart-for-woocommerce' ),
                                wp_kses_post( '<span></span>' )
                            )
                        );
                    ?>
                </div>
                <div class="mpcex-features">
                    <p><?php echo esc_html__( 'Unlock advanced features like custom columns for different tables, support for more product types, and an \'Add to Cart\' button with the PRO version. These tools are designed to streamline your workflow, enhance your experience, and boost your sales. We\'re committed to delivering the best solutions for you, 24/7.', 'multiple-products-to-cart-for-woocommerce' ); ?> <a href="<?php echo esc_url( self::$data['prolink'] ); ?>" target="_blank"><?php echo esc_html__( 'Read more', 'multiple-products-to-cart-for-woocommerce' ); ?></a></p>
                </div>
                <a class="mpc-get-pro" href="<?php echo esc_url( self::$data['prolink'] ); ?>" target="_blank"><?php echo esc_html__( 'Upgrade Now', 'multiple-products-to-cart-for-woocommerce' ); ?></a>
            </div>
        </div>
        <?php
    }

    /**
     * Display settings sidebar
     */
    public static function sidebar() {
        $path = MPC_PATH . 'templates/admin/sidebar.php';
        $path = apply_filters( 'mpc_settings_sidebar', $path );

        if ( file_exists( $path ) ) {
            include $path;
        }
    }



    /**
     * Dsiplay admin settings page menu
     */
    public static function navigation() {
        $tab = self::$tab;

        $menus = array(
            array(
                'tab'  => __( 'All Tables', 'multiple-products-to-cart-for-woocommerce' ),
                'icon' => 'dashicons-saved',
            ),
            array(
                'tab'  => __( 'New Table', 'multiple-products-to-cart-for-woocommerce' ),
                'icon' => 'dashicons-shortcode',
            ),
            array(
                'tab'  => __( 'General Settings', 'multiple-products-to-cart-for-woocommerce' ),
                'icon' => 'dashicons-admin-settings',
            ),
            array(
                'tab'  => __( 'Labels', 'multiple-products-to-cart-for-woocommerce' ),
                'icon' => 'dashicons-text',
            ),
            array(
                'tab'  => __( 'Appearence', 'multiple-products-to-cart-for-woocommerce' ),
                'icon' => 'dashicons-admin-appearance',
            ),
            array(
                'tab'  => __( 'Column Sorting', 'multiple-products-to-cart-for-woocommerce' ),
                'icon' => 'dashicons-sort',
            ),
            array(
                'tab'  => __( 'Export', 'multiple-products-to-cart-for-woocommerce' ),
                'icon' => 'dashicons-download',
            ),
            array(
                'tab'  => __( 'Import', 'multiple-products-to-cart-for-woocommerce' ),
                'icon' => 'dashicons-upload',
            ),
        );

        $nonce = wp_create_nonce( 'mpc_option_tab' );
        $page  = admin_url( 'admin.php?page=mpc-settings' );

        if ( isset( $_GET['nonce'] ) && ! empty( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
            $tab = isset( $_GET['mpctable'] ) && ! empty( $_GET['mpctable'] ) && 'new-table' === $tab ? 'all-tables' : $tab;
        }

        foreach ( $menus as $nav ) {
            $nav_ = sanitize_title( $nav['tab'] );

            $url = $page . '&tab=' . $nav_ . '&nonce=' . $nonce;
            if ( 'all-tables' === $nav_ ) {
                $url = admin_url( 'admin.php?page=mpc-shortcodes' );
            } elseif ( 'new-table' === $nav_ ) {
                $url = admin_url( 'admin.php?page=mpc-shortcode' );
            }

            $is_active = $nav_ === $tab ? 'active' : '';
            ?>
            <a href="<?php echo esc_url( $url ); ?>">
                <div class="mpcdp_settings_tab_control <?php echo esc_attr( $is_active ); ?>" data-tab="<?php echo esc_attr( $nav_ ); ?>">
                    <span class="dashicons <?php echo esc_attr( $nav['icon'] ); ?>"></span>
                    <span class="label">
                        <?php echo esc_html( $nav['tab'] ); ?>
                    </span>
                </div>
            </a>
            <?php
        }
    }
    public static function log( $data ) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $data ) || is_object( $data ) ) {
                error_log( print_r( $data, true ) );
            } else {
                error_log( $data );
            }
        }
    }

    /**
     * Display admin settings page save button(s)
     */
    public static function save_btn() {
        if( in_array( self::$tab, array( 'all-tables', 'export', 'import' ), true ) ) return;

        $long  = __( 'Save Changes', 'multiple-products-to-cart-for-woocommerce' );
        $short = __( 'Save', 'multiple-products-to-cart-for-woocommerce' );

        if ( 'new-table' === self::$tab ) {
            $long  = __( 'Create Table', 'multiple-products-to-cart-for-woocommerce' );
            $short = __( 'Create', 'multiple-products-to-cart-for-woocommerce' );

            if ( isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'mpc_option_tab' ) ) {
                if ( isset( $_GET['mpctable'] ) && ! empty( $_GET['mpctable'] ) ) {
                    $long  = __( 'Update Table', 'multiple-products-to-cart-for-woocommerce' );
                    $short = __( 'Save', 'multiple-products-to-cart-for-woocommerce' );
                }
            }
        }
        ?>
        <div class="mpcdp_settings_submit">
            <div class="submit">
                <button class="mpcdp_submit_button">
                    <div class="save-text"><?php echo esc_html( $long ); ?></div>
                    <div class="save-text save-text-mobile"><?php echo esc_html( $short ); ?></div>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Admin settings page handler
     */
    public static function render_tab() {
        $tab = self::$tab;
        if ( in_array( $tab, array( 'new-table', 'all-tables', 'column-sorting', 'import', 'export' ), true ) ) {
            MPC_Settings_Template::get_template( $tab );
            return;
        }

        if ( ! isset( self::$data['fields'][ $tab ] ) || ! isset( self::$data['fields'][ $tab ] ) ) {
            return;
        }

        self::display_notice();

        foreach ( self::$data['fields'][ $tab ] as $section ) {
            self::render_tab_section( $section );
        }
    }
    public static function display_notice(){
        $notice = '';
        if ( isset( $_POST['mpc_admin_settings'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mpc_admin_settings'] ) ), 'mpc_admin_settings_save' ) ) {
            $notice = __( 'Settings Saved', 'multiple-products-to-cart-for-woocommerce' );
        }

        if ( empty( $notice ) ) {
            return;
        }
        ?>
        <div class="mpcdp_settings_section">
            <div class="mpc-notice mpcdp_settings_toggle mpcdp_container" data-toggle-id="footer_theme_customizer">
				<div class="mpcdp_settings_option visible" data-field-id="footer_theme_customizer">
					<div class="mpcdp_settings_option_field_theme_customizer first_customizer_field">
						<span class="theme_customizer_icon dashicons dashicons-saved"></span>
						<div class="mpcdp_settings_option_description">
							<div class="mpcdp_option_label"><?php echo esc_html( $notice ); ?></div>
						</div>
					</div>
				</div>
			</div>
        </div>
        <?php
    }

    /**
     * Render tab section
     *
     * @param array $section Tab section data.
     */
    public static function render_tab_section( $section ){
        ?>
        <div class="mpcdp_settings_section">
            <div class="mpcdp_settings_section_title">
                <?php echo esc_html( $section['section'] ); ?>
            </div>
            <?php
                foreach ( $section['fields'] as $field ) {
                    self::render_tab_section_field( $field );
                }
            ?>
        </div>
        <?php wp_nonce_field( 'mpc_admin_settings_save', 'mpc_admin_settings' ); ?>
        <?php
    }
    
    /**
     * Render tab section field
     *
     * @param array $field Field data.
     */
    public static function render_tab_section_field( $field ){
        $name  = $field['key'] ?? '';
        $label = $field['label'] ?? '';
        $desc  = $field['desc'] ?? '';
        ?>
        <div class="mpcdp_settings_toggle mpcdp_container" data-toggle-id="<?php echo esc_attr( $name ); ?>">
            <div class="mpcdp_settings_option visible" data-field-id="<?php echo esc_attr( $name ); ?>">
                <?php self::pro_ribbon( $field ); ?>
                <div class="mpcdp_row">
                    <div class="mpcdp_settings_option_description col-md-6">
                        <div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
                        <?php if ( ! empty( $desc ) ) : ?>
                            <div class="mpcdp_option_description">
                                <?php echo wp_kses_post( $desc ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php MPC_Admin_Fields::get( $field ); ?>
                </div>
            </div>
            <?php self::followup_field( $field ); ?>
        </div>
        <?php
    }

    /**
     * Display settings field follow-up, extra field options
     *
     * @param array $field settings field data.
     */
    public static function followup_field( $field ) {
        if ( ! isset( $field['followup'] ) || empty( $field['followup'] ) ) {
            return;
        }

        $display = 'none';
        if ( isset( $field['followup_depends'] ) && ! empty( $field['followup_depends'] ) ) {
            $value = get_option( $field['key'] );

            if ( ! empty( $value ) && $value === $field['followup_depends'] ) {
                $display = 'block';
            }
        }

        foreach ( $field['followup'] as $sfld ) :
            $name        = $sfld['key'] ?? '';
            $label       = $sfld['label'] ?? '';
            $desc        = $sfld['desc'] ?? '';
            $placeholder = $sfld['placeholder'] ?? '';
            $value       = get_option( $name );
            ?>
            <div class="mpcdp_settings_option" data-field-id="<?php echo esc_attr( $field['key'] ); ?>" data-depends-on-<?php echo esc_attr( $field['key'] ); ?>="on" data-depends-on="<?php echo esc_attr( $field['key'] ); ?>" data-visible="false" style="display: <?php echo esc_attr( $display ); ?>;">
                <div class="mpcdp_row">
                    <div class="mpcdp_settings_option_description col-md-6">
                        <div class="mpcdp_option_label"><?php echo esc_html( $label ); ?></div>
                        <?php if ( ! empty( $desc ) ) : ?>
                            <div class="mpcdp_option_description">
                                <?php echo wp_kses_post( $desc ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mpcdp_settings_option_field mpcdp_settings_option_field_text col-md-6">
                        <input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_html( $value ); ?>" placeholder="<?php echo esc_html( $placeholder ); ?>">
                    </div>
                </div>
            </div>
            <?php
        endforeach;
    }

    /**
     * Settings field PRO ribbon
     *
     * @param array $field settings field data.
     */
    public static function pro_ribbon( $field ) {
        // if not pro field, skip.
        if ( ! isset( $field['pro'] ) || empty( $field['pro'] ) ) {
            return;
        }

        // if pro enabled, skip.
        if ( isset( self::$data['has_pro'] ) && true === self::$data['has_pro'] ) {
            return;
        }

        ?>
        <div class="mpcdp_settings_option_ribbon mpcdp_settings_option_ribbon_new">
            <?php echo esc_html__( 'PRO', 'multiple-products-to-cart-for-woocommerce' ); ?>
        </div>
        <?php
    }
}
