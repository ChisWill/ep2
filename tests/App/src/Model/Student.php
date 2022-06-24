<?php

declare(strict_types=1);

namespace Ep\Tests\App\Model;

use Ep\Db\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\{
    Callback,
    HasLength,
    InRange,
    Number,
    Regex,
    Required,
};
use Yiisoft\Validator\ValidationContext;

/**
 * @property int $id
 * @property int $class_id
 * @property string $name
 * @property string $password
 * @property int $age
 * @property string $birthday
 * @property int $sex
 * @property string $desc
 */
class Student extends ActiveRecord implements IdentityInterface
{
    public const PK = 'id';

    public function tableName(): string
    {
        return '{{%student}}';
    }

    final protected function rules(): array
    {
        return $this->userRules() + [
            'class_id' => [
                new Required(),
                new Number(asInteger: true),
            ],
            'name' => [
                new Required(),
                new HasLength(max: 50),
            ],
            'password' => [
                new Required(),
                new HasLength(max: 100),
            ],
            'age' => [
                new Number(asInteger: true, skipOnEmpty: true),
            ],
            'sex' => [
                new Number(asInteger: true, skipOnEmpty: true),
            ],
        ];
    }

    protected function userRules(): array
    {
        $ageRange = array_keys(array_fill(18, 30, 1));
        return [
            'age' => [
                new Number(asInteger: true, max: 99, tooBigMessage: '最多99岁'),
                new InRange(range: $ageRange, skipOnEmpty: true, message: sprintf('Range is: %s', implode(', ', $ageRange)))
            ],
            'name' => [new HasLength(max: 8, min: 2, tooLongMessage: '用户名最多8个字')],
            'password' => [new HasLength(max: 6, tooLongMessage: '最多6个'), new Regex(pattern: '/^[a-z\d]{4,6}$/i', message: '4-8个字符')],
            'birthday' => [new Callback(callback: [self::class, 'checkDate'])]
        ];
    }

    public static function checkDate($value, ?ValidationContext $context = null): Result
    {
        $result = new Result();
        if (strtotime($value) === false) {
            $result->addError('生日时间格式不正确');
        }
        return $result;
    }

    public function getClass(): ActiveQuery
    {
        return $this->hasOne(Classes::class, ['id' => 'class_id']);
    }

    public function getId(): ?string
    {
        return (string) $this->id ?: null;
    }

    public function getData(): mixed
    {
        return $this->getAttributes();
    }
}
