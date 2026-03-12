<?php

namespace App\Services;


class ElementArchivageServices{
    public function sortElementsGenerationally($elements, $parent = null) {
        $result = [];
    
        foreach ($elements as $element) {
            if ($element->getParent() == $parent) {
                $children = $this->sortElementsGenerationally($elements, $element);
                $elementData = [
                    'parent' => $element,
                    'children' => $children,
                ];
                $result[] = $elementData;
            }
        }
    
        return $result;
    }
}