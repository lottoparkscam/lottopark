<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
            include(APPPATH . "views/whitelabel/bonuses/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Users"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("You can view users who used promo codes of your campaign here."); ?>
        </p>

        <a href="/bonuses/promocodes<?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>

        <div class="container-fluid container-admin">
            <?php
                if (isset($data) && count($data) > 0):
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-sort">
                    <thead>
                        <tr>
                            <th>
                                <?= _("Code"); ?>
                            </th>
                            <th>
                                <?= _("User ID &bull; User Name"); ?>
                                <br>
                                <?= _("E-mail"); ?>
                            </th>
                            <th>
                                <?= _("Transaction ID"); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($data as $item):
                        ?>
                            <tr>
                                <td>
                                    <?= $item["full_code"]; ?>
                                </td>
                                <td class="text-nowrap">
                                    <?php
                                        echo $item["user_data"];
                                    ?>
                                    <br>
                                    <?php
                                        echo $item["email"];
                                        if ($item['show_deleted']):
                                    ?>
                                        <br>
                                        <span class="text-danger">
                                            <?= _("Deleted"); ?>
                                        </span>
                                    <?php
                                        endif;
                                    ?>
                                    <br>
                                    <a href="<?= $item['show_user_url']; ?>" 
                                    class="btn btn-xs btn-primary">
                                    <span class="glyphicon glyphicon-user"></span> 
                                    <?= _("View user"); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo $item["full_transaction_token"];
                                        if (isset($item['ttoken'])):
                                            echo "<br>";
                                    ?>
                                        <a href="<?= $item['view_transaction_url']; ?>" 
                                            class="btn btn-xs btn-primary">
                                            <span class="glyphicon glyphicon-th-list"></span> 
                                            <?= _("View transaction"); ?>
                                        </a>
                                    <?php
                                        endif;
                                    ?>
                                </td>
                            </tr>
                        <?php
                            endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
                else:
            ?>
                <p class="text-info"><?= _("Code has not been used by any user yet."); ?></p>
            <?php
                endif;
            ?>
        </div>
    </div>
</div>