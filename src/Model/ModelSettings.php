<?php

namespace Mascame\Artificer\Model;

// Todo: get column type http://stackoverflow.com/questions/18562684/how-to-get-database-field-type-in-laravel
use Illuminate\Support\Str;
use Mascame\Formality\Parser\Parser;
use Mascame\Artificer\Fields\FieldFactory;

/**
 * @property $name
 * @property $route
 * @property $table
 * @property $class
 * @property $model
 * @property $fillable
 * @property $columns
 * @property $hidden
 * @property $relations
 */
class ModelSettings
{
    use Relationable;

    /**
     * @var ModelSchema
     */
    private $schema;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $model;

    /**
     * @var string
     */
    private $class;

    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $route;

    /**
     * @var
     */
    private $table;

    /**
     * @var
     */
    private $fillable;

    /**
     * @var
     */
    private $title;

    /**
     * @var
     */
    private $values = null;

    /**
     * @var array|mixed
     */
    private $options = [];

    /**
     * @var array|mixed
     */
    private $relations = [];

    /**
     * For commodity (to avoid making a bunch of getters).
     *
     * @var array
     */
    private $visibleProperties = [
        'name',
        'route',
        'table',
        'class',
        'model',
        'fillable',
        'columns',
        'hidden',
        'relations',
        'options',
        'title',
    ];

    /**
     * Model constructor.
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param $options
     */
    public function __construct(\Illuminate\Database\Eloquent\Model $model, $options)
    {
        $this->model = $model;
        $this->name = $options['name'];
        $this->class = $options['class'];
        $this->route = $options['route'];

        $this->schema = new ModelSchema($model, $this->name);
        $this->table = $this->schema->getTable();
        $this->columns = $this->schema->getColumns();

        $this->fillable = $this->model->getFillable();
        $this->hidden = $this->isHidden();
        $this->relations = $this->getRelations();
        $this->title = $this->getTitle();

        $this->addFieldOptions($this->columns);
    }

    /**
     * @param $modelName
     * @return bool
     */
    public function isHidden()
    {
        return in_array($this->name, config('admin.model.hidden'));
    }

    /**
     * @return bool
     */
    public function hasGuarded()
    {
        return ! empty($this->getGuarded());
    }

    /**
     * Look for admin model config, if there is nothing fallback to Model property.
     *
     * @return array|mixed
     */
    public function getGuarded()
    {
        return $this->getOption('guarded', $this->model->getGuarded());
    }

    /**
     * Look for admin model config, if there is nothing fallback to Model property.
     *
     * @return array|mixed
     */
    public function getFillable()
    {
        $fillable = $this->getOption('fillable', $this->model->getFillable());

        if ($fillable == ['*']) {
            $fillable = array_diff($this->columns, $this->getGuarded());
        }

        return $fillable;
    }

    /**
     * @return bool
     */
    public function hasFillable()
    {
        return ! empty($this->getFillable());
    }

    /**
     * @return mixed
     */
    private function getDefaultOptions()
    {
        return config('admin.model.default');
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if ($this->options) {
            return $this->options;
        }

        return $this->options = array_merge(
            $this->getDefaultOptions(),
            config('admin.models.'.$this->name, [])
        );
    }

    /**
     * Take care! This are the options reflected in config files.
     * Processed guarded/fillable should be used with their own getters.
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getOption($key, $default = null)
    {
        return $this->getOptions()[$key] ?? $default;
    }

    /**
     * Fills all fields in config if they are not declared and applies default attributes.
     *
     * @param $columns
     * @param null $model
     */
    private function addFieldOptions($columns)
    {
        foreach ($columns as $column) {
            if (! isset($this->options['fields'][$column])) {
                $this->options['fields'][$column] = [];
            }

            if (! isset($this->options['fields'][$column]['attributes'])) {
                $this->options['fields'][$column]['attributes'] = $this->getDefaultOptions()['attributes'];
            }
        }
    }

    /**
     * @return string
     */
    private function getTitle()
    {
        return $this->getOption('title', Str::title(str_replace('_', ' ', $this->table)));
    }

    /**
     * @param \Eloquent|null $values
     * @return mixed
     */
    public function toForm($values = null)
    {
        $modelFields = $this->getOption('fields');
        $types = config('admin.fields.types');
        $fields = [];

        $values = $values ?? $this->values;

        foreach ($this->columns as $column) {
            $options = [];

            if (isset($modelFields[$column])) {
                $options = $modelFields[$column];
            }

            // Get eloquent value
            if (is_object($values)) {
                $options['value'] = $values->$column;
            } elseif (is_array($values)) {
                $options['value'] = $values[$column] ?? null;
            }

            $fields[$column] = $options;
        }

        $fieldFactory = new FieldFactory(new Parser($types), $types, $fields, config('admin.fields.classmap'));

        return $fieldFactory->makeFields();
    }

    public function withValues($values)
    {
        $this->setValues($values);

        return $this;
    }

    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (! in_array($name, $this->visibleProperties)) {
            throw new \InvalidArgumentException();
        }

        return $this->$name;
    }
}
