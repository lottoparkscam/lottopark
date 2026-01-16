<?php

use Fuel\Tasks\Factory\Utils\Faker;

final class Tests_Feature_Model_Raffle_Orm_Draw extends Test_Feature
{
    public function setUp(): void
    {
        parent::setUp();
        $this->markTestIncomplete();
    }

    public function test_creates_draw_by_providing_fk_ids()
    {
        $model = new RaffleDraw($this->get_defaults());

        $this->assert_relations($model);

        $model->save();
    }

    /**
     * Called when we provide new (not saved yet) instance.
     * New relation should be stored at once.
     * All FK should change.
     *
     * @throws ErrorException
     * @throws Throwable
     */
    public function test_creates_draw_by_providing_objects_to_new_model_instance()
    {
        $raffle = Factory_Orm_Raffle::forge()->randomize()->build(false);
        $this->assertNotEmpty($raffle);

        $model = new RaffleDraw($this->get_defaults());

        $model->raffle = $raffle;
        $model->save();

        $this->assert_relations($model);

        $this->assertSame($model->raffle_id, $raffle->id);
        $this->assertNotSame($model->raffle_id, 1);
    }

    /**
     * Called when we provide existing relation instance.
     * New relation should be associated with existing item.
     * All FK should change.
     *
     * @throws ErrorException
     * @throws Throwable
     */
    public function test_creates_draw_by_providing_existing_object()
    {
        $raffle = Factory_Orm_Raffle::forge()->randomize()->build();
        $this->assertNotEmpty($raffle);
        $this->assertNotEmpty($raffle->id);

        $model = new RaffleDraw($this->get_defaults());

        $model->raffle = $raffle;
        $model->save();

        $this->assert_relations($model);

        $this->assertSame($model->raffle_id, $raffle->id);
        $this->assertNotSame($model->raffle_id, 1);
    }

    private function get_defaults(): array
    {
        return [
            'raffle_id' => 1,
            'raffle_rule_id' => 1,
            'currency_id' => 1,
            'draw_no' => Faker::forge()->randomNumber(2),
            'date' => Faker::forge()->dateTimeBetween('-7 days')->format('Y-m-d H:i:s'),
            'numbers' => json_encode([Faker::forge()->numberBetween(1, 5), Faker::forge()->numberBetween(5, 20)]),
            'is_calculated' => Faker::forge()->boolean(20),
            'sale_sum' => Faker::forge()->randomFloat(2, 10, 1000),
            'prize_total' => Faker::forge()->randomFloat(2, 1000, 5000),
            'lines_won_count' => Faker::forge()->randomNumber(2),
            'tickets_count' => Faker::forge()->randomNumber(2),
        ];
    }

    private function assert_relations(RaffleDraw $model): void
    {
        $this->assertNotEmpty($model->raffle);
        $this->assertNotEmpty($model->raffle_id);
        $this->assertNotEmpty($model->rule);
        $this->assertNotEmpty($model->raffle_rule_id);
        $this->assertNotEmpty($model->currency);
        $this->assertNotEmpty($model->currency_id);
    }
}
