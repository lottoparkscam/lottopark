<?php 
if (null !== ($message = Session::get_flash("message"))):
?>
    <div class="alert alert-<?= $message[0]; ?>" role="alert">
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        <?php 
            if (is_array($message[1])):
                foreach ($message[1] as $msg):
        ?>
                    <p>
                        <?= Security::htmlentities($msg); ?>
                    </p>
        <?php 
                endforeach;
            else:
        ?>
                <p>
                    <?= Security::htmlentities($message[1]); ?>
                </p>
        <?php 
            endif;
        ?>
    </div>
<?php 
endif;
