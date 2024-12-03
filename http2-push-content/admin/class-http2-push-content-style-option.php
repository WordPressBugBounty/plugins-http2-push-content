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
        var general_async_css_list = <?php echo json_encode(($general_list == false) ? array(): array_values($general_list)); ?>;
        </script>
        <script id="async_css_list_tmpl" type="text/x-jsrender">
        <div class="flex pisol-group pisol-group-{{if value.group_slug }}{{: value.group_slug}}{{/if}}{{if !value.group_slug }}all{{/if}}" >
        <input required type='text' class="form-control w-50 css_identifier" name="http2_async_css_list[{{: count}}][css]" value="{{: value.css}}" placeholder="CSS Identifier e.g: twentytwenty/style.css">
        <select required class="form-control w-25" name="http2_async_css_list[{{: count}}][to]">
                        <option disabled><?php _e('Select', 'http2-push-content'); ?></option>
                        <option value="async" {{if value.to == 'async'}}selected="selected"{{/if}}>Asynchronous</option>
                        <option value="remove" {{if value.to == 'remove'}}selected="selected"{{/if}}>Remove</option>
                        <option value="async-exclude" {{if value.to == 'async-exclude'}}selected="selected"{{/if}}>Asynchronous on all excluding</option>
                        <option value="remove-exclude" {{if value.to == 'remove-exclude'}}selected="selected"{{/if}}>Remove on all excluding</option>
        </select>
        <select required class="general_async_css_list_rule form-control w-25"  name="http2_async_css_list[{{: count}}][apply_to]"  data-count="{{: count}}"  data-name="http2_async_css_list">
            <?php 
                $obj = new Http2_Push_Content_Apply_To(); 
                $obj->apply_to_options();
            ?>
        </select>
        {{if value.id != undefined && (value.apply_to == 'specific_pages' || value.apply_to == 'not_specific_pages' || value.apply_to == 'specific_posts' || value.apply_to == 'not_specific_posts') }}
        <input class="pisol-ids form-control" type="text" name="http2_async_css_list[{{: count}}][id]" value="{{: value.id}}" id="http2_async_css_list_{{: count}}_id"  placeholder="e.g: 12, 22, 33">
        {{/if}}
        <input type='text' class="form-control w-25 css_identifier" name="http2_async_css_list[{{: count}}][group]" value="{{: value.group}}" placeholder="Group name" title="this helps you to group similar rule to gather so you can manage them properly, So if you are removing some content from home page you can name this as group Home, if you are removing some thing from all the pages then you can name it as All">
        <a class="remove_css_resource" href="javascript:void(0);"><span class="dashicons dashicons-trash pi-icon"></span></a>
        </div>
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
}

add_action($this->plugin_name.'_general_option', new Http2_Push_Content_Style_Option($this->plugin_name));