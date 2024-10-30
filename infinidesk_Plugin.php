<?php
/*
Plugin Name: Chativo
Description: Enable Chativo Chat Widget
Version: 1.0.0
Author: Chativo.io
*/

//Catch anyone trying to directly acess the plugin - which isn't allowed
if (!function_exists('add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

class ChativoChatWidget extends WP_Widget {
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
	    parent::__construct(
			'infinidesk_chat_widget', // Base ID
			__( 'Chativo Chat', 'text_domain' ), // Name
			array( 'description' => __( 'Enable Chativo Chat Widget', 'text_domain' ), ) // Args
		);
	}

/**
 * Outputs the options form on admin
 *
 * @param array $instance The widget options
 */
public function form($instance) {

}  

/**
 * Saves changes
 * @param array $new_instance The widget options
 * @param array $old_instance The widget options
 */

public function update( $new_instance, $old_instance ) {

}

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
public function widget( $args, $instance ) {

    //Get current user & Get user ID
    $current_user = wp_get_current_user();
    $current_user_id = $current_user->ID;
    
    //Check if user already exists
    function user_id_exists($user){

        global $wpdb, $lang, $api, $kind, $src, $isrc, $a, $encryptKey;
    
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user));
        if($count == 1){ return true; }else{ return false; }
    }

    //Encryption for ref
    $now = round(microtime(true) * 1000);
    
    function encrypt($now, $plaintext, $key) {
        $method = "AES-256-CBC";
        $iv = openssl_random_pseudo_bytes(16);
        $ciphertext = openssl_encrypt("$now|$plaintext", $method, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $ciphertext);   
    }

    //random number gen
    function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
    
            // 16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
    
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
    $a = get_option('infinidesk_settings'); // Initialize
    $api = $a['APIKey_field'];
    $lang = $a['Lang_field'];
    $env = $a['Env_field'];
    $kind = $a['Kind_field'];
    $encryptKey = $a['Encrpyt_field'];
    $ver = "";
    
    if($a['Encrpyt_field'] == ""){
        $ver = "2";
    }else {
        $ver = "3";
    }
    
    if (user_id_exists($current_user_id)) {
        //Get user email & Get user name
        $current_user_email = $current_user->user_email;
        $current_user_name = $current_user->display_name;
        $encryptedRef = encrypt($now, $current_user_email, base64_decode($encryptKey));
    }
    else{
    //If Desk, for Visitors, currently set to: If not logged in, dont render widget
    //If Support, render support chat
        if($kind == "Desk"){
            $current_user_email = "";
            $current_user_name = "";
            $api = ""; //If api-key not present, widget wont show
            $encryptedRef = "";
        }
    }

    if($kind == "Desk"){
        switch ($env) {
                case 'SIT':
                    $src = "https://widgetsit.chativo.io";
                    $isrc = "https://widgetsit.chativo.io/widget.js";
                    break;
                case 'UAT':
                    $src = "https://widgetuat.chativo.io";
                    $isrc = "https://widgetuat.chativo.io/widget.js";
                    break;
                case 'PROD':
                    $src = "https://widget.chativo.io";
                    $isrc = "https://widget.chativo.io/widget.js";
                    break;
                default:
                    $env = "SIT";
                    $src = "https://widgetsit.chativo.io";
                    $isrc = "https://widgetsit.chativo.io/widget.js";
        }
        // outputs the content of the widget
        echo <<<EOL
        <script type="text/javascript">
        var infObj = {
        apiKey: "$api",
        idRef: "$encryptedRef",
        idRefType: 'user',
        lang: "$lang",
        name: "$current_user_name",
        email: "$current_user_email",
        src: "$src",
        version: "$ver",
        };

        const elem = document.createElement('div');
        elem.id = 'infinidesk-web-widget';
        document.body.appendChild(elem);

        const imported = document.createElement('script');
        imported.src = "$isrc";
        document.head.appendChild(imported);
        </script>
EOL;
    }else{
        switch ($env) {
            case 'SIT':
                $src = "https://supwidsit.chativo.io";
                $isrc = "https://supwidsit.chativo.io/widget.js";
                break;
            case 'UAT':
                $src = "https://supwiduat.chativo.io";
                $isrc = "https://supwiduat.chativo.io/widget.js";
                break;
            case 'PROD':
                $src = "https://supwid.chativo.io";
                $isrc = "https://supwid.chativo.io/widget.js";
                break;
            default:
                $env = "SIT";
                $src = "https://supwidsit.chativo.io";
                $isrc = "https://supwidsit.chativo.io/widget.js";
    }
    // outputs the content of the widget
    echo <<<EOL
        <script type="text/javascript">
        var infObj = {
        apiKey: "$api",
        lang: "$lang",
        src: "$src",
        };

        const elem = document.createElement('div');
        elem.id = 'infinidesk-web-widget';
        document.body.appendChild(elem);

        const imported = document.createElement('script');
        imported.src = "$isrc";
        document.head.appendChild(imported);
        </script>
EOL;
    }
}
}
//Config Page
//===================================================================
add_action( 'admin_menu', 'infinidesk_add_admin_menu' );
add_action( 'admin_init', 'infinidesk_settings_init' );

function infinidesk_add_admin_menu(  ) {
    add_menu_page( 'Chativo', 'Chativo', 'manage_options', 'settings-api-page', 'infinidesk_options_page' );
}

function infinidesk_settings_init(  ) {
    register_setting( 'idPlugin', 'infinidesk_settings' );

    add_settings_section(
        'infinidesk_idPlugin_section',
        __( '', 'wordpress' ),
        'infinidesk_settings_section_callback',
        'idPlugin'
    );

    add_settings_field(
        'APIKey_field',
        __( 'API-Key', 'wordpress' ),
        'APIKey_field_render',
        'idPlugin',
        'infinidesk_idPlugin_section'
    );

    add_settings_field(
        'Lang_field',
        __( 'Language', 'wordpress' ),
        'Lang_field_render',
        'idPlugin',
        'infinidesk_idPlugin_section'
    );

    add_settings_field(
        'Env_field',
        __( 'Environment', 'wordpress' ),
        'Env_field_render',
        'idPlugin',
        'infinidesk_idPlugin_section'
    );

    add_settings_field(
        'Kind_field',
        __( 'Kind', 'wordpress' ),
        'Kind_field_render',
        'idPlugin',
        'infinidesk_idPlugin_section'
    );

    add_settings_field(
        'Encrpyt_field',
        __( 'Encryption Key', 'wordpress' ),
        'Encrypt_field_render',
        'idPlugin',
        'infinidesk_idPlugin_section'
    );
}

function modify_admin_bar_css() { ?>
    <style type="text/css">
        #wpadminbar {
            z-index: 10000; /*Ensure widget is stacked in front of admin bar*/
        }
    </style>
<?php }

add_action( 'wp_head', 'modify_admin_bar_css' );

function APIKey_field_render(  ) {
    $options = get_option( 'infinidesk_settings' );
    ?>
        <input type='text' style="width:90%;background-color:#DCDCDC;" autocomplete="off" name='infinidesk_settings[APIKey_field]' value=''>
    <?php
}

function Lang_field_render(  ) {
    $options = get_option( 'infinidesk_settings' );    
    ?>
    <select name='infinidesk_settings[Lang_field]'>
        <option value='en-US' <?php selected( $options['Lang_field'], 1 ); ?>>English</option>
        <option value='zh-CN' <?php selected( $options['Lang_field'], 2 ); ?>>Chinese</option>
    </select>
<?php
}

function Env_field_render(  ) {
    $options = get_option( 'infinidesk_settings' );
    ?>
        <select name='infinidesk_settings[Env_field]'>
            <option value='PROD' <?php selected( $options['Env_field'], 1 ); ?>>PROD</option>
            <option value='UAT' <?php selected( $options['Env_field'], 2 ); ?>>UAT</option>
            <option value='SIT' <?php selected( $options['Env_field'], 3 ); ?>>SIT</option>
        </select>
    <?php
}

function Kind_field_render(  ) {
    $options = get_option( 'infinidesk_settings' );    
    ?>
    <select name='infinidesk_settings[Kind_field]'>
        <option value='Desk' <?php selected( $options['Kind_field'], 1 ); ?>>Desk</option>
        <option value='Support' <?php selected( $options['Kind_field'], 2 ); ?>>Support</option>
    </select>
<?php
}

function Encrypt_field_render(  ) {
    $options = get_option( 'infinidesk_settings' );
    ?>
        <input type='text' style="width:90%;background-color:#DCDCDC;" autocomplete="off" name='infinidesk_settings[Encrpyt_field]' value=''>
    <?php
}

function infinidesk_settings_section_callback(  ) {
    echo __( '', 'wordpress' );
}

function infinidesk_options_page(  ) {
    
        ?>
            <script>
                function togg(){
                    var x = document.getElementById("domainPanel");
                    var y = document.getElementById("settingsPanel");
                    if (x.style.display === "none") {
                        x.style.display = "";
                        y.style.display = "none";
                    } else {
                        y.style.display = "none";
                    }
                }
    
                function domainTogg(){
                    var x = document.getElementById("settingsPanel");
                    var y = document.getElementById("domainPanel");
                    if (x.style.display === "none") {
                        x.style.display = "";
                        y.style.display = "none";
                    }
                }
            </script>
        <?php

    ?>
    <form action='options.php' method='post' style="margin-top:18px;">

        <table style="background-color:white;border:1px solid #D3D3D3;width:95%;border-collapse:collapse;">
            <tr style="padding:10px 0px 10px 0px;">
                <td style="border:1px solid #D3D3D3;padding-top:10px;">
                <div style="text-align: center;padding: 0px 60px 0px 60px;display:inline-block;">
                    <h1 style="font-size:4em;">Chativo</h1>
                </div>
                <div style="display:inline-block;">
                    <h2 style="color: grey;font-size:3em;">Plugin Settings</h2>
                </div>
                </td>
            </tr>
            <tr>
                <td style="background-color:white;border:1px solid #D3D3D3;padding-left:12px;">
                    <?php submit_button(); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="background-color:white;border:1px solid #D3D3D3;border-collapse:collapse;width:100%;">
                        <tr>
                            <td style="border:1px solid #D3D3D3;">
                                <table style="border-collapse:collapse;">
                                <tbody>
                                    <tr>
                                        <td>
                                            <button type="button" onclick="togg()" style="text-align:center;width:15em;background-color:#E8E8E8;padding:13px 0px 13px 0px;margin:0px;"><h4>Account Settings</h4></button>    
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div style="text-align:center;width:10em;padding:15px 0px 15px 0px;margin:0px;"><h4></h4></div> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div style="text-align:center;width:10em;padding:15px 0px 15px 0px;margin:0px;"><h4></h4></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div style="text-align:center;width:10em;padding:15px 0px 15px 0px;margin:0px;"><h4></h4></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div style="text-align:center;width:10em;padding:15px 0px 15px 0px;margin:0px;"><h4></h4></div>
                                        </td>
                                    </tr>
                                </tbody>
                                </table>
                            </td>
                            <td style="border:1px solid #D3D3D3;width:100%;">
                                <div id="domainPanel" style="border:1px solid #D3D3D3;padding:0px 20px 0px 20px;margin:4px;height:17.8em;">
                                    <table>
                                        <tr>
                                            <td>
                                                <h1>Domain Name: 
                                                    <?php
                                                        $blogname = get_option('blogname');
                                                        echo $blogname;
                                                    ?>
                                                </h1>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                            <button type="button" style="font-weight:bold;border:1px solid grey;background-color:#E8E8E8;width:100%;padding:10px 15px 10px 15px;border-radius:5px;text-align:center;" onclick="domainTogg()">Edit</button>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="settingsPanel" style="border:1px solid #D3D3D3;padding:0px 20px 0px 20px;margin:4px;display:none;">
                                    <?php
                                        settings_fields( 'idPlugin' );
                                        do_settings_sections( 'idPlugin' );
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left:12px;">
                            <?php
                                submit_button();
                            ?>
                            </td>
                            <td>
                                <p style="float:right;margin-right:8px;">Having trouble and need some help? Check out our <a href="https://infinacle.com/desk/knowledgebase/#1561537651100-5032d0a9-f6a9">Knowledge Base</a>.</p>
                            </td>
                        </tr>
                    </table>            
                </td>
            </tr>
        </table>
    </form>
    
    <?php
}
//===================================================================
add_action( 'widgets_init', function(){
 register_widget( 'ChativoChatWidget' );
});


?>
