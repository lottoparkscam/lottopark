<?php

use Fuel\Core\Controller;
use Fuel\Core\View;
use Services\AffAuthService;

class Controller_Aff_Password extends Controller
{
    private AffAuthService $affAuthService;

    public function before(): void
    {
        $this->affAuthService = Container::get(AffAuthService::class);
        $this->prepareTemplate();
    }

    public function action_lost_password(): Response
    {
        $hash = $this->param('hash');
        $action = Input::post('action');

        if (!empty($hash)) {
            if ($action === 'process') {
                $this->processSetNewPassword();
            }

            $inside = View::forge('aff/auth/reset_password');
        } else {
            $inside = View::forge('aff/auth/lost_password');

            if ($action === 'process') {
                $this->processForm();
            }
        }

        $this->view->inside = $inside;

        return Response::forge($this->view);
    }

    private function processSetNewPassword(): void
    {
        $hash = $this->param('hash');

        try {
            $this->affAuthService->processNewPasswordForm($hash);

            Session::set_flash("message", ["success", _("Password has been successfully changed.")]);
            Response::redirect("/");
        } catch (Exception $e) {
            Session::set_flash("message", ["danger", _($e->getMessage())]);
        }
    }

    private function processForm(): void
    {
        try {
            $this->affAuthService->processPasswordResetForm();

            Session::set_flash("message", [
                "success",
                _("We have sent you an e-mail with password reset link. Please follow the link to complete the process.")
            ]);
        } catch (Exception $e) {
            Session::set_flash("message", ["danger", _($e->getMessage())]);
        }
    }

    private function prepareTemplate(): void
    {
        $this->view = View::forge("aff/index");
        $this->view->header = View::forge("aff/shared/header");
        $this->view->navbar = null;
        $this->view->footer = View::forge("aff/shared/footer");
    }
}
