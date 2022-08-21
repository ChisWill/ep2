<?php

declare(strict_types=1);

namespace Ep\Tests\App\Model;

use Ep\Db\ActiveRecord;
use Yiisoft\Validator\Rule\{
    HasLength,
    Number,
    Required,
};

/**
 * @property int $id
 * @property int $school_id
 * @property string $name
 */
class Classes extends ActiveRecord
{
    public const PK = 'id';

    public static function tableName(): string
    {
        return '{{%class}}';
    }

    final protected function rules(): array
    {
        return $this->userRules() + [];
    }

    protected function userRules(): array
    {
        return [];
    }

    public function getData(): mixed
    {
        return $this->getAttributes();
    }
}
