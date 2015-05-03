<div class="boots-admin boots-admin-screen-<?php echo $slug; ?> yui3-cssreset">

    <div class="boots-admin-header boots-admin_color">
        <img src="<?php echo $Data['logo']; ?>" />
        <h1><?php echo $Data['h1']; ?></h1>
        <?php if(count($Data['sections']) > 1) : ?>
        <h2 class="nav-tab-wrapper">
            <?php
                foreach(array_keys($Data['sections']) as $section)
                {
                    echo '<a class="nav-tab';
                    echo in_array($section, $Data['active'])
                    ? ' nav-tab-active"' : '"';
                    echo ' href="#' . rawurlencode($section) . '">' . $section . '</a>';
                }
            ?>
            <span class="boots-admin-meta">
			    <span class="boots-admin-icon"></span>
                <?php echo apply_filters('boots_admin_buttons', apply_filters('boots_admin_buttons_'.$slug, ''), $slug); ?>
                <?php if(isset($Data['restore'])) : ?>
                <a href="#" class="button-secondary js-restore-all"><?php echo $Data['restore']['restore']; ?></a>
                <?php endif; ?>
                <a href="#" class="button-primary js-save-all"><?php echo $Data['save']; ?></a>
            </span>
        </h2>
        <?php endif; ?>
    </div>

    <div class="boots-admin-body">

        <form name="boots_admin_form">

        <?php
            switch($Data['layout'])
            {
                case 'grid' :
                    $class = 'awesome-grid';
                    break;
                default :
                    $class = '';
                    break;
            }
        ?>

        <div class="boots-form <?php echo $class; ?>">
            <div class="boots-admin-sidebar">
                <!-- sidebar content -->
            </div>
            <?php
                foreach($Data['sections'] as $section => $Fields)
                {
                    echo '<ul data-as="section" data-section="' . rawurlencode($section) . '"';
                    echo in_array($section, $Data['active'])
                    ? ' class="active">' : '>';
                    echo "\n";
                    foreach($Fields as $group => $Arr)
                    {
                        $type = isset($Arr['type'])
                        ? $Arr['type'] : null;
                        $Atts = isset($Arr['args'])
                        ? $Arr['args'] : null;
                        $Requires = isset($Arr['requires'])
                        ? $Arr['requires'] : array();

                        if($Atts && !is_array($Atts)) // its a custom field
                        {
                            $uniqueid = uniqid('', true);
                            echo '<li data-id="' . $uniqueid . '">';
                            if(is_callable($Atts))
                            call_user_func($Atts);
                            else echo '<i>' . $Atts . '</i> is not callable';
                            if($Requires) include $this->dir . '/requires.php';
                            echo '</li>';
                        }
                        else if($type && $Atts) // its a single field
                        {
                            $uniqueid = uniqid(isset($Atts['name']) ? ($Atts['name'] . '-') : '', true);
                            echo '<li data-id="' . $uniqueid . '"';
                            echo isset($Atts['x']) && is_numeric($Atts['x'])
                            ? (' data-x="' . $Atts['x'] . '"')
                            : '';
                            echo $type == 'hidden'
                            ? ' class="boots-admin_hidden">' : ' class="clearfix">';
                            if($type == '_') // its a custom callable field
                            {
                                if(is_callable($Atts))
                                call_user_func($Atts);
                                else echo '<i>' . $Atts . '</i> is not callable';
                                echo  "\n";
                            }
                            else { // its a form field
                                echo $this->Boots->Form->generate($type, $Atts) . "\n";
                            }
                            if($Requires) include $this->dir . '/requires.php';
                            echo '</li>' . "\n";
                        }
                        else if(!$type) // its a group
                        {
                            $GroupProp = isset($Data['groups'][$group])
                            ? $Data['groups'][$group]
                            : array();
                            echo '<li class="clearfix"';
                            echo isset($GroupProp['x']) && is_numeric($GroupProp['x'])
                            ? (' data-x="' . $GroupProp['x'] . '">')
                            : '>';
                            echo '<label>' . $group . '</label>';
                            foreach($Arr as $GroupArr)
                            {
                                $type = isset($GroupArr['type'])
                                ? $GroupArr['type'] : null;
                                $Atts = isset($GroupArr['args'])
                                ? $GroupArr['args'] : null;
                                $Requires = isset($GroupArr['requires'])
                                ? $GroupArr['requires'] : array();
                                if($type && $Atts)
                                {
                                    $uniqueid = uniqid(isset($Atts['name']) ? ($Atts['name'] . '-') : '', true);
                                    echo '<div data-id="' . $uniqueid . '" class="boots-form-group ';
                                    echo $type == 'hidden'
                                    ? ' boots-admin_hidden">'
                                    : '">';
                                    if($type == '_') // its a custom callable field
                                    {
                                        if(is_callable($Atts))
                                        call_user_func($Atts);
                                        else echo '<i>' . $Atts . '</i> is not callable';
                                    }
                                    else { // its a form field
                                        echo $this->Boots->Form->generate($type, $Atts);
                                    }
                                    echo '</div>' . "\n";
                                    if($Requires) include $this->dir . '/requires.php';
                                }
                            }
                            echo isset($GroupProp['help'])
                            ? ('<p>' . $GroupProp['help'] . '</p>')
                            : '';
                            echo '</li>' . "\n";
                        }
                    }
                    echo '</ul>';
                }
            ?>
            <?php if(count($Data['sections']) == 1) : ?>
           	<div style="margin-top: 21px;">
                <a href="#" class="button-primary js-save-all"><?php echo $Data['save']; ?></a>
                <?php if(isset($Data['restore'])) : ?>
                <a href="#" class="button-secondary js-restore-all"><?php echo $Data['restore']['restore']; ?></a>
                <?php endif; ?>
                <?php echo apply_filters('boots_admin_buttons', apply_filters('boots_admin_buttons_'.$slug, ''), $slug); ?>
                <span class="boots-admin-icon"></span>
            </div>
            <?php endif; ?>
        </div>

        </form>

    </div>

    <?php if(isset($Data['restore'])) : ?>
    <div id="boots_admin_restore_lb">
        <h3><?php echo $Data['restore']['confirm']; ?></h3>
        <hr />
        <a href="#" class="button-primary js-restore-all-ok" rel="modal:close"><?php echo $Data['restore']['ok']; ?></a>
        <a href="#" class="button-secondary js-restore-all-cancel" rel="modal:close"><?php echo $Data['restore']['cancel']; ?></a>
    </div>
    <?php endif; ?>

</div>
