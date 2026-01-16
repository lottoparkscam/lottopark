<?php

final class Model_Whitelabel_User_Popup_Queue extends Model_Model
{
    /**
     *
     * @var string
     */
    public static $_table_name = 'whitelabel_user_popup_queue';
    
    /**
     * Time interval between getting popup from queue
     */
    const MESSAGE_DELAY = 300;

    /**
     *
     * @param int $whitelabel_id
     * @param int $user_id
     * @param string $title
     * @param string $content
     * @param int $is_promocode
     * @return void
     * @throws Exception
     */
    public static function push_message(int $whitelabel_id, int $user_id, string $title, string $content, int $is_promocode = 0): void
    {
        $model_popup_message = new Model_Whitelabel_User_Popup_Queue();
        $model_popup_message->set(
            [
                'whitelabel_id' => $whitelabel_id,
                'whitelabel_user_id' => $user_id,
                'title' => $title,
                'content' => $content,
                'created_at' => (new DateTime('NOW'))->format('Y-m-d H:i:s'),
                'is_promocode' => $is_promocode
            ]
        );
        $model_popup_message->save();
    }
    
    /**
     *
     * @param int $whitelabel_id
     * @param int $user_id
     * @return array|null
     */
    public static function pop_message(int $whitelabel_id, int $user_id): ?array
    {
        $query = 'SELECT 
            whitelabel_user_popup_queue.* 
        FROM whitelabel_user_popup_queue 
        WHERE whitelabel_id = :whitelabel_id
            AND whitelabel_user_id = :user_id 
        ORDER BY created_at
        LIMIT 1';
        
        $db_query = DB::query($query);
        $db_query->param(':whitelabel_id', $whitelabel_id);
        $db_query->param(':user_id', $user_id);
        $popup_messages = $db_query->execute()->as_array();
        
        if (empty($popup_messages)) {
            return null;
        }
        
        $query = 'DELETE FROM whitelabel_user_popup_queue WHERE id = :id';
        $db_query = DB::query($query);
        $db_query->param(':id', $popup_messages[0]['id']);
        $db_query->execute();
        
        return $popup_messages[0];
    }
}
