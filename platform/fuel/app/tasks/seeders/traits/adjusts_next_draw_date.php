<?php

/**
* Adjusts next draw date for initial seeders
*/
trait Adjusts_Next_Draw_Date
{
    /**
     * Uses legacy methods to determine next draw after the seed has been inserted into database
     */
    protected function adjust_next_draw_date_post_execute_callback(): void
    {
        $lottery_data = array_combine($this->columnsStaging()['lottery'], $this->rowsStaging()['lottery'][0]);
        $slug = $lottery_data['slug'];
        $lottery_with_provider = Model_Lottery::for_slug_with_provider($slug);

        $next_draw_date = Lotto_Helper::get_lottery_next_draw($lottery_with_provider, true);

        $lottery = Model_Lottery::find_by_slug($slug);
        $this->update_lottery_draw($lottery[0], $next_draw_date);
    }

    protected function update_lottery_draw(
        Model_Lottery $lottery,
        string $next_draw_date
    ): void {
        $lottery->next_date_local = $next_draw_date;
        $lottery->next_date_utc = Helpers_Time::utc(
            $next_draw_date,
            $lottery->timezone,
        );
        $lottery->save();
    }
}
