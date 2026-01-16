<?php

final class Task_Result implements Task_Interface_Result
{
    /**
     *
     * @var bool
     */
    private $success;
    /**
     *
     * @var int
     */
    private $result_code;

    /**
     * Data result of the task.
     * Empty, unless set explicitly by task concretization.
     *
     * @var array
     */
    private $data = [];

    /**
     * Result code not used.
     */
    public const NOT_USED = -999;

    /**
     * Create new task result.
     *
     * @param boolean $success true if task executed successfully.
     * @param integer $result_code code with which task finished, NOT_USED on default.
     */
    public function __construct(bool $success = true, int $result_code = null)
    {
        $this->success = $success;
        $this->result_code = $result_code ?? self::NOT_USED;
    }

    /**
     * Check if task succeeded.
     * @return bool true if task executed fully.
     */
    public function is_successful(): bool
    {
        return $this->success;
    }

    /**
     * Check if task failed.
     * @return bool true if task failed.
     */
    public function is_failed(): bool
    {
        return !$this->is_successful();
    }

    /**
     * Get the value of result_code
     * Note: it may return @see NOT_USED
     * @return int code with which task finished.
     */
    public function get_result_code(): int
    {
        return $this->result_code;
    }

    /**
     * Mark task as failed.
     *
     * @return  Task_Interface_Result
     */
    public function mark_as_failed(): Task_Interface_Result
    {
        $this->success = false;

        return $this;
    }

    /**
     * Set the value of result_code
     *
     * @param  int  $result_code
     *
     * @return  Task_Interface_Result
     */
    public function set_result_code(int $result_code): Task_Interface_Result
    {
        $this->result_code = $result_code;

        return $this;
    }

    /**
     * Set flag in result code.
     *
     * @param integer $flag to set (appropriate bits)
     *
     * @return Task_Interface_Result
     */
    public function set_flag(int $flag): Task_Interface_Result
    {
        $this->result_code |= $flag;

        return $this;
    }

    /**
     * Check if flag is set in result code.
     *
     * @param integer $flag to check (const)
     * @return bool true if passed flag is set in result code.
     */
    public function is_flag_set(int $flag): bool
    {
        return (bool)($this->result_code & $flag);
    }


    /**
     * Get data result of the task.
     *
     * @return  array
     */
    public function get_data(): array
    {
        return $this->data;
    }

    /**
     * Set data result of the task.
     *
     * @param  array  $data  Data result of the task.
     *
     * @return  Task_Interface_Result
     */
    public function set_data(array $data): Task_Interface_Result
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get item from data.
     *
     * @param mixed $index
     * @return  mixed data item.
     */
    public function get_data_item($index)
    {
        return $this->data[$index];
    }

    /**
     * Put new item into task data.
     * In most cases you should use this method, unless you have so many items, that
     * it's better to set whole array.
     *
     * @param  string $key index of the item
     * @param  mixed $value of the item
     *
     * @return  Task_Interface_Result
     */
    public function put_data_item(string $key, $value): Task_Interface_Result
    {
        $this->data[$key] = $value;

        return $this;
    }
}
