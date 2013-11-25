<?php

class ReusableHtmlHelpers implements TemplateGlobalProvider
{
    public static function get_template_global_variables()
    {
        return array(
            'Multiply',
            'IfClass',
            'IsA',
            'SourceToOptions'
        );
    }

    public static function SourceToOptions($source, $fieldValue)
    {
        $options = array();

        if ($source) {
            foreach ($source as $value => $title) {

                $checked = (is_array($fieldValue) && in_array($value, $fieldValue)) ? true : $value == $fieldValue;

                $options[] = new ArrayData(
                    array(
                        'value' => $value,
                        'title' => $title,
                        'checked' => $checked
                    )
                );

            }
        }

        return new ArrayList($options);
    }
    
    public static function Multiply($left, $right)
    {
        return $left * $right;
    }

    public static function IfClass($prefix, $suffix)
    {
        return $prefix && $suffix ? (string) $prefix . '-' . $suffix : '';
    }

    public static function IsA($class1, $class2)
    {
        return is_a($class1, $class2, true);
    }

    public static function required(&$res)
    {
        if ($res['ArgumentCount'] === 0) {
            throw new Exception('<% required %> must have at least 1 argument');
        }
        
        $php = 'ReusableHtmlHelpers::checkRequired(array(';
        
        foreach ($res['Arguments'] as $arg) {
            $php .= "'{$arg['text']}' => ".str_replace('$$FINAL', 'hasValue', ($arg['ArgumentMode'] == 'default') ? $arg['lookup_php'] : $arg['php']) . ',';
        }
        
        $php = substr($php, 0, -1) . '));';
        
        return $php . (isset($res['Template']) ? $res['Template']['php'] : '');
    }

    public static function dataattrs(&$res)
    {
        return '$val .= ReusableHtmlHelpers::createDataAttributes($scope);';
    }
    
    public static function createDataAttributes($scope)
    {
        $content = '';
        $overlay =  (new ReflectionObject($scope))->getProperty('overlay');
        $overlay->setAccessible(true);
        foreach ($overlay->getValue($scope) as $name => $value) {
            if (substr($name, 0, 5) == "data-") {
                $content .= $name . '="' . $scope->XML_val($name) . '" ';
            }
        }
        return rtrim($content);
    }
    
    public static function checkRequired($check)
    {
        foreach ($check as $name => $value) {
            if ($value !== true) {
                throw new InvalidArgumentException(
                    sprintf(
                        "The field '%s' is required",
                        $name
                    )
                );
            }
        }
    }
}