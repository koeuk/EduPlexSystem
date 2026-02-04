<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class UniversalSearchFilter implements Filter
{
    protected array $fields;
    protected bool $useJoin;

    public function __construct(array $fields, bool $useJoin = false)
    {
        $this->fields = $fields;
        $this->useJoin = $useJoin;
    }

    public function __invoke(Builder $query, $value, string $property): void
    {
        $query->where(function (Builder $query) use ($value) {
            foreach ($this->fields as $index => $field) {
                $method = $index === 0 ? 'where' : 'orWhere';

                if (str_contains($field, '.') && !$this->useJoin) {
                    [$relation, $column] = explode('.', $field, 2);
                    $relationMethod = $index === 0 ? 'whereHas' : 'orWhereHas';
                    $query->{$relationMethod}($relation, function (Builder $q) use ($column, $value) {
                        $q->where($column, 'like', "%{$value}%");
                    });
                } else {
                    $query->{$method}($field, 'like', "%{$value}%");
                }
            }
        });
    }
}
