<?php 
    if (null !== ($message = Session::get_flash("message"))):
?>
        <div class="alert alert-<?= $message[0]; ?>" role="alert">
            <button type="button" 
                    class="close" 
                    data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
            <p>
                <?= Security::htmlentities($message[1]); ?>
            </p>
        </div>
<?php 
    endif;
