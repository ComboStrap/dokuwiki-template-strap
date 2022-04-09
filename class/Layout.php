<?php

namespace ComboStrap;

class Layout
{


    /**
     * @throws ExceptionNotFound
     */
    public function load($layoutName){

        $layoutDirectory = DokuPath::createDokuPath(":layout:$layoutName:", DokuPath::COMBO_DRIVE);
        if(!FileSystems::exists($layoutDirectory)){
            throw new ExceptionNotFound("The layout ($layoutName) does not exist at $layoutDirectory");
        }

    }

}
