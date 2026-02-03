<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class UniversalSearchFilter implements Filter
{
    protected array $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function __invoke(Builder $query, $value, string $property): void
    {
        $query->where(function (Builder $query) use ($value) {
            foreach ($this->fields as $index => $field) {
                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field, 2);
                    $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                    $query->{$method}($relation, function (Builder $q) use ($column, $value) {
                        $q->where($column, 'like', "%{$value}%");
                    });
                } else {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($field, 'like', "%{$value}%");
                }
            }
        });
    }
}
