<?php


use Orm\Query;
use Models\Whitelabel;

class Test_Feature_Model_SaveTest extends Test_Feature
{

    /** @var string */
    private $email;

    /** @var string */
    private $whitelabel_name;

    public function setUp(): void
    {
        parent::setUp();

        $this->email = 'new@unit.test';
        $this->whitelabel_name = 'whitelabel_test';
    }

    public function test_create_user_by_constructor(): void
    {
        $this->create_user_by_constructor();
        $this->assert_user_has_been_created();
    }

    public function test_create_orm_whitelabel_by_constructor(): void
    {
        $this->create_orm_whitelabel_by_constructor();
        $this->assert_orm_whitelabel_has_been_created_by_email();
    }

    public function test_create_language_by_constructor(): void
    {
        $language = new Model_Language($this->get_language_data());
        $language->save();

        $this->assert_language_has_been_created('te_ST');
    }

    public function test_create_language_by_forge(): void
    {
        $this->create_language_by_forge();
        $this->assert_language_has_been_created('te_ST');
    }

    public function test_create_orm_whitelabel_by_forge(): void
    {
        $this->create_orm_whitelabel_by_forge();
        $this->assert_orm_whitelabel_has_been_created_by_email();
    }

    public function test_create_user_by_constructor_and_not_change_data(): void
    {
        $user = $this->create_user_by_constructor();
        $user->save();

        $this->assert_user_has_been_created();
    }

    public function test_create_orm_whitelabel_by_constructor_and_not_change_data(): void
    {
        $whitelabel = $this->create_orm_whitelabel_by_constructor();
        $whitelabel->save();

        $this->assert_orm_whitelabel_has_been_created_by_name();
    }

    public function test_create_user_by_constructor_and_change_data(): void
    {
        $this->create_user_by_constructor();
        $this->assert_user_has_been_created();
        $this->change_and_check_user_data();
    }

    public function test_create_orm_whitelabel_by_constructor_and_change_data(): void
    {
        $this->create_orm_whitelabel_by_constructor();
        $this->assert_orm_whitelabel_has_been_created_by_email();
        $this->change_and_check_whitelabel_data();
    }

    public function test_create_language_by_forge_and_change_data(): void
    {
        $language = $this->create_language_by_forge();

        // check change data by using set
        $language->set([
            'code' => 'st_TE'
        ]);
        $language->save();

        $this->assert_language_has_been_created('st_TE');

        // check change data by using __set
        $language->code = 'tt_TT';
        $language->save();

        $this->assert_language_has_been_created('tt_TT');
    }

    private function create_user_by_constructor(): Model_Whitelabel_User
    {
        $user = new Model_Whitelabel_User();
        $user = $this->prepare_user_data($user);

        $user->set([
            'email' => $this->email
        ]);

        $user->save();

        return $user;
    }

    private function assert_user_has_been_created(): void
    {
        $user = Model_Whitelabel_User::find_one_by('email', $this->email);

        $this->assertIsObject($user);
        $this->assertInstanceOf(Model_Whitelabel_User::class, $user);
    }

    private function create_orm_whitelabel_by_constructor(): Whitelabel
    {
        $whitelabel = new Whitelabel($this->get_whitelabel_data());
        $whitelabel->save();

        return $whitelabel;
    }

    private function assert_orm_whitelabel_has_been_created_by_email(): void
    {
        $whitelabel = Whitelabel::find('first', [
            'where' => [
                'email' => $this->email
            ]
        ]);

        $this->assertIsObject($whitelabel);
        $this->assertInstanceOf(Whitelabel::class, $whitelabel);
    }

    private function assert_orm_whitelabel_has_been_created_by_name(): void
    {
        $whitelabel = Whitelabel::find('first', [
            'where' => [
                'name' => $this->whitelabel_name
            ]
        ]);
        $this->assertIsObject($whitelabel);
        $this->assertInstanceOf(Whitelabel::class, $whitelabel);
    }

    private function create_orm_whitelabel_by_forge(): void
    {
        $whitelabel = Whitelabel::forge($this->get_whitelabel_data());

        $whitelabel->save();
    }

    /**
     * The most important part of this test
     * Check if blow case could happen
     *
     * 1. Find user and call it $user_1
     * 2. Find the same user and call it $user_2
     * 3. Change balance(50) and name(Stefan) of $user_1
     * 4. Change balance(100) of $user_2
     * 5. Save $user_1
     * 6. Save $user_2
     *
     * If everything is correct, find another user
     * should return balance 100 and name Stefan.
     *
     * Previously $user_2 after ->save() overwrites
     * property name to previous one (it will be overwrite $user_1 changes), because
     * ->save() was saving all properties, even if it's not been changed
     *
     */
    private function change_and_check_user_data(): void
    {
        $user = Model_Whitelabel_User::find_one_by('email', $this->email);

        $this->assert_user_has_been_created();

        // check change data by using set
        $user->set([
            'balance' => 200
        ]);
        $user->save();

        $user = Model_Whitelabel_User::find_one_by('email', $this->email);

        $this->assert_user_has_been_created();

        $new_balance = $user->balance;

        $this->assertEquals("200.00", $new_balance);

        // check change data by using __set
        $user->balance = 300;
        $user->save();

        $user = Model_Whitelabel_User::find_one_by('email', $this->email);

        $this->assert_user_has_been_created();

        $new_balance = $user->balance;

        $this->assertEquals("300.00", $new_balance);


        $user_1 = Model_Whitelabel_User::find_one_by('email', $this->email);
        $user_2 = Model_Whitelabel_User::find_one_by('email', $this->email);

        $user_1->set([
            'balance' => 50,
            'name' => 'Stefan'
        ]);

        $user_2->set([
            'balance' => 100
        ]);

        $user_1->save();
        $user_2->save();

        // check if fuel save only changed data
        $this->assertEquals("50.00", $user_1->balance);
        $this->assertEquals("Stefan", $user_1->name);
        $this->assertEquals("100.00", $user_2->balance);
        $this->assertEquals("New User", $user_2->name);

        $user_3 = Model_Whitelabel_User::find_one_by('email', $this->email);

        $this->assertEquals("100.00", $user_3->balance);
        $this->assertEquals("Stefan", $user_3->name);
    }

    private function change_and_check_whitelabel_data(): void
    {
        $whitelabel = Whitelabel::find('first', [
            'where' => [
                'name' => $this->whitelabel_name
            ]
        ]);

        $this->assert_orm_whitelabel_has_been_created_by_name();

        // check change data by using set
        $whitelabel->set([
            'prefix' => 'AA'
        ]);
        $whitelabel->save();

        $whitelabel = Whitelabel::find('first', [
            'where' => [
                'name' => $this->whitelabel_name
            ]
        ]);

        $this->assert_orm_whitelabel_has_been_created_by_name();

        $new_prefix = $whitelabel->prefix;

        $this->assertEquals("AA", $new_prefix);

        // check change data by using __set
        $whitelabel->prefix = "BB";
        $whitelabel->save();

        $whitelabel = Whitelabel::find('first', [
            'where' => [
                'name' => $this->whitelabel_name
            ]
        ]);

        $this->assert_orm_whitelabel_has_been_created_by_name();

        $new_prefix = $whitelabel->prefix;

        $this->assertEquals("BB", $new_prefix);

        Query::caching(false);

        $whitelabel_1 = Whitelabel::find('first', [
            'where' => [
                'name' => $this->whitelabel_name
            ]
        ]);
        $whitelabel_2 = Whitelabel::find('first', [
            'where' => [
                'name' => $this->whitelabel_name
            ]
        ]);

        $whitelabel_1->set([
            'prefix' => 'CC',
            'realname' => 'BBB'
        ]);

        $whitelabel_2->set([
            'prefix' => 'DD'
        ]);

        $whitelabel_1->save();
        $whitelabel_2->save();

        // check if fuel save only changed data
        $this->assertEquals("CC", $whitelabel_1->prefix);
        $this->assertEquals("BBB", $whitelabel_1->realname);
        $this->assertEquals("DD", $whitelabel_2->prefix);
        $this->assertEquals("asd", $whitelabel_2->realname);

        $whitelabel_3 = Whitelabel::find('first', [
            'where' => [
                'name' => $this->whitelabel_name
            ]
        ]);

        $this->assertEquals("DD", $whitelabel_3->prefix);
        $this->assertEquals("BBB", $whitelabel_3->realname);
    }

    private function get_language_data(): array
    {
        return [
            'default_currency_id' => 2,
            'code' => 'te_ST',
            'js_currency_format' => '{c}{n}.{s}'
        ];
    }

    private function create_language_by_forge(): Model_Language
    {
        $language = Model_Language::forge($this->get_language_data());
        $language->save();

        return $language;
    }

    private function assert_language_has_been_created(string $code): void
    {
        $language = Model_Language::find_one_by('code', $code);

        $this->assertIsObject($language);
        $this->assertInstanceOf(Model_Language::class, $language);
    }

    private function get_whitelabel_data(): array
    {
        return [
            'manager_site_currency_id' => 1,
            'language_id' => 1,
            'name' => $this->whitelabel_name,
            'domain' => 'domain.loc',
            'user_activation_type' => 1,
            'aff_activation_type' => 0,
            'max_payout' => 0,
            'username' => $this->email,
            'hash' => 'asdasd',
            'salt' => 'asdasd',
            'email' => $this->email,
            'realname' => 'asd',
            'timezone' => 'Europe/Warsaw',
            'prefix' => 'TS',
            'prepaid' => 10,
            'user_balance_change_limit' => 0,
        ];
    }

    private function prepare_user_data(Model_Whitelabel_User $user): Model_Whitelabel_User
    {
        $date = (new DateTime())->format('Y-m-d');

        $user->set([
            'token' => '12345678',
            'whitelabel_id' => 1,
            'language_id' => 1,
            'currency_id' => 1,
            'is_active' => 1,
            'is_confirmed' => 0,
            'hash' => 'asdasdasd',
            'salt' => 'zxczxczxc',
            'name' => 'New User',
            'surname' => 'Surname',
            'balance' => 0,
            'address_1' => 'Address',
            'address_2' => '',
            'city' => '',
            'country' => '',
            'state' => '',
            'zip' => '',
            'phone_country' => '',
            'phone' => '',
            'timezone' => 'UTC',
            'gender' => 0,
            'national_id' => 0,
            'date_register' => $date,
            'register_ip' => '127.0.0.1',
            'last_ip' => '127.0.0.1',
            'last_active' => $date,
            'last_update' => $date
        ]);

        return $user;
    }
}
