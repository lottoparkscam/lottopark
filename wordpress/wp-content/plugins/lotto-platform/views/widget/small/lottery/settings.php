<?php
if (!defined('WPINC')) {
    die;
}
?>
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>">
        <?php echo Security::htmlentities(_('Title')); ?>:
    </label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
           name="<?php echo $this->get_field_name('title'); ?>" 
           type="text" value="<?php echo $title; ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id('lottery'); ?>">
        <?php echo Security::htmlentities(_('Lottery')); ?>:
    </label>
    <br>	
	<select class="widefat" id="<?php echo $this->get_field_id('lottery'); ?>" 
            name="<?php echo $this->get_field_name('lottery'); ?>">
        <?php
            foreach ($lotteries['__by_id'] as $item):
        ?>
                <option value="<?php echo $item['id']; ?>"<?php
                        if ((empty($lottery) && $item['id'] == 1) ||
                            $lottery == $item['id']
                        ):
                            echo ' selected="selected"';
                        endif;
                    ?>>
                    <?php echo Security::htmlentities(_($item['name'])); ?>
                </option>
        <?php
            endforeach;
        ?>
	</select>
</p>

<p>
    <label for="<?php echo $this->get_field_id('content'); ?>">
        <?php echo Security::htmlentities(_('Content')); ?>:
    </label>
    <textarea class="widefat" id="<?php echo $this->get_field_id('content'); ?>" 
              name="<?php echo $this->get_field_name('content'); ?>"><?php
              echo $content;
        ?></textarea>
</p>

<p>
    <label for="<?php echo $this->get_field_id('mobilehide'); ?>">
        <input type="checkbox" value="1" id="<?php echo $this->get_field_id('mobilehide'); ?>" 
               name="<?php echo $this->get_field_name('mobilehide'); ?>"<?php
                    if ($mobilehide == 1):
                        echo ' checked="checked"';
                    endif;
                ?>>
        <?php echo Security::htmlentities(_('Hide on mobile')); ?>
    </label>
</p>