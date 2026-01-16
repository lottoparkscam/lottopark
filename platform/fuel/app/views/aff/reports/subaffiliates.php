<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/aff/reports/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?= _("Your sub affiliate data"); ?>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <strong><?= _("Sub Affiliate Ref"); ?>:</strong> <?= strtoupper($user['sub_affiliate_token']); ?><br>
                            <strong><?= _("Sub Affiliate Casino Ref"); ?>:</strong> <?= strtoupper($user['sub_affiliate_token']); ?>
                        </div>
                        <div class="form-group">
                            <label for="general_link">
                                <?= _("General link"); ?>:
                            </label>
                            <input type="text"
                                class="form-control"
                                id="general_link"
                                value="<?= $subaffiliateLink; ?>"
                                readonly="readonly">
                        </div>
                        <div class="form-group">
                            <label for="general_link">
                                <?= _("Casino general link"); ?>:
                            </label>
                            <input type="text"
                                class="form-control"
                                id="general_link"
                                value="<?= $casinoSubaffiliateLink; ?>"
                                readonly="readonly">
                            <p class="help-block">
                                <?= _("You can direct your traffic to any URL you want to use as landing page.") ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
            <?php
                if ($affiliateCanCreateSubAffiliates):
                    ?>
                    <div class="pull-right">
                        <a href="/subaffiliates/create" class="btn btn-success">
                            <span class="glyphicon glyphicon-plus"></span> <?= _('Add New') ?></a>
                    </div>
            <?php endif; ?>
            <h2>
                <?php echo _("Sub-affiliates"); ?>
            </h2>
            <p class="help-block">
                <?php echo _("Here you can see your sub-affiliates."); ?>
            </p>

            <?php
                include(APPPATH . "views/aff/reports/subaffiliates_filters.php");
            ?>

            <div class="container-fluid container-admin">
                <?php
                    include(APPPATH . "views/aff/shared/messages.php");
                    // show table if it's not empty.
                    if (!empty($subaffiliates)):
                        echo $pages;
                ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered table-sort">
                                <thead>
                                    <tr>
                                        <th class="text-center">
                                            <?= _("ID") ?>
                                        </th>
                                        <th class="text-center">
                                            <?= _("Username") ?>
                                        </th>
                                        <th class="text-center">
                                            <?= _("Phone") ?>
                                        </th>
                                        <th class="text-center">
                                            <?= _("Country") ?>
                                        </th>
                                        <th class="text-center">
                                            <?= _("Language") ?>
                                        </th>
                                        <th class="text-center">
                                            <?= _("Time Zone") ?>
                                        </th>
                                        <th class="text-center">
                                            <?= _("Created") ?>
                                        </th>
                                        <th class="text-center">
                                            <?= _("Last Active<br>IP<br>Country") ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        foreach ($subaffiliates as $id => $row):
                                    ?>
                                            <tr>
                                                <?php 
                                                    foreach ($row as $item) :
                                                ?>
                                                        <td class="text-center">
                                                            <?= $item ?>
                                                        </td>
                                                <?php 
                                                    endforeach;
                                                ?>
                                            </tr>
                                    <?php 
                                        endforeach;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                <?php 
                        echo $pages;
                    else:
                ?>
                        <p class="text-info">
                            <?= _("No sub-affiliates.") ?>
                        </p>
                <?php 
                    endif;
                ?>
            </div>
        </div>
    </div>
</div>