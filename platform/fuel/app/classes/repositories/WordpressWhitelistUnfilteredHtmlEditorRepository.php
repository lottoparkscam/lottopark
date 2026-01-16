<?php

namespace Repositories;

use Repositories\Orm\AbstractRepository;
use Models\WordpressWhitelistUnfilteredHtmlEditor;

class WordpressWhitelistUnfilteredHtmlEditorRepository extends AbstractRepository
{
    public function __construct(WordpressWhitelistUnfilteredHtmlEditor $model)
    {
        parent::__construct($model);
    }
}
