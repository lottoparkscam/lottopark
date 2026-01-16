<?php

/**
 * User registration features
 */
trait Traits_User_Registration
{
    
    /**
     * @param array $whitelabel
     * @param string $email
     * @return bool
     */
    private function check_email_used(array $whitelabel, string $email): bool
    {
        $res = Model_Whitelabel_User::get_count_for_whitelabel_and_email(
            $whitelabel,
            $email
        );

        $users_count = $res[0]['count'];

        if ($users_count > 0) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    private function generate_token_salt_hash(int $whitelabel_id, string $password): array
    {
        $token = Lotto_Security::generate_user_token($whitelabel_id);
        $salt = Lotto_Security::generate_salt();
        $hash = Lotto_Security::generate_hash(
            $password,
            $salt
        );

        return [
            $token,
            $salt,
            $hash
        ];
    }
}
