<?php

namespace ComboStrap;

require_once (__DIR__."/LayoutArea.php");

class Layout
{

    /**
     * @var LayoutArea[]
     */
    private $layoutAreas;

    public function getOrCreateArea($areaName): LayoutArea
    {
        $layoutArea = $this->layoutAreas[$areaName];
        if($layoutArea===null){
            $layoutArea = new LayoutArea($areaName);
            $this->layoutAreas[$areaName]=$layoutArea;
        }
        return $layoutArea;
    }
}
