<?php

trait Task_Trait_Cli_Evaluator
{
    /** Evaluate task result and show appropriate info using stdout. */
    private function show_result(Task_Task $task, string $successMessage): void
    {
        if ($task->get_result()->is_successful()) {
            echo $successMessage;
        } else {
            echo $task->get_last_error_message() . "\r\n";
        }
    }
}
