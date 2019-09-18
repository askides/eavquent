<?php

namespace Rennypoz\Eavquent\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

trait Eavquent
{
    public static function __callStatic($method, $parameters)
    {
        if ($method === 'find') {
            return (new static )->findInEav(...$parameters);
        }

        return (new static )->$method(...$parameters);
    }

    public function save(array $options = [])
    {
        $query = $this->newModelQuery();

        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        if ($this->exists) {
            $saved = $this->isDirty() ?
            $this->performEavUpdate($query) : true;
        } else {
            $saved = $this->performEavInsert($query);

            if (!$this->getConnectionName() && $connection = $query->getConnection()) {
                $this->setConnection($connection->getName());
            }
        }

        if ($saved) {
            $this->finishSave($options);
        }

        return $saved;
    }

    public function isDirty($attributes = null)
    {
        return $this->hasChanges(
            $this->getDirty(), is_array($attributes) ? $attributes : func_get_args()
        );
    }

    public function getDirty()
    {
        $dirty = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (!$this->originalIsEquivalent($key, $value)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    protected function hasChanges($changes, $attributes = null)
    {
        if (empty($attributes)) {
            return count($changes) > 0;
        }

        foreach (Arr::wrap($attributes) as $attribute) {
            if (array_key_exists($attribute, $changes)) {
                return true;
            }
        }

        return false;
    }

    protected function performEavInsert(Builder $query)
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        $this->setEntityId();

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $attributes = $this->getAttributes();

        if ($this->getIncrementing()) {
            $this->insertAndSetId($query, $attributes);
        } else {
            if (empty($attributes)) {
                return true;
            }
            $query->insert($attributes);
        }

        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    protected function performEavUpdate(Builder $query)
    {
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            $freshQuery = clone $query;

            foreach ($dirty as $column => $value) {
                $query = clone $freshQuery;

                $eavAttributes = $this->columnize($this->getKeyForSaveQuery(), $column, $value);

                $this->setKeysForSaveQuery($query->whereEntityAttribute($column))->update($eavAttributes);
            }

            $this->syncChanges();
            $this->fireModelEvent('updated', false);
        }

        return true;
    }

    protected function setEntityId()
    {
        $lastEntityId = $this->newModelQuery()->max('entity_id');

        $this->entityId = $lastEntityId + 1;

        return $this;
    }

    protected function insertAndSetId(Builder $query, $attributes)
    {
        foreach ($attributes as $column => $value) {

            if ($column === 'entityId') {
                continue;
            }

            $eavAttributes = $this->columnize($this->entityId, $column, $value);

            // Use legacy functions
            $id = $query->insertGetId($eavAttributes, $keyName = $this->getKeyName());

        }

        $this->setAttribute($keyName, $this->entityId);

        unset($this['entityId']);
    }

    protected function columnize($id, $attribute, $value)
    {
        return [
            'entity_id' => $id,
            'entity_attribute' => $attribute,
            'entity_value' => $value,
        ];
    }

    protected function setKeysForSaveQuery(Builder $query)
    {
        $query->where('entity_id', '=', $this->getKeyForSaveQuery());

        return $query;
    }

    public function findInEav($id, $columns = ['*'])
    {
        $rows = $this->whereEntityId($id)->get();

        $attributes = [];

        foreach ($rows as $key => $row) {
            $attributes[$row->entity_attribute] = $row->entity_value;
        }

        // Set manually entity id
        $attributes['id'] = $rows->first()->entity_id;

        return empty($attributes) ? null : $rows->first()->setRawAttributes($attributes, true);
    }

    // public static function all($columns = ['*'])
    // {
    //     //dump('lol');exit();
    //     return static::query()->get(
    //         is_array($columns) ? $columns : func_get_args()
    //     );
    // }
}
