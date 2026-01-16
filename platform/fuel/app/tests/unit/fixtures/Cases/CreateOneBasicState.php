<?php

namespace Unit\Fixtures\Cases;

interface CreateOneBasicState
{
    /**
     * Given fixture<ConcreteModel>
     * When createOne is called with BASIC state as argument
     * Then fixture should create model and persist in DB
     * @return mixed|void
     */
    public function createOne_Basic_SavesInDb();
}
