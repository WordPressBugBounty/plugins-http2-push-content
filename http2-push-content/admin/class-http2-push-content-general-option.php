<?php

class Http2_Push_Content_General_Option{

    public $plugin_name;

    private $setting = array();

    private $active_tab;

    private $this_tab = 'default';

    private $tab_name = "General setting";

    private $setting_key = 'http2_push_content_general';

    public $as = array('script', 'style', "embed", "fetch", "font", "image", "object", "video");

    public $to = array("push-preload", "push", "preload", "push-preload-exclude", "push-exclude", "preload-exclude",);

    public $settings = array();

    public $tab;

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;
        
        $this->tab = sanitize_text_field(filter_input( INPUT_GET, 'tab'));
        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        
        add_action('init', [$this, 'init']);

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }

        add_action($this->plugin_name.'_tab', array($this,'tab'),1);

        add_filter('pre_update_option_http2_push_general_list',array($this, 'remove_blank_values'));
        
        
    }

    function init(){
        $this->settings = array(
            array('field'=>'http2_push_general_list'),
            array('field'=>'push_all_style', 'label'=>__('Push/Preload all style','http2-push-content'),'type'=>'select', 'value'=>array(false =>__('Do Nothing','http2-push-content'), 'push'=>__('Push','http2-push-content'), 'preload'=>__('Preload','http2-push-content'), 'push-preload'=>__('Push Preload','http2-push-content')), 'default'=>'push-preload', 'desc'=>__('This push and preload all the style sheet added using enque method','http2-push-content')),
            array('field'=>'push_all_script', 'label'=>__('Push/Preload all script','http2-push-content'),'type'=>'select', 'value'=>array(false =>__('Do Nothing','http2-push-content'),'push'=>__('Push','http2-push-content'), 'preload'=>__('Preload','http2-push-content'), 'push-preload'=>__('Push Preload','http2-push-content')), 'default'=>'push-preload', 'desc'=>__('This push and preload all the script added using enqueue method','http2-push-content')),
        );
        $this->register_settings();
    }

    function remove_blank_values($resources){
        if(is_array($resources)):
            foreach($resources as $key => $link){
                if($link['url'] == "" || !in_array($link['as'], $this->as) || !in_array($link['to'], $this->to)){
                    unset($resources[$key]);
                } 
            }
        endif;
        return $resources;
    }

    function register_settings(){   

        foreach($this->settings as $setting){
                register_setting( $this->setting_key, $setting['field']);
        }
    
    }

    function tab(){
        $page = sanitize_text_field(filter_input( INPUT_GET, 'page'));
        ?>
        <a class=" px-3 text-light d-flex align-items-center  border-left border-right  <?php echo ($this->active_tab == $this->this_tab ? 'bg-primary' : 'bg-secondary'); ?>" href="<?php echo admin_url( 'admin.php?page='.$page.'&tab='.$this->this_tab ); ?>">
            <?php _e( $this->tab_name, 'http2-push-content' ); ?> 
        </a>
        <?php
    }

    function tab_content(){
        $general_list = get_option('http2_push_general_list',false);
       ?>
        <script type="text/javascript">
        var general_push_list = <?php echo count(($general_list == false) ? array(): array_values($general_list)); ?>;
        </script>
        <script id="resource_tmpl" type="text/html">
        <?php echo Http2_Push_Content_General_Option::templateRow(); ?>
        </script>
        <form method="post" action="options.php"  class="pisol-setting-form">
        <?php settings_fields( $this->setting_key ); ?>
        <?php
            foreach($this->settings as $setting){
                new pisol_class_form($setting, $this->setting_key);
            }
        ?>
        <h2><?php echo __('Push or Preload or Do Both to any content: (put exact url of the resource from the page source)','http2-push-content'); ?><br><small>e.g: http://yoursite.com/wp-includes/js/jquery/jquery.js</small></h2>
        
        <div id="push-resource-list">
        <?php
        if($general_list){
            $count = 0;
            foreach($general_list as $key => $value){
                self::templateRow($count, $value);
                $count++;
            }
        }
        ?>
        </div>
        <br>
        <a class="btn btn-info btn-sm" href="javascript:void(0);" id="add_resource_to_push"><span class="dashicons dashicons-plus-alt pi-icon"></span> Add Resource to push</a>
        <br>
        <input type="submit" class="mt-3 btn btn-primary btn-lg" value="Save Option" />
        </form>
       <?php
    }

    static function templateRow($count = '{{count}}', $value = array()){
        $enabled = isset($value['enabled']) ? (!empty($value['enabled']) ? true : false) : true;
        $checked = $enabled ? 'checked' : '';
        $saved_as = isset($value['as']) ? $value['as'] : '';
        $saved_to = isset($value['to']) ? $value['to'] : '';
        $apply_to = isset($value['apply_to']) ? (is_array($value['apply_to']) ? $value['apply_to'] : [$value['apply_to']]) : [];
        $conditions = ['specific_pages', 'not_specific_pages', 'specific_posts', 'not_specific_posts'];
        $intercept = array_intersect($apply_to, $conditions);
        if(count($intercept) > 0){
            $show_id = true;
        }else{
            $show_id = false;
        }
        ?>
            <div class="flex pisol-group">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input pi-enabled-checkbox" data-name="http2_push_general_list[<?php echo esc_attr($count); ?>][enabled]" id="pi_enabled_<?php echo esc_attr($count); ?>" <?php echo esc_attr($checked); ?>>
                <input type="hidden" name="http2_push_general_list[<?php echo esc_attr($count); ?>][enabled]" value="<?php echo esc_attr($enabled ? 1 : 0); ?>">
                <label class="custom-control-label" for="pi_enabled_<?php echo esc_attr($count); ?>"></label>
            </div>
            <input required type='text' class="form-control w-50 css_identifier" name="http2_push_general_list[<?php echo esc_attr($count); ?>][url]" value="<?php echo esc_attr($value['url'] ?? ''); ?>" placeholder="Full url of resource">
            <select required  class="form-control w-25" name="http2_push_general_list[<?php echo esc_attr($count); ?>][as]">
                        <option disabled selected value><?php _e('Select resource type', 'http2-push-content'); ?></option>
                        <option value="script" <?php echo esc_attr($saved_as == 'script' ? 'selected' : ''); ?>>script</option>
                        <option value="style" <?php echo esc_attr($saved_as == 'style' ? 'selected' : ''); ?>>style</option>
                        <option value="audio" <?php echo esc_attr($saved_as == 'audio' ? 'selected' : ''); ?>>audio</option>
                        <option value="embed" <?php echo esc_attr($saved_as == 'embed' ? 'selected' : ''); ?>>embed</option>
                        <option value="fetch" <?php echo esc_attr($saved_as == 'fetch' ? 'selected' : ''); ?>>fetch</option>
                        <option value="font" <?php echo esc_attr($saved_as == 'font' ? 'selected' : ''); ?>>font</option>
                        <option value="image" <?php echo esc_attr($saved_as == 'image' ? 'selected' : ''); ?>>image</option>
                        <option value="object" <?php echo esc_attr($saved_as == 'object' ? 'selected' : ''); ?>>object</option>
                        <option value="video" <?php echo esc_attr($saved_as == 'video' ? 'selected' : ''); ?>>video</option>
            </select>
            <select required  class="form-control w-25" name="http2_push_general_list[<?php echo esc_attr($count); ?>][to]">
                        <option disabled selected value><?php _e('Select Push/Pull', 'http2-push-content'); ?></option>
                        <option value="push-preload" <?php echo esc_attr($saved_to == 'push-preload' ? 'selected' : ''); ?>>Push and Preload</option>
                        <option value="push" <?php echo esc_attr($saved_to == 'push' ? 'selected' : ''); ?>>Push</option>
                        <option value="preload" <?php echo esc_attr($saved_to == 'preload' ? 'selected' : ''); ?>>Preload</option>
                        <option value="push-preload-exclude" <?php echo esc_attr($saved_to == 'push-preload-exclude' ? 'selected' : ''); ?>>Push and Preload on all excluding</option>
                        <option value="push-exclude" <?php echo esc_attr($saved_to == 'push-exclude' ? 'selected' : ''); ?>>Push on all excluding</option>
                        <option value="preload-exclude" <?php echo esc_attr($saved_to == 'preload-exclude' ? 'selected' : ''); ?>>Preload on all excluding</option>
            </select>
            <select required  class="general_async_css_list_rule form-control w-25 pages-to-apply" name="http2_push_general_list[<?php echo esc_attr($count); ?>][apply_to][]"  data-count="<?php echo esc_attr($count); ?>"  data-name="http2_push_general_list" multiple>
                <?php 
                    $obj = new Http2_Push_Content_Apply_To(); 
                    $obj->apply_to_options_v2($apply_to);
                ?>
            </select>
            <?php if(isset($value['id']) && $show_id){ ?>
                <input class="pisol-ids form-control" type="text" name="http2_push_general_list[<?php echo esc_attr($count); ?>][id]" value="<?php echo esc_attr($value['id'] ?? ''); ?>" id="http2_push_general_list_<?php echo esc_attr($count); ?>_id"  placeholder="e.g: 12, 22, 33">
            <?php }else{ ?>
                <input class="pisol-ids form-control" type="text" name="http2_push_general_list[<?php echo esc_attr($count); ?>][id]" value="" id="http2_push_general_list_<?php echo esc_attr($count); ?>_id"  placeholder="e.g: 12, 22, 33" style="display:none;">
            <?php } ?>  
            <a class="remove_css_resource" href="javascript:void(0);"><span class="dashicons dashicons-trash pi-icon"></span></a>
            </div>
        <?php
    }
}

add_action($this->plugin_name.'_general_option', new Http2_Push_Content_General_Option($this->plugin_name));