<?php
class FormHelper {
    protected $values = array();
    public function __construct($values = array()) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->values = $_POST;
        } else {
            $this->values = $values;
        }
    }

    public function input($type, $attributes = array(), //Метод для формирования разметки
                          $isMultiple = false) {
        $attributes['type'] = $type;
        if (($type == 'radio') || ($type == 'checkbox')) {
            if ($this->isOptionSelected($attributes['name']
                ?? null, $attributes['value'] ?? null)) {
                $attributes['checked'] = true;
            }
        }
        return $this->tag('input', $attributes, $isMultiple);
    }

    public function select($options, $attributes = array()) { //Разметка для дикскрипторов в стиле select
        $multiple = $attributes['multiple'] ?? false;
        return
            $this->start('select', $attributes, $multiple) .
            $this->options($attributes['name'] ?? null, $options) .
            $this->end('select');
    }

    public function textАrea($attributes = array()) { //Метод формированяия текста
        $name = $attributes['name'] ?? null;
        $value = $this->values[$name] ?? '';
        return $this->start('textarea', $attributes) .
            htmlentities($value) .
            $this->end('textarea');
    }

    public function tag($tag, $attributes = array(), //Метод закрывающий разметку </input>
                        $isMultiple = false) {
        return
            "<$tag {$this->attributes($attributes, $isMultiple)} />";
    }

    public function start($tag, $attributes = array(),  //ФОрмирования начального диксриптора формы
                          $isMultiple = false) {
/* Дескрипторы <select> и <textarea> не получают
 атрибуты value*/
        $valueAttribute =
            (! (($tag == 'select')||($tag == 'textarea')));
        $attrs = $this->attributes($attributes, $isMultiple,
            $valueAttribute);
        return "<$tag $attrs>";
    }
    public function end($tag) { //Формирования конечного дискриптора формы
        return "</$tag>";
    }
    protected function attributes($attributes, $isMultiple, $valueAttribute = true) {   //Формиорвание атрибутов
        $tmp = array();

        /* Если данный дескриптор может содержать атрибут value,
         а его имени соответствует элемент в массиве значений,
         то установить этот атрибут*/
        if ($valueAttribute && isset($attributes['name'])
            && array_key_exists($attributes['name'],
                $this->values)) {
            $attributes['value'] =
                $this->values[$attributes['name']];
        }
        foreach ($attributes as $k => $v) {
        /* Истинное логическое значение означает
         логический атрибут*/
            if (is_bool($v)) {
                if ($v) { $tmp[] = $this->encode($k); }
            }
        // иначе k = v
            else {
                $value = $this->encode($v);
        /* Если это многозначный элемент, присоединить
         квадратные скобки ([]) к его имени*/
                if ($isMultiple && ($k == 'name')) {
                    $value .= '[]';
                }
                $tmp[] = "$k=\"$value\"";
            }
        }
        return implode(' ', $tmp);
        }

    protected function options($name, $options) { // Форматирование options для select
        $tmp = array();
        foreach($options as $k => $v){
            $s = "<option value=\"{$this->encode($k)}\"";
            if($this->isOptionSelected($name, $k)){
                $s .=' selected';
            }
            $s .=">{$this->encode($v)}</option>";
            $tmp[] = $s;
        }
        return implode('', $tmp);
    }

    protected function isOptionSelected($name, $value){    //Реакция на нажатие в select
        if(!isset($this->values[$name])){
            return false;
        }elseif (is_array($this->values[$name])){
            return in_array($value, $this->values[$name]);
        }
        else{
            return $value == $this->values[$name];
        }
    }

    public function encode($s){
        return htmlentities($s);
    }
}

