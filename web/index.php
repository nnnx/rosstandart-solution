<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

function printRLevel($data, $level = 2)
{
    static $innerLevel = 1;

    static $tabLevel = 1;

    $self = __FUNCTION__;

    $type       = gettype($data);
    $tabs       = str_repeat('    ', $tabLevel);
    $quoteTabes = str_repeat('    ', $tabLevel - 1);
    $output     = '';
    $elements   = array();

    $recursiveType = array('object', 'array');

    // Recursive
    if (in_array($type, $recursiveType))
    {
        // If type is object, try to get properties by Reflection.
        if ($type == 'object')
        {
            $output     = get_class($data) . ' ' . ucfirst($type);
            $ref        = new \ReflectionObject($data);
            $properties = $ref->getProperties();

            foreach ($properties as $property)
            {
                $property->setAccessible(true);

                $pType = $property->getName();

                if ($property->isProtected())
                {
                    $pType .= ":protected";
                }
                elseif ($property->isPrivate())
                {
                    $pType .= ":" . $property->class . ":private";
                }

                if ($property->isStatic())
                {
                    $pType .= ":static";
                }

                $elements[$pType] = $property->getValue($data);
            }
        }
        // If type is array, just retun it's value.
        elseif ($type == 'array')
        {
            $output   = ucfirst($type);
            $elements = $data;
        }

        // Start dumping data
        if ($level == 0 || $innerLevel < $level)
        {
            // Start recursive print
            $output .= "\n{$quoteTabes}(";

            foreach ($elements as $key => $element)
            {
                $output .= "\n{$tabs}[{$key}] => ";

                // Increment level
                $tabLevel = $tabLevel + 2;
                $innerLevel++;

                $output  .= in_array(gettype($element), $recursiveType) ? $self($element, $level) : $element;

                // Decrement level
                $tabLevel = $tabLevel - 2;
                $innerLevel--;
            }

            $output .= "\n{$quoteTabes})\n";
        }
        else
        {
            $output .= "\n{$quoteTabes}*MAX LEVEL*\n";
        }
    }
    else
    {
        $output = $data;
    }

    return $output;
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
