<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Payment_TruevoCC
 */
final class Forms_Whitelabel_Payment_TruevoCC extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;

    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("truevocc");

        $regex = "/^[a-zA-Z0-9=.\-_]+$/u";
        // Based on Truevo example value of Authorization Bearer it seems that key should be
        // 60 chars long, but I allow longer strings - maybe it should be changed
        // back to 60
        $validation->add("input.truevocc_authorization_bearer", _("Authorization Bearer"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("match_pattern", $regex)
            ->add_rule("max_length", 169);

        $validation->add("input.truevocc_entity_id", _("Entity ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric"])
            ->add_rule("max_length", 76)
            ->add_rule("min_length", 76);

        $validation->add("input.truevocc_brands", _("Brands"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 300)
            ->add_rule("valid_string", ["alpha", "spaces", "uppercase"]);

        $validation->add("input.truevocc_descriptor", _("Descriptor"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 300);

        $validation->add("input.truevocc_test", _("Test account"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        return $validation;
    }

    /**
     *
     * @param array $data
     * @param array $errors
     * @return array
     */
    public function prepare_data_to_show(
        array $data = null,
        array $errors = null
    ): array {
        $truevocc = [];

        $truevocc_authorization_bearer_error_class = '';
        if (isset($errors['input.truevocc_authorization_bearer'])) {
            $truevocc_authorization_bearer_error_class = ' has-error';
        }
        $truevocc['authorization_bearer_error_class'] = $truevocc_authorization_bearer_error_class;

        $truevocc_authorization_bearer_value_prepared = '';
        if (null !== Input::post("input.truevocc_authorization_bearer")) {
            $truevocc_authorization_bearer_value_prepared = Input::post("input.truevocc_authorization_bearer");
        } elseif (isset($data['truevocc_authorization_bearer'])) {
            $truevocc_authorization_bearer_value_prepared = $data['truevocc_authorization_bearer'];
        }
        $truevocc['authorization_bearer_value'] = Security::htmlentities($truevocc_authorization_bearer_value_prepared);

        $truevocc_entity_id_error_class = '';
        if (isset($errors['input.truevocc_entity_id'])) {
            $truevocc_entity_id_error_class = ' has-error';
        }
        $truevocc['entity_id_error_class'] = $truevocc_entity_id_error_class;

        $truevocc_entity_id_value_prepared = '';
        if (null !== Input::post("input.truevocc_entity_id")) {
            $truevocc_entity_id_value_prepared = Input::post("input.truevocc_entity_id");
        } elseif (isset($data['truevocc_entity_id'])) {
            $truevocc_entity_id_value_prepared = $data['truevocc_entity_id'];
        }
        $truevocc['entity_id_value'] = Security::htmlentities($truevocc_entity_id_value_prepared);

        $truevocc_brands_error_class = '';
        if (isset($errors['input.truevocc_brands'])) {
            $truevocc_brands_error_class = ' has-error';
        }
        $truevocc['brands_error_class'] = $truevocc_brands_error_class;

        $truevocc_brands_value_prepared = '';
        if (null !== Input::post("input.truevocc_brands")) {
            $truevocc_brands_value_prepared = Input::post("input.truevocc_brands");
        } elseif (isset($data['truevocc_brands'])) {
            $truevocc_brands_value_prepared = $data['truevocc_brands'];
        }
        $truevocc['brands_value'] = Security::htmlentities($truevocc_brands_value_prepared);

        $truevocc_descriptor_error_class = '';
        if (isset($errors['input.truevocc_descriptor'])) {
            $truevocc_descriptor_error_class = ' has-error';
        }
        $truevocc['descriptor_error_class'] = $truevocc_descriptor_error_class;

        $truevocc_descriptor_value_prepared = '';
        if (null !== Input::post("input.truevocc_descriptor")) {
            $truevocc_descriptor_value_prepared = Input::post("input.truevocc_descriptor");
        } elseif (isset($data['truevocc_descriptor'])) {
            $truevocc_descriptor_value_prepared = $data['truevocc_descriptor'];
        }
        $truevocc['descriptor_value'] = Security::htmlentities($truevocc_descriptor_value_prepared);

        $link_url = "https://docs.truevo.eu/reference/brands-reference";
        $truevocc_brands_info =  _(
            "You can find list of all brands on "
        );
        $truevocc_brands_info .= "<a href=\"" . $link_url .
            "\">" . $link_url . "</a>";
        $truevocc_brands_info .= _(
            " page. Please enter all brands names separated " .
            "by space and use uppercase characters."
        );
        $truevocc['brands_info'] = $truevocc_brands_info;

        $truevocc_test_checked = '';
        if ((null !== Input::post("input.truevocc_test") &&
                (int)Input::post("input.truevocc_test") === 1) ||
            (isset($data['truevocc_test']) &&
                (int)$data['truevocc_test'] === 1)
        ) {
            $truevocc_test_checked = ' checked="checked"';
        }
        $truevocc['test_checked'] = $truevocc_test_checked;

        $truevocc['test_info'] = _("Check it for test account.");

        return $truevocc;
    }
    
    /**
     *
     * @param Validation|null $additional_values_validation
     * @return array
     */
    public function get_data(
        ?Validation $additional_values_validation
    ): array {
        $data = [];
        $data['truevocc_authorization_bearer'] = $additional_values_validation->validated("input.truevocc_authorization_bearer");
        $data['truevocc_entity_id'] = $additional_values_validation->validated("input.truevocc_entity_id");
        $data['truevocc_brands'] = $additional_values_validation->validated("input.truevocc_brands");
        $data['truevocc_descriptor'] = $additional_values_validation->validated("input.truevocc_descriptor");
        $data['truevocc_test'] = $additional_values_validation->validated("input.truevocc_test") == 1 ? 1 : 0;
        
        return $data;
    }
}
