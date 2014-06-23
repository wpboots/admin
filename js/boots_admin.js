/**
 * Admin - javascript
 *
 * @package Boots
 * @subpackage Admin
 * @version 1.0.0
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
                $("select", $item).select2('destroy').select2({
                    width: 'element'
                });
                $item.parent().height($item.parent().height() + $item.height());
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
            self.default_tab();
            self.ev_tabs();
            self.save_options();
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
                $('.boots-form > ul li', self.$elem).hide();
                $(window).load(function(){
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

            $('.boots-admin-header > h2 a', self.$elem).on('click', function(){
                if(!$(this).hasClass('js-save-all'))
                {
                    return self.switch_tab($(this));
                }
            });
        },

        // save options
        // uses $.BootsAjax()
        save_options : function()
        {
            var self = this;

            $('a.js-save-all', self.$elem).on('click', function(){
                var $a = $(this);
                var $parent = $a.parent();
                var $icon = $('.boots-admin-icon', $parent);
                var form = $('form[name="boots_admin_form"]', self.$elem).serialize();
                form += ('&_menu=' + self.menu_slug);
                $.BootsAjax({
                    data : form,
                    action : self.action_save_options,
                    nonce : self.nonce_save_options,
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
                    }
                });
                return false;
            });
        }
    };

    $(document).ready(function(){
        BootsAdmin.init('.boots-admin');
    });

})(jQuery);

