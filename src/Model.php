<?php

namespace ItsRennyMan\Eavquent;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    // Override Builder
    public function newEloquentBuilder($builder)
    {
        return new Builder($builder);
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if ($method === 'find') {
            return (new static)->findEav(...$parameters);
        }
        return (new static)->$method(...$parameters);
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->mergeAttributesFromClassCasts();

        $query = $this->newModelQuery();

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        if ($this->exists) {
            $saved = $this->isDirty() ?
                $this->performUpdate($query) : true;
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
            $saved = $this->performEavInsert($query);

            if (
                !$this->getConnectionName() &&
                $connection = $query->getConnection()
            ) {
                $this->setConnection($connection->getName());
            }
        }

        // If the model is successfully saved, we need to do a few more things once
        // that is done. We will call the "saved" method here to run any actions
        // we need to happen after a model gets successfully saved right here.
        if ($saved) {
            $this->finishSave($options);
        }

        return $saved;
    }

    /**
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performEavInsert(Builder $query)
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // If the model has an incrementing key, we can use the "insertGetId" method on
        // the query builder, which will give us back the final inserted ID for this
        // table from the database. Not all tables have to be incrementing though.
        $attributes = $this->getAttributes();

        if ($this->getIncrementing()) {
            $this->insertEavAndSetId($query, $attributes);
        }

        // If the table isn't incrementing we'll simply insert these attributes as they
        // are. These attribute arrays must contain an "id" column previously placed
        // there by the developer as the manually determined key for these models.
        else {
            throw new \Exception('EAV Models without incrementing not implemented yet.'); // XXX

            if (empty($attributes)) {
                return true;
            }

            $query->insert($attributes);
        }

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    /**
     * Insert the given attributes and set the ID on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $attributes
     * @return void
     */
    protected function insertEavAndSetId(Builder $query, $attributes)
    {
        $nextEntityId = $this->max('entity_id') + 1;

        foreach ($attributes as $attribute => $value) {
            $query->insertGetId($this->makeEavRow($nextEntityId, $attribute, $value), $keyName = $this->getKeyName());
        }

        $this->setAttribute($keyName, $nextEntityId);
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function findEav($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns); // XXX
        }

        $eavRows = $this->whereEntityId($id)->get();

        return $this->toModel($eavRows);

        return $this->whereKey($id)->first($columns);
    }

    public function toModel($eavRows)
    {
        // Set id
        $this->setAttribute('id', $eavRows->first()->entity_id);

        foreach ($eavRows->toArray() as $eavRow) {
            $this->setAttribute($eavRow['entity_attribute'], $eavRow['entity_value']);
        }

        return $this;
    }

    /**
     * Da commentare XXX
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $attributes
     * @return void
     */
    public function makeEavRow($id, $attribute, $value)
    {
        return [
            'entity_id' => $id,
            'entity_attribute' => $attribute,
            'entity_value' => $value
        ];
    }
}
