<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Container;
use Model_Whitelabel_Language;
use Models\Language;
use Models\Whitelabel;
use Models\WhitelabelLanguage;
use Repositories\Orm\AbstractRepository;

/**
 * @method findOneById(int $int)
 */
class WhitelabelLanguageRepository extends AbstractRepository
{
    public function __construct(WhitelabelLanguage $model)
    {
        parent::__construct($model);
    }

    public function findLanguageByCode(string $code): Language
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');

        $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('language'),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabel->id),
            new Model_Orm_Criteria_Where('language.code', $code)
        ]);

        /** @var WhitelabelLanguage $whitelabelLanguage */
        $whitelabelLanguage = $this->findOne();

        return $whitelabelLanguage->language;
    }

    public function getAll(Whitelabel $whitelabel = null): array
    {
        /** @var ?Whitelabel $whitelabel */
        $whitelabel = $whitelabel ?: Container::get('whitelabel');
        if (is_null($whitelabel)) {
            return [];
        }

        $languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel->to_array());
        array_walk($languages, function(&$language) {
            $isSerbian = $language['code'] === 'sr_RS';
            $suffix = $isSerbian ? '@latin' : '.utf8';
            $language['full_code'] = $language['code'] . $suffix;
            return $language;
        });

        return $languages;
    }
}
