<?php
require 'FormHelper.php';
$states = [ 'AL', 'АК', 'AZ', 'AR', 'СА', 'СО', 'СТ', 'DC',
    'DE', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IА',
    'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN',
    'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM',
    'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI',
    'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA',
    'WV', 'WI', 'WY' ];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    list($errors, $input) = validateForm();
    if ($errors) {
        showForm($errors);
    } else {
        processForm($input);
    }
} else {
    showForm();
}
function showForm($errors = array()) {
    $form = new FormHelper();
    include 'shipping-form.php';
}
function validateForm() {
    $input = array();
    $errors = array();
    foreach (['from','to'] as $addr) {
// проверить обязательные поля
        foreach(['Name' => 'name', 'Address 1' => 'address1', 'City' => 'city', 'State' => 'state']
                as $label => $field) {
            $input[$addr.'_'.$field] = $_POST[$addr.'_'.$field] ?? '';
            if (strlen($input[$addr.'_'.$field]) == 0) {
                $errors[] = "Please enter a value for $addr $label.";
            }
        }
// проверить штат
        $input[$addr.'_state'] =$GLOBALS['states'][$input[$addr.'_state']] ?? '';
        if (! in_array($input[$addr.'_state'], $GLOBALS['states'])) {
            $errors[] = "Please select a valid $addr state.";
        }
// проверить почтовый индекс
        $input[$addr.'_zip'] = filter_input(INPUT_POST, $addr.'_zip',
            FILTER_VALIDATE_INT, ['options' =>
                ['min_range'=>10000,
                    'max_range'=>99999]]);
        if (is_null($input[$addr.'_zip']) ||
            ($input[$addr.'_zip']===false)) {
            $errors[] = "Please enter a valid $addr ZIP";
        }
// He забыть о втором адресе address2!
        $input[$addr.'_address2'] = $_POST[$addr.'_address2'] ?? '';
    }
/* высота, ширина, глубина, вес посылки должны быть
 выражены числовыми положительными значениями*/
    foreach(['height','width','depth','weight'] as $field) {
        $input[$field] =filter_input(INPUT_POST, $field,
            FILTER_VALIDATE_FLOAT);
/* Нулевое значение является недействительным, поэтому
 проверить следующее условие только на истинность вместо
 того, чтобы проверять на пустое или ложное значение*/
        if (! ($input[$field] && ($input[$field] > 0))) {
            $errors[] = "Please enter a valid $field.";
        }
    }
// проверить вес посылки
    if ($input['weight'] > 150) {
        $errors[] = "The package must weigh no more than 150 lbs.";
    }
// проверить размеры посылки
    foreach(['height', 'width','depth'] as $dim) {
        if ($input[$dim] > 36) {
            $errors[] = "The package $dim must be no more
            than 36 inches.";
        }
    }
return array($errors, $input);
}
function processForm($input) {
// создать шаблон для отчета
    $tpl=<<<HTML
    <p>Your package is {height}" x {width}" x {depth}" and weights {weight}</p>
    <p>IT is coming from:</p>
    <pre>
    {from_name}
    {from_address}
    {from_city}, {from_state}, {from_zip} 
    </pre>
    <p>It is going to</p>
    <pre>
    {to_name}
    {to_address}
    {to_city}, {to_state}, {to_zip}
    </pre>
    HTML;
    foreach(['from','to'] as $addr){
        $input[$addr.'_address']=$input[$addr.'_address1'];
        if(strlen($input[$addr.'_address2'])){
            $input[$addr.'_address'] .= "\n"
                . $input[$addr.'_address2'];
        }
    }
    $html = $tpl;
    foreach ($input as $k => $v) {
        $html=str_replace('{'.$k.'}', $v, $html);
    }
    print $html;
}
?>