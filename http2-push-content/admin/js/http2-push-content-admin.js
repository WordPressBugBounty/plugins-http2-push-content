(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  jQuery(document).ready(function ($) {
    

    function row_manager_js(saved_count, container, template, add_btn, remove_row, rule_element) {
      this.count = saved_count;
      this.container = container;
      this.template = template;
      this.add_btn = add_btn;
      this.remove_row = remove_row;
      this.rule_element = rule_element;
      this.init = function(){
        this.addRowEvent();
        this.removeRowEvent();
        this.rule_change();
      }

      this.addRowEvent = function(){
        var parent = this;
        jQuery(document).on('click', parent.add_btn, function(){
          var template = jQuery(parent.template).html();
          //remove {{count}} from the template
          template = template.replace(/{{count}}/g, parent.count);
          jQuery(parent.container).append(template);
          parent.count++;

          if (jQuery.fn && typeof jQuery.fn.selectWoo === "function") {
            jQuery(".pages-to-apply").selectWoo({
              placeholder: "Where to do?"
            });
          }

        });
      }

      this.removeRowEvent = function(){
        var parent = this;
        $(document).on("click", parent.remove_row , e => {
          var selected = $(e.currentTarget).parent();
          selected.remove();
          /*this.count--;*/
        });
      }

      this.rule_change = function(){
        var parent = this;
        $(document).on('change', parent.rule_element, function () {
          var value = $(this).val();
          console.log(value);
          var count = $(this).data('count');
          var name = $(this).data('name');
          var conditions = ['specific_pages', 'not_specific_pages', 'specific_posts', 'not_specific_posts'];
          var $target = $('#' + name + '_' + count + '_id');
          
          $target.hide();

          if (Array.isArray(value)) {
              // If multi-select, check if any condition matches
              if (value.some(function (v) { return conditions.includes(v); })) {
                  $target.show();
              }
          } else {
              // If single value, check directly
              if (conditions.includes(value)) {
                  $target.show();
              }
          }

        });
      }
    }

    var saved_count_js = typeof general_async_js_list !== 'undefined' ? general_async_js_list : 0;
    var row_manager_js_obj = new row_manager_js(saved_count_js, "#js-resource-list", '#async_js_list_tmpl', '#add_js', '.remove_js_resource', ".general_async_js_list_rule");
    row_manager_js_obj.init();

    var saved_count_css = typeof general_async_css_list !== 'undefined' ? general_async_css_list : 0;
    var row_manager_css_obj = new row_manager_js(saved_count_css, "#css-resource-list", '#async_css_list_tmpl', '#add_css', '.remove_css_resource', ".general_async_css_list_rule");
    row_manager_css_obj.init();

    var saved_count_general = typeof general_push_list !== 'undefined' ? general_push_list : 0;
    var row_manager_general_obj = new row_manager_js(saved_count_general, "#push-resource-list", '#resource_tmpl', '#add_resource_to_push', '.remove_resource_to_push', ".general_push_list_rule");
    row_manager_general_obj.init();



    jQuery(document).on('click', '.pisol-filter-group', function () {

      jQuery(this).addClass('pi-active').siblings().removeClass('pi-active');
      // Get the group data attribute from the clicked element
      var group = jQuery(this).data('group');
      
      if (group === 'all') {
          // If the 'all' group is clicked, fade in all groups
          jQuery('.pisol-group').fadeIn();
      } else {
          // Fade out all groups that don't match the selected group, and fade in the matching group
          jQuery('.pisol-group').not('.pisol-group-' + group).fadeOut();
          jQuery('.pisol-group-' + group).fadeIn();
      }
    });

    if (jQuery.fn && typeof jQuery.fn.selectWoo === "function") {
      jQuery(".pages-to-apply").selectWoo({
        placeholder: "Where to do?"
      });
    }

    jQuery(document).on('change', '.pi-enabled-checkbox', function () {
      var target = jQuery(this).data('name');
      if(jQuery(this).is(':checked')){
        jQuery('input[name="'+target+'"]').val('1');
      }else{
        jQuery('input[name="'+target+'"]').val('0');
      }
    });
    

  });
})(jQuery);
