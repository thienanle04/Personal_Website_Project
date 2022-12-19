<?php
include "mo-openid-social-login-functions.php";
require_once(ABSPATH.'wp-admin/includes/user.php' );
add_action( 'wp_login', 'mo_openid_link_account', 5, 2);
add_action( 'mo_user_register', 'mo_openid_update_role', 1, 2);
add_action( 'mo_user_register','mo_openid_send_email',1, 2 );
if(get_option('mo_openid_popup_window')=='0') {
    add_action('wp_login', 'mo_openid_login_redirect', 11, 2);
}
else {
    add_action('wp_login', 'mo_openid_login_redirect_pop_up', 11, 2);
}
add_action( 'delete_user', 'mo_openid_delete_account_linking_rows' );

add_action('manage_users_custom_column', 'mo_openid_delete_profile_column', 9, 3);
add_filter('manage_users_columns', 'mo_openid_add_custom_column1');
add_action('admin_head', 'mo_openid_delete_social_profile_script');
add_filter( 'login_message', 'mo_openid_account_linking');
add_action('widgets_init', function () {
    register_widget("mo_openid_login_wid");
});
add_action('widgets_init', function () {
    register_widget("mo_openid_sharing_ver_wid");
});
add_action('widgets_init', function () {
    register_widget("mo_openid_sharing_hor_wid");
});

if (get_option('mo_openid_logout_redirection_enable') == 1) {
    add_filter('logout_url', 'mo_openid_redirect_after_logout', 0, 1);
}


class mo_openid_login_wid extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'mo_openid_login_wid',
            'miniOrange Social Login Widget',
            array(
                'description' => __( 'Login using Social Apps like Google, Facebook, LinkedIn, Microsoft, Instagram.', 'flw' ),
                'customize_selective_refresh' => true,
            )
        );
    }

    public function widget( $args, $instance ) {
        extract( $args );

        echo $args['before_widget'];
        $this->openidloginForm();

        echo $args['after_widget'];
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
        return $instance;
    }


    public function openidloginForm(){

        if ($GLOBALS['pagenow'] === 'wp-login.php') {
            ?>
            <script
                    src="https://code.jquery.com/jquery-1.12.4.js"
                    integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU="
                    crossorigin="anonymous"></script>
            <?php
        }

        $selected_theme = esc_attr(get_option('mo_openid_login_theme'));
        $appsConfigured = get_option('mo_openid_google_enable') | get_option('mo_openid_salesforce_enable') | get_option('mo_openid_facebook_enable') | get_option('mo_openid_linkedin_enable') | get_option('mo_openid_instagram_enable') | get_option('mo_openid_amazon_enable') | get_option('mo_openid_twitter_enable') | get_option('mo_openid_vkontakte_enable') | get_option('mo_openid_yahoo_enable') | get_option('mo_openid_snapchat_enable') | get_option('mo_openid_dribbble_enable') | get_option('mo_openid_discord_enable');
        $spacebetweenicons = esc_attr(get_option('mo_login_icon_space'));
        $customWidth = esc_attr(get_option('mo_login_icon_custom_width'));
        $customHeight = esc_attr(get_option('mo_login_icon_custom_height'));
        $customSize = esc_attr(get_option('mo_login_icon_custom_size'));
        $customBackground = esc_attr(get_option('mo_login_icon_custom_color'));
        $customHoverBackground = esc_attr(get_option('mo_login_icon_custom_hover_color'));
        $customSmartBackground1 = esc_attr(get_option('mo_login_icon_custom_smart_color1'));
        $customSmartBackground2 = esc_attr(get_option('mo_login_icon_custom_smart_color2'));
        $customTheme = esc_attr(get_option('mo_openid_login_custom_theme'));
        $customTextofTitle = esc_attr(get_option('mo_openid_login_button_customize_text'));
        $customBoundary = esc_attr(get_option('mo_login_icon_custom_boundary'));
        $customLogoutName = esc_attr(get_option('mo_openid_login_widget_customize_logout_name_text'));
        $customLogoutLink = get_option('mo_openid_login_widget_customize_logout_text');
        $customTextColor=esc_attr(get_option('mo_login_openid_login_widget_customize_textcolor'));
        $customText=esc_html(get_option('mo_openid_login_widget_customize_text'));
        $effectStatus =esc_html(get_option('mo_openid_button_theme_effect'));
        $application_pos = get_option('app_pos');

        if(get_option('mo_openid_gdpr_consent_enable')) {
            $gdpr_setting = "disabled='disabled'";
        }
        else
            $gdpr_setting ='';

        $url = esc_url(get_option('mo_openid_privacy_policy_url'));
        $text = esc_html(get_option('mo_openid_privacy_policy_text'));

        if( !empty($text) && strpos(get_option('mo_openid_gdpr_consent_message'),$text)){
            $consent_message = str_replace(get_option('mo_openid_privacy_policy_text'),'<a target="_blank" href="'.$url.'">'.$text.'</a>',get_option('mo_openid_gdpr_consent_message'));
        }else if(empty($text)){
            $consent_message = get_option('mo_openid_gdpr_consent_message');
        }


        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $sign_up_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $mo_URL=strstr($sign_up_url, "?", true);
        if($mo_URL) {
            setcookie("mo_openid_signup_url",  $mo_URL, time() + (86400 * 30), "/");}
        else{
            setcookie("mo_openid_signup_url", $sign_up_url, time() + (86400 * 30), "/");}
        if(get_option('mo_openid_gdpr_consent_enable'))
        {
            $dis =  "dis";
        }
        else{
            $dis = '';
        }
        if( ! is_user_logged_in() ) {
            $values=array(
                'appsConfigured' => $appsConfigured,
                'selected_apps' => '',
                'application_pos' => $application_pos,
                'customTextColor' => $customTextColor,
                'customText' => $customText,
                'consent_message' => $consent_message,
                'selected_theme' => $selected_theme,
                'view' => '',
                'gdpr_setting' => $gdpr_setting,
                'spacebetweenicons' => $spacebetweenicons,
                'customWidth' => $customWidth,
                'customHeight' => $customHeight,
                'customBoundary' => $customBoundary,
                'buttonText' => $customTextofTitle,
                'dis' => $dis,
                'customTextofTitle' => $customTextofTitle,
                'customSize' => $customSize,
                'html' => '',
                'customBackground' => $customBackground,
                'customHoverBackground' => $customHoverBackground,
                'customSmartBackground1' => $customSmartBackground1,
                'customSmartBackground2' => $customSmartBackground2,
                'customTheme' => $customTheme,
                'effectStatus'=>$effectStatus
            );
            $html=$this->display_apps($values);
            $html.='<br/>';
            echo $html;

        }
        else {
            global $current_user;
            $current_user = wp_get_current_user();
            $customLogoutName = str_replace('##username##', $current_user->display_name, $customLogoutName);
            $link_with_username = $customLogoutName;
            if (empty($customLogoutName)  || empty($customLogoutLink)) {
                ?>
                <div id="logged_in_user" class="mo_openid_login_wid">
                    <li><?php echo $link_with_username;?> <a href="<?php echo wp_logout_url( site_url() ); ?>" title="<?php _e('Logout','flw');?>"><?php _e($customLogoutLink,'flw');?></a></li>
                </div>
                <?php
            }
            else {
                ?>
                <div id="logged_in_user" class="mo_openid_login_wid">
                    <li><?php echo $link_with_username;?> <a href="<?php echo wp_logout_url( site_url() ); ?>" title="<?php _e('Logout','flw');?>"><?php _e($customLogoutLink,'flw');?></a></li>
                </div>
                <?php
            }
        }
    }

    public function mo_openid_customize_logo(){
        $logo =" <div style='float:left;margin-bottom: 0px;margin-top: 0px;' class='mo_image_id'>
                <a target='_blank' href='https://www.miniorange.com/'>
                <img alt='logo' src='". plugins_url('/includes/images/miniOrange.png',__FILE__) ."' class='mo_openid_image'>
                </a>
                </div>
                <br/><br/>";
        return $logo;
    }

    public function if_custom_app_exists($app_name){

        if(get_option('mo_openid_apps_list'))
        {
            $appslist = maybe_unserialize(get_option('mo_openid_apps_list'));
            if(isset($appslist[$app_name])) {
                if (get_option('mo_openid_enable_custom_app_' . $app_name))
                    return 'true';
                else
                    return 'false';
            }
            else
                return 'false';
        }
        return 'false';
    }

    public function openidloginFormShortCode( $atts ){
        global $post;
        $html = '';
        $apps="";
        $selected_theme = isset( $atts['shape'] )? esc_attr($atts['shape']) : esc_attr(get_option('mo_openid_login_theme'));
        $selected_apps = isset( $atts['apps'] )? esc_attr($atts['apps']) : "";
        $application_pos = get_option('app_pos');
        $appsConfigured = get_option('mo_openid_facebook_enable') | get_option('mo_openid_google_enable') | get_option('mo_openid_vkontakte_enable') | get_option('mo_openid_twitter_enable') | get_option('mo_openid_instagram_enable') | get_option('mo_openid_linkedin_enable') |  get_option('mo_openid_amazon_enable') | get_option('mo_openid_salesforce_enable') | get_option('mo_openid_yahoo_enable') | get_option('mo_openid_snapchat_enable') | get_option('mo_openid_dribbble_enable') | get_option('mo_openid_discord_enable');
        $spacebetweenicons = isset( $atts['space'] )? esc_attr(intval($atts['space'])) : esc_attr(intval(get_option('mo_login_icon_space')));
        $customWidth = isset( $atts['width'] )? esc_attr(intval($atts['width'])) : esc_attr(intval(get_option('mo_login_icon_custom_width')));
        $customHeight = isset( $atts['height'] )? esc_attr(intval($atts['height'])) : esc_attr(intval(get_option('mo_login_icon_custom_height')));
        $customSize = isset( $atts['size'] )? esc_attr(intval($atts['size'])) : esc_attr(intval(get_option('mo_login_icon_custom_size')));
        $customBackground = isset( $atts['background'] )? esc_attr($atts['background']) : esc_attr(get_option('mo_login_icon_custom_color'));
        $customHoverBackground = isset( $atts['background_hover'] )? esc_attr($atts['background_hover']) : esc_attr(get_option('mo_login_icon_custom_hover_color'));
        $customSmartBackground1 = isset( $atts['background_smart1'] )? esc_attr($atts['background_smart1']) : esc_attr(get_option('mo_login_icon_custom_smart_color1'));
        $customSmartBackground2 = isset( $atts['background_smart2'] )? esc_attr($atts['background_smart2']) : esc_attr(get_option('mo_login_icon_custom_smart_color2'));
        $effectStatus = isset( $atts['effectStatus'] )? esc_attr($atts['effectStatus']) : esc_attr(get_option('mo_openid_button_theme_effect'));
        $customTheme = isset( $atts['theme'] )? esc_attr($atts['theme']) : esc_attr(get_option('mo_openid_login_custom_theme'));
        $buttonText = esc_html(get_option('mo_openid_login_button_customize_text'));
        $customTextofTitle = esc_attr(get_option('mo_openid_login_button_customize_text'));
        $logoutUrl = esc_url(wp_logout_url(site_url()));
        $customBoundary = isset( $atts['edge'] )? esc_attr($atts['edge']) : esc_attr(get_option('mo_login_icon_custom_boundary'));
        $customLogoutName = esc_attr(get_option('mo_openid_login_widget_customize_logout_name_text'));
        $customLogoutLink = get_option('mo_openid_login_widget_customize_logout_text');
        $customTextColor= isset( $atts['color'] )? esc_attr($atts['color']) : esc_attr(get_option('mo_login_openid_login_widget_customize_textcolor'));
        $customText=isset( $atts['heading'] )? esc_html($atts['heading']) :esc_html(get_option('mo_openid_login_widget_customize_text'));
        $view=isset( $atts['view'] )? esc_attr($atts['view']) : "";
        $appcnt=isset( $atts['appcnt'] )? esc_attr($atts['appcnt']) : "";
        if($selected_theme == 'longbuttonwithtext'){
            $selected_theme = 'longbutton';
        }

        if($customTheme == 'custombackground'){
            $customTheme = 'custom';
        }

        if(get_option('mo_openid_gdpr_consent_enable')) {
            $gdpr_setting = "disabled='disabled'";
        }
        else
            $gdpr_setting = '';

        $url = esc_url(get_option('mo_openid_privacy_policy_url'));
        $text = esc_html(get_option('mo_openid_privacy_policy_text'));

        if( !empty($text) && strpos(get_option('mo_openid_gdpr_consent_message'),$text)){
            $consent_message = str_replace(get_option('mo_openid_privacy_policy_text'),'<a target="_blank" href="'.$url.'">'.$text.'</a>',get_option('mo_openid_gdpr_consent_message'));
        }else if(empty($text)){
            $consent_message = get_option('mo_openid_gdpr_consent_message');
        }
        if(get_option('mo_openid_gdpr_consent_enable'))
        {
            $dis =  "dis";
        }
        else{
            $dis = '';
        }
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $sign_up_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $mo_URL=strstr($sign_up_url, "?", true);
        if($mo_URL) {setcookie("mo_openid_signup_url",  $mo_URL, time() + (86400 * 30), "/");}
        else{setcookie("mo_openid_signup_url", $sign_up_url, time() + (86400 * 30), "/");}
        if( ! is_user_logged_in() ) {

            $values=array(
                'appsConfigured' => $appsConfigured,
                'selected_apps' => $selected_apps,
                'application_pos' => $application_pos,
                'customTextColor' => $customTextColor,
                'customText' => $customText,
                'consent_message' => $consent_message,
                'selected_theme' => $selected_theme,
                'view' => $view,
                'gdpr_setting' => $gdpr_setting,
                'spacebetweenicons' => $spacebetweenicons,
                'customWidth' => $customWidth,
                'customHeight' => $customHeight,
                'customBoundary' => $customBoundary,
                'customHoverBackground' => $customHoverBackground,
                'customSmartBackground1' => $customSmartBackground1,
                'customSmartBackground2' => $customSmartBackground2,
                'buttonText' => $buttonText,
                'dis' => $dis,
                'customTextofTitle' => $customTextofTitle,
                'customSize' => $customSize,
                'html' => $html,
                'customBackground' => $customBackground,
                'customTheme' => $customTheme,
                'effectStatus'=> $effectStatus,
                'appcnt' => $appcnt
            );
            $html=$this->display_apps($values);

        }else {
            global $current_user;
            $current_user = wp_get_current_user();
            $customLogoutName = str_replace('##username##', $current_user->display_name, $customLogoutName);
            $flw = __($customLogoutLink,"flw");
            if (empty($customLogoutName)  || empty($customLogoutLink)) {
                $html .= '<div id="logged_in_user" class="mo_openid_login_wid">' . $customLogoutName . ' <a href=' . $logoutUrl .' title=" ' . $flw . '"> ' . $flw . '</a></div>';
            }
            else {
                $html .= '<div id="logged_in_user" class="mo_openid_login_wid">' . $customLogoutName . ' <a href=' . $logoutUrl .' title=" ' . $flw . '"> ' . $flw . '</a></div>';
            }
        }
        return $html;
    }

    public function display_apps($values){
        $appsConfigured=$values['appsConfigured'];
        $selected_apps=$values['selected_apps'];
        $application_pos=$values['application_pos'];
        $customTextColor=$values['customTextColor'];
        $customText=$values['customText'];
        $consent_message=$values['consent_message'];
        $gdpr_setting=$values['gdpr_setting'];
        $spacebetweenicons=$values['spacebetweenicons'];
        $customWidth=$values['customWidth'];
        $customHeight=$values['customHeight'];
        $customBoundary=$values['customBoundary'];
        $buttonText=$values['buttonText'];
        $dis=$values['dis'];
        $customTextofTitle=$values['customTextofTitle'];
        $customSize=$values['customSize'];
        $selected_theme=$values['selected_theme'];
        $html=$values['html'];
        $view=$values['view'];
        $customBackground=$values['customBackground'];
        $customHoverBackground=$values['customHoverBackground'];
        $customSmartBackground1=$values['customSmartBackground1'];
        $customSmartBackground2=$values['customSmartBackground2'];
        $effectStatus= $values['effectStatus'];
        $customTheme=$values['customTheme'];
        $appcnt=isset($values['appcnt'])?$values['appcnt']:'';
        if( $appsConfigured || $selected_apps!="") {

            if($selected_apps!="")
                $apps=explode(',', $selected_apps);
            else
                $apps=explode('#', $application_pos);

            $this->mo_openid_load_login_script();
            $html .= "<div class='mo-openid-app-icons'>
					 <p style='color:#".$customTextColor."; width: fit-content;'> $customText</p>";
            if(get_option('mo_openid_gdpr_consent_enable')){
                $html .= '<label class="mo-consent" style="width: 100%"><input type="checkbox" onchange="mo_openid_on_consent_change(this)" value="1" id="mo_openid_consent_checkbox">';
                $html .=  $consent_message.'</label>';
            }
            $count = -1;
            if($selected_apps!=""){
                if(mo_openid_is_customer_registered())
                {
                    foreach ($apps as $select_apps) {
                        $app_dis = "";
                        if ($selected_theme == 'longbutton') {
                            if ($view == "horizontal" && isset($appcnt)) {
                                $count++;
                                if ("".$count == "".$appcnt) {
                                    $html .= "<br/>";
                                    $count = 0;
                                }
                            }
                        }
                        $app_values= array(
                            'gdpr_setting' => $gdpr_setting,
                            'spacebetweenicons' => $spacebetweenicons,
                            'customWidth' => $customWidth,
                            'customHeight' => $customHeight,
                            'customBoundary' => $customBoundary,
                            'buttonText' => $buttonText,
                            'dis' => $dis,
                            'customTextofTitle' => $customTextofTitle,
                            'customSize' => $customSize,
                            'selected_theme' => $selected_theme,
                            'html' => $html,
                            'view' => $view,
                            'customBackground' => $customBackground,
                            'customHoverBackground' => $customHoverBackground,
                            'customSmartBackground1' => $customSmartBackground1,
                            'customSamrtBackground2' => $customSmartBackground2,
                            'effectStatus'=> $effectStatus,
                            'app_dis' => $app_dis,
                            'customTheme' => $customTheme,
                            'customer_register' => 'yes',
                        );
                        $html=$this->select_app($select_apps,$app_values);
                    }
                }
                else{
                    foreach ($apps as $select_apps) {
                        $app_dis = "";
                        if ($selected_theme == 'longbutton') {
                            if ($view == "horizontal" && isset($appcnt)) {
                                $count++;
                                if ($count == $appcnt) {
                                    $html .= "<br/>";
                                    $count = 0;
                                }
                            }
                        }
                        $app_values= array(
                            'gdpr_setting' => $gdpr_setting,
                            'spacebetweenicons' => $spacebetweenicons,
                            'customWidth' => $customWidth,
                            'customHeight' => $customHeight,
                            'customBoundary' => $customBoundary,
                            'buttonText' => $buttonText,
                            'dis' => $dis,
                            'customTextofTitle' => $customTextofTitle,
                            'customSize' => $customSize,
                            'selected_theme' => $selected_theme,
                            'html' => $html,
                            'view' => $view,
                            'customBackground' => $customBackground,
                            'customHoverBackground' => $customHoverBackground,
                            'customSmartBackground1' => $customSmartBackground1,
                            'customSamrtBackground2' => $customSmartBackground2,
                            'effectStatus'=> $effectStatus,
                            'app_dis' => $app_dis,
                            'customTheme' => $customTheme,
                            'customer_register' => 'no',
                        );
                        $html=$this->select_app($select_apps,$app_values);
                    }
                }
            }
            else{
                foreach ($apps as $select_apps) {
                    if (get_option('mo_openid_'.$select_apps.'_enable')) {
                        $app_dis = "";
                        if ($selected_theme == 'longbutton') {
                            if ($view == "horizontal" && isset($appcnt)) {
                                $count++;
                                if ($count == $appcnt) {
                                    $html .= "<br/>";
                                    $count = 0;
                                }
                            }
                        }
                        $app_values= array(
                            'gdpr_setting' => $gdpr_setting,
                            'spacebetweenicons' => $spacebetweenicons,
                            'customWidth' => $customWidth,
                            'customHeight' => $customHeight,
                            'customBoundary' => $customBoundary,
                            'buttonText' => $buttonText,
                            'dis' => $dis,
                            'customTextofTitle' => $customTextofTitle,
                            'customSize' => $customSize,
                            'selected_theme' => $selected_theme,
                            'html' => $html,
                            'view' => $view,
                            'customBackground' => $customBackground,
                            'customHoverBackground' => $customHoverBackground,
                            'customSmartBackground1' => $customSmartBackground1,
                            'customSamrtBackground2' => $customSmartBackground2,
                            'effectStatus'=> $effectStatus,
                            'app_dis' => $app_dis,
                            'customTheme' => $customTheme,
                            'customer_register' => 'yes',
                        );
                        $html=$this->select_app($select_apps,$app_values);
                    }
                }
            }
            $html .= '</div> <br>';
        }
        else {
            $html .= '<div>No apps configured. Please contact your administrator.</div>';
        }
        if( $appsConfigured && get_option('moopenid_logo_check') == 1 ){
            $logo_html=$this->mo_openid_customize_logo();
            $html .= $logo_html;
        }
        return $html;
    }

    public function select_app($select_apps,$app_values){
        if( get_option('mo_openid_fonawesome_load') == 1)
            wp_enqueue_style( 'mo-openid-sl-wp-font-awesome',plugins_url('includes/css/mo-font-awesome.min.css', __FILE__), false );
        wp_enqueue_style( 'mo-wp-style-icon',plugins_url('includes/css/mo_openid_login_icons.css?version='.MO_OPENID_SOCIAL_LOGIN_VERSION, __FILE__), false );
        wp_enqueue_style( 'mo-wp-bootstrap-social',plugins_url('includes/css/bootstrap-social.css', __FILE__), false );
        if( get_option('mo_openid_bootstrap_load') == 1)
            wp_enqueue_style( 'mo-wp-bootstrap-main',plugins_url('includes/css/bootstrap.min-preview.css', __FILE__), false );

        $gdpr_setting = $app_values['gdpr_setting'];
        $spacebetweenicons = $app_values['spacebetweenicons'];
        $customWidth = $app_values['customWidth'];
        $customHeight = $app_values['customHeight'];
        $customBoundary = $app_values['customBoundary'];
        $buttonText = $app_values['buttonText'];
        $dis = $app_values['dis'];
        $customTextofTitle = $app_values['customTextofTitle'];
        $customSize = $app_values['customSize'];
        $selected_theme = $app_values['selected_theme'];
        $html = $app_values['html'];
        $view = $app_values['view'];
        $customBackground = $app_values['customBackground'];
        $customHoverBackground = $app_values['customHoverBackground'];
        $customSmartBackground1 = $app_values['customSmartBackground1'];
        $customSmartBackground2 = $app_values['customSamrtBackground2'];
        $effectStatus = $app_values['effectStatus'];
        $app_dis = $app_values['app_dis'];
        $customTheme = $app_values['customTheme'];
        $customer_register  = $app_values['customer_register'];

        if($select_apps == 'facebook' || $select_apps == 'fb' || $select_apps == 'dribbble' || $select_apps == 'snapchat' || $select_apps == 'discord'){
            if($select_apps=='fb')
                $select_apps='facebook';
            $custom_app = esc_attr($this->if_custom_app_exists($select_apps));;
            $app_dis == "false" ? $app_dis = "disable" : $app_dis = "";
        }
        else{
            $custom_app = esc_attr($this->if_custom_app_exists($select_apps));
            $app_dis = $this->check_capp_reg_cust($customer_register, $custom_app);
        }

        $html = $this->add_apps($select_apps, $customTheme, $gdpr_setting, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $dis, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $customHoverBackground,$customSmartBackground1,$customSmartBackground2,$effectStatus, $app_dis);
        return $html;
    }

    public function check_capp_reg_cust($customer_register,$custom_app)
    {
        if($customer_register=='no' && $custom_app =='false')
            return 'disable';
    }
    //for shortcode
    public function add_apps($app_name,$theme,$gdpr_setting,$spacebetweenicons,$customWidth,$customHeight,$customBoundary,$buttonText,$dis,$customTextofTitle,$customSize,$selected_theme,$custom_app,$html,$view,$customBackground,$customHoverBackground,$customSmartBackground1,$customSmartBackground2,$effectStatus,$app_dis)
    {
        $default_color= array('facebook'=>'#1877F2','google'=>'#DB4437','vkontakte'=>'#466482','twitter'=>'#2795e9','yahoo'=>'#430297','yandex'=>'#2795e9','instagram'=>'#3f729b','linkedin'=>'#007bb6','amazon'=>'#ff9900','paypal'=>'#0d127a','salesforce'=>'#1ab7ea','apple'=>'#000000','steam'=>'#000000','wordpress'=>'#587ea3','pinterest'=>'#cb2027','spotify'=>'#19bf61','tumblr'=>'#2c4762','twitch'=>'#720e9e','github'=>'#000000','dribbble'=>'#ee66aa','flickr'=>'#ff0084','stackexchange'=>'0000ff','snapchat'=>'#fffc00','reddit'=>'#ff4301','odnoklassniki'=>'#f97400','foursquare'=>'#f94877','wechat'=>'#00c300','vimeo'=>'#1ab7ea','line'=>'#00c300','hubspot'=>'#fa7820','trello'=>'#0079bf','discord'=>'#7289da','meetup'=>'#e51937','stackexchange'=>'#0000FF','wiebo'=>'#df2029','kakao'=>'#ffe812','livejournal'=>'#3c1361','naver'=>'#3EAF0E','teamsnap'=>'#ff9a1a','slack'=>'#4c154d','gitlab'=>'#30353e','dropbox'=>'#0061ff','mailru'=>'#0000FF');

        if($customWidth!=='auto'||$customWidth=='Auto'||$customWidth=='AUTO')
            $customWidth.='px';
        if($theme=="default")
        {
            $a[] = $app_name;
            foreach($a as $app){
                $icon = $app;
                if($app == "vkontakte"){
                     $icon = "vk";
                }
             
                if($app_name=="google")
                {
                    if ($selected_theme == 'longbutton') {
                        $html .= "<a  rel='nofollow' " . $gdpr_setting . " style='margin-left: " . $spacebetweenicons . "px !important;background: rgb(255,255,255)!important; background:linear-gradient(90deg, rgba(255,255,255,1) 40px, rgb(79, 113, 232) 5%) !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;border-color: rgba(79, 113, 232, 1);border-bottom-width: thin;'";
                        if ($view == "horizontal") {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block-inline mo_btn-social mo_btn-google mo_btn-custom-dec login-button mo_btn_".$effectStatus."'";
                        } else {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block mo_btn-social mo_btn-google mo_btn-custom-dec login-button mo_btn_".$effectStatus."'";
                        }
                        if($app_dis!="disable")
                            $html .= "onClick=\"moOpenIdLogin('google','" . $custom_app . "');\"";
                        $html.="> <img  class='fa'  style='padding-top:" . ($customHeight - 35) . "px !important; margin-top: 0' src='" . plugins_url('includes/images/icons/g.png', __FILE__) . "'>" . $buttonText . " Google</a>";
                    } else {
                        $html .= "<a class='" . $dis . " login-button ' rel='nofollow' title= ' " . $customTextofTitle . " Google'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('google','" . $custom_app . "');\""; $html.=" title= ' " . $customTextofTitle . "  google'><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:".$default_color['google'].";font-size: " . ($customSize - 16) . "px !important;text-align:center; padding-top: 8px;color:white'  class='fab fa-google  mo_btn-mo mo_openid-login-button login-button mo_btn_".$effectStatus."_i  " . $selected_theme . "' ></i></a>";
                    }
                    return $html;
                }else{

                    if ($selected_theme == 'longbutton') {
                        $html .= "<a rel='nofollow' " . $gdpr_setting . " style='margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                        if ($view == "horizontal") {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block-inline mo_btn-social mo_btn-".$icon." mo_btn-custom-dec login-button mo_btn_".$effectStatus." '";
                        } else {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block mo_btn-social mo_btn-".$icon." mo_btn-custom-dec login-button mo_btn_".$effectStatus."_i  '";
                        }
                        if($app_dis!="disable")
                            $html .= "onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\"";
                        $html .= "> <i  class='fab fa-".$icon."'  style='padding-top:" . ($customHeight - 35) . "px !important; margin-top: 0' src='" . plugins_url('includes/images/icons/'.$app.'.png', __FILE__) . "'></i>" . $buttonText . " ".ucfirst($app)."</a>";
                    } else {
                        $html .= "<a class='" . $dis . " login-button' rel='nofollow' title= ' " . $customTextofTitle . " ".$app."'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\""; $html.=" title= ' " . $customTextofTitle . "  ".$app."'><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:".$default_color[$app].";font-size: " . ($customSize - 16) . "px !important;text-align:center; padding-top: 8px;color:white'  class='fab fa-".$icon."  mo_btn-mo mo_openid-login-button login-button mo_btn_".$effectStatus."_i  " . $selected_theme . "' ></i></a>";
                    }
                    return $html;
                }
            }
        }
            else if($theme=="custom"){

                $a[] = $app_name;
                foreach($a as $app){
                    $icon = $app;
                    if($app == "vkontakte"){
                         $icon = "vk";
                    }
                    if ($selected_theme == 'longbutton') {
                        $html .= "<a rel='nofollow'   " . $gdpr_setting . ""; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\"";$html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:#" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                        if ($view == "horizontal") {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block-inline mo_btn-social mo_btn-customtheme mo_btn-custom-dec login-button mo_btn_".$effectStatus."'";
                        } else {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block mo_btn-social mo_btn-customtheme mo_btn-custom-dec login-button mo_btn_".$effectStatus."'";
                        }
                        $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='fab fa-".$icon."'></i> " . $buttonText . " ".ucfirst($app)."</a>";
                    } else {
                        $html .= "<a class='" . $dis . " login-button' rel='nofollow' title= ' " . $customTextofTitle . " ".$app."'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\"";$html.=" ><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:#" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='fab mo_btn-mo fa-".$icon." custom-login-button  mo_btn_".$effectStatus."_i  " . $selected_theme . "' ></i></a>";
                    }
                    return $html;
                }
            }
            else if($theme=="white"){

                $a[] = $app_name;
                foreach($a as $app){
                    $icon = $app;
                    if($app == "vkontakte"){
                         $icon = "vk";
                    }
                  
                    if ($selected_theme == 'longbutton') {
                        $html .= "<a rel='nofollow'   " . $gdpr_setting . ""; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\"";$html.=" style='border-color:#000000;margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; border-radius: " . $customBoundary . "px !important;color:#000000;background:#ffffff'";
                        if ($view == "horizontal") {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block-inline mo_btn-social mo_btn-".$app."-white mo_openid_mo_btn-custom-dec login-button mo_btn_".$effectStatus."  '";
                        } else {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block mo_btn-social mo_btn-".$app."-white mo_openid_mo_btn-custom-dec login-button mo_btn_".$effectStatus." '";
                        }
                        if ($app_dis != "disable")
                            $html .= "onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\"";
                        $html .= "> <i style='color:".$default_color[$app]."; border-right:#ffffff; padding-top:" . ($customHeight - 35) . "px !important;' class='fab fa-".$icon."'></i>" . $buttonText . " ".ucfirst($app)."</a>";
                    } else {
                        $html .= "<a class='" . $dis . " login-button' rel='nofollow' title= ' " . $customTextofTitle . " ".ucfirst($app)."'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\""; $html.=" title= ' " . $customTextofTitle . "  ".$app."'><i style='background:white;margin-top:10px;text-align:center;padding:7px 0px 1px 0px;box-sizing:initial;width:" . $customSize . "px !important;height:" . ($customSize-8) . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;color:" . $default_color[$app] . " !important;font-size: " . ($customSize - 14) . "px !important;border: 1px solid black;'  class='fab fa-".$icon." mo_btn-mo  mo_btn_".$effectStatus."_i  " . $selected_theme . "' ></i></a>";
                    }
                    return $html;
                }
            }
            else if($theme=="hover"){

                $a[] = $app_name;
                foreach($a as $app){
                    $icon = $app;
                    if($app == "vkontakte"){
                         $icon = "vk";
                    }
                    
                    if ($selected_theme == 'longbutton') {
                        $html .= "<a rel='nofollow' " . $gdpr_setting . " style='border-color:#000000; margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                        if ($view == "horizontal") {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block-inline mo_btn-social mo_btn-".$icon."-hov mo_openid_mo_btn-custom-dec login-button mo_btn_".$effectStatus." '";
                        } else {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block mo_btn-social mo_btn-".$icon."-hov mo_openid_mo_btn-custom-dec login-button mo_btn_".$effectStatus."'";
                        }
                        $html .= "onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\"";
                        $html .= "> <i style='color: ".$default_color[$app]."; border-right:#ffffff; padding-top:" . ($customHeight - 35) . "px !important' class='fab fa-".$icon."'></i>" . $buttonText ." ".ucfirst($app_name)."</a>";
                    } else {
                        $html .= "<a class='" . $dis . " login-button' rel='nofollow' title= ' " . $customTextofTitle . " ".ucfirst($app)."'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\""; $html.=" title= ' " . $customTextofTitle . " " .ucfirst($app)."'><i style='background:white;margin-top:10px;text-align:center;padding:7px 0px 1px 0px;box-sizing:initial;width:" . $customSize . "px !important;height:" . ($customSize-8) . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;color:" . $default_color[$app] . ";font-size: " . ($customSize - 14) . "px !important;border: 1px solid black;'  class='fab fa-".$icon." mo_openid_i".$icon."-hov mo_btn_".$effectStatus."_i   " . $selected_theme . "' ></i></a>";
                    }
                    return $html;
                }
            }
            else if($theme=="custom_hover"){

                $a[] = $app_name;
                foreach($a as $app){
                    $icon = $app;
                    if($app == "vkontakte"){
                         $icon = "vk";
                    }
                    
                    if ($selected_theme == 'longbutton') {
                        $html .= "<a rel='nofollow'   " . $gdpr_setting . ""; if($app_dis!="disable") $html.=" onMouseOver=\"this.style.color= 'white';this.style.background= '#".$customHoverBackground."';\"
        onMouseOut=\"this.style.color= '#".$customHoverBackground."';this.style.background= 'white';\" onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\"";$html.=" style='background:white; margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; color:#" . $customHoverBackground . ";border-color:#" . $customHoverBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                        if ($view == "horizontal") {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block-inline mo_btn-social  mo_openid_mo_btn-custom-dec login-button mo_btn_".$effectStatus." '";
                        } else {
                            $html .= "class='mo_btn mo_btn-mo mo_btn-block mo_btn-social mo_openid_mo_btn-custom-dec login-button mo_btn_".$effectStatus."'";
                        }
                        $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='fab fa-".$icon."'></i> " . $buttonText . " ".ucfirst($app)."</a>";
                    } else {
                        $html .= "<a class='" . $dis . " login-button' rel='nofollow' title= ' " . $customTextofTitle . " ".ucfirst($app)."'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\"";
                        $html.=" title= ' " . $customTextofTitle . " " .ucfirst($app)."'><i onMouseOver=\"this.style.color= 'white';this.style.background= '#".$customHoverBackground."';\" onMouseOut=\"this.style.color= '#".$customHoverBackground."';this.style.background= 'white';\"  style='background:white;margin-top:10px;text-align:center;padding:7px 0px 1px 0px;box-sizing:initial;width:" . $customSize . "px !important;height:" . ($customSize-8) . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;color:#" . $customHoverBackground . ";font-size: " . ($customSize - 14) . "px !important;border: 1px solid #" . $customHoverBackground . ";'  class='fab fa-".$icon."  " . $selected_theme . "  mo_btn_".$effectStatus."_i  ' ></i></a>";
                    }
                    return $html;
                }
            }
            else if($theme=="smart"){
                $a[] = $app_name;
                foreach($a as $app){
                    $icon = $app;
                    if($app == "vkontakte"){
                         $icon = "vk";
                    }
                    
                    if ($selected_theme == 'longbutton') {
                        $html .= "<a rel='nofollow'   " . $gdpr_setting . ""; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\"";$html.=" style='color:#ffffff;background:linear-gradient(90deg,#$customSmartBackground1,#$customSmartBackground2);margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; border-radius: " . $customBoundary . "px !important;'";
                        if ($view == "horizontal") {
                            $html .= "class='mo_btn_smart mo_btn-mo mo_btn-block-inline mo_btn-social mo_openid_mo_btn-customtheme mo_openid_mo_btn-custom-dec login-button mo_btn_".$effectStatus."'";
                        } else {
                            $html .= "class='mo_btn_smart mo_btn-mo mo_btn-block mo_btn-social mo_openid_mo_btn-customtheme mo_openid_mo_btn-custom-dec login-button mo_btn_".$effectStatus."'";
                        }
                        $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important;' class='fab fa-".$icon."'></i> " . $buttonText . " ".ucfirst($app)."</a>";
                    } else {
                        $html .= "<a class='" . $dis . " login-button' rel='nofollow' title= ' " . $customTextofTitle . " ".ucfirst($app)."'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('".$app."','" . $custom_app . "');\"";$html.=" ><i style='margin-top:10px;text-align:center;padding:7px 0px 1px 0px;box-sizing:initial;width:" . $customSize . "px !important;height:" . ($customSize-8) . "px !important;margin-left:" . ($spacebetweenicons+4) . "px !important; background:linear-gradient(90deg,#$customSmartBackground1,#$customSmartBackground2)!important;font-size: " . ($customSize - 16) . "px !important;color:white'  class='fab mo_btn-mo fa-".$icon." mo_openid_custom-login-button mo_button_smart_i mo_btn_".$effectStatus."_i " . $selected_theme . "' ></i></a>";
                    }
                    return $html;
                }
            }

    }

    private function mo_openid_load_login_script() {
        wp_enqueue_script( 'js-cookie-script',plugins_url('includes/js/mo_openid_jquery.cookie.min.js', __FILE__), array('jquery'));
        if(!get_option('mo_openid_gdpr_consent_enable')){?>
            <script>
                jQuery(".mo_btn-mo").prop("disabled",false);
            </script>
        <?php }
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery(".login-button").css("cursor", "pointer");
            });
            function mo_openid_on_consent_change(checkbox){
                if (! checkbox.checked) {
                    jQuery('#mo_openid_consent_checkbox').val(1);
                    jQuery(".mo_btn-mo").attr("disabled", true);
                    jQuery(".login-button").addClass("dis");
                } else {
                    jQuery('#mo_openid_consent_checkbox').val(0);
                    jQuery(".mo_btn-mo").attr("disabled", false);
                    jQuery(".login-button").removeClass("dis");
                }
            }

            var perfEntries = performance.getEntriesByType("navigation");

            if (perfEntries[0].type === "back_forward") {
                location.reload(true);
            }
            function HandlePopupResult(result) {
                window.location = "<?php echo mo_openid_get_redirect_url();?>";
            }
            function moOpenIdLogin(app_name,is_custom_app) {
                var current_url = window.location.href;
                var cookie_name = "redirect_current_url";
                var d = new Date();
                d.setTime(d.getTime() + (2 * 24 * 60 * 60 * 1000));
                var expires = "expires="+d.toUTCString();
                document.cookie = cookie_name + "=" + current_url + ";" + expires + ";path=/";

                <?php
                if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
                    $http = "https://";
                } else {
                    $http =  "http://";
                }
                ?>
                var base_url = '<?php echo esc_url(site_url());?>';
                var request_uri = '<?php echo $_SERVER['REQUEST_URI'];?>';
                var http = '<?php echo $http;?>';
                var http_host = '<?php echo $_SERVER['HTTP_HOST'];?>';
                var default_nonce = '<?php echo wp_create_nonce( 'mo-openid-get-social-login-nonce' ); ?>';
                var custom_nonce = '<?php echo wp_create_nonce( 'mo-openid-oauth-app-nonce' ); ?>';

                if(is_custom_app == 'false'){
                    if ( request_uri.indexOf('wp-login.php') !=-1){
                        var redirect_url = base_url + '/?option=getmosociallogin&wp_nonce=' + default_nonce + '&app_name=';

                    }else {
                        var redirect_url = http + http_host + request_uri;
                        if(redirect_url.indexOf('?') != -1){
                            redirect_url = redirect_url +'&option=getmosociallogin&wp_nonce=' + default_nonce + '&app_name=';
                        }
                        else
                        {
                            redirect_url = redirect_url +'?option=getmosociallogin&wp_nonce=' + default_nonce + '&app_name=';
                        }
                    }
                }
                else {
                    if ( request_uri.indexOf('wp-login.php') !=-1){
                        var redirect_url = base_url + '/?option=oauthredirect&wp_nonce=' + custom_nonce + '&app_name=';


                    }else {
                        var redirect_url = http + http_host + request_uri;
                        if(redirect_url.indexOf('?') != -1)
                            redirect_url = redirect_url +'&option=oauthredirect&wp_nonce=' + custom_nonce + '&app_name=';
                        else
                            redirect_url = redirect_url +'?option=oauthredirect&wp_nonce=' + custom_nonce + '&app_name=';
                    }

                }
                if( <?php echo get_option('mo_openid_popup_window');?>) {
                    var myWindow = window.open(redirect_url + app_name, "", "width=700,height=620");
                }
                else{
                    window.location.href = redirect_url + app_name;
                }
            }
        </script>
        <?php
    }
}

/**
 * Sharing Widget Horizontal
 *
 */
class mo_openid_sharing_hor_wid extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'mo_openid_sharing_hor_wid',
            'miniOrange Sharing - Horizontal',
            array(
                'description' => __( 'Share using horizontal widget. Lets you share with Social Apps like Google, Facebook, LinkedIn, Pinterest, Reddit.', 'flw' ),
                'customize_selective_refresh' => true,
            )
        );
    }

    public function widget( $args, $instance ) {
        extract( $args );

        echo $args['before_widget'];
        $this->show_sharing_buttons_horizontal();

        echo $args['after_widget'];
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
        return $instance;
    }

    public function show_sharing_buttons_horizontal(){
        global $post;
        $title = str_replace('+', '%20', urlencode($post->post_title));
        $content=strip_shortcodes( strip_tags( get_the_content() ) );
        $post_content=$content;
        $excerpt = '';
        $landscape = 'horizontal';
        include( plugin_dir_path( __FILE__ ) . 'class-mo-openid-social-share.php');
    }

}


/**
 * Sharing Vertical Widget
 *
 */
class mo_openid_sharing_ver_wid extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'mo_openid_sharing_ver_wid',
            'miniOrange Sharing - Vertical',
            array(
                'description' => __( 'Share using a vertical floating widget. Lets you share with Social Apps like Google, Facebook, LinkedIn, Pinterest, Reddit.', 'flw' ),
                'customize_selective_refresh' => true,
            )
        );
    }

    public function widget( $args, $instance ) {
        extract( $args );
        extract( $instance );

        $wid_title = apply_filters( 'widget_title', $instance['wid_title'] );
        $alignment = apply_filters( 'alignment', isset($instance['alignment'])? $instance['alignment'] : 'left');
        $left_offset = apply_filters( 'left_offset', isset($instance['left_offset'])? $instance['left_offset'] : '20');
        $right_offset = apply_filters( 'right_offset', isset($instance['right_offset'])? $instance['right_offset'] : '0');
        $top_offset = apply_filters( 'top_offset', isset($instance['top_offset'])? $instance['top_offset'] : '100');
        $space_icons = apply_filters( 'space_icons', isset($instance['space_icons'])? $instance['space_icons'] : '10');

        echo $args['before_widget'];
        if ( ! empty( $wid_title ) )
            echo $args['before_title'] . $wid_title . $args['after_title'];

        echo "<div style='display:inline-block !important; overflow: visible; z-index: 10000000; padding: 10px; border-radius: 4px; opacity: 1; -webkit-box-sizing: content-box!important; -moz-box-sizing: content-box!important; box-sizing: content-box!important; width:40px; position:fixed;" .(isset($alignment) && $alignment != '' && isset($instance[$alignment.'_offset']) ? esc_attr($alignment) .': '. ( $instance[$alignment.'_offset'] == '' ? 0 : esc_attr($instance[$alignment.'_offset'] )) .'px;' : '').(isset($top_offset) ? 'top: '. ( $top_offset == '' ? 0 : esc_attr($top_offset )) .'px;' : '') ."'>";

        $this->show_sharing_buttons_vertical($space_icons);

        echo '</div>';

        echo $args['after_widget'];
    }

    /*Called when user changes configuration in Widget Admin Panel*/
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
        $instance['alignment'] = $new_instance['alignment'];
        $instance['left_offset'] = $new_instance['left_offset'];
        $instance['right_offset'] = $new_instance['right_offset'];
        $instance['top_offset'] = $new_instance['top_offset'];
        $instance['space_icons'] = $new_instance['space_icons'];
        return $instance;
    }


    public function show_sharing_buttons_vertical($space_icons){
        global $post;
        if($post->post_title) {
            $title = str_replace('+', '%20', urlencode($post->post_title));
        } else {
            $title = get_bloginfo( 'name' );
        }
        $content=strip_shortcodes( strip_tags( get_the_content() ) );
        $post_content=$content;
        $excerpt = '';
        $landscape = 'vertical';

        include( plugin_dir_path( __FILE__ ) . 'class-mo-openid-social-share.php');
    }

    /** Widget edit form at admin panel */
    function form( $instance ) {
        /* Set up default widget settings. */
        $defaults = array('alignment' => 'left', 'left_offset' => '20', 'right_offset' => '0', 'top_offset' => '100' , 'space_icons' => '10');

        foreach( $instance as $key => $value ){
            $instance[ $key ] = esc_attr( $value );
        }

        $instance = wp_parse_args( (array)$instance, $defaults );
        ?>
        <p>
            <script>
                function moOpenIDVerticalSharingOffset(alignment){
                    if(alignment == 'left'){
                        jQuery('.moVerSharingLeftOffset').css('display', 'block');
                        jQuery('.moVerSharingRightOffset').css('display', 'none');
                    }else{
                        jQuery('.moVerSharingLeftOffset').css('display', 'none');
                        jQuery('.moVerSharingRightOffset').css('display', 'block');
                    }
                }
            </script>
            <label for="<?php echo esc_attr($this->get_field_id( 'alignment' )); ?>">Alignment</label>
            <select onchange="moOpenIDVerticalSharingOffset(this.value)" style="width: 95%" id="<?php echo esc_attr($this->get_field_id( 'alignment' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'alignment' )); ?>">
                <option value="left" <?php echo $instance['alignment'] == 'left' ? 'selected' : ''; ?>>Left</option>
                <option value="right" <?php echo $instance['alignment'] == 'right' ? 'selected' : ''; ?>>Right</option>
            </select>
        <div class="moVerSharingLeftOffset" <?php echo $instance['alignment'] == 'right' ? 'style="display: none"' : ''; ?>>
            <label for="<?php echo esc_attr($this->get_field_id( 'left_offset' )); ?>">Left Offset</label>
            <input style="width: 95%" id="<?php echo esc_attr($this->get_field_id( 'left_offset' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'left_offset' )); ?>" type="text" value="<?php echo $instance['left_offset']; ?>" />px<br/>
        </div>
        <div class="moVerSharingRightOffset" <?php echo $instance['alignment'] == 'left' ? 'style="display: none"' : ''; ?>>
            <label for="<?php echo esc_attr($this->get_field_id( 'right_offset' )); ?>">Right Offset</label>
            <input style="width: 95%" id="<?php echo esc_attr($this->get_field_id( 'right_offset' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'right_offset' )); ?>" type="text" value="<?php echo $instance['right_offset']; ?>" />px<br/>
        </div>
        <label for="<?php echo esc_attr($this->get_field_id( 'top_offset' )); ?>">Top Offset</label>
        <input style="width: 95%" id="<?php echo esc_attr($this->get_field_id( 'top_offset' )); ?>" name="<?php echo esc_attr( $this->get_field_name( 'top_offset' )); ?>" type="text" value="<?php echo $instance['top_offset']; ?>" />px<br/>
        <label for="<?php echo esc_attr($this->get_field_id( 'space_icons' )); ?>">Space between icons</label>
        <input style="width: 95%" id="<?php echo esc_attr($this->get_field_id( 'space_icons' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'space_icons' )); ?>" type="text" value="<?php echo $instance['space_icons']; ?>" />px<br/>
        </p>
        <?php
    }

}

function mo_openid_disabled_register_message()
{
    $message = get_option('mo_openid_register_disabled_message') . ' Go to <a href="' . site_url() . '">Home Page</a>';
    wp_die($message);
}

function mo_openid_get_redirect_url() {
    $current_url = isset($_COOKIE["redirect_current_url"]) ? $_COOKIE["redirect_current_url"]:get_option('siteurl');
    $pos = strpos($_SERVER['REQUEST_URI'], '/openidcallback');

    if ($pos === false) {
        $url = str_replace('?option=moopenid','',$_SERVER['REQUEST_URI']);
        $current_url = str_replace('?option=moopenid','',$current_url);

    } else {
        $temp_array1 = explode('/openidcallback',$_SERVER['REQUEST_URI']);
        $url = $temp_array1[0];
        $temp_array2 = explode('/openidcallback',$current_url);
        $current_url = $temp_array2[0];
    }

    $option = get_option( 'mo_openid_login_redirect' );
    $redirect_url = site_url();

    if( $option == 'same' ) {
        if(!is_null($current_url)){
            if(strpos($current_url,get_option('siteurl').'/wp-login.php')!== false)
            {
                $redirect_url=get_option('siteurl');
            }
            else
                $redirect_url = $current_url;
        }
        else{
            if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
                $http = "https://";
            } else {
                $http =  "http://";
            }
            $redirect_url = urldecode(html_entity_decode(esc_url($http . $_SERVER["HTTP_HOST"] . $url)));
            if(html_entity_decode(esc_url(remove_query_arg('ss_message', $redirect_url))) == wp_login_url() || strpos($_SERVER['REQUEST_URI'],'wp-login.php') !== FALSE || strpos($_SERVER['REQUEST_URI'],'wp-admin') !== FALSE){
                $redirect_url = site_url().'/';
            }
        }
    } else if( $option == 'homepage' ) {
        $redirect_url = site_url();
    } else if( $option == 'dashboard' ) {
        $redirect_url = admin_url();
    } else if( $option == 'custom' ) {
        $redirect_url = get_option('mo_openid_login_redirect_url');
    }else if($option == 'relative') {
        $redirect_url =  site_url() . (null !== get_option('mo_openid_relative_login_redirect_url')?get_option('mo_openid_relative_login_redirect_url'):'');
    }

    if(strpos($redirect_url,'?') !== FALSE) {
        $redirect_url .= get_option('mo_openid_auto_register_enable') ? '' : '&autoregister=false';
    } else{
        $redirect_url .= get_option('mo_openid_auto_register_enable') ? '' : '?autoregister=false';
    }
    return $redirect_url;
}

function mo_openid_redirect_after_logout($logout_url)
{
    if (get_option('mo_openid_logout_redirection_enable')) {
        $logout_redirect_option = get_option('mo_openid_logout_redirect');
        $redirect_url = site_url();
        if ($logout_redirect_option == 'homepage') {
            $redirect_url = $logout_url . '&redirect_to=' . home_url();
        } else if ($logout_redirect_option == 'currentpage') {
            if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                $http = "https://";
            } else {
                $http = "http://";
            }
            $redirect_url = $logout_url . '&redirect_to=' . $http . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
        } else if ($logout_redirect_option == 'login') {
            $redirect_url = $logout_url . '&redirect_to=' . site_url() . '/wp-admin';
        } else if ($logout_redirect_option == 'custom') {
            $redirect_url = $logout_url . '&redirect_to=' . site_url() . (null !== get_option('mo_openid_logout_redirect_url') ? get_option('mo_openid_logout_redirect_url') : '');
        }
        return $redirect_url;
    } else {
        return $logout_url;
    }

}

function get_valid_user_based_on_mo_token( $token = '', $fields = 'all' ) {
    if ( empty( $token ) ) {
        return false;
    }

    $args = array(
        'fields'     => $fields,
        'meta_key'   => 'mo_expire',
        'order'      => 'DESC',
        'orderby'    => 'meta_value',
        'meta_query' => array(
            0 => array(
                'key'     => 'mo_token',
                'value'   => sanitize_text_field( $token ),
                'compare' => '=',
            ),
        ),
    );

    $users = new WP_User_Query( $args );

    $users_data = $users->get_results();


    if ( empty( $users_data ) ) {
        return false;
    }

//    foreach ( $users_data as $key => $user ) {
//        $expire = get_user_meta( $user->ID, '_wtlwp_expire', true );
//
//        if ( is_string( $expire ) && strpos( $expire, '_after_access' ) ) {
//            $expiry_options = get_expiry_options();
//            $timestamp      = ! empty( $expiry_options[ $expire ] ) ? $expiry_options[ $expire ]['timestamp'] : 0;
//            $expire         = get_current_gmt_timestamp() + $timestamp;
//            update_user_meta( $user->ID, '_wtlwp_expire', $expire );
//        } elseif ( $expire <= get_current_gmt_timestamp() ) {
//            unset( $users_data[ $key ] );
//        }
//    }

    return $users_data;

}

function get_expiry_options() {

    $expiry_options = array(
        'hour'                 => array( 'group' => 'from_now', 'group_name' => __( 'From Now', 'temporary-login-without-password' ), 'label' => __( 'One Hour', 'temporary-login-without-password' ), 'timestamp' => HOUR_IN_SECONDS, 'order' => 5 ),
        'min'                 => array( 'group' => 'from_now', 'group_name' => __( 'From Now', 'temporary-login-without-password' ), 'label' => __( 'One Hour', 'temporary-login-without-password' ), 'timestamp' => MINUTE_IN_SECONDS, 'order' => 6 ),
        '3_hours'              => array( 'group' => 'from_now', 'group_name' => __( 'From Now', 'temporary-login-without-password' ), 'label' => __( 'Three Hours', 'temporary-login-without-password' ), 'timestamp' => HOUR_IN_SECONDS * 3, 'order' => 10 ),
        'day'                  => array( 'group' => 'from_now', 'group_name' => __( 'From Now', 'temporary-login-without-password' ), 'label' => __( 'One Day', 'temporary-login-without-password' ), 'timestamp' => DAY_IN_SECONDS, 'order' => 15 ),
        '3_days'               => array( 'group' => 'from_now', 'group_name' => __( 'From Now', 'temporary-login-without-password' ), 'label' => __( 'Three Days', 'temporary-login-without-password' ), 'timestamp' => DAY_IN_SECONDS * 3, 'order' => 20 ),
        'week'                 => array( 'group' => 'from_now', 'group_name' => __( 'From Now', 'temporary-login-without-password' ), 'label' => __( 'One Week', 'temporary-login-without-password' ), 'timestamp' => WEEK_IN_SECONDS, 'order' => 25 ),
        'month'                => array( 'group' => 'from_now', 'group_name' => __( 'From Now', 'temporary-login-without-password' ), 'label' => __( 'One Month', 'temporary-login-without-password' ), 'timestamp' => MONTH_IN_SECONDS, 'order' => 30 ),
        'hour_after_access'    => array( 'group' => 'after_access', 'group_name' => __( 'After Access', 'temporary-login-without-password' ), 'label' => __( 'One Hour', 'temporary-login-without-password' ), 'expiry_label' => __( '1 hour after access', 'temporary-login-without-password' ), 'timestamp' => HOUR_IN_SECONDS, 'order' => 6 ),
        '3_hours_after_access' => array( 'group' => 'after_access', 'group_name' => __( 'After Access', 'temporary-login-without-password' ), 'label' => __( 'Three Hours', 'temporary-login-without-password' ), 'expiry_label' => __( '3 hours after access', 'temporary-login-without-password' ), 'timestamp' => HOUR_IN_SECONDS * 3, 'order' => 11 ),
        'day_after_access'     => array( 'group' => 'after_access', 'group_name' => __( 'After Access', 'temporary-login-without-password' ), 'label' => __( 'One Day', 'temporary-login-without-password' ), 'expiry_label' => __( '1 day after access', 'temporary-login-without-password' ), 'timestamp' => DAY_IN_SECONDS, 'order' => 16 ),
        '3_days_after_access'  => array( 'group' => 'after_access', 'group_name' => __( 'After Access', 'temporary-login-without-password' ), 'label' => __( 'Three Days', 'temporary-login-without-password' ), 'expiry_label' => __( '3 days after access', 'temporary-login-without-password' ), 'timestamp' => DAY_IN_SECONDS * 3, 'order' => 21 ),
        'week_after_access'    => array( 'group' => 'after_access', 'group_name' => __( 'After Access', 'temporary-login-without-password' ), 'label' => __( 'One Week', 'temporary-login-without-password' ), 'expiry_label' => __( '1 week after access', 'temporary-login-without-password' ), 'timestamp' => WEEK_IN_SECONDS, 'order' => 26 ),
        'month_after_access'   => array( 'group' => 'after_access', 'group_name' => __( 'After Access', 'temporary-login-without-password' ), 'label' => __( 'One Month', 'temporary-login-without-password' ), 'expiry_label' => __( '1 month after access', 'temporary-login-without-password' ), 'timestamp' => MONTH_IN_SECONDS, 'order' => 31 ),
        'custom_date'          => array( 'group' => 'custom', 'group_name' => __( 'Custom', 'temporary-login-without-password' ), 'label' => __( 'Custom Date', 'temporary-login-without-password' ), 'timestamp' => 0, 'order' => 35 ),
    );

    // Now, one can add their own options.
    $expiry_options = apply_filters( 'tlwp_expiry_options', $expiry_options );

    // Get Order options to sort $expiry_options array by it's array
    foreach ( $expiry_options as $key => $options ) {
        $expiry_options[ $key ]['order']        = ! empty( $options['order'] ) ? $options['order'] : 100;
        $expiry_options[ $key ]['group']        = ! empty( $options['group'] ) ? $options['group'] : __( 'from_now', '' );
        $expiry_options[ $key ]['group_name']  = ! empty( $options['group_name'] ) ? $options['group_name'] : __( 'From Now', '' );
        $expiry_options[ $key ]['expiry_label'] = ! empty( $options['expiry_label'] ) ? $options['expiry_label'] : '';

        $orders[ $key ] = ! empty( $options['order'] ) ? $options['order'] : 100;
    }

    // Sort $expiry_options array by it's order value
    array_multisort( $orders, SORT_ASC, $expiry_options );

    return $expiry_options;
}

function get_current_gmt_timestamp() {
    return strtotime( gmdate( 'Y-m-d H:i:s', time() ) );

}

function get_user_expire_time( $expiry_option = 'day', $date = '' ) {

    $expiry_options = get_expiry_options();

    $expiry_option = in_array( $expiry_option, array_keys( $expiry_options ) ) ? $expiry_option : 'day';

    if ( 'custom_date' === $expiry_option ) {

        // For the custom_date option we need to simply expire login at particular date
        // So, we don't need to do addition in the current timestamp
        $current_timestamp = 0;
        $timestamp         = strtotime( $date );
    } elseif ( strpos( $expiry_option, '_after_access' ) > 0 ) {
        return $expiry_option;
    } else {

        // We need current gmt timestamp and from now we need to expire temporary login
        // after specified time. So, we need to add into current timestamp
        $current_timestamp = get_current_gmt_timestamp();
        $timestamp         = $expiry_options[ $expiry_option ]['timestamp'];
    }

    return $current_timestamp + floatval( $timestamp );

}

function is_login_expired( $user_id = 0 ) {

    if ( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    if ( empty( $user_id ) ) {
        return false;
    }

    $expire = get_user_meta( $user_id, 'mo_expire', true );

    return ! empty( $expire ) && is_numeric( $expire ) && get_current_gmt_timestamp() >= floatval( $expire ) ? true : false;

}

function is_valid_temporary_login( $user_id = 0, $check_expiry = true ) {

    if ( empty( $user_id ) ) {
        return false;
    }

    $check = get_user_meta( $user_id, 'mo_user', true );

    if ( ! empty( $check ) && $check_expiry ) {
        $check = ! ( is_login_expired( $user_id ) );
    }

    return ! empty( $check ) ? true : false;

}

function mf_remove_menu_pages() {
    remove_submenu_page('users.php' , 'user-new.php' );
}

function init_wtlwp() {
    $i = get_current_user_id();
    $temp_user = get_user_meta($i, 'temporary_url', true);
    if ($temp_user) {
        add_action('admin_menu', 'mf_remove_menu_pages');
    }
    if ($GLOBALS['pagenow'] === 'users.php') {
        if ($temp_user) {
            echo "
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js'></script>
    <script type='text/javascript'>

        jQuery(window).on('load',function () {
        document.getElementsByClassName('page-title-action')[0].remove();
        });      </script>";
        }
    }

    if ( ! empty( $_GET['mo_token'] ) ) {

        $wtlwp_token = sanitize_key( $_GET['mo_token'] );  // Input var okay.
        $users       = get_valid_user_based_on_mo_token( $wtlwp_token );

        $temporary_user = '';
        if ( ! empty( $users ) ) {
            $temporary_user = $users[0];
        }

        if ( ! empty( $temporary_user ) ) {

            $temporary_user_id = $temporary_user->ID;
            $do_login          = true;
            if ( is_user_logged_in() ) {
                $current_user_id = get_current_user_id();
                if ( $temporary_user_id !== $current_user_id ) {
                    wp_logout();
                } else {
                    $do_login = false;
                }
            }

            if ( $do_login ) {
                if ( ! empty( $temporary_user_id)) {
                    if (is_login_expired($temporary_user_id)) {
                        wp_logout();
                        wp_delete_user( $temporary_user_id);
                        wp_safe_redirect(home_url());
                        exit();
                    }
                }
                $temporary_user_login = $temporary_user->login;
                update_user_meta( $temporary_user_id, 'mo_last_login', get_current_gmt_timestamp() ); // phpcs:ignore
                wp_set_current_user( $temporary_user_id, $temporary_user_login );
                wp_set_auth_cookie( $temporary_user_id );

                // Set login count
                $login_count_key = 'mo_login_count';
                $login_count     = get_user_meta( $temporary_user_id, $login_count_key, true );

                // If we already have a count, increment by 1
                if ( ! empty( $login_count ) ) {
                    $login_count ++;
                } else {
                    $login_count = 1;
                }

                update_user_meta( $temporary_user_id, $login_count_key, $login_count );
                do_action( 'wp_login', $temporary_user_login, $temporary_user );
            }

            $request_uri = $_SERVER['REQUEST_URI'];
            $request_uri = '';
            //var_dump($request_uri);

            $redirect_to_url = apply_filters( 'tlwp_login_redirect', apply_filters( 'login_redirect', network_site_url( remove_query_arg( 'mo_token', $request_uri ) ), false, $temporary_user ), $temporary_user );
            //var_dump($redirect_to_url);exit();
        } else {
            // Temporary user not found?? Redirect to home page.
            $redirect_to_url = home_url();
        }

        wp_safe_redirect( $redirect_to_url ); // Redirect to given url after successful login.
        exit();
    } else {
        return 1;
    }

}

function mo_openid_login_validate(){


    $present_time_rateus_pop = date('Y-m-d');
    if(get_option('check_ten_rate_us') < 5 ){
        if(get_option('mo_openid_user_activation_date') < $present_time_rateus_pop){
            update_option('mo_openid_rateus_activation','1');
            update_option('check_ten_rate_us','6');
        }
    }

    if ( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'getmosociallogin' ) !== false ) {
        if (isset($_REQUEST['wp_nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['wp_nonce']);
            if (!wp_verify_nonce($nonce, 'mo-openid-get-social-login-nonce')) {
                wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
            } else {
                mo_openid_initialize_social_login();
            }
        }
    }
    else if( isset($_POST['mo_openid_go_back_registration_nonce']) and isset( $_POST['option'] ) and $_POST['option'] == "mo_openid_go_back_registration" ){
        $nonce = sanitize_text_field($_POST['mo_openid_go_back_registration_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-go-back-register-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
        } else {
            update_option('mo_openid_verify_customer','true');
        }
    }
    else if ( isset($_POST['mo_openid_custom_form_submitted_nonce']) and isset($_POST['username']) and $_POST['option'] == 'mo_openid_custom_form_submitted' ){
        $nonce = sanitize_text_field($_POST['mo_openid_custom_form_submitted_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-custom-form-submitted-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
        } else {
            global $wpdb;
            $db_prefix = $wpdb->prefix;
            $curr_user=get_current_user_id();
            if($curr_user!=0) {
                update_custom_data($curr_user);
                header("Location:".get_option('profile_completion_page'));
                exit;
            }
            $user_picture = sanitize_text_field($_POST["user_picture"]);
            $user_url = sanitize_text_field($_POST["user_url"]);
            $last_name = sanitize_text_field($_POST["last_name"]);
            $username=sanitize_text_field($_POST["username"]);
            $user_email=sanitize_text_field($_POST["user_email"]);
            $random_password=sanitize_text_field($_POST["random_password"]);
            $user_full_name = sanitize_text_field($_POST["user_full_name"]);
            $first_name = sanitize_text_field($_POST["first_name"]);
            $decrypted_app_name = sanitize_text_field($_POST["decrypted_app_name"]);
            $decrypted_user_id = sanitize_text_field($_POST["decrypted_user_id"]);
            $call = sanitize_text_field($_POST["call"]);
            $user_profile_url = sanitize_text_field($_POST["user_profile_url"]);
            $social_app_name = sanitize_text_field($_POST["social_app_name"]);
            $social_user_id = sanitize_text_field($_POST["social_user_id"]);

            $userdata = array(
                'user_login'  => $username,
                'user_email'    => $user_email,
                'user_pass'   =>  $random_password,
                'display_name' => $user_full_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'user_url' => $user_url,
            );

			// Checking if username already exist
            $user_name_user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users where user_login = %s", $userdata['user_login']));

            if( isset($user_name_user_id) ){
                $email_array = explode('@', $user_email);
                $user_name = $email_array[0];
                $user_name_user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users where user_login = %s", $user_name));
                $i = 1;
                while(!empty($user_name_user_id) ){
                    $uname=$user_name.'_' . $i;
                    $user_name_user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " .$db_prefix."users where user_login = %s", $uname));
                    $i++;
                    if(empty($user_name_user_id)){
                        $userdata['user_login']= $uname;
                        $username =$uname;
                    }
                }

                if($i==1)
                    $userdata['user_login'] = $uname;

                if( isset($user_name_user_id) ){
                    echo '<br/>'."Error Code Existing Username: ".get_option('mo_existing_username_error_message');
                    exit();
                }
            }

            $user_id   = wp_insert_user( $userdata);
            if(is_wp_error( $user_id )) {
                print_r($user_id);
                wp_die("Error Code ".$call.": ".get_option('mo_registration_error_message'));
            }

            update_option('mo_openid_user_count',get_option('mo_openid_user_count')+1);

            if($social_app_name!="")
                $appname=$social_app_name;
            else
                $appname=$decrypted_app_name;

            $session_values= array(
                'username' => $username,
                'user_email' => $user_email,
                'user_full_name' => $user_full_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'user_url' => $user_url,
                'user_picture' => $user_picture,
                'social_app_name' => $appname,
                'social_user_id' => $social_user_id,
            );

            mo_openid_start_session_login($session_values);
            $user	= get_user_by('id', $user_id );
            update_custom_data($user_id);
            //registration hook
            do_action( 'mo_user_register', $user_id,$user_profile_url);
            mo_openid_link_account($user->user_login, $user);
            $linked_email_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM " . $db_prefix . "mo_openid_linked_user where linked_social_app = \"%s\" AND identifier = %s", $appname, $social_user_id));
            mo_openid_login_user($linked_email_id,$user_id,$user,$user_picture,0);
        }
    }

    else if(isset($_POST['mo_openid_profile_form_submitted_nonce']) and isset( $_POST['option'] ) and $_POST['option'] == "mo_openid_profile_form_submitted"){
        $nonce = sanitize_text_field($_POST['mo_openid_profile_form_submitted_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-profile-form-submitted-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
        } else {
            $username = sanitize_text_field($_POST['username_field']);
            $user_email = sanitize_email($_POST['email_field']);
            $user_picture = sanitize_text_field($_POST["user_picture"]);
            $user_url = sanitize_text_field($_POST["user_url"]);
            $last_name = sanitize_text_field($_POST["last_name"]);
            $user_full_name = sanitize_text_field($_POST["user_full_name"]);
            $first_name = sanitize_text_field($_POST["first_name"]);
            $decrypted_app_name = sanitize_text_field($_POST["decrypted_app_name"]);
            $decrypted_user_id = sanitize_text_field($_POST["decrypted_user_id"]);
            mo_openid_save_profile_completion_form($username, $user_email, $first_name, $last_name, $user_full_name, $user_url, $user_picture, $decrypted_app_name, $decrypted_user_id);
        }
    }
    else if( isset($_POST['mo_openid_go_back_login_nonce']) and isset( $_POST['option'] ) and $_POST['option'] == "mo_openid_go_back_login" ){
        $nonce = sanitize_text_field($_POST['mo_openid_go_back_login_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-go-back-login-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
        } else {
            update_option('mo_openid_registration_status','');
            delete_option('mo_openid_admin_email');
            delete_option('mo_openid_admin_phone');
            delete_option('mo_openid_admin_password');
            delete_option('mo_openid_admin_customer_key');
            delete_option('mo_openid_verify_customer');
        }
    }
    else if(isset($_POST['mo_openid_forgot_password_nonce']) and isset($_POST['option']) and $_POST['option'] == 'mo_openid_forgot_password'){
        $nonce = sanitize_text_field($_POST['mo_openid_forgot_password_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-forgot-password-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
        } else {
            $email ='';
            if( mo_openid_check_empty_or_null( $email ) ) {
                if( mo_openid_check_empty_or_null( $_POST['email'] ) ) {
                    update_option( 'mo_openid_message', 'No email provided. Please enter your email below to reset password.');
                    mo_openid_show_error_message();
                    if(get_option('regi_pop_up') =="yes"){
                        update_option("pop_login_msg",get_option("mo_openid_message"));
                        mo_pop_show_verify_password_page();
                    }
                    return;
                } else {
                    $email = sanitize_email($_POST['email']);
                }
            }
            $customer = new CustomerOpenID();
            $content = json_decode($customer->forgot_password($email),true);
            if(strcasecmp($content['status'], 'SUCCESS') == 0){
                update_option( 'mo_openid_message','You password has been reset successfully. Please enter the new password sent to your registered mail here.');
                mo_openid_show_success_message();
                if(get_option('regi_pop_up') =="yes"){
                    update_option("pop_login_msg",get_option("mo_openid_message"));
                    mo_pop_show_verify_password_page();
                }
            }else{
                update_option( 'mo_openid_message','An error occurred while processing your request. Please make sure you are registered in miniOrange with the <b>'. $content['email'] .'</b> email address. ');
                mo_openid_show_error_message();
                if(get_option('regi_pop_up') =="yes"){
                    update_option("pop_login_msg",get_option("mo_openid_message"));
                    mo_pop_show_verify_password_page();
                }
            }
        }
    }
    else if( isset($_POST['mo_openid_connect_register_nonce']) and isset( $_POST['option'] ) and $_POST['option'] == "mo_openid_connect_register_customer" ) {	//register the admin to miniOrange
        $nonce = sanitize_text_field($_POST['mo_openid_connect_register_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-connect-register-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
        } else {
            mo_openid_register_user();
        }
    }
    else if( isset( $_POST['show_login'] ) )
    {
        mo_pop_show_verify_password_page();
    }
    else if( isset($_POST['mo_openid_account_linking_nonce']) and isset($_POST['option'] ) and strpos( $_POST['option'], 'mo_openid_account_linking' ) !== false ){
        $nonce = sanitize_text_field($_POST['mo_openid_account_linking_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-account-linking-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
        } else {
            mo_openid_start_session();
            //link account
            if (!isset($_POST['mo_openid_create_new_account'])) {
                $nonce = wp_create_nonce('mo-openid-disable-social-login-nonce');
                $url = site_url() . '/wp-login.php?option=disable-social-login&wp_nonce=' . $nonce;
                header('Location:' . $url);
                exit;
            } //create new account
            else {
                $user_details = array(
                    'username' => sanitize_text_field($_POST['username']),
                    'user_email' => sanitize_email($_POST['user_email']),
                    'user_full_name' => sanitize_text_field($_POST['user_full_name']),
                    'first_name' => sanitize_text_field($_POST['first_name']),
                    'last_name' => sanitize_text_field($_POST['last_name']),
                    'user_url' => sanitize_text_field($_POST['user_url']),
                    'user_picture' => sanitize_text_field($_POST['user_picture']),
                    'social_app_name' => sanitize_text_field($_POST['decrypted_app_name']),
                    'social_user_id' => sanitize_text_field($_POST['decrypted_user_id']),
                );
                if (get_option('mo_openid_enable_profile_completion') && ($user_details['user_email'] == '' || $user_details['username'] == '')) { // if newa user and profile completion is enabled
                    echo mo_openid_profile_completion_form($user_details);
                    exit;
                }
                else
                    mo_create_new_user($user_details);
            }
        }
    }
	else if( isset($_POST['mo_openid_show_profile_form_nonce']) and isset( $_POST['option'] ) and strpos( $_POST['option'], 'mo_openid_show_profile_form' ) !== false ){
    $nonce = sanitize_text_field($_POST['mo_openid_show_profile_form_nonce']);
    if ( ! wp_verify_nonce( $nonce, 'mo-openid-user-show-profile-form-nonce' ) ) {
        wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
    } else {

       $user_details= array(
            'username' => sanitize_text_field($_POST['username_field']),
            'user_email' => sanitize_email($_POST['email_field']),
            'user_full_name' => sanitize_text_field($_POST["user_full_name"]),
            'first_name' => sanitize_text_field($_POST["first_name"]),
            'last_name' => sanitize_text_field($_POST["last_name"]),
            'user_url' => sanitize_text_field($_POST["user_url"]),
            'user_picture' => sanitize_text_field($_POST["user_picture"]),
            'social_app_name' => sanitize_text_field($_POST["decrypted_app_name"]),
            'social_user_id' => sanitize_text_field($_POST["decrypted_user_id"]),
        );
        echo mo_openid_profile_completion_form($user_details,'1');
        exit;
    }
}

    else if((isset($_POST['action'])) && (strpos($_POST['action'], 'delete_social_profile_data') !== false) && isset($_POST['user_id'])){
        // delete first name, last name, user_url and profile_url from usermeta
        $id = sanitize_text_field($_POST['user_id']);
        mo_openid_delete_social_profile($id);
    }
    else if ( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'oauthredirect' ) !== false ) {
        if(isset($_REQUEST['wp_nonce'])){
            $nonce = sanitize_text_field($_REQUEST['wp_nonce']);
            if ( ! wp_verify_nonce( $nonce, 'mo-openid-oauth-app-nonce' ) ) {
                wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
            }
            else {
                $appname = sanitize_text_field($_REQUEST['app_name']);
                mo_openid_custom_app_oauth_redirect($appname);
            }
        }
    }

    else if( isset($_POST['mo_openid_user_otp_validation_nonce']) and isset( $_POST['otp_field']) and $_POST['option'] == 'mo_openid_otp_validation' )
    {
        $nonce = sanitize_text_field($_POST['mo_openid_user_otp_validation_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-user-otp-validation-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
        } else {
            $username = sanitize_text_field($_POST["username_field"]);
            $user_email = sanitize_email($_POST["email_field"]);
            $transaction_id = sanitize_text_field($_POST["transaction_id"]);
            $otp_token = sanitize_text_field($_POST['otp_field']);
            $user_picture = sanitize_text_field($_POST["user_picture"]);
            $user_url = sanitize_text_field($_POST["user_url"]);
            $last_name = sanitize_text_field($_POST["last_name"]);
            $user_full_name = sanitize_text_field($_POST["user_full_name"]);
            $first_name = sanitize_text_field($_POST["first_name"]);
            $decrypted_app_name = sanitize_text_field($_POST["decrypted_app_name"]);
            $decrypted_user_id = sanitize_text_field($_POST["decrypted_user_id"]);
            if (isset($_POST['resend_otp'])) {
                $send_content = send_otp_token($user_email);
                if ($send_content['status'] == 'FAILURE') {
                    $message = "Error Code 3: " . get_option('mo_email_failure_message');
                    wp_die($message);
                }
                $transaction_id = $send_content['tId'];
                echo mo_openid_validate_otp_form($username, $user_email, $transaction_id, $user_picture, $user_url, $last_name, $user_full_name, $first_name, $decrypted_app_name, $decrypted_user_id);

                exit;
            }

            mo_openid_social_login_validate_otp($username, $user_email, $first_name, $last_name, $user_full_name, $user_url, $user_picture, $decrypted_app_name, $decrypted_user_id, $otp_token, $transaction_id);
        }
    }

    else if( isset($_POST['mo_openid_connect_verify_nonce']) and isset( $_POST['option'] ) and $_POST['option'] == "mo_openid_connect_verify_customer" ) {    //register the admin to miniOrange
        $nonce = sanitize_text_field($_POST['mo_openid_connect_verify_nonce']);
        if (!wp_verify_nonce($nonce, 'mo-openid-connect-verify-nonce')) {
            wp_die('<strong>ERROR</strong>: Please Go back and Refresh the page and try again!<br/>If you still face the same issue please contact your Administrator.');
        }
        else {
            mo_register_old_user();
        }
    }

    else if ( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'moopenid' ) !== false ){
        mo_openid_process_social_login();
    }

    else if( strpos( $_SERVER['REQUEST_URI'], "openidcallback") !== false ||((strpos( $_SERVER['REQUEST_URI'], "oauth_token")!== false)&&(strpos( $_SERVER['REQUEST_URI'], "oauth_verifier") ))) {
        mo_openid_process_custom_app_callback();
    }
}

function get_current_customer(){
    $customer = new CustomerOpenID();
    $content = $customer->get_customer_key();
    $customerKey = json_decode( $content, true );
    if( isset($customerKey) ) {
        update_option( 'mo_openid_admin_customer_key', $customerKey['id'] );
        update_option( 'mo_openid_admin_api_key', $customerKey['apiKey'] );
        update_option( 'mo_openid_customer_token', $customerKey['token'] );
        update_option('mo_openid_admin_password', '' );
        update_option( 'mo_openid_message', 'Your account has been retrieved successfully.' );
        delete_option('mo_openid_verify_customer');
        delete_option('mo_openid_new_registration');
        if(isset($_POST['action'])?$_POST['action']=='mo_register_new_user':0)
            wp_send_json(["success" => 'Your account has been retrieved successfully.']);
        else
            mo_openid_show_success_message();
    } else {
        update_option( 'mo_openid_message', 'You already have an account with miniOrange. Please enter a valid password.');
        update_option('mo_openid_verify_customer', 'true');
        delete_option('mo_openid_new_registration');
        if(isset($_POST['action'])?$_POST['action']=='mo_register_new_user':0)
            wp_send_json(["error" => 'You already have an account with miniOrange. Please enter a valid password.']);
        else {
            mo_openid_show_error_message();
            if (get_option('regi_pop_up') == "yes") {
                update_option("pop_login_msg", get_option("mo_openid_message"));
                mo_pop_show_verify_password_page();
            }
        }
    }
}

function encrypt_data($data, $key) {

    return base64_encode(openssl_encrypt($data, 'aes-128-ecb', $key, OPENSSL_RAW_DATA));

}

function mo_openid_update_role($user_id='', $user_url=''){
    // save the profile url in user meta // this was added to save facebook url in user meta as it is more than 100 chars
    update_user_meta($user_id, 'moopenid_user_profile_url',$user_url);
    if(get_option('mo_openid_customised_field_enable') != 1 || get_option('mo_openid_update_role_addon') != 1) {
        if (get_option('mo_openid_login_role_mapping')) {
            $user = get_user_by('ID', $user_id);
            $user->set_role(get_option('mo_openid_login_role_mapping'));
        }
    }
}



function mo_openid_login_redirect($username = '', $user = NULL){
    mo_openid_start_session();
    if(is_string($username) && $username && is_object($user) && !empty($user->ID) && ($user_id = $user->ID) && isset($_SESSION['mo_login']) && $_SESSION['mo_login']){
        $_SESSION['mo_login'] = false;
        wp_set_auth_cookie( $user_id, true );
        $redirect_url = mo_openid_get_redirect_url();
        wp_redirect($redirect_url);
        exit;
    }
}

function mo_openid_login_redirect_pop_up($username = '', $user = NULL){

    mo_openid_start_session();
    if(is_string($username) && $username && is_object($user) && !empty($user->ID) && ($user_id = $user->ID) && isset($_SESSION['mo_login']) && $_SESSION['mo_login']){
        $_SESSION['mo_login'] = false;
        wp_set_auth_cookie( $user_id, true );
    }
    ?>
    <script>
        window.opener.HandlePopupResult("true");
        window.close();

    </script> <?php

    if(get_option("account_linking_flow")){
        update_option( 'account_linking_flow', 0);
        exit;
    }
    if ( empty( $_REQUEST['redirect_to'] ) ) {
        exit;
    }
}


function mo_openid_link_account( $username, $user ){
    if($user){
        $userid = $user->ID;
    }
    mo_openid_start_session();

    $user_email =  isset($_SESSION['user_email']) ? sanitize_text_field($_SESSION['user_email']):'';
    $social_app_identifier = isset($_SESSION['social_user_id']) ? sanitize_text_field($_SESSION['social_user_id']):'';
    $social_app_name = isset($_SESSION['social_app_name']) ? sanitize_text_field($_SESSION['social_app_name']):'';
    if(empty($user_email)){
        $user_email=$user->user_email;
    }
    //if user is coming through default wordpress login, do not proceed further and return
    if(isset($userid) && empty($social_app_identifier) && empty($social_app_name) ) {
        return;
    }
    elseif(!isset($userid)){
        return;
    }

    global $wpdb;
    $db_prefix = $wpdb->prefix;
    $linked_email_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM ".$db_prefix."mo_openid_linked_user where linked_email = \"%s\" AND linked_social_app = \"%s\"",$user_email,$social_app_name));

    // if a user with given email and social app name doesn't already exist in the mo_openid_linked_user table
    if(!isset($linked_email_id)){
        mo_openid_insert_query($social_app_name,$user_email,$userid,$social_app_identifier);
    }
}


function mo_openid_delete_profile_column($value, $columnName, $userId){
    if('mo_openid_delete_profile_data' == $columnName){
        global $wpdb;
        $socialUser = $wpdb->get_var($wpdb->prepare('SELECT id FROM '. $wpdb->prefix .'mo_openid_linked_user WHERE user_id = %d ', $userId));

        if($socialUser > 0 && !get_user_meta($userId,'mo_openid_data_deleted')){
            return '<a href="javascript:void(0)" onclick="javascript:moOpenidDeleteSocialProfile(this, '. $userId .')">Delete</a>';
        }
        else
            return '<p>NA</p>';
    }
    if('mo_openid_linked_social_app' == $columnName){
        global $wpdb;
        $socialUser = $wpdb->get_var($wpdb->prepare('SELECT id FROM '. $wpdb->prefix .'mo_openid_linked_user WHERE user_id = %d ', $userId));
        $a=$wpdb->get_col('SELECT all linked_social_app FROM '. $wpdb->prefix .'mo_openid_linked_user where user_id='.$userId);
        $b='';
        foreach ($a as $x=>$y)
        {
            if($y=='facebook') {$y='Facebook';}if($y=='google') {$y='Google';}if($y=='instagram'){$y='Instagram';}if($y=='linkedin'){$y='LinkedIn';}if($y=='amazon') {$y='Amazon';}
            if($y=='pinterest'){$y='Pinterest';}if($y=='twitch'){$y='Twitch';} if($y=='vkontakte'){$y='vKontakte';} if($y=='twitter'){$y='Twitter';}if($y=='salesforce'){$y='Salesforce';}if($y=='yahoo'){$y='Yahoo';}if($y=='yahoo'){$y='Yahoo';}if($y=='wordpress'){$y='Wordpress';}if($y=='disqus'){$y='Disqus';}

            $b=$b.' '.$y.'<br>';
        }
        if($socialUser > 0){
            return $b;
        }
    }
}