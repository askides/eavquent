<?php

namespace App;

use Rennypoz\Eavquent\Traits\Eavable;
use Illuminate\Database\Eloquent\Model;

class EavModel extends Model
{
    use Eavable;

    /**
     * Define a one-to-one relationship with eav.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newHasOneEav($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

    /**
     * Instantiate a new HasOneEav relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Rennypoz\Eavquent\Database\Eloquent\Relations\HasOneEav
     */
    protected function newHasOneEav(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        return new HasOneEav($query, $parent, $foreignKey, $localKey);
    }
}