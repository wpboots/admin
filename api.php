<?php

/**
 * Admin
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

class Boots_Admin
{
    private $Boots;
    private $Settings;

    private $id;
    private $nick;
    private $dir;
    private $url;

    private $menu_slug = null;
    private $submenu_slug = null;
    private $section = null;

    public function __construct($Boots, $Args, $dir, $url)
    {
        $this->Boots = $Boots;
        $this->Settings = $Args;
        $this->dir = $dir;
        $this->url = $url;

        $this->id = $Args['APP_ID'];
        $this->nick = $Args['APP_NICK'];

        // allow ajax action hook for image fetch
        $Boots->Form;

        if(!has_action('boots_ajax_admin_save_options', array(&$this, 'ajax_save_options')))
        {
            add_action('boots_ajax_admin_save_options', array(&$this, 'ajax_save_options'));
        }
    }

    public function init()
    {
        add_action('admin_init', array(&$this, 'scripts_and_styles'));
    }

    public function scripts_and_styles()
    {
        foreach($this->Menus as $slug => $menu)
        {
            add_action('admin_print_styles-' . $menu['menu'], array(&$this, 'styles'));
            add_action('admin_print_scripts-' . $menu['menu'], array(&$this, 'scripts'));

            if(!has_action('admin_head', array(&$this, 'skin')))
            {
                add_action('admin_head', array(&$this, 'skin'));
            }

            do_action('boots_admin_print_styles-' . $slug, 'boots_admin');
            do_action('boots_admin_print_scripts-' . $slug, 'boots_admin');
        }
    }

    public function styles()
    {
        $this->Boots->Form->styles();

        $this->Boots->Enqueue
        ->raw_style('cssreset-context-min')
            ->source($this->url . '/css/cssreset-context-min.css')
            ->done()
        ->raw_style('boots_admin')
            ->source($this->url . '/css/boots_admin.css')
            ->requires('cssreset-context-min')
            ->requires('boots_form')
            ->done();
    }

    public function scripts()
    {
        $slug = sanitize_text_field($_GET['page']);

        $this->Boots->Form->scripts();

        $this->Boots->Ajax->scripts();

        $this->Boots->Enqueue
        ->script('jquery')->done()
        ->raw_script('boots_admin_awesome_grid')
            ->source($this->url . '/third-party/awesome-grid/awesome-grid.min.js')
            ->requires('jquery')
            ->done(true)
        ->raw_script('boots_admin')
            ->source($this->url . '/js/boots_admin.min.js')
            ->requires('boots_ajax')
            ->requires('boots_admin_awesome_grid')
            ->vars('menu_slug', $slug)
            ->vars('action_save_options', 'admin_save_options')
            ->vars('nonce_save_options', wp_create_nonce('boots_admin_save_options'))
            ->done(true);
    }

    public function skin()
    {
        global $_wp_admin_css_colors;
        $Skins = $_wp_admin_css_colors;

        $skin = get_user_meta(get_current_user_id(), 'admin_color', true);

        if(isset($Skins[$skin]))
        {
            echo '<style>';
            $Colors = $Skins[$skin]->colors;
            switch($skin)
            {
                case 'fresh':
                    $color = $Colors[0];
                    break;
                case 'light':
                case 'coffee':
                case 'ectoplasm':
                case 'ocean':
                case 'sunrise':
                case 'midnight':
                    $color = $Colors[1];
                    break;
                case 'blue':
                    $color = $Colors[2];
                    break;
                case '':
                    $color = $Colors[3];
                    break;
                default:
                    $color = '#272727';
                    break;
            }
            echo '
            .boots-admin_color,
            .boots-admin .boots-admin-header
            ul li.active a,
            .boots-admin .boots-admin-header
            ul li.active a:hover {
                color: ' . $color . ';
            }
            .boots-admin_bg {
                background-color: ' . $color . ';
            }
            .boots-admin_border {
                border-color: ' . $color . ';
            }';
            echo '</style>';
        }
    }

    public function render()
    {
        if(!isset($_GET['page']))
        {
            return false;
        }

        $slug = sanitize_text_field($_GET['page']);

        if(!isset($this->Menus[$slug]))
        {
            return false;
        }

        $Menu = $this->Menus[$slug];

        $Data = array();

        $Parent = $Menu['parent']
        ? $this->Menus[$Menu['parent']]
        : false;

        $Data['parent'] = $Parent
        ? (
            ($Parent['x2']
            ? $Parent['x2']['page_title']
            : $Parent['page_title'])
        ) : false;


        $Data['parent'] = !$Data['parent']
        ? (
            ($Menu['x2']
            ? $Menu['x2']['page_title']
            : false) // $Menu['page_title']
        ) : $Data['parent'];

        $Data['title'] = $Menu['label'];

        $Data['h1'] = $Data['parent']
        ? ($Data['parent'] . ' &rarr; ')
        : '';
        $Data['h1'] .= $Data['title'];

        $Data['logo'] = $this->Settings['APP_LOGO'];

        $Data['sections'] = array();

        if(isset($Menu['sections']) && count($Menu['sections']))
        {
            reset($Menu['sections']);

            $Sections = $Menu['sections'];

            $Data['sections'] = $Sections;


            $Data['active'] = count($Menu['active_s'])
            ? $Menu['active_s']
            : array(key($Sections));
        }

        $Data['layout'] = $Menu['layout'];

        $rendered = apply_filters('boots_admin_template', $Data, $slug);

        if($rendered !== true)
        {
            include $this->dir . '/admin.php';
        }
    }

    public function icon($path, $external = false)
    {
        if(!$this->menu_slug)
        {
            $this->Boots->error($this->error());
            return false;
        }

        $this->Menus[$this->menu_slug]['icon'] = $external
        ? $path : ($this->Settings['APP_URL'] . '/' . $path);

        return $this;
    }

    public function layout($style) // [default], grid
    {
        if(!$this->menu_slug)
        {
            $this->Boots->error($this->error());
            return false;
        }

        $this->Menus[$this->menu_slug]['layout'] = $style;
        if($this->submenu_slug)
        {
            $this->Menus[$this->submenu_slug]['layout'] = $style;
        }

        return $this;
    }

    public function menu($slug, $label = false, $page_title = false, $allowed = 'manage_options')
    {
        $x2 = $this->menu_slug && ($this->menu_slug == $slug)
        ? array(
            'label' => $this->Menus[$slug]['label'],
            'page_title' => $this->Menus[$slug]['page_title'],
            'allow' => $this->Menus[$slug]['allow'],
            'icon' => $this->Menus[$slug]['icon']
        ) : false;

        $parent = $this->menu_slug && !$x2
        ? $this->menu_slug
        : false;

        $this->Menus[$slug] = array(
            'menu'       => null,
            'label'      => $label ? $label : $this->nick,
            'page_title' => $page_title
                            ? $page_title
                            : ((($label != $this->nick) ? ($label . ' - ') : '') . $this->nick),
            'allow'      => $allowed,
            'icon'       => $this->Settings['APP_ICON'] ? $this->Settings['APP_ICON'] : false,
            'sections'   => array(),
            'active_s'   => array(),
            'layout'     => 'default',
            'parent'     => $parent,
            'x2'         => $x2
        );

        if($parent)
        {
            $this->submenu_slug = $slug;
        }
        else
        {
            $this->menu_slug = $slug;
        }

        return $this;
    }

    public function section($name, $active = false)
    {
        if(!$this->menu_slug)
        {
            $this->Boots->error($this->error());
            return false;
        }

        $slug = $this->submenu_slug ? $this->submenu_slug : $this->menu_slug;
        $name = sanitize_text_field($name);

        $Section = array($name => array());
        $Sections = array_merge_recursive((array) $this->Menus[$slug]['sections'], $Section);
        $this->Menus[$slug]['sections'] = $Sections;

        if($active)
        {
            $this->Menus[$slug]['active_s'][] = $name;
        }

        $this->section = $name;
        return $this;
    }

    public function add($field_str, $Args = array())
    {
        if(!$this->menu_slug)
        {
            $this->Boots->error($this->error());
            return false;
        }

        if(!$this->section)
        {
            $this->section('Section');
        }

        $slug = $this->submenu_slug ? $this->submenu_slug : $this->menu_slug;
        $section = $this->section;

        if($field_str == '_')
        {
            $this->Menus[$slug]['sections'][$section][] = array('_' => $Args);
        }
        else if(is_array($field_str))
        {
            foreach($field_str as $Field)
            {
                if(!is_array($Field) || (!isset($Field['_'])))
                {
                    $this->Menus[$slug]['sections'][$section][] = array('_' => $Field);
                }
                else
                {
                    $f = $Field['_'];
                    unset($Field['_']);
                    $args = $Field;
                    $this->Menus[$slug]['sections'][$section][] = array($f => $args);
                }
            }
        }
        else
        {
            $this->Menus[$slug]['sections'][$section][] = array($field_str => $Args);
        }

        return $this;
    }

    public function cb($func)
    {
        return $this->add('_', $func);
    }

    public function done()
    {
        if(!$this->menu_slug)
        {
            $this->Boots->error($this->error());
            return false;
        }

        foreach($this->Menus as $slug => & $Menu)
        {
            if($Menu['x2'])
            {
                $Menu['x2']['menu'] = add_menu_page(
                    $Menu['x2']['page_title'],
                    $Menu['x2']['label'],
                    $Menu['x2']['allow'],
                    $slug,
                    array(&$this, 'render'),
                    $Menu['x2']['icon']
                );
            }
            if(!$Menu['parent'] && !$Menu['x2'])
            {
                $Menu['menu'] = add_menu_page(
                    $Menu['page_title'],
                    $Menu['label'],
                    $Menu['allow'],
                    $slug,
                    array(&$this, 'render'),
                    $Menu['icon']
                );
            }
            else
            {
                $Menu['menu'] = add_submenu_page(
                    !$Menu['x2'] ? $Menu['parent'] : $slug,
                    $Menu['page_title'],
                    $Menu['label'],
                    $Menu['allow'],
                    $slug,
                    array(&$this, 'render')
                );
            }
        }

        $this->menu_slug = null;
        $this->submenu_slug = null;
        $this->section = null;

        $this->scripts_and_styles();

        return $this;
    }

    public function ajax_save_options($nonce)
    {
        header('content-type: application/json; charset=utf-8');
        // check for $nonce first
        if(!wp_verify_nonce($nonce, 'boots_admin_save_options'))
        {
            die(json_encode(array('error'=>'insecure access')));
        }
        // good to go

        $Response = array();

        $menu = sanitize_text_field($_POST['_menu']);
        unset($_POST['_menu']);

        $Options = apply_filters('boots_admin_save_options-'.$menu, $_POST);

        # save the options here
        foreach($Options as $term => $value)
        {
            $this->Boots->Database
            ->term($term)
                ->update($value);
        }
        $Response['success'] = true;

        $Response = apply_filters('boots_admin_ajax_response-'.$menu, $Response);

        // return response
        die(json_encode($Response));
    }

    private function error($msg = false)
    {
        if(!$msg)
        {
            $msg = 'Could not find any menu. ';
            $msg .= 'Have you called <em>Admin&rarr;menu</em> ?';
        }
        return $msg;
    }
}
