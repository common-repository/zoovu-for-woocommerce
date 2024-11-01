<?php

namespace Progressus\Zoovu\Helpers\Forms;

use Rakit\Validation\Validator;

class FormGenerator
{
    const ACTION_SLUG = 'form-generator-action';
    const ACTION_CREATE = 'create';
    const ACTION_EDIT = 'edit';

    private $action;

    private $fields = [];

    private $hiddenFields = [self::ACTION_SLUG];

    private $values = [];

    private $validation;

    /**
     * FormGenerator constructor.
     * @param array $data
     */
    public function __construct()
    {

    }

    public function wasSubmitted()
    {
        return isset($_REQUEST[self::ACTION_SLUG]) && $_REQUEST[self::ACTION_SLUG];
    }

    public function getData()
    {
        return \collect($_REQUEST)
            ->only(\collect($this->fields)
            ->pluck('name'))
            ->except($this->hiddenFields)
            ->toArray();
    }

    public function setData($data)
    {
        foreach ($this->fields as $key => $field)
        {
            if (isset($data[$field['name']]) && $data[$field['name']]) {
                $field['value'] = $data[$field['name']];

                $this->fields[$key] = $field;
            }
        }
    }

    public function getAction()
    {
        return $this->action;
    }

    private function setAction($action)
    {
        $this->action = $action;
    }

    public function valid()
    {
        $validator = new Validator;
        $rules = [];

        foreach ($this->fields as $field)
        {
            if (! $field['rules']) {
                continue;
            }

            $rules[$field['name']] = $field['rules'];
        }

        $this->validation = $validator->make($_REQUEST, $rules);

        $this->validation->validate();

        if ($this->validation->fails()) {
            $this->showAdminErrors();
            return false;
        }

        return true;
    }

    public function getErrors()
    {
        return $this->validation ? $this->validation->errors()->all() : [];
    }

    private function showAdminErrors()
    {
        $errors = $this->getErrors();

        add_action('admin_notices', function () use ($errors) {

            foreach ($errors as $error) {
                echo sprintf('
                    <div class="notice notice-warning is-dismissible">
                        <p>%s</p>
                    </div>
                ', $error);
            }
        });
    }

    /**
     * @param $slug
     * @param null $post
     * @return false | array
     */
    private function getFormValues($slug, $post = null)
    {
        return $post ? get_post_meta($post, 'fg_' . $slug, true) : get_option('fg_' . $slug, []);
    }

    private function getValue($name, $default = null)
    {
        return isset($this->values[$name]) ? $this->values[$name] : $default;
    }

    public function addText($name, $label, $help = null, $class = null, $rules = null, $conditions = [])
    {
        $this->fields[] = [
            'name' => $name,
            'label' => $label,
            'help' => $help,
            'class' => $class,
            'type' => 'text',
            'value' => $this->getValue($name),
            'conditions' => $conditions,
            'rules' => $rules
        ];

        return $this;
    }

    /**
     * @param string $name
     * @param string $label
     * @param array $options
     * @param null $help
     * @param null $class
     * @param null $rules
     * @param array $conditions
     * @return $this
     */
    public function addSelect(string $name, string $label, array $options, $help = null, $class = null, $rules = null, $conditions = [])
    {
        $this->fields[] = [
            'name' => $name,
            'label' => $label,
            'help' => $help,
            'class' => $class,
            'options' => $options,
            'type' => 'select',
            'value' => $this->getValue($name),
            'conditions' => $conditions,
            'rules' => $rules
        ];

        return $this;
    }

    public function addCheckbox($name, $label, $help = null, $class = null, $rules = null, $conditions = [])
    {
        $this->fields[] = [
            'name' => $name,
            'label' => $label,
            'help' => $help,
            'class' => $class,
            'type' => 'checkbox',
            'value' => $this->getValue($name, true),
            'conditions' => $conditions,
            'rules' => $rules
        ];

        return $this;
    }

    public function addDateTimePicker($name, $label, $help = null, $class = null, $rules = null, $conditions = [])
    {
        $this->fields[] = [
            'name' => $name,
            'label' => $label,
            'help' => $help,
            'class' => $class,
            'type' => 'datetimepicker',
            'value' => $this->getValue($name),
            'conditions' => $conditions,
            'rules' => $rules
        ];

        return $this;
    }

    public function addTimePicker($name, $label, $help = null, $class = null, $rules = null, $conditions = [])
    {
        $this->fields[] = [
            'name' => $name,
            'label' => $label,
            'help' => $help,
            'class' => $class,
            'type' => 'timepicker',
            'value' => $this->getValue($name),
            'conditions' => $conditions,
            'rules' => $rules
        ];

        return $this;
    }

    public function addAutocomplete($name, $label, array $options, $ajax_route = false, $multiple = false, $help = null, $class = null, $rules = null, $conditions = [])
    {
        $this->fields[] = [
            'name' => $name,
            'label' => $label,
            'help' => $help,
            'class' => $class,
            'type' => 'autocomplete',
            'options' => $options,
            'multiple' => $multiple,
            'value' => $this->getValue($name),
            'conditions' => $conditions,
            'ajax_route' => $ajax_route,
            'rules' => $rules
        ];

        return $this;
    }

    public function addHidden($name, $value, $class = null, $rules = null)
    {
        $this->fields[] = [
            'name' => $name,
            'class' => $class,
            'type' => 'hidden',
            'value' => $this->getValue($name, $value),
            'rules' => $rules
        ];

        return $this;
    }

    public function render()
    {
        include_once 'templates/form.php';
    }
}
