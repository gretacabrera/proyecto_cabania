<?php

namespace App\Core;

/**
 * Clase para validación de datos
 */
class Validator 
{
    private $errors = [];
    private $data = [];
    
    public function __construct($data = [])
    {
        $this->data = $data;
    }
    
    /**
     * Validar campos requeridos
     */
    public function required($field, $message = null)
    {
        $value = $this->data[$field] ?? '';
        if (empty(trim($value))) {
            $this->errors[$field] = $message ?: "El campo {$field} es requerido";
        }
        return $this;
    }
    
    /**
     * Validar longitud mínima
     */
    public function minLength($field, $min, $message = null)
    {
        $value = $this->data[$field] ?? '';
        if (strlen(trim($value)) < $min && !empty($value)) {
            $this->errors[$field] = $message ?: "El campo {$field} debe tener al menos {$min} caracteres";
        }
        return $this;
    }
    
    /**
     * Validar longitud máxima
     */
    public function maxLength($field, $max, $message = null)
    {
        $value = $this->data[$field] ?? '';
        if (strlen(trim($value)) > $max) {
            $this->errors[$field] = $message ?: "El campo {$field} no puede tener más de {$max} caracteres";
        }
        return $this;
    }
    
    /**
     * Validar formato de email
     */
    public function email($field, $message = null)
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?: "El campo {$field} debe ser un email válido";
        }
        return $this;
    }
    
    /**
     * Validar formato numérico
     */
    public function numeric($field, $message = null)
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = $message ?: "El campo {$field} debe ser numérico";
        }
        return $this;
    }
    
    /**
     * Validar fecha válida
     */
    public function date($field, $format = 'Y-m-d', $message = null)
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value)) {
            $date = \DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->errors[$field] = $message ?: "El campo {$field} debe ser una fecha válida";
            }
        }
        return $this;
    }
    
    /**
     * Validar que un valor esté en una lista
     */
    public function in($field, $values, $message = null)
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !in_array($value, $values)) {
            $this->errors[$field] = $message ?: "El campo {$field} debe ser uno de los valores válidos";
        }
        return $this;
    }
    
    /**
     * Validar que coincidan dos campos (ej: password confirmation)
     */
    public function matches($field1, $field2, $message = null)
    {
        $value1 = $this->data[$field1] ?? '';
        $value2 = $this->data[$field2] ?? '';
        if ($value1 !== $value2) {
            $this->errors[$field1] = $message ?: "Los campos no coinciden";
        }
        return $this;
    }
    
    /**
     * Validar precio/decimal positivo
     */
    public function positiveDecimal($field, $message = null)
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && (!is_numeric($value) || floatval($value) < 0)) {
            $this->errors[$field] = $message ?: "El campo {$field} debe ser un número positivo";
        }
        return $this;
    }
    
    /**
     * Validar entero positivo
     */
    public function positiveInteger($field, $message = null)
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && (!filter_var($value, FILTER_VALIDATE_INT) || intval($value) < 0)) {
            $this->errors[$field] = $message ?: "El campo {$field} debe ser un número entero positivo";
        }
        return $this;
    }
    
    /**
     * Validación personalizada con callback
     */
    public function custom($field, $callback, $message = null)
    {
        $value = $this->data[$field] ?? '';
        if (!$callback($value)) {
            $this->errors[$field] = $message ?: "El campo {$field} no cumple con la validación requerida";
        }
        return $this;
    }
    
    /**
     * Verificar si hay errores
     */
    public function fails()
    {
        return !empty($this->errors);
    }
    
    /**
     * Verificar si la validación es exitosa
     */
    public function passes()
    {
        return empty($this->errors);
    }
    
    /**
     * Obtener todos los errores
     */
    public function errors()
    {
        return $this->errors;
    }
    
    /**
     * Obtener errores de un campo específico
     */
    public function error($field)
    {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Obtener el primer error
     */
    public function firstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    /**
     * Limpiar datos eliminando espacios y caracteres especiales
     */
    public function sanitize($field)
    {
        if (isset($this->data[$field])) {
            $this->data[$field] = trim(strip_tags($this->data[$field]));
        }
        return $this;
    }
    
    /**
     * Obtener los datos sanitizados
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Crear instancia estática para validaciones rápidas
     */
    public static function make($data)
    {
        return new self($data);
    }
}