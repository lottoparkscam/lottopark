<?php

/**
 * Parent of database migration executed gracefully.
 * NOTE: not using before and after methods with premeditation, since after will not trigger on failure
 * meaning we could not trigger down/up appropriately.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-04
 * Time: 12:53:01
 */
abstract class Database_Migration_Graceful extends Database_Migration
{
 // TODO: {Vordis 2019-07-04 18:40:58} we could chain exception from fallback as previous (via reflection)

    /**
     * Run migration.
     *
     * @return void
     */
    public function up(): void
    {
        try {
            $this->up_gracefully();
        } catch (\Throwable $throwable) {
            try {
                $this->down_gracefully(); // direct down
            } catch (\Throwable $e) {
            } // ignore exception, it would block main one
            throw $throwable;
        }
    }

    /**
     * Logic for up migration.
     *
     * @return void
     */
    abstract protected function up_gracefully(): void;

    /**
     * Rollback migration.
     *
     * @return void
     */
    public function down(): void
    {
        try {
            $this->down_gracefully();
        } catch (\Throwable $throwable) {
            try {
                $this->up_gracefully(); // direct down
            } catch (\Throwable $e) {
            } // ignore exception, it would block main one
            throw $throwable;
        }
    }

    /**
     * Logic for down migration.
     *
     * @return void
     */
    abstract protected function down_gracefully(): void;
}
