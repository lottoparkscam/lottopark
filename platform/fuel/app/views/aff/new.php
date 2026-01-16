<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/aff/reports/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("New sub-affiliate"); ?>
        </h2>

        <p class="help-block">
            <?= _("You can add new sub-affiliate here."); ?>
        </p>

        <a href="/subaffiliates<?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>

        <div class="container-fluid container-admin row">
            <?php include(APPPATH . "views/aff/shared/messages.php"); ?>
            <div class="col-md-6">
                <form method="post" action="/subaffiliates/store">
                    <?php
                    if (!empty($this->errors)) {
                        include(APPPATH . "views/whitelabel/shared/errors.php");
                    }
                    ?>
                    <div class="form-group <?= $errorClasses['login']; ?>">
                        <label class="control-label" for="inputLogin">
                            <?= _("Login"); ?>:
                        </label>
                        <input type="text"
                               required
                               autofocus
                               value="<?= $subAffValues['login']; ?>"
                               class="form-control"
                               id="inputLogin"
                               name="input[login]"
                               placeholder="<?= _("Enter login"); ?>">
                    </div>

                    <div class="form-group <?= $errorClasses['email']; ?>">
                        <label class="control-label" for="inputEmail">
                            <?= _("E-mail"); ?>:
                        </label>
                        <input type="email"
                               required
                               value="<?= $subAffValues['email']; ?>"
                               class="form-control"
                               id="inputEmail"
                               name="input[email]"
                               placeholder="<?= _("Enter e-mail"); ?>">
                    </div>

                    <div class="form-group <?= $errorClasses['password']; ?>">
                        <label class="control-label" for="inputPassword">
                            <?= _("Password"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="text"
                                   required="required"
                                   autofocus
                                   class="form-control clear"
                                   id="inputPassword"
                                   name="input[password]"
                                   placeholder="<?= _("Enter password"); ?>">
                            <span class="input-group-btn">
                                <button type="button"
                                        class="btn btn-default"
                                        id="generatePassword">
                                    <span class="glyphicon glyphicon-refresh"></span> <?= _("Random"); ?>
                                </button>
                            </span>
                        </div>
                        <p class="help-block" id="generatedPassword">
                            <?= _("Generated password"); ?>: <span></span>
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
