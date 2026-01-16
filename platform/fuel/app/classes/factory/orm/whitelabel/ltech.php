<?php

use Models\WhitelabelLtech;
use Classes\Orm\AbstractOrmModel;


/** @deprecated - use new fixtures instead */
class Factory_Orm_Whitelabel_Ltech extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $data = [
            'id'                => 1,
            'whitelabel_id'     => 999,
            'is_enabled'        => true,
            'locked'            => false,
            'can_be_locked'     => false,
            'key'               => 'some string',
            'name'              => 'some string',
            'secret'            => 'some string'
        ];

        $this->props = array_merge($data, $props);
    }

    /**
     * @return WhitelabelLtech
     * @throws Throwable
     * @deprecated - use new fixtures instead
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        $whitelabel_ltech = new WhitelabelLtech($this->props);

        if ($save) {
            $whitelabel_ltech->save();
        }

        return $whitelabel_ltech;
    }
}
