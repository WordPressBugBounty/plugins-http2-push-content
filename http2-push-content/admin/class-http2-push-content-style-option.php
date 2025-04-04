<?php

class Http2_Push_Content_Style_Option{

    public $plugin_name;

    private $setting = array();

    private $active_tab;

    private $this_tab = 'general-style';

    private $tab_name = "Async / Remove CSS file";

    private $setting_key = 'http2_async_css_style';

    public $settings = array();

    public $tab;

   
    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;
        
        $this->tab = sanitize_text_field(filter_input( INPUT_GET, 'tab'));
        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        $this->settings = array(
                array('field'=>'http2_async_css_list')
            );
        

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }

        add_action($this->plugin_name.'_tab', array($this,'tab'),1);

        add_filter('pre_update_option_http2_async_css_list',array($this, 'remove_blank_values'));

        $this->register_settings();
    }

    function remove_blank_values($resources){
        if(is_array($resources)):
            foreach($resources as $key => $link){
                if($link['css'] == "" ){
                    unset($resources[$key]);
                } 

                if(isset($link['group']) && empty($link['group'])){
                    $resources[$key]['group'] = 'All';
                    $resources[$key]['group_slug'] = 'all';
                }

                if(isset($link['group']) && !empty($link['group'])){
                    $resources[$key]['group_slug'] = sanitize_title($link['group']);
                }

                if(!isset($link['group'])){
                    $resources[$key]['group'] = 'All';
                    $resources[$key]['group_slug'] = 'all';
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
        $general_list = get_option('http2_async_css_list',false);
        $this->createButton($general_list);
       ?>
        <script type="text/javascript">
        var general_async_css_list = <?php echo count(($general_list == false) ? array(): array_values($general_list)); ?>;
        </script>
        <script id="async_css_list_tmpl" type="text/html">
        <?php echo Http2_Push_Content_Style_Option::templateRow(); ?>
        </script>
        <form method="post" action="options.php"  class="pisol-setting-form">
        <?php settings_fields( $this->setting_key ); ?>
        <div class="pisol_grid">
        <?php
            foreach($this->settings as $setting){
                new pisol_class_form($setting, $this->setting_key);
            }
        ?>
        </div>
        <h2><?php echo __('Asynchronous or Remove CSS file','http2-push-content'); ?></h2>
        <div id="css-resource-list">
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
        <a class="btn btn-info btn-sm" href="javascript:void(0);" id="add_css"><span class="dashicons dashicons-plus-alt pi-icon"></span> Add CSS</a>
        <br>
        <input type="submit" class="mt-3 btn btn-primary btn-lg" value="Save Option" />
        </form>
       <?php
    }

    function createButton($general_list){
        if(empty($general_list)) return;

        $finale_list = array();
        foreach($general_list as $key => $value){
            $finale_list[$value['group_slug']] = $value['group'];
        }
            
        echo '<div class="text-center my-3">';
        ?>
        <a class="btn btn-sm btn-primary mx-1 pisol-filter-group pi-active" data-group="all" href="javascript:void(0);">ALL</a>
        <?php
        foreach( $finale_list as $key => $value){
            if($key == 'all') continue;
            ?>
            <a class="btn btn-sm btn-primary mx-1 pisol-filter-group" data-group="<?php echo esc_attr($key); ?>" href="javascript:void(0);">
                <?php echo $value; ?> 
            </a>
            <?php
        }
        echo '</div>';
    }

    static function templateRow($count = '{{count}}', $value = array()){
        $enabled = isset($value['enabled']) ? (!empty($value['enabled']) ? true : false) : true;
        $checked = $enabled ? 'checked' : '';
        $saved_to = isset($value['to']) ? $value['to'] : '';
        $apply_to = isset($value['apply_to']) ? (is_array($value['apply_to']) ? $value['apply_to'] : [$value['apply_to']]) : [];
        ?>
            <div class="flex pisol-group pisol-group-<?php echo esc_attr(isset($value['group_slug']) ? $value['group_slug'] : 'all'); ?>">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input pi-enabled-checkbox" data-name="http2_async_css_list[<?php echo esc_attr($count); ?>][enabled]" id="pi_enabled_<?php echo esc_attr($count); ?>" <?php echo esc_attr($checked); ?>>
                <input type="hidden" name="http2_async_css_list[<?php echo esc_attr($count); ?>][enabled]" value="<?php echo esc_attr($enabled ? 1 : 0); ?>">
                <label class="custom-control-label" for="pi_enabled_<?php echo esc_attr($count); ?>"></label>
            </div>
            <input required type='text' class="form-control w-50 css_identifier" name="http2_async_css_list[<?php echo esc_attr($count); ?>][css]" value="<?php echo esc_attr($value['css'] ?? ''); ?>" placeholder="CSS Identifier e.g: twentytwenty/style.css">
            <select required  class="form-control w-25" name="http2_async_css_list[<?php echo esc_attr($count); ?>][to]">
                            <option disabled selected value=""><?php _e('What to do with this CSS?', 'http2-push-content'); ?></option>
                            <option value="async" <?php echo esc_attr($saved_to == 'async' ? 'selected' : ''); ?>>Asynchronous</option>
                            <option value="remove" <?php echo esc_attr($saved_to == 'remove' ? 'selected' : ''); ?>>Remove</option>
                            <option value="async-exclude" <?php echo esc_attr($saved_to == 'async-exclude' ? 'selected' : ''); ?>>Asynchronous on all excluding</option>
                            <option value="remove-exclude" <?php echo esc_attr($saved_to == 'remove-exclude' ? 'selected' : ''); ?>>Remove on all excluding</option>
            </select>
            <select required  class="general_async_css_list_rule form-control w-25 pages-to-apply" name="http2_async_css_list[<?php echo esc_attr($count); ?>][apply_to][]"  data-count="<?php echo esc_attr($count); ?>"  data-name="http2_async_css_list" multiple>
                <?php 
                    $obj = new Http2_Push_Content_Apply_To(); 
                    $obj->apply_to_options_v2($apply_to);
                ?>
            </select>
            <?php if(isset($value['id']) && ($value['apply_to'] == 'specific_pages' || $value['apply_to'] == 'not_specific_pages' || $value['apply_to'] == 'specific_posts' || $value['apply_to'] == 'not_specific_posts')){ ?>
                <input class="pisol-ids form-control" type="text" name="http2_async_css_list[<?php echo esc_attr($count); ?>][id]" value="<?php echo esc_attr($value['id'] ?? ''); ?>" id="http2_async_css_list_<?php echo esc_attr($count); ?>_id"  placeholder="e.g: 12, 22, 33">
            <?php }else{ ?>
                <input class="pisol-ids form-control" type="text" name="http2_async_css_list[<?php echo esc_attr($count); ?>][id]" value="" id="http2_async_css_list_<?php echo esc_attr($count); ?>_id"  placeholder="e.g: 12, 22, 33" style="display:none;">
            <?php } ?>  
            <input type='text' class="form-control w-25 css_identifier" name="http2_async_css_list[<?php echo esc_attr($count); ?>][group]" value="<?php echo esc_attr($value['group'] ?? ''); ?>" placeholder="Group name" title="this helps you to group similar rule to gather so you can manage them properly, So if you are removing some content from home page you can name this as group Home, if you are removing some thing from all the pages then you can name it as All">
            <a class="remove_css_resource" href="javascript:void(0);"><span class="dashicons dashicons-trash pi-icon"></span></a>
            </div>
        <?php
    }
}

add_action($this->plugin_name.'_general_option', new Http2_Push_Content_Style_Option($this->plugin_name));