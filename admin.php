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
                    foreach($Fields as $Field)
                    {
                        if(!is_array($Field) && is_callable($Field))
                        {
                            echo '<li>' . call_user_func($Field) . '</li>';
                        }
                        else if(is_array($Field))
                        {
                            foreach($Field as $type => $Atts)
                            {
                                echo '<li';
                                echo isset($Atts['x']) && is_numeric($Atts['x'])
                                ? (' data-x="' . $Atts['x'] . '"')
                                : '';
                                echo $type == 'hidden'
                                ? ' class="boots-admin_hidden">' : ' class="clearfix">';
                                if($type == '_')
                                {
                                    call_user_func($Atts);
                                }
                                else {
                                    echo $this->Boots->Form->generate($type, $Atts);
                                }
                                echo '</li>';
                            }
                        }
                    }
                    echo '</ul>';
                }
            ?>
            <?php if(count($Data['sections']) == 1) : ?>
            <div style="margin-top: 21px;">
                <?php if(isset($Data['restore'])) : ?>
                <a href="#" class="button-secondary js-restore-all"><?php echo $Data['restore']['restore']; ?></a>
                <?php endif; ?>
                <a href="#" class="button-primary js-save-all"><?php echo $Data['save']; ?></a>
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