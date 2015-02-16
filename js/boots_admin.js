/**
 * Admin - javascript
 *
 * @package Boots
 * @subpackage Admin
 * @version 1.0.1
 * @license GPLv2
 *
 * Boots - The missing WordPress framework. http://wpboots.com
 *
 * Copyright (C) <2014>  <M. Kamal Khan>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */
(function($){
    "use strict";

    var BootsAdmin = {

        menu_slug           : boots_admin.menu_slug,
        action_save_options : boots_admin.action_save_options,
        nonce_save_options  : boots_admin.nonce_save_options,
        action_restore_options : boots_admin.action_restore_options,
        nonce_restore_options  : boots_admin.nonce_restore_options,

        layout : null,

        grid_options : {
            rowSpacing  : 63,
            colSpacing  : 42,
            initSpacing : 42,
            responsive  : true,
            hiddenClass : 'boots-admin_hidden',
            columns     : {
                'defaults' : 3,
                '800'      : 2,
                '500'      : 1
            },
            onReady     : function($item)
            {
                $item.show();

                // select2
                $('select', $item).select2('destroy').select2({
                    width: 'element'
                });

                // range
                var $range = $('input.powerange', $item);
                var $rangewrap = $range.parent();
                var $rangebar = $range.next('.range-bar');
                var $rangeQuantity = $('.range-quantity', $rangebar);
                var $rangeHandle = $('.range-handle', $rangebar);
                var $rangeMin = $('.range-min', $rangebar);
                var $rangeMax = $('.range-max', $rangebar);
                var rangeHandleWidth = $rangeHandle.width();
                var rangValue = parseFloat($range.val());
                var rangeMin = parseFloat($rangeMin.text());
                var rangeMax = parseFloat($rangeMax.text());
                var rangeWidth
                = $rangewrap.width() - $rangeMin.width() - $rangeMax.width()
                - 20;

                var rangeHandlePos
                = rangeWidth / (rangeMax / rangValue)
                - (rangeHandleWidth / 2);

                $rangebar.width(rangeWidth);
                $rangeQuantity.width(rangeHandlePos);
                $rangeHandle.css('left', (rangeHandlePos < 0 ? 0 : rangeHandlePos ) + 'px');
                $rangeMin.css('left', -1 * $rangeMin.width() - 10);
                $rangeMax.css('right', -1 * $rangeMax.width() - 10);

                // iris
                $('.iris', $item)
                .iris('option', 'width', $item
                .find('.boots-form-input').width());
            }
        },

        init : function(elem)
        {
            var self = this;
            self.elem = elem;
            self.$elem = $(elem);

            // method calls
            self.which_layout();
            self.render_layout();
            self.refresh_grid();
            self.default_tab();
            self.ev_tabs();
            self.save_options();
            self.restore_options();
        },

        // set the layout var
        which_layout : function()
        {
            var self = this;

            if($('.boots-form', self.$elem).hasClass('awesome-grid'))
            {
                self.layout = 'grid';
            }
            else {
                self.layout = 'default';
            }
        },

        render_layout : function()
        {
            var self = this;

            if(self.layout == 'grid')
            {
                $('.boots-form > ul > li', self.$elem).hide();
                $(window).load(function(){
                    $('.boots-form > ul.active', self.$elem)
                    .AwesomeGrid(self.grid_options);
                });
            }
        },

        refresh_grid : function()
        {
            var self = this;

            if(self.layout == 'grid')
            {
                $('a.wp-color-result', self.$elem).on('click', function(){
                    $('.boots-form > ul.active', self.$elem)
                    .AwesomeGrid(self.grid_options);
                });
                $('.boots-form > ul.active > li .boots-form-input', self.$elem).on('resize', function(){
                    $('.boots-form > ul.active', self.$elem)
                    .AwesomeGrid(self.grid_options);
                });
            }
        },

        // switch tab
        switch_tab : function($a)
        {
            var self = this;

            var $parent = $a.parent();
            var section = $a.attr('href').split('#')[1];
            if(!$a.hasClass('nav-tab-active'))
            {
                $('a', $parent).removeClass('nav-tab-active');
                $a.addClass('nav-tab-active');

                var $sections = $('.boots-admin-body ul[data-as="section"]', self.$elem)
                .stop(true, true).hide().removeClass('active');

                $.each($sections, function(id, ul){
                    if($(ul).data('section'))
                    {
                        if($(ul).data('section') == section)
                        {
                            $(ul).stop(true, true).addClass('active').fadeIn('fast', function(){
                                if(self.layout == 'grid')
                                {
                                    $(ul).AwesomeGrid(self.grid_options);
                                }
                            });
                            window.location.hash = section;
                        }
                    }
                });
            }
            return false;
        },

        // load default tab based on url hash
        default_tab : function()
        {
            var self = this;

            window
            if(window.location.hash)
            {
                var section_hash = window.location.hash;
                var active = $('.boots-admin-header > h2 a[href="'+section_hash+'"]', self.$elem);
                if(active)
                {
                    self.switch_tab(active);
                }
            }
        },

        // tabs event
        ev_tabs : function()
        {
            var self = this;

            $('.boots-admin-header > h2 a.nav-tab', self.$elem).on('click', function(){
                return self.switch_tab($(this));
            });
        },

        ajax : function($a, action, nonce, reload)
        {
            var self = this;
            var $parent = $a.parent();
            var $icon = $('.boots-admin-icon', $parent);
            if(getUserSetting('editor'))
            {
                if(typeof tinymce != 'undefined' && tinymce != null)
                {
                    $('.boots-form-input textarea.wp-editor-area').each(function(i){
                        tinymce.execCommand('mceRemoveEditor', false, $(this).attr('id'));
                        tinymce.execCommand('mceAddEditor', false, $(this).attr('id'));
                    });
                }
            }
            var form = $('form[name="boots_admin_form"]', self.$elem).serialize();
            form += ('&_menu=' + self.menu_slug);
            $.BootsAjax({
                data : form,
                action : action,
                nonce : nonce,
                beforeSend : function(){
                    $icon.addClass('boots-admin-icon-spinner');
                },
                done : function(Data){
                    if(!Data.error)
                    {
                        $icon.removeClass('boots-admin-icon-spinner').addClass('boots-admin-icon-tick');
                    }
                },
                always : function(){
                    setTimeout(function(){
                        $icon
                        .removeClass('boots-admin-icon-spinner')
                        .removeClass('boots-admin-icon-cross')
                        .removeClass('boots-admin-icon-tick')
                    }, 800);
                    if(reload)
                    {
                        location.reload();
                    }
                }
            });
        },

        // save options
        // uses $.BootsAjax()
        save_options : function()
        {
            var self = this;

            $('a.js-save-all', self.$elem).on('click', function(e){
                e.preventDefault();
                self.ajax($(this), self.action_save_options, self.nonce_save_options);
            });
        },

        // restore options
        // uses $.BootsAjax()
        restore_options : function()
        {
            var self = this;

            $('a.js-restore-all', self.$elem).on('click', function(e){
                e.preventDefault();
                $("#boots_admin_restore_lb").modal({
                    fadeDuration: 200,
                    zIndex: 999999
                });
            });
            $('a.js-restore-all-ok', self.$elem).on('click', function(e){
                e.preventDefault();
                self.ajax($('a.js-restore-all', self.$elem), self.action_restore_options, self.nonce_restore_options, true);
            });
        }
    };

    $(document).ready(function(){
        BootsAdmin.init('.boots-admin');
    });

})(jQuery);
