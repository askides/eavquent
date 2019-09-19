<?php

namespace Rennypoz\Eavquent\Database\Eloquent;

class Model extends \Illuminate\Database\Eloquent\Model
{
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

        if (in_array('Rennypoz\Eavquent\Traits\Eavable', class_uses($this))) {
            return $this->newHasOneEav($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
        } 

        return $this->newHasOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
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